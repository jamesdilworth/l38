<?php

/* Include custom admin css */
function s4o_custom_admin() {
    echo '<link rel="stylesheet" type="text/css"  href="http://netdna.bootstrapcdn.com/font-awesome/latest/css/font-awesome.css">';
    echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('stylesheet_directory') . '/css/admin/wp-admin.css" />';
}
add_action( 'admin_head', 's4o_custom_admin' );

// Filter Yoast Meta Priority to the bottom
add_filter( 'wpseo_metabox_prio', function() { return 'low';});

// Filter enforce strong passwords plugin to not apply to any role... doesn't seem to work!
function modify_enforce_strong_passwords_caps() {
    return array('edit-pages');
}
add_filter( 'slt_fsp_caps_check', 'modify_enforce_strong_passwords_caps', 20 );

/* Registers Editor stylesheet for TinyMCE */
function s4o_add_editor_styles() {
    add_editor_style( 'css/admin/editor-styles.css' );
}
add_action( 'admin_init', 's4o_add_editor_styles' );

/* Let's customize the visual editor a little*/
function s4o_mce_buttons( $buttons ) {
    // array_unshift( $buttons, 'formatselect' );
    return $buttons;
}
add_filter( 'mce_buttons', 's4o_mce_buttons' );

/* Let's customize the visual editor a little*/
function s4o_mce_buttons_2( $buttons ) {
    if(($key = array_search('formatselect', $buttons)) !== false) {
        unset($buttons[$key]);
    }
    return $buttons;
}
add_filter( 'mce_buttons_2', 's4o_mce_buttons_2' );

// Customize mce editor font sizes
function s4o_mce_changes( $initArray ){

    // Add block format elements you want to show in dropdown
    $initArray['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Pre=pre;Code=code';

    return $initArray;
}
add_filter( 'tiny_mce_before_init', 's4o_mce_changes' );

/**
 * Use this as a structure to move some of the menu items around... we're not actually using Yoast GA - JD.
 * Move the main settings page for Yoast Google Analytics from it's own parent menu into a submenu page in either the Yoast SEO menu or in the general settings
 * @return null
 */

function rkv_yoast_ga_menu() {
    // check for the Yoast GA class in the event the plugin has been removed
    if ( ! class_exists( 'Yoast_GA_Options' ) ) {
        return;
    }
    // set the path
    $path   = plugin_dir_path( GAWP_FILE );

    // first remove the top level menu item
    remove_menu_page( 'yst_ga_dashboard' );

    // check for Yoast SEO plugin first and if present, add the item there
    if ( function_exists( 'wpseo_auto_load' ) ) {
        add_submenu_page( 'wpseo_dashboard', __( 'Google Analytics', 'google-analytics-for-wordpress' ), __( 'Google Analytics', 'google-analytics-for-wordpress' ), 'manage_options', 'yst_ga_settings', 'rkv_yoast_ga_page' );
    } else {
        // Yoast SEO not installed, just put it into main settings
        add_options_page( __( 'Yoast Google Analytics', 'google-analytics-for-wordpress' ), __( 'Yoast Google Analytics', 'google-analytics-for-wordpress' ), 'manage_options', 'admin.php?page=yst_ga_settings', 'rkv_yoast_ga_page' );
    }
}

add_action( 'admin_menu', 'rkv_yoast_ga_menu', 2001 );
/**
 * call the files required for the Yoast GA page to
 * render properly
 * @return null
 */
function rkv_yoast_ga_page() {
    // check for the defined file in the event that the plugin
    // has been removed
    if ( ! defined( 'GAWP_FILE' ) ) {
        return;
    }
    // set the path
    $path   = plugin_dir_path( GAWP_FILE );
    // load our two files
    require_once( $path . 'admin/class-admin-ga-js.php' );
    require_once( $path . 'admin/pages/settings.php' );
}


