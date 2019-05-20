<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Create Classified WP Query.
 *
 * @param array $instance - A series of arguments that can start the build of a listing.
 * @return string
 */
function get_the_classys($instance = array()) {

    $defaults = array(
        'post_type' => 'classy',
        'posts_per_page' => 20,
        'post_status' => 'publish',
        'paged' => 1,
        'orderby' => 'modified',
        'order' => 'DESC'
    );
    $args = wp_parse_args((array) $instance, $defaults);

    $output = "";
    $ads = new WP_Query($args);

    if ( $ads->have_posts() ) {
        // $output = "<div class='totals'>" . $ads->max_num_pages . " ads match your search</div>";
        // Run the loop first, because calls in the loop might change the number of posts in the edition.
        while ($ads->have_posts()) {
            $ads->the_post();

            $img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'medium') : get_bloginfo('stylesheet_directory') .  '/images/default-classy-ad.png';
            $title = get_field('boat_length') . "' " . get_field('boat_model') . ", " . get_field('boat_year');

            $output .= "<div class='ad' style='background-image:url($img)'>";
            $output .= "  <div class='meta'>";
            $output .= "    <div class='title'><a href='". get_the_permalink() ."'>" . get_the_title() . "</a></div>";
            $output .= "    <div class='price'>" . money_format('%.0n', (int) get_field('ad_asking_price')) . "</div>";
            $output .= "    <div class='location'>" . get_field('boat_location') . "</div>";
            $output .= "</div></div>";
        }
        if($ads->max_num_pages > 1) {
            $output .= "<a href='' class='more-ads' data-paged='" . (max( 1, $args[ 'paged' ]) + 1) . "'>More...</a>";
        }

    } else {
        $output = "<div class='no-results'>There are no results that matched your search. Sorry. </div>";
    }
    wp_reset_postdata();
    return $output;
}





