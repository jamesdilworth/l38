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

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.1.0
 * @package    Classyads
 * @subpackage Classyads/includes
 * @author     James D <james@jamesdilworth.com>
 */
class Classyads_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.1.0
	 */
	public static function activate() {

	    // Parts of this plugin rely on common registration components as set up by my UGC Plugin
        if ( ! is_plugin_active( 'jzugc/jzugc.php' )) {
            // Stop activation redirect and show error
            wp_die('Sorry, but this plugin requires Jaymz\'s UGC Toolkit Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
        }
	}
}
