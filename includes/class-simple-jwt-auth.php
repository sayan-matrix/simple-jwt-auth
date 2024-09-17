<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @link       https://github.com/sayandey18
 * @since      1.0.0
 * 
 * @package    Simple_Jwt_Auth
 * @subpackage Simple_Jwt_Auth/includes
 * @author     Sayan Dey <mr.sayandey18@outlook.com>
 */
class Simple_Jwt_Auth {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since	1.0.0
	 * @access	protected
	 * @var		Simple_Jwt_Auth_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected Simple_Jwt_Auth_Loader $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since	1.0.0
	 * @access	protected
	 * @var		string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected string $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since	1.0.0
	 * @access	protected
	 * @var		string $version The current version of the plugin.
	 */
	protected string $version;

	/**
	 * The endpoint of this plugin API.
	 *
	 * @since	1.0.0
	 * @access	protected
	 * @var		string $endpoint The JWT endpoint of this plugin.
	 */
	protected string $endpoint;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since	1.0.0
	 */
	public function __construct() {
		if ( defined( 'SIMPLE_JWT_AUTH_VERSION' ) ) {
			$this->version = SIMPLE_JWT_AUTH_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		if ( defined( 'SIMPLE_JWT_AUTH_TEXT_DOMAIN' ) ) {
			$this->plugin_name = SIMPLE_JWT_AUTH_TEXT_DOMAIN;
		} else {
			$this->plugin_name = 'simple-jwt-auth';
		}

		if ( defined( 'SIMPLE_JWT_AUTH_ENDPOINT' ) ) {
			$this->endpoint = SIMPLE_JWT_AUTH_ENDPOINT;
		} else {
			$this->endpoint = 'wp-jwt';
		}

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Simple_Jwt_Auth_Loader. Orchestrates the hooks of the plugin.
	 * - Simple_Jwt_Auth_i18n. Defines internationalization functionality.
	 * - Simple_Jwt_Auth_Admin. Defines all hooks for the admin area.
	 * - Simple_Jwt_Auth_Public. Defines all hooks for the public side of the site.
	 * - Simple_Jwt_Auth\Firebase\JWT. Wrapper namespace for prevent conflicts.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since	1.0.0
	 * @access	private
	 */
	private function load_dependencies() {
		/**
		 * Load dependencies managed by composer.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendor/autoload.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-jwt-auth-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-jwt-auth-i18n.php';

		/**
		 * Class responsible for creating a `wrapper namespace` to load the Firebase's JWT & Key
		 * classes and prevent conflicts with other plugins using the same library
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-jwt-auth-namespace.php';

		/**
		 * Class responsible for managing the plugin config data.
		 * It allows updating or inserting config values into the database.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-simple-jwt-auth-db-manager.php';
		
		/**
		 * The class responsible for encrypting and decrypting the provided data using 
		 * the OpenSSL AES-256-GCM algorithm.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/crypto/class-simple-jwt-auth-crypto.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-simple-jwt-auth-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-simple-jwt-auth-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/endpoints/api-simple-jwt-auth.php';

		$this->loader = new Simple_Jwt_Auth_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Simple_Jwt_Auth_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since	1.0.0
	 * @access	private
	 */
	private function set_locale() {
		$plugin_i18n = new Simple_Jwt_Auth_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since	1.0.0
	 * @access	private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Simple_Jwt_Auth_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'simplejwt_admin_menus' );
		$this->loader->add_filter( 'admin_body_class', $plugin_admin, 'simplejwt_admin_body_classes' );
		$this->loader->add_action( 'admin_post_simplejwt_settings_action', $plugin_admin, 'simplejwt_settings_callback');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since	1.0.0
	 * @access	private
	 */
	private function define_public_hooks() {
		$plugin_public = new Simple_Jwt_Auth_Public( $this->get_plugin_name(), $this->get_version() );

		$plugin_auth_public = new Simple_Jwt_Auth_Auth( $this->get_plugin_name(), $this->get_version(), $this->get_endpoint() );
		$this->loader->add_action( 'rest_api_init', $plugin_auth_public, 'simplejwt_add_api_routes' );
		$this->loader->add_filter( 'rest_api_init', $plugin_auth_public, 'simplejwt_add_cors_support' );
		$this->loader->add_filter( 'rest_pre_dispatch', $plugin_auth_public, 'simplejwt_rest_pre_dispatch', 10, 2 );
		$this->loader->add_filter( 'determine_current_user', $plugin_auth_public, 'simplejwt_determine_current_user' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since	1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since	1.0.0
	 * @return	string The name of the plugin.
	 */
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since	1.0.0
	 * @return	Simple_Jwt_Auth_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader(): Simple_Jwt_Auth_Loader {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since	1.0.0
	 * @return	string The version number of the plugin.
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Retrieve the api endpoint of the plugin.
	 *
	 * @since	1.0.0
	 * @return	string The current JWT REST API endpoint of this plugin.
	 */
	public function get_endpoint(): string {
		return $this->endpoint;
	}

}
