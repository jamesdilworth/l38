<?php

/* Include custom admin css */
function s4o_custom_admin() {
    echo '<link rel="stylesheet" type="text/css"  href="//netdna.bootstrapcdn.com/font-awesome/latest/css/font-awesome.css">';
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
    /*
    if(($key = array_search('formatselect', $buttons)) !== false) {
        unset($buttons[$key]);
    }
    */
    return $buttons;
}
// add_filter( 'mce_buttons', 's4o_mce_buttons' );

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

/* ---------------------------------------------------------------------------
	- Move ACF subtitle field right above the page title if present
--------------------------------------------------------------------------- */
function Move_ACF_Subtitle() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready( function( $ ) {
            var acf_container = $('.acf-field[data-name="alt_header"]');
            if ( acf_container.length > 0 && acf_container.css('display') != 'none') {
                acf_container.addClass('acf-subtitle');
                $("#titlediv").before( acf_container ).show();
            }
        });
    </script>
    <?php
}
add_action( 'admin_head', 'Move_ACF_Subtitle' );

// Hook into the 'wp_dashboard_setup' action to register our function
function customize_editor_admin() {
    if(!current_user_can('activate_plugins')) {
        // Not an admin.
        // remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
        remove_meta_box( 'simple_history_dashboard_widget', 'dashboard', 'side' );
    }
}
add_action('wp_dashboard_setup', 'customize_editor_admin' );

// Remove the Advanced Ads sales page for non-admins.
function advancedads_remove_mainsubpage() {
    if(!current_user_can('manage_options')) {
        $page = remove_submenu_page( 'advanced-ads', 'advanced-ads' );
    }
}
add_action( 'admin_menu', 'advancedads_remove_mainsubpage', 999 );

// Remove specified widgets from the dashboard for non-admins.
function remove_dashboardwidgets() {
    if(!current_user_can('manage_options')) {
        remove_meta_box( 'advads_dashboard_widget', 'dashboard', 'side' );
    }
}
add_action('wp_dashboard_setup', 'remove_dashboardwidgets' );

// Stop Editors from creating admins or deleting admins
class JPB_User_Caps {
    // Add our filters
    function __construct() {
        add_filter( 'editable_roles', array(&$this, 'editable_roles') );
        add_filter( 'map_meta_cap', array(&$this, 'map_meta_cap'), 10, 4 );
    }

    // Remove 'Administrator' from the list of roles if the current user is not an admin
    function editable_roles( $roles ){
        if( isset( $roles['administrator'] ) && !current_user_can('administrator') ){
            unset( $roles['administrator']);
        }
        return $roles;
    }

    // If someone is trying to edit or delete and admin and that user isn't an admin, don't allow it
    function map_meta_cap( $caps, $cap, $user_id, $args ){

        switch( $cap ){
            case 'edit_user':
            case 'remove_user':
            case 'promote_user':
                if( isset($args[0]) && $args[0] == $user_id )
                    break;
                elseif( !isset($args[0]) )
                    $caps[] = 'do_not_allow';
                $other = new WP_User( absint($args[0]) );
                if( $other->has_cap( 'administrator' ) ){
                    if(!current_user_can('administrator')){
                        $caps[] = 'do_not_allow';
                    }
                }
                break;
            case 'delete_user':
            case 'delete_users':
                if( !isset($args[0]) )
                    break;
                $other = new WP_User( absint($args[0]) );
                if( $other->has_cap( 'administrator' ) ){
                    if(!current_user_can('administrator')){
                        $caps[] = 'do_not_allow';
                    }
                }
                break;
            default:
                break;
        }
        return $caps;
    }
}
$jpb_user_caps = new JPB_User_Caps();

