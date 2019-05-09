<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Jzugc_
 * @subpackage Jzugc/public
 * @author     James Dilworth <james@jd.com>
 */
class Jzugc_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles() {
	    // wp_enqueue_style( 'filepond', 'https://unpkg.com/filepond/dist/filepond.css', array());
		wp_enqueue_style( $this->plugin_name, JZUGC_URL . 'public/css/jzugc.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
	    // Filepond allows us to better handle image uploads.
        // wp_enqueue_style( 'filepond', 'https://unpkg.com/filepond/dist/filepond.css', array());
        // wp_enqueue_script( 'filepond', 'https://unpkg.com/filepond/dist/filepond.js', array(), $this->version, true );
        wp_enqueue_script( 'jzugc', JZUGC_URL . 'public/js/jzugc.js', array( 'jquery' ), $this->version, true );

        // Toast is a whole class of plugins that handle notifications.... I'm using this one with the classyads, but might not be the best way forward.
        wp_enqueue_script( 'jquery-toast', JZUGC_URL . 'public/js/jquery.toast.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'jquery-validate', JZUGC_URL . 'public/js/jquery.validate.min.js', array( 'jquery' ), '1.19', true );
        wp_enqueue_script( 'jquery-steps', JZUGC_URL . 'public/js/jquery.steps.min.js', array( 'jquery' ), '1.1', true );
        wp_enqueue_script( 'jquery-simplycountable', JZUGC_URL . 'public/js/jquery.simplyCountable.js', array( 'jquery' ), '0.4.2', true );

	}

	public function init_jzugc_public() {

	    add_shortcode('jzugc-login-menu', array( $this, 'createLoginMenu'));

        if (!is_user_logged_in()) {
            wp_enqueue_script( 'ugc', JZUGC_URL. 'public/js/jzugc.js', array(), filemtime( JZUGC_PATH . 'public/js/jzugc.js'), true ); // load scripts in footer

            /*
            wp_localize_script( 'ajax-login-script', 'ajax_login_object', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'redirecturl' => home_url(),
                'loadingmessage' => __('Sending user info, please wait...')
            ));
            */

            // Enable the user with no privileges to run ajax_login() in AJAX
            add_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );
        }
    }

    public function define_jzugc_templates($template) {

        if (is_page('my-account')) {
            return JZUGC_PATH . 'public/templates/page-my-account.php';
        } else if(is_page('edit-profile')) {
            return JZUGC_PATH . 'public/templates/page-edit-profile.php';
        }
        return $template;
    }


    // Called by Shortcode to build the login menu in the toolbar.
    public function createLoginMenu($atts) {

        ob_start();
        include( JZUGC_PATH . 'public/partials/jzugc-login-menu.php');
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /* Change wp registration url  */
    function set_register_url($link){
        return str_replace(site_url('wp-login.php?action=register', 'login'),site_url('register'),$link);
    }

    /* Redirect users on login based on user role  */
    function login_redirect( $url, $request, $user ){
        if( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
            if( $user->has_cap( 'edit_pages' ) ) {
                // TODO redirect to $request url if requested.
                $url = admin_url();
            } else {
                if(stripos($request,'wp-admin'))
                    $url = '/my-account/';
                else
                    $url = $request;
            }
        }
        return $url;
    }
}
