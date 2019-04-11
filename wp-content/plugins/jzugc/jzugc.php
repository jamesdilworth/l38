<?php
/*
Plugin Name: Jaymz's User Generated Content Kit
Description: A Series of Pages and Tools to Make User Logins and User Generated Content Easier
Author: James Dilworth
Version: 0.1.0
*/

if( !defined("JZUGC_FILE") ) {
    define( "JZUGC_FILE", __FILE__ );
    define( "JZUGC_PATH", plugin_dir_path( JZUGC_FILE ) );
    define( "JZUGC_URL", plugins_url() . "/" . basename(JZUGC_PATH) ."/");
}

function activate_jzugc() {
    // TODO: Check ACF and JZToolkit are Installed.

    require_once JZUGC_PATH . 'includes/class-jzugc-activator.php';
    Jzugc_Activator::activate();
}

function deactivate_jzugc() {
    require_once JZUGC_PATH . 'includes/class-jzugc-deactivator.php';
    Jzugc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_jzugc' );
register_deactivation_hook( __FILE__, 'deactivate_jzugc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require JZUGC_PATH . 'includes/class-jzugc.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_jzugc() {

    $plugin = new Jzugc();
    $plugin->run();

}
run_jzugc();