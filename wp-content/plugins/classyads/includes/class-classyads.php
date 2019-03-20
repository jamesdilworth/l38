<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, andc public-facing site hooks. Also
 * maintains the unique identifier of this plugin as well as the current version of the plugin.
 *
 * @since      1.0.0
 * @package    Classyads
 * @subpackage Classyads/includes
 * @author     James D <james@jamesdilworth.com>
 */
class Classyads {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Classyads_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		if ( defined( 'CLASSYADS_VERSION' ) ) {
			$this->version = CLASSYADS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'classyads';

		$this->load_dependencies();
		$this->set_locale();
        $this->define_setup_hooks();
        $this->define_import_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Classyads_Loader. Orchestrates the hooks of the plugin.
	 * - Classyads_i18n. Defines internationalization functionality.
	 * - Classyads_Admin. Defines all hooks for the admin area.
	 * - Classyads_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		// Responsible for orchestrating the actions and filters of the core plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-loader.php';

		// Responsible for defining internationalization functionality of the plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-i18n.php';

		// We'll use this for all the general setup of the plugin. Registering CPT's, Taxonomies, etc.
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-setup.php';

        // Mish mash of non-oo functions that help us do certain things.
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helpers.php';

		// We'll use this for all the import scripts
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-import.php';

		// Rsponsible for defining all actions that occur in the admin area.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-classyads-admin.php';

		// Responsible for defining all actions that occur in the public-facing side of the site.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-classyads-public.php';

		$this->loader = new Classyads_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Classyads_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Classyads_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

    private function define_setup_hooks() {

        $plugin_setup = new Classyads_Setup( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action('init', $plugin_setup, 'define_classy_cpt'); // Define Classy CPT.
        $this->loader->add_action('init', $plugin_setup, 'define_adcat_tax'); // Define Adcats

    }

	private function define_admin_hooks() {

		$plugin_admin = new Classyads_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' ); // Add menu item
        $this->loader->add_action('admin_init', $plugin_admin, 'options_update'); // Options Update

        // Add Settings link to the plugin
        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
        $this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

    }

	private function define_public_hooks() {

		$plugin_public = new Classyads_Public( $this->get_plugin_name(), $this->get_version() );

        // ENQUEUE SCRIPTS AND STYLES
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        // SINGLE TEMPLATE FOR CLASSIES
        $this->loader->add_filter('single_template', $plugin_public, 'define_classy_template');

        // UGC AJAX EDITING SCRIPTS...
        $this->loader->add_action('wp_ajax_update_classy_mainphoto', $plugin_public,  'update_classy_mainphoto');

        // AJAX FILTERING
        $this->loader->add_action('wp_ajax_update_classy_list',  $plugin_public, 'update_classy_list');
        $this->loader->add_action('wp_ajax_nopriv_update_classy_list',  $plugin_public, 'update_classy_list');

        // WIDGETS
        $this->loader->add_action('widgets_init', $plugin_public, 'define_classy_template');

    }

    private function define_import_hooks() {
        $plugin_import = new Classyads_Import( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action('wp_ajax_add_classy_from_lasso', $plugin_import, 'add_classy_from_lasso');
        $this->loader->add_action('wp_ajax_nopriv_add_classy_from_lasso', $plugin_import, 'add_classy_from_lasso');

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
