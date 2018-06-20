<?php


add_action( 'admin_bar_menu', 'S4O_remove_items', 999 );
function S4O_remove_items( $wp_admin_bar ) {
    $wp_admin_bar->remove_node( 'wp-logo' );
    $wp_admin_bar->remove_node( 'customize' );
}

/* Suppress WYSIWYG editor for Tutorials... it can mess with the code examples.  */
add_filter('user_can_richedit', 'disable_wyswyg_for_custom_post_type');
function disable_wyswyg_for_custom_post_type( $default ){
    if( get_post_type() === 'tutorial') return false;
    return $default;
}

/* Add CSS for the Login Page */
add_action('login_head', 's4o_custom_login_style');
function s4o_custom_login_style() {
    echo '<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />';
    echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('stylesheet_directory') . '/css/admin/login.css" />';
}

/* Additions to the header */
function s4o_custom_header() {
    // INJECTION OF CUSTOM CSS IS NOW IN /classes/class-fl-child-theme.php

    // INSERT TEMPLATE NAME FOR REFERENCE
    echo "\n<!-- Template is : " .  get_current_template() . " -->\n";

}
add_action( 'wp_head', 's4o_custom_header' );

/* Additions to the footer */
function s4o_custom_footer() {

    if(get_field('js_include')) {
        echo "<!--Start Page-specific footer code - Set per page in the ACF field, 'footer_code' -->\n\r";
        echo "<script type='text/javascript' language='javascript'>";
        echo get_field('js_include');
        echo "</script>";
        echo "\n\r<!--/End Page-specific footer code -->";
    }
}
add_action( 'wp_footer', 's4o_custom_footer', 100 );

function s4o_body_classes( $classes ) {
    $insider = get_query_var( 'insider', 0 );
    if($insider)
       $classes[] = 'insider';

    $path_array = array_filter(explode('/', $_SERVER['REQUEST_URI']));
    $length = count($path_array);
    for ($i = 1; $i <= $length; $i++) {
        if($i < $length) {
            $classes[] = 'path-' . $path_array[$i];
        } else {
            $classes[] = 'page-' . $path_array[$i];
        }
    }
    return $classes;
}
add_filter( 'body_class','s4o_body_classes' );


