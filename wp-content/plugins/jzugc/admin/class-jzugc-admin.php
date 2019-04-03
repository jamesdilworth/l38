<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.jamesdilworth.com/
 * @since      0.1.0
 *
 * @package    Jzugc
 * @subpackage Jzugc/admin
 */

class Jzugc_Admin {

	private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/jzugc-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jzugc-admin.js', array( 'jquery' ), $this->version, false );
	}

    /**
     * Override gravatar and use ACF image field as avatar
     * @author Mike Hemberger
     * @link http://thestizmedia.com/acf-pro-simple-local-avatars/
     * @uses ACF Pro image field (tested return value set as Array )
     */

    public function acf_profile_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
        $user = '';

        // Get user by id or email
        if ( is_numeric( $id_or_email ) ) {
            $id   = (int) $id_or_email;
            $user = get_user_by( 'id' , $id );
        } elseif ( is_object( $id_or_email ) ) {
            if ( ! empty( $id_or_email->user_id ) ) {
                $id   = (int) $id_or_email->user_id;
                $user = get_user_by( 'id' , $id );
            }
        } else {
            $user = get_user_by( 'email', $id_or_email );
        }
        if ( ! $user ) {
            return $avatar;
        }
        // Get the user id
        $user_id = $user->ID;
        // Get the file id
        $image_id = get_user_meta($user_id, 'user_avatar', true); // CHANGE TO YOUR FIELD NAME
        // Bail if we don't have a local avatar
        if ( ! $image_id ) {
            return $avatar;
        }
        // Get the file size
        $image_url  = wp_get_attachment_image_src( $image_id, 'thumbnail' ); // Set image size by name
        // Get the file url
        $avatar_url = $image_url[0];
        // Get the img markup
        $avatar = '<img alt="' . $alt . '" src="' . $avatar_url . '" class="avatar avatar-' . $size . '" height="' . $size . '" width="' . $size . '"/>';
        // Return our new avatar
        return $avatar;
    }

    // Add post state to the User Profile and User Account Dashboard pages
    function set_post_states( $post_states, $post ) {
        if( $post->post_name == 'edit-profile' ) {
            $post_states[] = 'Profile edit page';
        }
        if( $post->post_name == 'my-account' ) {
            $post_states[] = 'User Account Dashboard';
        }
        return $post_states;
    }

    //======================================================================
    // Add notice to profile edit page
    //======================================================================
    function add_post_notices() {
        global $post;
        if( isset( $post->post_name ) && ( $post->post_name == 'edit-profile' ) ) {
            /* Add a notice to the edit page */
            add_action( 'edit_form_after_title', array( $this, 'add_profile_page_notice'), 1 );
            /* Remove the WYSIWYG editor */
            remove_post_type_support( 'page', 'editor' );
        }
    }

    function add_profile_page_notice() {
        echo '<div class="notice notice-warning inline"><p>' . __( 'You are currently editing the profile edit page. Do not edit the title or slug of this page!', 'textdomain' ) . '</p></div>';
    }


}
