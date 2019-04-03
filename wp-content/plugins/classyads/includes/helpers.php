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
            $output .= "    <div class='title'><a href='". get_the_permalink() ."'>" . get_field('boat_length') . "' " . get_field('boat_model') . ", " . get_field('boat_year') . "</a></div>";
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


/*
 *  Returns array of datetime objects about an ad, based on the expiry date in unixtime.
 */
function get_dates_from_expiry($epoch) {

    if(!isset($epoch)) return false;

    $today = new DateTime();
    $day = $today->format('d');


    $expirydate = new DateTime();
    $expirydate->setTimestamp($epoch);

    $cutoff = clone $expirydate;
    $cutoff->setDate($cutoff->format('Y'), ($cutoff->format('m') -1), 15);

    // Initially the renewal deadline is before the next cutoff.
    $renewal_deadline = clone $cutoff;
    $renewal_deadline->modify('+1 month');

    // But if it's already passed, we still want to convince them to renew for the following month.
    if($today > $renewal_deadline) {
        $renewal_deadline = clone $today;
        if(absint($day) < 15) {
            $renewal_deadline->setDate($today->format('Y'), $today->format('m'), 15);
        } else {
            $renewal_deadline->modify('next month');
            $renewal_deadline->modify($renewal_deadline->format('Y') . $renewal_deadline->format('m') . "-15");
        }
    }

    $ad_edition = clone $cutoff;
    $ad_edition->modify('+1 month');
    $ad_edition->modify('first day of ' . $ad_edition->format('F'));

    $next_ad_edition = clone $renewal_deadline;
    $next_ad_edition->modify('+1 month');

    $key_dates = array();
    $key_dates['expiry'] = $expirydate;
    $key_dates['cutoff'] = $cutoff;
    $key_dates['renewal_deadline'] = $renewal_deadline;
    $key_dates['ad_edition'] = $ad_edition;
    $key_dates['next_ad_edition'] = $next_ad_edition;

    return $key_dates;
}



function classyads_get_plan_amount($plan) {
    global $classyads_config;
    $plan = $classyads_config['plans'][$plan];
    return $plan['amount'];
}
