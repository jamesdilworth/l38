<?php

/**
 * Fired during plugin activation
 *
 * @link       www.jamesdilworth.com
 * @since      0.1.0
 *
 * @package    Classyads
 * @subpackage Classyads/includes
 */
$l38_db_version = 1.1;

class Classyads_Activator {

    public function __construct() {

    }

    public static function activate() {
        global $wpdb;

        // Parts of this plugin rely on common registration components as set up by my UGC Plugin
        if ( ! is_plugin_active( 'jzugc/jzugc.php' )) {
            // Stop activation redirect and show error
            wp_die('Sorry, but this plugin requires Jaymz\'s UGC Toolkit Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
        }

        // Create a new hook 'classy_cleanup_hook' and schedule it to run daily.
        if ( ! wp_next_scheduled( 'classy_cleanup_hook' ) ) {
            wp_schedule_event( time(), 'daily', 'classy_cleanup_hook' );
        }

        Classyads_Setup::setupDB();

	}




}
