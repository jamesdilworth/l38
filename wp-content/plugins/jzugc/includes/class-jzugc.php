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
 * @since      0.1.0
 * @package    Jzugc_
 * @subpackage Jzugc/includes
 * @author     James Dilworth <james@jd.com>
 */
class Jzugc_ {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      Jzugc_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {
		if ( defined( 'JZUGC_VERSION' ) ) {
			$this->version = JZUGC_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'jzugc';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_ajax_listeners();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {

	    // Responsible for orchestrating the actions and filters of the core plugin.
		require_once JZUGC_PATH . 'includes/class-jzugc-loader.php';

        // Mish-mash of non-oo functions useful for orchestrating user admin.
        require_once JZUGC_PATH . 'includes/jzugc-helpers.php';

        // Responsible for defining internationalization functionality of the plugin.
		require_once JZUGC_PATH . 'includes/class-jzugc-i18n.php';

        // Responsible for defining all ajax listeners
        require_once JZUGC_PATH . 'includes/class-jzugc-ajax.php';

        // Responsible for defining all actions that occur in the admin area.
		require_once JZUGC_PATH . 'admin/class-jzugc-admin.php';

		// Responsible for defining all actions that occur in the public-facing side of the site.
        require_once JZUGC_PATH . 'public/class-jzugc-public.php';

		$this->loader = new Jzugc_Loader();

	}

	private function set_locale() {
		$jzugc_i18n = new Jzugc_i18n();
		$this->loader->add_action( 'plugins_loaded', $jzugc_i18n, 'load_plugin_textdomain' );
	}

	private function define_ajax_listeners() {
        $jzugc_ajax = new Jzugc_Ajax();

        $this->loader->add_action( 'wp_ajax_nopriv_ajaxlogin', $jzugc_ajax, 'ajax_login' ); // Listen for AJAX Login.
        // Listen for async file uploads.

    }

	private function define_admin_hooks() {

		$jzugc_admin = new Jzugc_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $jzugc_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $jzugc_admin, 'enqueue_scripts' );

        $this->loader->add_filter('get_avatar', $jzugc_admin, 'acf_profile_avatar', 10, 5); // Alter to use meta field for profile avatar.
        $this->loader->add_filter( 'display_post_states', $jzugc_admin, 'set_post_states', 10, 2 ); // Add post state to the User Profile and User Account Dashboard pages
        $this->loader->add_action( 'admin_notices', $jzugc_admin,  'add_post_notices' );

    }

	private function define_public_hooks() {

	    $jzugc_public = new Jzugc_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $jzugc_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $jzugc_public, 'enqueue_scripts' );

        $this->loader->add_action( 'init', $jzugc_public,'init_jzugc_public');

        $this->loader->add_filter('register', $jzugc_public, 'set_register_url');
        $this->loader->add_filter('login_redirect', $jzugc_public,'login_redirect', 10, 3 );

    }

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
