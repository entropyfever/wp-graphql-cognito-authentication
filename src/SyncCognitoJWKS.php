<?php

namespace WPGraphQL\Cognito_Authentication;

function sync(){

    // Get options related to plugin and cognito jwks
    $aws_cognito_graphql_options = get_option( 'aws_cognito_graphql_option_name' ); // Array of All Options
    $aws_cognito_region = $aws_cognito_graphql_options['aws_cognito_region']; // Region
    $aws_cognito_poolid = $aws_cognito_graphql_options['aws_cognito_poolid']; // Pool Id

    // create the jwks url
    $url = "https://cognito-idp." . $aws_cognito_region . ".amazonaws.com/" . $aws_cognito_poolid . "/.well-known/jwks.json";

    // fetch the json
    $cognito_response = wp_remote_get($url);
    $body = wp_remote_retrieve_body($cognito_response);
    $jwks = json_decode(json_encode($body));

    $updated = update_option('cognito_jwks', $jwks);

    $response = array();
    $response['response'] = $jwks;

    header( "Content-Type: application/json" );
    echo json_encode($response);

    //Don't forget to always exit in the ajax function.
    exit();
}

add_action('wp_ajax_sync_cognito_jwks', '\\WPGraphQL\\Cognito_Authentication\\sync');
add_action('wp_ajax_nopriv_sync_cognito_jwks', '\\WPGraphQL\\Cognito_Authentication\\sync');


?>
