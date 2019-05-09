<?php

/**
 * Fired during plugin activation
 */

class Jzugc_Activator {

	/**
	 * Short Description. (use period)
	 * Long Description.
	 *
	 * @since    0.1.0
	 */
	public static function activate() {

        if(!JZUGC_slug_exists('my-account'))  {
            // Create Account Dashboard
            $account_page = wp_insert_post(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => 'My Account',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_content' => "[Do not edit this page]"
            ));
        }

        if(!JZUGC_slug_exists('edit-profile')) {
            // Create Edit Profile
            $profile_page = wp_insert_post(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => 'Edit Profile',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_content' => "[Do not edit this page]"
            ));
        }

        /* Create a log file for payments */
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . '/logs';
        if (! is_dir($upload_dir)) {
            mkdir( $upload_dir, 0700 );
        }

	}
}
