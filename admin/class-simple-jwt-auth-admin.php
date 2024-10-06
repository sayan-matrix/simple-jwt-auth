<?php

/* Require the Crypto and DB Manager library. */
use Simple_Jwt_Auth\OpenSSL\Crypto;
use Simple_Jwt_Auth\Database\DBManager;
use Simple_Jwt_Auth\Notice\JWTNotice;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/sayandey18
 * @since      1.0.0
 *
 * @package    Simple_Jwt_Auth
 * @subpackage Simple_Jwt_Auth/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Simple_Jwt_Auth
 * @subpackage Simple_Jwt_Auth/admin
 * @author     Sayan Dey <mr.sayandey18@outlook.com>
 */
class Simple_Jwt_Auth_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		string The current version of this plugin.
	 */
	private string $version;

	/**
     * Supported algorithms to sign the token.
     * 
     * @since   1.0.0
	 * @access	private
	 * @see     https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
     */
    private array $supported_algos = [
		'HS256', 'HS384', 'HS512', 
		'RS256', 'RS384', 'RS512', 
		'ES256', 'ES384', 'ES512', 
		'PS256', 'PS384', 'PS512'
	];

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since	1.0.0
	 * @param	string $plugin_name The name of this plugin.
	 * @param	string $version The version of this plugin.
	 */
	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since	1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-jwt-auth-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since	1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/simple-jwt-auth-admin.js', array( 'jquery' ), $this->version, true );
	}

	/**
	 * Create hook callback function for plugin admin menu.
	 * 
	 * @since	1.0.0
	 */
	public function simplejwt_admin_menus() {
		// Call the `simplejwt_menu_icon()` method to get the base64 SVG
		$svg_icon = $this->simplejwt_menu_icon();

		// Add the main menu.
		add_menu_page( 
			__( 'Simple JWT Auth', 'simple-jwt-auth' ),
			__( 'JWT Auth', 'simple-jwt-auth' ),
			'manage_options',
			'simple-jwt-auth',
			'',
			$svg_icon
		);

		// Add "Settings" as the first submenu and default page.
		add_submenu_page(
			'simple-jwt-auth',
			__( 'JWT Auth Settings', 'simple-jwt-auth' ),
			__( 'Settings', 'simple-jwt-auth' ),
			'manage_options',
			'simple-jwt-auth',
			array( $this, 'simplejwt_page_settings' ),
		);

		// Add "Options" as a second submenu.
		add_submenu_page(
			'simple-jwt-auth',
			__( 'JWT Auth Options', 'simple-jwt-auth' ),
			__( 'Options', 'simple-jwt-auth' ),
			'manage_options',
			'simple-jwt-auth-options',
			array( $this, 'simplejwt_page_option' ),
		);
	}

	/**
	 * Callback function for the "Settings" submenu page of the plugin.
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function simplejwt_page_settings() {
		// Fetch the configuration data from the custom table.
		$config = $this->simplejwt_get_plugin_configs();

		// Check and mention the current stack info
		$versions_info = $this->simplejwt_version_info();

		// Include the template file and pass the config data.
		include plugin_dir_path( __FILE__ ) . 'partials/simple-jwt-auth-admin-settings.php';
	}

	/**
	 * Callback function for the "Options" submenu page of the plugin.
	 * 
	 * @since	1.0.0
	 * @return	void
	 */
	public function simplejwt_page_option() {
		// Include the template file and pass the config data.
		include plugin_dir_path( __FILE__ ) . 'partials/simple-jwt-auth-admin-options.php';
	}

	/**
	 * Adds Settings link to the plugin action links on plugin page.
	 * 
	 * @since	1.0.0
	 * @param	array $links Array of action links related to the plugin.
	 * @return	array Modified array of links.
	 */
	public function simplejwt_quick_links( array $links ) {
		// Create the URL for the settings page.
		$links['settings'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			admin_url( 'admin.php?page=simple-jwt-auth' ),
			esc_attr__( 'Go to JWT Settings page', 'simple-jwt-auth' ),
			esc_html__( 'Settings', 'simple-jwt-auth' )
		);

		return $links;
	}

	/**
	 * Get the all config data as Array from the config` table.
	 * 
	 * @since	1.0.0
	 * @return	array
	 */
	private function simplejwt_get_plugin_configs() {
		global $wpdb;

		// Set the table name.
		$table_name = $wpdb->prefix . 'simplejwt_config';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$results = $wpdb->get_results( 
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT * FROM {$table_name}", // Get all configuration data.
			ARRAY_A
		);
	
		$config = array();
		foreach ( $results as $row ) {
			$config[$row['config_name']] = $row['config_value'];
		}
	
		return $config;
	}

	/**
	 * Create a parent class to override the admin style for plugin UI/UX.
	 * 
	 * @since	1.0.0
	 * 
	 * @param	string $classes
	 * @return	string
	 */
	public function simplejwt_admin_body_classes( string $classes ) {
		$screen = get_current_screen();

		// Check if the current screen is one of your plugin's pages.
		if (strpos($screen->id, 'simple-jwt-auth') !== false ) {
            $classes .= ' simplejwt';
        }

		return $classes;
	}

	/**
	 * Callback function to handle the JWT settings form, and perform 
	 * sql query to save the settings into database.
	 * 
	 * @since	1.0.0
	 */
	public function simplejwt_settings_callback() {
		if ( isset( $_POST['simplejwt_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['simplejwt_nonce'] ) ), 'simplejwt_nonce' ) ) {

			// Checks the all form values.
			$enable_auth    = isset( $_POST['simplejwt_enable_auth'] ) ? filter_var( wp_unslash( $_POST['simplejwt_enable_auth'] ), FILTER_VALIDATE_BOOLEAN ) : false;
			$enable_cors    = isset( $_POST['simplejwt_enable_cors'] ) ? filter_var( wp_unslash( $_POST['simplejwt_enable_cors'] ), FILTER_VALIDATE_BOOLEAN ) : false;
			$disable_xmlrpc = isset( $_POST['simplejwt_disable_xmlrpc'] ) ? filter_var( wp_unslash( $_POST['simplejwt_disable_xmlrpc'] ), FILTER_VALIDATE_BOOLEAN ) : false;
			$algorithm      = isset( $_POST['simplejwt_algorithm'] ) ? sanitize_text_field( wp_unslash( $_POST['simplejwt_algorithm'] ) ) : 'HS256';
			$secret_key     = isset( $_POST['simplejwt_secret_key'] ) ? sanitize_textarea_field( wp_unslash( $_POST['simplejwt_secret_key'] ) ) : '';
			$private_key    = isset( $_POST['simplejwt_private_key'] ) ? sanitize_textarea_field( wp_unslash( $_POST['simplejwt_private_key'] ) ) : '';
    		$public_key     = isset( $_POST['simplejwt_public_key'] ) ? sanitize_textarea_field( wp_unslash( $_POST['simplejwt_public_key'] ) ) : '';

			// Send error response if the algorithms not matched.
			if ( !empty( $algorithm ) && !in_array( $algorithm, $this->supported_algos ) ) {
				$this->simplejwt_admin_redirect( false, JWTNotice::get_notice( 'unsupported_algo' ) );
			}

			if ( $enable_auth ) {
				// Checks if algorithm is HS256, HS384, or HS512 and if secret key is empty.
				if ( in_array( $algorithm, ['HS256', 'HS384', 'HS512'], true ) && empty( $secret_key ) ) {
					$this->simplejwt_admin_redirect( false, JWTNotice::get_notice( 'empty_secret_key' ) );
				}

				// If the algorithm is not HS*, check for public and private keys.
				if ( !in_array( $algorithm, ['HS256', 'HS384', 'HS512'], true ) ) {
					if ( empty( $private_key ) ) {
						$this->simplejwt_admin_redirect( false, JWTNotice::get_notice( 'empty_private_key' ) );
					}

					if ( empty( $public_key ) ) {
						$this->simplejwt_admin_redirect( false, JWTNotice::get_notice( 'empty_public_key' ) );
					}
				}
			}

			// Checks for a valid private key using OpenSSL.
			if ( !empty( $private_key ) && !openssl_pkey_get_private( $private_key ) ) {
				$this->simplejwt_admin_redirect( false, JWTNotice::get_notice( 'invalid_private_key' ) );
			}

			// Checks for a valid public key using OpenSSL.
			if ( !empty( $public_key ) && !openssl_pkey_get_public( $public_key ) ) {
				$this->simplejwt_admin_redirect( false, JWTNotice::get_notice( 'invalid_public_key' ) );
			}

			// Encrypt sensitive keys.
			if ( !empty( $secret_key ) ) {
				$secret_key = Crypto::encrypt( $secret_key );

				if ( is_wp_error( $secret_key ) ) {
					// Get the error message.
					$error_message = $secret_key->get_error_message();
					$this->simplejwt_admin_redirect( false, $error_message );
				}
			}

			if ( !empty( $private_key ) ) {
				$private_key = Crypto::encrypt( $private_key );

				if ( is_wp_error( $private_key ) ) {
					// Get the error message.
					$error_message = $private_key->get_error_message();
					$this->simplejwt_admin_redirect( false, $error_message );
				}
			}

			if ( !empty( $public_key ) ) {
				$public_key = Crypto::encrypt( $public_key );

				if ( is_wp_error( $public_key ) ) {
					// Get the error message.
					$error_message = $public_key->get_error_message();
					$this->simplejwt_admin_redirect( false, $error_message );
				}
			}

			// Prepare the data for store in database.
			$configs = [
				'algorithm'      => $algorithm,
				'enable_auth'    => $enable_auth ? '1' : '0',
				'secret_key'     => $secret_key ? $secret_key : '',
				'private_key'    => $private_key ? $private_key: '',
				'public_key'     => $public_key ? $public_key : '',
				'enable_cors'    => $enable_cors ? '1' : '0', 
				'disable_xmlrpc' => $disable_xmlrpc ? '1' : '0',
			];

			// Initiate the DBManager class to update the config data.
			$update_config = DBManager::save_config( $configs );

			// Return with unknown error message if the update is failed.
			if ( $update_config === false ) {
				$this->simplejwt_admin_redirect( false, JWTNotice::get_notice( 'unknown_error' ) );
			}
	
			// Redirect with a success message if the update is successful.
			$this->simplejwt_admin_redirect( true, JWTNotice::get_notice( 'success' ) );
		} else {
			wp_die(
				esc_html__( 'Invalid nonce specified!', 'simple-jwt-auth' ),
				esc_html__( 'JWT Error', 'simple-jwt-auth' ),
				[
					'response'  => 403,
					'back_link' => 'admin.php?page=simple-jwt-auth'
				]
			);
		}
	}

	/**
	 * Callback function to handle the JWT options form, and perform sql 
	 * query to save the options into database that used in plugin uninstallation.
	 * 
	 * @since	1.0.0
	 */
	public function simplejwt_options_callback() {
		if ( isset( $_POST['simplejwt_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['simplejwt_nonce'] ) ), 'simplejwt_nonce' ) ) {
			// Checks the all form values.
			$remove_configs = isset( $_POST['simplejwt_drop_configs'] ) ? filter_var( wp_unslash( $_POST['simplejwt_drop_configs'] ), FILTER_VALIDATE_BOOLEAN ) : false;

			// Update the option based on the checkbox value.
			$update_option = update_option( 'simplejwt_drop_configs', $remove_configs );

			// Return with unknown error message if the update is failed.
			if ( $update_option === false ) {
				$this->simplejwt_admin_redirect( false, JWTNotice::get_notice( 'unknown_error' ), 'options' );
			}

			// Redirect with a success message if the update is successful.
			$this->simplejwt_admin_redirect( true, JWTNotice::get_notice( 'success' ), 'options' );
		} else {
			wp_die(
				esc_html__( 'Invalid nonce specified!', 'simple-jwt-auth' ),
				esc_html__( 'JWT Error', 'simple-jwt-auth' ),
				[
					'response'  => 403,
					'back_link' => 'admin.php?page=simple-jwt-auth-options'
				]
			);
		}
	}
	
	/**
	 * Redirects to a specified plugin page with custom transient key.
	 * 
	 * This function performs a redirection to a WordPress JWT plugin settings
	 * page and set `simplejwt_admin_notice` in transient for admin notice.
	 * 
	 * @since		  1.0.0
	 * @param boolean $status
	 * @param string  $message
	 */
	public function simplejwt_admin_redirect( bool $status, string $message, ?string $location = null ) {
		if ( get_transient( 'simplejwt_admin_notice' ) ) {
			delete_transient( 'simplejwt_admin_notice' );
		}

		$set_notice = set_transient( 'simplejwt_admin_notice', [
			'status'  => $status ? 'success' : 'error',
			'message' => $message
		], MINUTE_IN_SECONDS );

		if ( $set_notice === true ) {
			// Set the redirect location.
			$redirect = $location ? admin_url( 'admin.php?page=simple-jwt-auth-' . $location ) : admin_url( 'admin.php?page=simple-jwt-auth' );

			// Redirect to the appropriate page.
			wp_redirect( $redirect );
			exit();
		}
	}

	/**
	 * Displays admin notices in the WordPress admin area.
	 * 
	 * This function checks `simplejwt_admin_notice` key is present in the transient.
	 * If the transient is set show admin notice accordingly.
	 * 
	 * Nonce verification is handled during form submission to ensure data integrity.
	 * 
	 * @since	1.0.0
	 */
	public function simplejwt_admin_notices( ?string $status = null, ?string $message = null ) {
		// Check if status and message are provided, if so display notice.
		if ( !empty( $status ) && !empty( $message ) ) {
			$status  = sanitize_text_field( wp_unslash( $status ) );
			$message = sanitize_text_field( wp_unslash( $message ) );

			if ( $status === 'success' ) {
				// translators: %s is the success notice message.
				printf( '<div class="notice notice-success is-dismissible"><p><strong>%s</strong></p></div>', esc_html( $message ) );
			} else {
				// translators: %s is the error notice message.
				printf( '<div class="notice notice-error is-dismissible"><p><strong>%s</strong></p></div>', esc_html( $message ) );
			}
		} else {
			// Checks the `simplejwt_admin_notice` key is present in transient.
			if ( get_transient( 'simplejwt_admin_notice' ) ) {
				$get_notice = get_transient( 'simplejwt_admin_notice' );

				// Checks `status` and `message` are already set.
				if ( isset( $get_notice['status'], $get_notice['message'] ) ) {
					$status  = sanitize_text_field( wp_unslash( $get_notice['status'] ) );
					$message = sanitize_text_field( wp_unslash( $get_notice['message'] ) );

					// Delete the existing transient to store new notice.
					$delete_transist = delete_transient( 'simplejwt_admin_notice' );
					if ( $delete_transist ) {
						if ( $status === 'success' ) {
							// translators: %s is the success notice message.
							printf( '<div class="notice notice-success is-dismissible"><p><strong>%s</strong></p></div>', esc_html( $message ) );
						} else {
							// translators: %s is the error notice message.
							printf( '<div class="notice notice-error is-dismissible"><p><strong>%s</strong></p></div>', esc_html( $message ) );
						}
					}
				}
			}
		}
	}

	/**
	 * Check if XML-RPC should be disabled based on the configuration.
	 * 
	 * @since	1.0.0
	 * @param	bool $enabled The current XML-RPC enabled/disabled state.
	 * @return	bool Whether XML-RPC is enabled (true) or disabled (false).
	 */
	public function simplejwt_disable_xmlrpc( bool $enabled ) {
		// If XML-RPC is already disabled return state.
		if ( $enabled === false ) {
			return false;
		}

		// Get the XML-RPC status from database.
		$disable_xmlrpc = filter_var(
			DBManager::get_config( 'disable_xmlrpc' ),
			FILTER_VALIDATE_BOOLEAN
		);

		// If $disable_xmlrpc is true, disable XML-RPC.
		if ( $disable_xmlrpc === true ) {
			return false; // Disable XML-RPC
		}
	
		// Otherwise, return the current $enabled state.
		return $enabled;
	}

	/**
	 * Function to check current WordPress and PHP version and notify the
	 * admin users if there is updated/recommended version is available.
	 * 
	 * @since	1.0.0
	 * @return	array
	 */
	private function simplejwt_version_info() {
		global $wp_version;

		// Check the current WordPress version and available updates.
		$current_wp = $wp_version;
		$recommended_wp = get_site_transient( 'update_core' )->updates[0]->current ?? false;
		$wp_update_message = '';
		$wp_body_message = __( 'Website is running on the latest version.', 'simple-jwt-auth' );

		if ( $recommended_wp && version_compare( $current_wp, $recommended_wp, '<' ) ) {
			// translators: %s is the latest WordPress/PHP version to update.
			$wp_update_message = sprintf( __( 'Update to %s is recommended.', 'simple-jwt-auth' ), esc_html( $recommended_wp ) );
			$wp_body_message = __( 'An update is available for your WordPress installation.', 'simple-jwt-auth' );
		}

		// Check the current PHP version and notify recommended version.
		$current_php = PHP_VERSION;
		$recommended_php = '8.2';
		$php_update_message = '';
    	$php_body_message = __( 'Website is running on the recommended version.', 'simple-jwt-auth' );

		if ( $recommended_php && version_compare( $current_php, $recommended_php, '<' ) ) {
			// translators: %s is the latest WordPress/PHP version to update.
			$php_update_message = sprintf( __( 'Update to %s is recommended.', 'simple-jwt-auth' ), esc_html( $recommended_php ) );
			$php_body_message = __( 'Various updates and fixes are available in the newest version.', 'simple-jwt-auth' );
		}

		return [
			'wp_version'         => $current_wp,
			'wp_body_message'    => $wp_body_message,
			'wp_update_message'  => $wp_update_message,
			'php_version'        => $current_php,
			'php_body_message'   => $php_body_message,
			'php_update_message' => $php_update_message,
		];
	}

	/**
	 * Set SVG icon fo the plugin admin menu.
	 * 
	 * @since	1.0.0
	 * 
	 * @param	boolean $base64
	 * @return	string
	 */
	private function simplejwt_menu_icon( $base64 = true ) {
		$svg_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-1.92 -1.92 27.84 27.84" width="256" height="256"><path fill="#a7aaad" d="M10.2 0v6.456L12 8.928l1.8-2.472V0zm3.6 6.456v3.072l2.904-.96L20.52 3.36l-2.928-2.136zm2.904 2.112-1.8 2.496 2.928.936 6.144-1.992-1.128-3.432zM17.832 12l-2.928.936 1.8 2.496 6.144 1.992 1.128-3.432zm-1.128 3.432-2.904-.96v3.072l3.792 5.232 2.928-2.136zM13.8 17.544 12 15.072l-1.8 2.472V24h3.6zm-3.6 0v-3.072l-2.904.96L3.48 20.64l2.928 2.136zm-2.904-2.112 1.8-2.496L6.168 12 .024 13.992l1.128 3.432zM6.168 12l2.928-.936-1.8-2.496-6.144-1.992-1.128 3.432zm1.128-3.432 2.904.96V6.456L6.408 1.224 3.48 3.36z"></path></svg>';

		if ( $base64 ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- This encoding is intended.
			return 'data:image/svg+xml;base64,' . base64_encode( $svg_icon );
		}

		return $svg_icon;
	}

	/**
	 * Dynamically generate the plugin's API endpoints.
	 * 
	 * @since	1.0.0
	 * @return	string
	 */
	private function simplejwt_public_endpoints( ?string $path = '' ) {
		// Check if constant is defined and assign its value, defaulting to '1.0.0' and 'auth'.
		$plugin_version = defined( 'SIMPLE_JWT_AUTH_VERSION' ) ? SIMPLE_JWT_AUTH_VERSION : '1.0.0';
		$plugin_endpoint = defined( 'SIMPLE_JWT_AUTH_ENDPOINT' ) ? SIMPLE_JWT_AUTH_ENDPOINT : 'auth';

		// Construct the REST endpoint.
		$api_endpoint = sprintf('%s/v%d/%s', $plugin_endpoint, intval( $plugin_version ), $path );

		return rest_url( $api_endpoint );
	}

}
