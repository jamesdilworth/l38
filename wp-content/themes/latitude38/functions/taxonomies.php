<?php

// MODIFY CATEGORIES
function modify_categories(){
    $labels = array(
        'name'				=> _x( 'Topics', 'taxonomy general name' ),
        'singular_name'		=> _x( 'Topics', 'taxonomy singular name' ),
        'search_items'		=>  __( 'Topics' ),
        'all_items'			=> __( 'All Topics' ),
        'parent_item'		=> __( 'Group' ),
        'parent_item_colon'	=> __( 'Group:' ),
        'edit_item'			=> __( 'Related Topics' ),
        'update_item'		=> __( 'Update Topic' ),
        'add_new_item'		=> __( 'Add New Topic' ),
        'new_item_name'		=> __( 'New Topic' ),
        'menu_name'			=> __( 'Topics' )
    );

    register_taxonomy( 'category', array( 'post'), array(
        'hierarchical'	=> true,
        'labels'		=> $labels,
        'publicly_queryable' => true,
        'show_ui'		=> true,
        'query_var'		=> true,
        'description'   => 'Please select no more than two or three topics. Choose the most specific.'
    ));
}
add_action( 'init', 'modify_categories', 0 );

// Regions
function define_regions() {
    $labels = array(
        'name'				=> _x( 'Regions', 'taxonomy general name' ),
        'singular_name'		=> _x( 'Region', 'taxonomy singular name' ),
        'search_items'		=>  __( 'Search Regions' ),
        'all_items'			=> __( 'All Regions' ),
        'parent_item'		=> __( 'Parent Region' ),
        'parent_item_colon'	=> __( 'Parent Region:' ),
        'edit_item'			=> __( 'Edit Region' ),
        'update_item'		=> __( 'Update Region' ),
        'add_new_item'		=> __( 'Add New Region' ),
        'new_item_name'		=> __( 'New Region' ),
        'menu_name'			=> __( 'Regions' )
    );

    register_taxonomy( 'region', array( 'post' ), array(
        'hierarchical'	=> true,
        'labels'		=> $labels,
        'show_ui'		=> true,
        'query_var'		=> true,
        'rewrite'		=> array(
            'slug'			=> 'region',
            'with_front'	=> true
        )
    ));
}
add_action( 'init', 'define_regions', 0 );

// Magazine Types
function define_magtypes() {
    $labels = array(
        'name'				=> _x( 'Types', 'taxonomy general name' ),
        'singular_name'		=> _x( 'Type', 'taxonomy singular name' ),
        'search_items'		=>  __( 'Search Types' ),
        'all_items'			=> __( 'All Types' ),
        'parent_item'		=> __( 'Parent Type' ),
        'parent_item_colon'	=> __( 'Parent Type:' ),
        'edit_item'			=> __( 'Edit Type' ),
        'update_item'		=> __( 'Update Type' ),
        'add_new_item'		=> __( 'Add New Type' ),
        'new_item_name'		=> __( 'New Type' ),
        'menu_name'			=> __( 'Types' )
    );

    register_taxonomy( 'magtype', array( 'magazine' ), array(
        'public'        => false,
        'hierarchical'	=> true,
        'labels'		=> $labels,
        'show_ui'	    => true,
        'show_in_quick_edit' => true,
        'query_var'		=> true,
        'rewrite'		=> array(
            'slug'			=> 'type',
            'with_front'	=> true
        )
    ));
}
add_action( 'init', 'define_magtypes', 0 );

// Boat Types... example Santana 22, J/105
function define_boattypes() {
    $labels = array(
        'name'				=> _x( 'Boat Types', 'taxonomy general name' ),
        'singular_name'		=> _x( 'Boat Type', 'taxonomy singular name' ),
        'search_items'		=>  __( 'Search Boats' ),
        'all_items'			=> __( 'All Boat Types' ),
        'edit_item'			=> __( 'Edit Boat Type' ),
        'update_item'		=> __( 'Update Boat Type' ),
        'add_new_item'		=> __( 'Add New Boat Type' ),
        'new_item_name'		=> __( 'New Boat Type' ),
        'menu_name'			=> __( 'Types' )
    );

    register_taxonomy( 'boattype', array('post'), array(
        'hierarchical'	=> false,
        'labels'		=> $labels,
        'show_ui'	    => true,
        'show_in_quick_edit' => true,
        'query_var'		=> true,
        'rewrite'		=> array(
            'slug'			=> 'boat-type',
            'with_front'	=> true
        )
    ));
}
add_action( 'init', 'define_boattypes', 0 );

// Ad Category... example Schooner, Motoryacht, etc.
function define_adcats() {
    $labels = array(
        'name'				=> _x( 'Ad Cats', 'taxonomy general name' ),
        'singular_name'		=> _x( 'Ad Cat', 'taxonomy singular name' ),
        'search_items'		=>  __( 'Search Boats' ),
        'all_items'			=> __( 'All Ad Types' ),
        'edit_item'			=> __( 'Edit Ad Type' ),
        'update_item'		=> __( 'Update Ad Type' ),
        'add_new_item'		=> __( 'Add New Ad Type' ),
        'new_item_name'		=> __( 'New Ad Type' ),
        'menu_name'			=> __( 'Ad Types' )
    );

    register_taxonomy( 'adcat', array('classy'), array(
        'hierarchical'	=> true,
        'labels'		=> $labels,
        'capabilities' => array(
            'assign_terms' => 'read',
        ),
        // This allows anyone with 'read' to pull from wp_terms_checklist, but they still need to be logged in?!?!?!
        // Added to make WP_terms checklist work for non-logged in users. https://stackoverflow.com/questions/36164916/wp-terms-checklist-checkboxes-are-disabled-in-the-subscriber-profile
        'show_ui'	    => true,
        'show_in_quick_edit' => true,
        'query_var'		=> true,
        'rewrite'		=> array(
            'slug'			=> 'adtype',
            'with_front'	=> true
        )
    ));
}
add_action( 'init', 'define_adcats', 0 );

