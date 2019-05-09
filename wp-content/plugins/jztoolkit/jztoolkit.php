<?php
/*
Plugin Name: Jaymz's - Basic Site ToolKit
Description: A Series of Tools to Make Running A Site Easier
Author: James Dilworth
Version: 0.1.0
*/



if( !defined("JZTOOLKIT_FILE") ) {
    define( "JZTOOLKIT_FILE", __FILE__ );
    define( "JZTOOLKIT_PATH", plugin_dir_path( JZTOOLKIT_FILE ) );
    define( "JZTOOLKIT_URL", plugins_url() . "/" . basename(JZTOOLKIT_PATH) ."/");
}

function activate_jztoolkit() {
    // Do nothing
}

function deactivate_jztoolkit() {
    // Do nothing.
}

register_activation_hook( __FILE__, 'activate_jztoolkit' );
register_deactivation_hook( __FILE__, 'deactivate_jztoolkit' );


function jztoolkit_init() {

    include_once JZTOOLKIT_PATH . 'includes/helpers.php';
    include_once JZTOOLKIT_PATH . 'includes/ops.php';
    include_once JZTOOLKIT_PATH . 'includes/public.php';
    include_once JZTOOLKIT_PATH . 'includes/admin.php';
    // include_once JZTOOLKIT_PATH . 'includes/debug.php'; // Uncomment to run in debugging mode.

    // Operational Hooks
    add_filter( 'posts_where', 'JZ_meta_or_tax', PHP_INT_MAX, 2 ); // Add in Meta or Tax Query into WP_Query

    // Public Hooks
    add_action( 'wp_head', 'JZ_Custom_Header' ); // Additional Items into the Header.
    add_action( 'wp_footer', 'JZ_Custom_Footer', 100 ); // Additional Items into the Footer.

    add_filter( 'body_class','JZ_body_classes' ); // Add pagename to the body class.
    add_filter( 'private_title_format',   'JZ_alter_private_in_titles' ); // Add span tags into the private to make it easier to style.
    add_filter( 'template_include', 'JZ_var_template_include', 1000 );

    // Admin Hooks
    add_action( 'admin_bar_menu', 'JZ_remove_items', 999 ); // Removes silly WP logo from Header
    add_action('wp_dashboard_setup', 'JZ_customize_editor_admin' ); // Remove unnecessary widgets from Admin Dashboard for non-admins.

    add_filter( 'tiny_mce_before_init','JZ_configure_tinymce'); // Filter MS Word Copy Paste into WYSIWYG editor.
    add_filter( 'editable_roles', 'JZ_editable_roles' ); // Stop Editors from creating admins or deleting admins
    add_filter( 'map_meta_cap', 'JZ_map_meta_cap', 10, 4 ); // Don't allow editors to edit admin roles.
    add_filter( 'wp_terms_checklist_args', 'JZ_wpcats_no_top_float', 10, 2 ); // Disable 'checked_ontop' for categories...

}
jztoolkit_init();

