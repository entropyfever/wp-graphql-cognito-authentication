<?php

namespace WPGraphQL\Cognito_Authentication;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class Auth {

    public static function graphql_auth_determine_current_user($user) {

        // Reset any previous value
        $user = null;

        // Check for token in headers
        $auth_header = self::get_auth_header();

        /**
        * If there is not Auth header, treat the request as a public one
        */
        if ( empty( $auth_header ) ) {
            return null;
        }

        /**
        * If there is an Auth header, verify the token
        */
        list( $token ) = sscanf( $auth_header, 'Bearer %s' );

        $payload = self::validate_idToken_against_jwks($token);

        /**
         * If no token was generated, return the existing value for the $user
         */
        if ( empty( $payload ) || is_wp_error( $payload ) ) {

            // set 403 status code to notify client that the request was handled as unauthenticated
            add_action(
                'graphql_response_status_code',
                function ($http_status_code){
                    return 403;
                },
                10,
                1
            );
            return null;
        }


        $userIds = get_users( array(
                    "meta_key" => "cognito_sub",
                    "meta_value" => $payload["sub"],
                    "fields" => "ID"
                ) );

        // TODO: better handling
        $total_users = count($userIds);
        if ($total_users !== 1){
            return null;
        }

        return absint( $userIds[0] );
    }

    public static function get_json_web_key_sets() {
        $jwks = get_option('cognito_jwks');
        $asArray = json_decode( $jwks, true );
        return $asArray;
    }

    /**
     * Get the value of the Authorization header from the $_SERVER super global
     *
     * @return mixed|string
     */
    public static function get_auth_header() {

        /**
         * Looking for the HTTP_AUTHORIZATION header, if not present just
         * return the user.
         */
        $auth_header = isset( $_SERVER['HTTP_AUTHORIZATION'] ) ? $_SERVER['HTTP_AUTHORIZATION'] : false;

        /**
         * Double check for different auth header string (server dependent)
         */
        $redirect_auth_header = isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;

        /**
         * If the $auth header is set, use it. Otherwise attempt to use the $redirect_auth header
         */
        $auth_header = $auth_header !== false ? $auth_header : ( $redirect_auth_header !== false ? $redirect_auth_header : null );

        return $auth_header;

    }

    public static function validate_idToken_against_jwks($idToken) {

        // TODO: validate the token is "id" and not "access"
        // TODO (OPTIONAL): validate the audience
        // (see more: https://docs.amazonaws.cn/en_us/cognito/latest/developerguide/amazon-cognito-user-pools-using-tokens-verifying-a-jwt.html)

        try {
            $idTokenHeader = json_decode(base64_decode(explode('.', $idToken)[0]), true);
            $keySet = self::get_json_web_key_sets();
            $keySets = JWK::parseKeySet($keySet);
            $remoteKey = $keySets[$idTokenHeader['kid']];
            $decoded = JWT::decode($idToken, $remoteKey, array($idTokenHeader['alg']));
            $payload = json_decode(json_encode($decoded), true);
        } catch(ExpiredException $e) {
            return new \WP_Error( 'ExpiredException', __( 'Id token is expired', 'cognito' ) );
        } catch(SignatureInvalidException $e) {
            return new \WP_Error( 'SignatureInvalidException', __( 'The id token signature is invalid', 'cognito' ) );
        } catch(BeforeValidException $e) {
            return new \WP_Error( 'BeforeValidException', __( 'Invalid token', 'cognito' ) );
        } catch(\Exception $e) {
            return new \WP_Error( 'InvalidConfigurationException', __( 'Something went wrong', 'wp-graphql-cognito-authentication' ) );
        }

        return $payload;
    }


}


