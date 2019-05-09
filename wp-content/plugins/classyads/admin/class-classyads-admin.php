<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.jamesdilworth.com
 * @since      0.1.0
 *
 * @package    Classyads
 * @subpackage Classyads/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Classyads
 * @subpackage Classyads/admin
 * @author     James D <james@jamesdilworth.com>
 */
class Classyads_Admin {

	private $plugin_name;
	private $version; // This is carried down so that the enqueues can cachebust.
    private $localize_vars;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->localize_vars = array(
		    'is_classy' => false,
		    'is_expired' => false,
            'test' => 'hello'
        );
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, CLASSYADS_URL . 'admin/css/classyads-admin.css', array(), $this->version, 'all' );
	}


	public function enqueue_scripts() {
	    wp_enqueue_script( 'classyads-admin', CLASSYADS_URL . 'admin/js/classyads-admin.js', array( 'jquery' ), filemtime( CLASSYADS_PATH . 'admin/js/classyads-admin.js'), true );
	}

	public function localize_footer_scripts() {
	    wp_localize_script( 'classyads-admin', 'localized',  $this->localize_vars );
    }


    public function add_plugin_admin_menu() {
        /**
         * Add a settings page for this plugin to the Settings menu.  http://codex.wordpress.org/Administration_Menus
         */
        add_options_page(
            'Latitude 38: Classy Ads',
            'L38 Classies',
            'manage_options',
            $this->plugin_name, // slug
            array($this, 'display_plugin_setup_page') // How does this work?
        );
    }

    public function add_action_links( $links ) {
        /**
        *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
        */
        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
        );
        return array_merge(  $settings_link, $links );

    }

    public function add_post_status_list() {
	    /**
         * We add the post status to the admin area with Javascript. Here, we're checking the post status and then
         * passing the variables into the localized version of the Javascript.
         */
        global $post;
        if($post->post_type == 'classy'){
            if($post->post_status == 'expired'){
                $this->localize_vars['is_classy_expired'] = true;
            }
            $this->localize_vars['is_classy_post_page'] = true;
        }
    }

    public function handle_acf_saved($post_id) {
	    // This gets fired after the post is saved through ACF.

        // bail early if no ACF data
        if( empty($_POST['acf']) ) {
            return;
        }

        // If the expired post has an expire date after today, unexpire it.
        /* Actually, this is not really necessary, as it is obvious.
        if(get_post_status($post_id) == 'expired') {
            $date = get_field('ad_expires', $post_id);
            $date = strtotime($date);
            if($date > time()) {
                // PC::debug('Post should no longer be expired');
            }
        }
        */

        // array of field values
        // $fields = $_POST['acf'];

        // specific field value
        // $field = $_POST['acf']['field_abc123'];
    }


    public function display_expired_state( $states ) {
	    /**
         * This is hooked into the main dashboard view of the classies to show the expired state.
         */
        global $post;
        $arg = get_query_var( 'post_status' );
        if($arg != 'expired'){ // if we haven't searched by
            if($post->post_status == 'expired'){
                return array('Expired');
            }
        }
        return $states;
    }

    /**
     * Render the settings page for this plugin.
     */
    public function display_plugin_setup_page() {
        include_once( 'partials/classyads-admin-display.php' );
    }

    public function options_update() {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

    public function validate($input) {
        // All checkboxes inputs
        $valid = array();

        //Cleanup
        $valid['show_on_home'] = (isset($input['show_on_home']) && !empty($input['show_on_home'])) ? 1 : 0;
        $valid['another_option'] = (isset($input['another_option']) && !empty($input['another_option'])) ? 1 : 0;
        $valid['another_text'] = esc_url($input['another_text']);

        return $valid;
    }

}
