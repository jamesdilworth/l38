<?php

// Magazine
function JZ_create_magazine_post_type()
{
    $labels = array(
        'name' => _x('Magazines', 'post type general name'),
        'singular_name' => _x('Magazine', 'post type singular name'),
        'add_new' => _x('Add New', 'Magazine'),
        'add_new_item' => __('Add New Magazine'),
        'edit_item' => __('Edit Magazine'),
        'new_item' => __('New Magazine'),
        'view_item' => __('View Magazine'),
        'search_items' => __('Search Magazines'),
        'not_found' => __('No items found'),
        'not_found_in_trash' => __('No items found in Trash'),
        'parent_item_colon' => ''
    );
    $supports = array('title', 'thumbnail');

    register_post_type('magazine',
        array(
            'labels' => $labels,
            'public' => true,
            'hierarchical' => false,
            'has_archive' => false,
            'supports' => $supports,
            'rewrite' => array(
                'slug' => 'issues',
                'with_front' => false
            ),
            'menu_icon' => 'dashicons-book'
        )
    );
}
add_action( 'init', 'JZ_create_magazine_post_type' );

function JZ_change_post_label() {
    global $menu;
    global $submenu;
    $menu[5][0] = 'Lectronic';
    $submenu['edit.php'][5][0] = 'Stories';
    $submenu['edit.php'][10][0] = 'Add Story';
    $submenu['edit.php'][16][0] = 'Tags';
}
function JZ_change_post_object() {
    global $wp_post_types;
    $labels = &$wp_post_types['post']->labels;
    $labels->name = 'Lectronic';
    $labels->singular_name = 'Lectronic Story';
    $labels->add_new = 'Add Story';
    $labels->add_new_item = 'Add Story';
    $labels->edit_item = 'Edit Story';
    $labels->new_item = 'News';
    $labels->view_item = 'View Story';
    $labels->search_items = 'Search Story';
    $labels->not_found = 'No Lectronic Stories found';
    $labels->not_found_in_trash = 'No News found in Trash';
    $labels->all_items = 'All Lectronic Stories';
    $labels->menu_name = 'Lectronic';
    $labels->name_admin_bar = 'Lectronic Story';
}

add_action( 'admin_menu', 'JZ_change_post_label' );
add_action( 'init', 'JZ_change_post_object' );


