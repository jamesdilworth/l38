<?php

function S4O_add_site_scripts() {

    // Enqueue Scripts
     wp_enqueue_script( 'pre-scripts', get_stylesheet_directory_uri(). '/js/pre-scripts.js', array('jquery'), filemtime( FL_CHILD_THEME_DIR . '/js/pre-scripts.js'), false ); // Early scripts for header.
    wp_enqueue_script( 'plugins', get_stylesheet_directory_uri().'/js/plugins.js', array('jquery','fl-automator'), filemtime( FL_CHILD_THEME_DIR . '/js/plugins.js'), true ); // load plugins in footer
    wp_enqueue_script( 'scripts', get_stylesheet_directory_uri(). '/js/scripts.js', array('plugins'), filemtime( FL_CHILD_THEME_DIR . '/js/scripts.js'), true ); // load scripts in footer

    if(is_front_page()) {
        // wp_enqueue_script( 'particles',  get_stylesheet_directory_uri(). '/js/particles.js', array(), filemtime(FL_CHILD_THEME_DIR . '/js/particles.js'), true ); // ParticlesJS
    }
    if(is_page()) {
        // BB only loads waypoints if a module has animation. To enable non bb waypoints on pages, we have to call this sepaarately.
        wp_enqueue_script('jquery-waypoints', '/wp-content/plugins/bb-plugin/js/jquery.waypoints.min.js', array('jquery'), filemtime(ABSPATH . 'wp-content/plugins/bb-plugin/js/jquery.waypoints.min.js'), true);
    }
    // Hubspot Forms
    // wp_enqueue_script( 'hubspot-forms',  '//js.hsforms.net/forms/v2.js', array(), '', false );
    
    // Google Fonts
    // wp_enqueue_style( 'roboto slab', '//fonts.googleapis.com/css?family=Roboto+Slab:400,300', array(), '400300' );

    // Stylesheets included from /classes/class-fl-child-theme.php
}
add_action( 'wp_enqueue_scripts', 'S4O_add_site_scripts' );