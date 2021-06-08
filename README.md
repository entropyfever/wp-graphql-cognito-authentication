This is an experimental (also my first) wordpress plugin.

The purpose is to authenticate a ```/graphql``` request with an Amazon Cognito token.

Once installed you will see a new tab ```AWS Cognito GraphQL``` in the dashboard panel.

Fill in the ```Region``` (ex. eu-central-1) and ```Pool Id``` (ex. eu-central-1_SiAn51kgC) fields and click Save changes.

Once saved, click the ```Sync JWKS``` button. What this does, is to fetch the cognito JWKS from the user pool (```$url = "https://cognito-idp." . $aws_cognito_region . ".amazonaws.com/" . $aws_cognito_poolid . "/.well-known/jwks.json")```

SEE MORE HERE: https://docs.aws.amazon.com/cognito/latest/developerguide/amazon-cognito-user-pools-using-tokens-verifying-a-jwt.html

These keys are used to decode and verify the cognito token claims.

When the authentication succeeds, the plugin tries to match the wordpress' user_id with the cognito sub from the user meta.

```
public static function graphql_auth_determine_current_user($user) {

        // Reset any previous value
        $user = null;

        ...

        $payload = self::validate_idToken_against_jwks($token);

        ...


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
```

and sets the current wordpress user by this (if any) id.

#### Related plugins installed

- WPGraphQL
- Headless CMS


#### !!! USE with caution. 
This is my first (and I hope the last one) wordpress plugin. It is NOT meant for production environment yet.

### Side note

The authentication is stateless, meaning at each request there is no wordpress-cognito communication.
If a user's refresh token is revoked, the ID Token is still valid (until it expires).
As of now, AWS Cognito's minimum expiration time for ID Token is 5min. This means, that even if a user's refresh token is revoked,
he/she will still have access for a maximum of minutes, until the ID Token gets expired.
Also, this plugin does not check for ID/Refresh token. Both work so feel free to make it more strict.
