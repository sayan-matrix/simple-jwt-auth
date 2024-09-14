<?php

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
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/simple-jwt-auth-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
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

		add_menu_page( 
			__( 'Simple JWT Auth', 'simple-jwt-auth' ),
			__( 'JWT Auth', 'simple-jwt-auth' ),
			'manage_options',
			'simple-jwt-auth',
			array( $this, 'simplejwt_page_dashboard' ),
			$svg_icon
		);
	}

	public function simplejwt_page_dashboard() {
		// Fetch the configuration data from the custom table.
		$config = $this->simplejwt_get_plugin_config();

		// Check and mention the current stack info
		$versions_info = $this->simplejwt_version_info();

		// Include the template file and pass the config data.
		include plugin_dir_path( __FILE__ ) . 'partials/simple-jwt-auth-admin-display.php';
	}

	/**
	 * Get the all config data as Array from the config` table.
	 * 
	 * @since	1.0.0
	 * @return 	(array)
	 */
	private function simplejwt_get_plugin_config() {
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
	 * @return	(string)
	 */
	public function simplejwt_admin_body_classes ( $classes ) {
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
	function simplejwt_settings_callback() {
		if ( isset( $_POST['simplejwt_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['simplejwt_nonce'] ) ), 'simplejwt_nonce' ) ) {
			// Set admin notice.
			
			$location = 'simple-jwt-auth';
			// Redirect the user to the appropriate page,
			$this->simplejwt_admin_redirect( true, $location);
			exit();
		} else {
			wp_die(
				esc_html__( 'Invalid nonce specified!', 'simple-jwt-auth' ),
				esc_html__( 'JWT Error', 'simple-jwt-auth' ),
				[
					'response' => 403,
					'back_link' => 'admin.php?page=simple-jwt-auth'
				]
			);
		}
	}
	
	/**
	 * Redirects to a specified admin page with custom query parameters.
	 * 
	 * This function performs a redirection to a WordPress admin page and appends
	 * custom query arguments to the URL. Specifically, it adds a `simplejwt_admin_notice`
	 * and `simplejwt_response` parameter to the URL. The destination page is determined
	 * by the `$location` parameter.
	 * 
	 * @since		 1.0.0
	 * @param string $notice
	 * @param string $response
	 * @param string $location
	 */
	public function simplejwt_admin_redirect( $status, $location ) {
		wp_redirect( 
			esc_url_raw( 
				add_query_arg( 
					array(
						'settings-updated' => $status ? 'true' : 'false'
					),
					admin_url( 'admin.php?page=' . $location )
				) 
			) 
		);
	}

	/**
	 * Displays admin notices in the WordPress admin area.
	 * 
	 * This function checks if a `simplejwt_notice` parameter is present in the request.
	 * If the parameter is set show admin notice accordingly.
	 * 
	 * Nonce verification is handled during form submission to ensure data integrity.
	 * 
	 * @since	1.0.0
	 */
	public function simplejwt_admin_notices() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['settings-updated'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if( $_REQUEST['settings-updated'] === 'true' ) {
				$message = __( 'Settings saved successfully.', 'simple-jwt-auth' );
				// translators: %s is the success notice message.
				printf( '<div class="notice notice-success is-dismissible"><p><strong>%s</strong></p></div>', esc_html( $message ) );
		  	} else {
				// translators: %s is the error notice message.
				$message = __( 'Settings saved failed!', 'simple-jwt-auth' );
				printf( '<div class="notice notice-error is-dismissible"><p><strong>%s</strong></p></div>', esc_html( $message ) );
			}
		}
	}

	/**
	 * Function to check current WordPress and PHP version and notify the
	 * admin users if there is updated/recommended version is available.
	 * 
	 * @since	1.0.0
	 * @return	(array)
	 */
	private function simplejwt_version_info() {
		global $wp_version;

		// Check the current WordPress version and available updates.
		$current_wp = $wp_version;
		$recommended_wp = get_site_transient( 'update_core' )->updates[0]->current ?? false;
		$wp_update_message = '';
		$wp_body_message = __( 'Website is running on the latest version.', 'simple-jwt-auth' );

		if ( $recommended_wp && version_compare( $current_wp, $recommended_wp, '<' ) ) {
			// translators: %s is the latest WordPress version to update.
			$wp_update_message = sprintf( __( 'Update to %s is recommended.', 'simple-jwt-auth' ), esc_html( $recommended_wp ) );
			$wp_body_message = __( 'An update is available for your WordPress installation.', 'simple-jwt-auth' );
		}

		// Check the current PHP version and notify recommended version.
		$current_php = PHP_VERSION;
		$recommended_php = '8.2';
		$php_update_message = '';
    	$php_body_message = __( 'Website is running on the recommended version.', 'simple-jwt-auth' );

		if ( $recommended_php && version_compare( $current_php, $recommended_php, '<' ) ) {
			// translators: %s is the recommended PHP version to update.
			$php_update_message = sprintf( __( 'Update to %s is recommended.', 'simple-jwt-auth' ), esc_html( $recommended_php ) );
			$php_body_message = __( 'Various updates and fixes are available in the newest version.', 'simple-jwt-auth' );
		}

		return [
			'wp_version' => $current_wp,
			'wp_body_message' => $wp_body_message,
			'wp_update_message' => $wp_update_message,
			'php_version' => $current_php,
			'php_body_message' => $php_body_message,
			'php_update_message' => $php_update_message,
		];
	}

	/**
	 * Set SVG icon fo the plugin admin menu.
	 * 
	 * @since	1.0.0
	 */
	private function simplejwt_menu_icon( $base64 = true ) {
		$svg_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#a7aaad" d="M10.2 0v6.456L12 8.928l1.8-2.472V0zm3.6 6.456v3.072l2.904-.96L20.52 3.36l-2.928-2.136zm2.904 2.112-1.8 2.496 2.928.936 6.144-1.992-1.128-3.432zM17.832 12l-2.928.936 1.8 2.496 6.144 1.992 1.128-3.432zm-1.128 3.432-2.904-.96v3.072l3.792 5.232 2.928-2.136zM13.8 17.544 12 15.072l-1.8 2.472V24h3.6zm-3.6 0v-3.072l-2.904.96L3.48 20.64l2.928 2.136zm-2.904-2.112 1.8-2.496L6.168 12 .024 13.992l1.128 3.432zM6.168 12l2.928-.936-1.8-2.496-6.144-1.992-1.128 3.432zm1.128-3.432 2.904.96V6.456L6.408 1.224 3.48 3.36z"></path></svg>';

		if ( $base64 ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- This encoding is intended.
			return 'data:image/svg+xml;base64,' . base64_encode( $svg_icon );
		}

		return $svg_icon;
	}

}
