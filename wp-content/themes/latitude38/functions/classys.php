<?php
/* Functions, Filters and Actions related to Classified Ads */

add_action('wp_ajax_update_classy_list', 'update_classy_list');
add_action('wp_ajax_nopriv_update_classy_list', 'update_classy_list');

// AJAX Handler for the Classified Listing
function update_classy_list() {

    if(!empty($_REQUEST['adcat'])) $adcat = $_REQUEST['adcat'];
    $custom = array();
    if($_REQUEST['search']) $custom['search'] = $_REQUEST['search'];
    if($_REQUEST['min_length']) $custom['min_length'] = intval($_REQUEST['min_length']);
    if($_REQUEST['max_length']) $custom['max_length'] = intval($_REQUEST['max_length']);

    if(!empty($adcat)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'adcat',
                'field' => 'id',
                'terms' => $adcat
            )
        );
    }

    if(!empty($custom)) {
        $args['meta_query'] = array();

        if($custom['min_length'] > 0) {
            $args['meta_query'][] = array(
                'key'     => 'boat_length',
                'value'   => $custom['min_length'],
                'compare' => '>='
            );
        }
        if($custom['max_length'] > 0) {
            $args['meta_query'][] = array(
                'key'     => 'boat_length',
                'value'   => $custom['max_length'],
                'compare' => '<='
            );
        }
        if(isset($custom['search'])) {
            $args['meta_query'][] = array(
                'key'     => 'boat_model',
                'value'   => $custom['search'],
                'compare' => 'LIKE'
            );
        }
    }

    $output = get_the_classys($args);

    echo $output;
    wp_die();
}

// Core Function to get the Classified Listings.....
function get_the_classys($instance = array()) {

    $defaults = array(
        'post_type' => 'classy',
        'posts_per_page' => -1
    );
    $args = wp_parse_args((array) $instance, $defaults);

    $output = "";

    $ads = new WP_Query($args);
    if ( $ads->have_posts() ) {
        // Run the loop first, because calls in the loop might change the number of posts in the edition.
        while ($ads->have_posts()) {
            $ads->the_post();

            $img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'medium') : get_bloginfo('stylesheet_directory') .  '/images/default-classy-ad.png';
            $title = get_field('boat_length') . "' " . get_field('boat_model') . ", " . get_field('boat_year');

            $output .= "<div class='ad' style='background-image:url($img)'>";
            $output .= "  <div class='meta'>";
            $output .= "    <div class='title'><a href='". get_the_permalink() ."'>" . get_field('boat_length') . "' " . get_field('boat_model') . ", " . get_field('boat_year') . "</a></div>";
            $output .= "    <div class='price'>" . money_format('%.0n',get_field('ad_asking_price')) . "</div>";
            $output .= "    <div class='location'>" . get_field('boat_location') . "</div>";
            $output .= "</div></div>";
        }
    } else {
        $output = "<div class='no-results'>There are no results that matched your search. Sorry. </div>";
    }
    wp_reset_postdata();

    return $output;

}