<?php
/**
 * Plugin Name: WPGraphQL Cognito Authentication
 * Description: Cognito Authentication for WPGraphQL
 * Author: Christos Miamis
 * Text Domain: wp-graphql-cognito-authentication
 * Domain Path: /languages
 * Version: 0.1.0
 */

namespace WPGraphQL\Cognito_Authentication;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( '\WPGraphQL\Cognito_Authentication' ) ) :

	/**
	 * Class - Cognito_Authentication
	 */
	final class Cognito_Authentication {
		/**
		 * Stores the instance of the Cognito_Authentication class
		 *
		 * @var Cognito_Authentication The one true Cognito_Authentication
		 * @since  0.0.1
		 * @access private
		 */
		private static $instance;

		/**
		 * The instance of the Cognito_Authentication object
		 *
		 * @return object|Cognito_Authentication - The one true Cognito_Authentication
		 * @since  0.0.1
		 * @access public
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Cognito_Authentication ) ) {

				self::$instance = new Cognito_Authentication;
				self::$instance->setup_constants();
				self::$instance->includes();
			}

			self::$instance->init();

			return self::$instance;
		}

		/**
		 * Throw error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single object
		 * therefore, we don't want the object to be cloned.
		 *
		 * @since  0.0.1
		 * @access public
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'The Init_Cognito_Authentication class should not be cloned.', 'wp-graphql-jwt-authentication' ), '0.0.1' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since  0.0.1
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// De-serializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the WPGraphQL class is not allowed', 'wp-graphql-jwt-authentication' ), '0.0.1' );
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since  0.0.1
		 * @return void
		 */
		private function setup_constants() {
			// Plugin version.
			if ( ! defined( 'WPGRAPHQL_COGNITO_AUTHENTICATION_VERSION' ) ) {
				define( 'WPGRAPHQL_COGNITO_AUTHENTICATION_VERSION', '0.1.0' );
			}

			// Plugin Folder Path.
			if ( ! defined( 'WPGRAPHQL_COGNITO_AUTHENTICATION_PLUGIN_DIR' ) ) {
				define( 'WPGRAPHQL_COGNITO_AUTHENTICATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'WPGRAPHQL_COGNITO_AUTHENTICATION_PLUGIN_URL' ) ) {
				define( 'WPGRAPHQL_COGNITO_AUTHENTICATION_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'WPGRAPHQL_COGNITO_AUTHENTICATION_PLUGIN_FILE' ) ) {
				define( 'WPGRAPHQL_COGNITO_AUTHENTICATION_PLUGIN_FILE', __FILE__ );
			}

			// Whether to autoload the files or not.
			if ( ! defined( 'WPGRAPHQL_COGNITO_AUTHENTICATION_AUTOLOAD' ) ) {
				define( 'WPGRAPHQL_COGNITO_AUTHENTICATION_AUTOLOAD', true );
			}
		}

		/**
		 * Include required files.
		 * Uses composer's autoload
		 *
		 * @access private
		 * @since  0.0.1
		 * @return void
		 */
		private function includes() {
			// Autoload Required Classes.
			if ( defined( 'WPGRAPHQL_COGNITO_AUTHENTICATION_AUTOLOAD' ) && true === WPGRAPHQL_COGNITO_AUTHENTICATION_AUTOLOAD ) {
				require_once( WPGRAPHQL_COGNITO_AUTHENTICATION_PLUGIN_DIR . 'vendor/autoload.php' );
			}
		}

		/**
		 * Initialize the plugin
		 */
		private static function init() {

            if ( is_admin() ){
                // Create the plugin page
                $aws_cognito_graphql = new AWSCognitoGraphQL();
            }

			/**
			 * When the GraphQL Request is initiated, determine current user by its token.
			 */
			add_action( 'init_graphql_request', function() {

			    // Filter how WordPress determines the current user.
                add_filter(
                    'determine_current_user',
                    [ '\WPGraphQL\Cognito_Authentication\Auth', 'graphql_auth_determine_current_user' ],
                    99 // as much priority as possible
                );

			} );
		}
	}

endif;

/**
 * Start Cognito_Authentication.
 */
function init() {
	return Cognito_Authentication::instance();
}
add_action( 'plugins_loaded', '\WPGraphQL\Cognito_Authentication\init', 1 );

add_option( 'cognito_jwks', '' );





