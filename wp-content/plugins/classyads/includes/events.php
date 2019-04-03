<?php
/**
 * Expires ads
 *
 * Find Classys that have expired (value in ad_expires meta field is lower then current timestamp) and
 * changes their status to 'expired'.
 *
 * @since 0.1
 * @return void
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// TODO... taken from wpadverts. Needs to be changed to work with Classyads
function classyads_expire_ads() {

    // find adverts with status 'publish' which exceeded expiration date
    // (_expiration_date is a timestamp)
    $posts = new WP_Query( array(
        "post_type" => "advert",
        "post_status" => "publish",
        "meta_query" => array(
            array(
                "key" => "_expiration_date",
                "value" => current_time( 'timestamp' ),
                "compare" => "<="
            )
        )
    ) );

    if( $posts->post_count ) {
        foreach($posts->posts as $post) {
            // change post status to expired.
            $update = wp_update_post( array(
                "ID" => $post->ID,
                "post_status" => "expired"
            ) );
        } // endforeach
    } // endif

}
