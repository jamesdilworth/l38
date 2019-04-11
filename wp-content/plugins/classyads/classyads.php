<?php

/**
 * The plugin bootstrap file.. based apon WP Plugin Boilerplate. Explained well in
 * this article.
 * https://code.tutsplus.com/articles/object-oriented-programming-in-wordpress-building-the-plugin-i--cms-21083
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.jamesdilworth.com
 * @since             0.1.0
 * @package           Classyads
 *
 * @wordpress-plugin
 * Plugin Name:       Latitude 38 Classy Ads
 * Plugin URI:        https://www.latitude38.com/classyads/
 * Description:       Runs the core functions of the Latitude 38 Classified System
 * Version:           0.1.0
 * Author:            James Dilworth
 * Author URI:        https://www.jamesdilworth.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       classyads
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CLASSYADS_VERSION', '0.1.0' );

/**
 * We're constantly calling file paths, so let's set these up now.
 */
define( "CLASSYADS_FILE", __FILE__ );
define( "CLASSYADS_PATH", plugin_dir_path( CLASSYADS_FILE )); // includes trailing slash.
define( "CLASSYADS_URL", plugins_url() . "/" . basename(CLASSYADS_PATH) . "/");

require_once(CLASSYADS_PATH . 'includes/class-classyads-config.php');



/**
 * Comoposer dependencies.
 */

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-classyads-activator.php
 *
 * Reference
 *
 * Plural : Classyads
 * Singular : Classy
 *
 */
function activate_classyads() {
	require_once CLASSYADS_PATH . 'includes/class-classyads-activator.php';
	Classyads_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-classyads-deactivator.php
 */
function deactivate_classyads() {
	require_once CLASSYADS_PATH . 'includes/class-classyads-deactivator.php';
	Classyads_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_classyads' );
register_deactivation_hook( __FILE__, 'deactivate_classyads' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require CLASSYADS_PATH . 'includes/class-classyads.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_classyads() {

	$classyads = new Classyads();
    $classyads->run();

}
run_classyads();

