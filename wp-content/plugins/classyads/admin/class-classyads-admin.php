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

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Classyads_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Classyads_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/classyads-admin.css', array(), $this->version, 'all' );
	}


	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Classyads_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Classyads_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/classyads-admin.js', array( 'jquery' ), $this->version, false );

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
