<?php

// MODIFY CATEGORIES
function modify_categories(){
    $labels = array(
        'name'				=> _x( 'Related Content', 'taxonomy general name' ),
        'singular_name'		=> _x( 'Related Content', 'taxonomy singular name' ),
        'search_items'		=>  __( 'Related Content' ),
        'all_items'			=> __( 'All Related Content' ),
        'parent_item'		=> __( 'Group' ),
        'parent_item_colon'	=> __( 'Group:' ),
        'edit_item'			=> __( 'Related Pages' ),
        'update_item'		=> __( 'Update Related Content' ),
        'add_new_item'		=> __( 'Add New Related Content' ),
        'new_item_name'		=> __( 'New Related Content' ),
        'menu_name'			=> __( 'Related Content' )
    );

    register_taxonomy( 'category', array( 'post'), array(
        'hierarchical'	=> true,
        'labels'		=> $labels,
        'publicly_queryable' => false,
        'show_ui'		=> true,
        'query_var'		=> true,
        'description'   => 'Please select no more than two or three items. Choose the most specific.'
    ));
}

// Event Series - Roadshows, Partner Breakfasts and the likes!
function define_event_series() {
    $labels = array(
        'name'				=> _x( 'Event Series', 'taxonomy general name' ),
        'singular_name'		=> _x( 'Event Series', 'taxonomy singular name' ),
        'search_items'		=>  __( 'Search Series' ),
        'all_items'			=> __( 'All Series' ),
        'parent_item'		=> __( 'Parent Series' ),
        'parent_item_colon'	=> __( 'Parent Series:' ),
        'edit_item'			=> __( 'Edit Series' ),
        'update_item'		=> __( 'Update Series' ),
        'add_new_item'		=> __( 'Add New Event Series' ),
        'new_item_name'		=> __( 'New Event Series' ),
        'menu_name'			=> __( 'Event Series' )
    );

    register_taxonomy( 'event_series', array( 'event' ), array(
        'hierarchical'	=> true,
        'labels'		=> $labels,
        'show_ui'		=> true,
        'query_var'		=> true,
        'rewrite'		=> array(
            'slug'			=> 'series',
            'with_front'	=> true
        )
    ));
}
add_action( 'init', 'define_event_series', 0 );
