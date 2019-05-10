<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, andc public-facing site hooks. Also
 * maintains the unique identifier of this plugin as well as the current version of the plugin.
 *
 * @since      0.1.0
 * @package    Classyads
 * @subpackage Classyads/includes
 * @author     James D <james@jamesdilworth.com>
 */
class Classyads {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
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
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'classyads';

		$this->load_dependencies();
		$this->set_locale();
        $this->define_setup_hooks();
        $this->define_import_hooks();
        $this->define_ajax_listeners();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	private function load_dependencies() {

		// Responsible for orchestrating the actions and filters of the core plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-loader.php';

        // Mish mash of non-oo functions that help us do certain things.
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helpers.php';

        // Event functions... such as garbage collection, setting expiries, and reminders.
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/events.php';

        // Mish mash of non-oo functions that help us do certain things.
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-import.php';

        // Responsible for defining internationalization functionality of the plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-i18n.php';

		// We'll use this for all the general setup of the plugin. Registering CPT's, Taxonomies, etc.
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-setup.php';

        // Core ClassyAd Class for individual object manipulation.
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-classyad.php';

        // We'll use this for all the import scripts
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-import.php';

        // Use this for the AJAX endpoints.
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-classyads-ajax.php';

		// Rsponsible for defining all actions that occur in the admin area.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-classyads-admin.php';

		// Responsible for defining all actions that occur in the public-facing side of the site.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-classyads-public.php';

		$this->loader = new Classyads_Loader();

	}

	private function set_locale() {
		$classyads_i18n = new Classyads_i18n();
		$this->loader->add_action( 'plugins_loaded', $classyads_i18n, 'load_plugin_textdomain' );
	}

    private function define_setup_hooks() {
        add_action('init', 'Classyads_Setup::define_classy_cpt');
        add_action('init', 'Classyads_Setup::define_adcat_tax');
        add_action('init', 'Classyads_Setup::define_post_statuses');
        add_action( 'plugins_loaded', 'Classyads_Setup::checkDB' );
        add_action( 'classy_cleanup_hook', 'Classyads_Setup::classy_cleanup' ); // This is our custom daily cleanup hook.
    }

    private function define_ajax_listeners() {
        $classyads_ajax = new Classyads_Ajax();

        // NEW AD SUBMISSION
        $this->loader->add_action('wp_ajax_create_classyad',  $classyads_ajax, 'create_classyad');

        // AD UPDATES
        $this->loader->add_action('wp_ajax_update_classyad',  $classyads_ajax, 'update_classyad');

        // UGC AJAX EDITING SCRIPTS...
        $this->loader->add_action('wp_ajax_update_classy_mainphoto', $classyads_ajax,  'update_classy_mainphoto');

        // OPEN TO ALL USERS: AJAX FILTERING
        $this->loader->add_action('wp_ajax_refresh_classy_list',  $classyads_ajax, 'refresh_classy_list');
        $this->loader->add_action('wp_ajax_nopriv_refresh_classy_list',  $classyads_ajax, 'refresh_classy_list');
    }

	private function define_admin_hooks() {
		$classyads_admin = new Classyads_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $classyads_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $classyads_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_footer', $classyads_admin, 'localize_footer_scripts', 10); // Call this seperately, so that it grabs an updated version of localize_vars

        $this->loader->add_action( 'admin_menu', $classyads_admin, 'add_plugin_admin_menu' ); // Add menu item
        $this->loader->add_action('admin_init', $classyads_admin, 'options_update'); // Options Update

        // Add Settings link to the plugin
        $classyads_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
        $this->loader->add_filter( 'plugin_action_links_' . $classyads_basename, $classyads_admin, 'add_action_links' );

        $this->loader->add_action('admin_head-post.php', $classyads_admin,'add_post_status_list'); // Add Expired to Drop Down... called this in the head so that the vars are available to add to the scripts below.
        $this->loader->add_filter( 'display_post_states', $classyads_admin,'display_expired_state' ); // Add Post Status to Classy List.
        $this->loader->add_action('acf/save_post', $classyads_admin,'handle_acf_saved', 20);

    }

	private function define_public_hooks() {
		$classyads_public = new Classyads_Public( $this->get_plugin_name(), $this->get_version() );

        // SINGLE TEMPLATE FOR CLASSIES
        $this->loader->add_filter('single_template', $classyads_public, 'define_classy_template');

        // WIDGETS
        $this->loader->add_action('widgets_init', $classyads_public, 'register_classyads_widgets');

        // ADD CLASSIES INTO MY-ACCOUNT PAGE
        $this->loader->add_filter('jzugc_my_account_sections', $classyads_public, 'add_classy_my_account');
    }

    private function define_import_hooks() {
        $classyads_import = new Classyads_Import( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action('wp_ajax_add_classy_from_lasso', $classyads_import, 'add_classy_from_lasso');
        $this->loader->add_action('wp_ajax_nopriv_add_classy_from_lasso', $classyads_import, 'add_classy_from_lasso');

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
