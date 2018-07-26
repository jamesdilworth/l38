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

// Magazine Sections
function define_magazine_section() {
    $labels = array(
        'name'				=> _x( 'Magazine Sections', 'taxonomy general name' ),
        'singular_name'		=> _x( 'Magazine Section', 'taxonomy singular name' ),
        'search_items'		=>  __( 'Search Sections' ),
        'all_items'			=> __( 'All Sections' ),
        'parent_item'		=> __( 'Parent Section' ),
        'parent_item_colon'	=> __( 'Parent Section:' ),
        'edit_item'			=> __( 'Edit Section' ),
        'update_item'		=> __( 'Update Section' ),
        'add_new_item'		=> __( 'Add New Magazine Section' ),
        'new_item_name'		=> __( 'New Magazine Section' ),
        'menu_name'			=> __( 'Magazine Sections' )
    );

    register_taxonomy( 'magazine_section', array( 'magazine' ), array(
        'hierarchical'	=> true,
        'labels'		=> $labels,
        'show_ui'		=> true,
        'query_var'		=> true,
        'rewrite'		=> array(
            'slug'			=> 'section',
            'with_front'	=> true
        )
    ));
}
add_action( 'init', 'define_magazine_section', 0 );

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
            'slug'			=> 'Region',
            'with_front'	=> true
        )
    ));
}
add_action( 'init', 'define_regions', 0 );
