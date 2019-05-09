<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       www.jamesdilworth.com
 * @since      0.1.0
 *
 * @package    Classyads
 * @subpackage Classyads/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Classyads
 * @subpackage Classyads/public
 * @author     James D <james@jamesdilworth.com>
 */
class Classyads_Public
{

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name; // The name of the plugin.
        $this->version = $version; // The version... used for enqueuing.
    }

    public static function enqueue_view_scripts() {
        wp_enqueue_style( 'classyads-view', CLASSYADS_URL . 'public/css/classyads-public.css', array(), CLASSYADS_VERSION, 'all');
        wp_enqueue_script( 'classyads-view', CLASSYADS_URL . 'public/js/classyads-view.js', array('jquery'), CLASSYADS_VERSION, true ); // load scripts in footer
    }

    /**
     * Static function as we call this from widgets and page templates?
     */
    public static function enqueue_form_scripts() {
        global $classyads_config;
        // wp_enqueue_style( 'filepond', 'https://unpkg.com/filepond/dist/filepond.css', array());
        // wp_enqueue_style( 'filepond-image-preview', 'https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css', array('filepond'));

        // Enqueue Filepond... an image upload library.
        // wp_enqueue_script( 'filepond-jquery', 'https://unpkg.com/jquery-filepond/filepond.jquery.js', array( 'jquery', 'filepond' ), '0.1.0', true );
        // wp_enqueue_script( 'filepond-image-preview', 'https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js', array(), CLASSYADS_VERSION, true );
        // wp_enqueue_script( 'filepond-plugin-file-encode', 'https://unpkg.com/filepond-plugin-file-encode/dist/filepond-plugin-file-encode.js', array(), CLASSYADS_VERSION, true );
        // wp_enqueue_script( 'filepond', 'https://unpkg.com/filepond/dist/filepond.min.js', array(), CLASSYADS_VERSION, true );

        $import_vars = array(
            'plans' => $classyads_config['plans'],
        );
        wp_enqueue_script( 'classyads-forms', CLASSYADS_URL . 'public/js/classyads-forms.js', array('jzugc'), filemtime( CLASSYADS_PATH . 'public/js/classyads-forms.js'), true ); // load scripts in footer
        wp_localize_script( 'classyads-forms', 'localized', $import_vars );
    }

    public function register_classyads_widgets() {
        // Classy Listings w/filters
        require_once CLASSYADS_PATH . 'public/widgets/class-classyads-listing-widget.php';
        register_widget('classyads_listing_widget');

        // Create New Classy
        require_once CLASSYADS_PATH . 'public/widgets/class-classyads-placead-widget.php';
        register_widget('classyads_placead_widget');
    }

    public function define_classy_template($template) {
        global $post;
        if (isset($post) && $post->post_type == 'classy') {
            return CLASSYADS_PATH . 'public/templates/single-classy.php';
        }
        return $template;
    }

    public function add_classy_my_account($sections) {
        $sections['My Classies'] = CLASSYADS_PATH . 'public/templates/my-account-classy-list.php';
        return $sections;
    }

    public function setup_account_screen() {
        // TODO Set up the screen on the my-account dashboard. This should tie into the dashboard we created with jzugc.


    }


}