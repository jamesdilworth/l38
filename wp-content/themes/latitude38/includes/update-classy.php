<?php

$ugc_validation = false;
$ugc_updated = false;

while ( $_SERVER['REQUEST_METHOD'] == 'POST' && !empty( $_POST['action'] )) {
    /* Recheck if user is logged in just to be sure, this should have been done already */
    if( !is_user_logged_in() ) {
        wp_redirect( home_url() );
        exit;
    }

    $current_user = wp_get_current_user();

    if($_POST['action'] == 'update-magad') {

        /* Check nonce first to see if this is a legit request */
        if( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'update-magad' ) ) {
            $ugc_validation = "unknown";
            break;
        }

        if ( !empty( $_POST['ad_mag_text'] ) ) {
            $ad_mag_text = esc_attr( $_POST['ad_mag_text'] );
            update_post_meta( $post->ID, 'ad_mag_text', $ad_mag_text );
        }

        /* We got here, assuming everything went OK */
        $ugc_updated=true;
        break;

    }

    if($_POST['action'] == 'update-classy') {
        /* Check nonce first to see if this is a legit request */
        if( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'update-classy' ) ) {
            $ugc_validation = "unknown";
            break;
        }
        /* Check honeypot for autmated requests */
        if( !empty($_POST['honey-name']) ) {
            $ugc_validation = "unknown";
            break;
        }
        /* Update profile fields */

        if ( !empty( $_POST['main_content'] ) ) {
            $post_content = esc_attr( $_POST['main_content'] ); // Probably want to do some cleaning here as it's a searchable field.
            $post_id = wp_update_post(array( 'ID' => $post->ID, 'post_content' => $post_content ), true);

            if (is_wp_error($post_id)) {
                $errors = $post_id->get_error_messages();
                foreach ($errors as $error) {
                    echo $error;
                    // wp_die();
                }
            }
        }

        // And now the post meta.
        if ( !empty( $_POST['ad_asking_price'] ) ) {
            $ad_asking_price = esc_attr( $_POST['ad_asking_price'] ); // Probably want to do some cleaning here as it's a searchable field.
            $ad_asking_price = preg_replace("/[^0-9\.]/", "", $ad_asking_price);
            update_post_meta( $post->ID, 'ad_asking_price', $ad_asking_price );
        }
        if ( !empty( $_POST['boat_location'] ) ) {
            $boat_location = esc_attr( $_POST['boat_location'] ); // Probably want to do some cleaning here as it's a searchable field.
            update_post_meta( $post->ID, 'boat_location', $boat_location );
        }

        /* Let plugins hook in, like ACF who is handling the profile picture all by itself. Got to love the Elliot */
        // do_action('edit_user_profile_update', $current_user->ID);

        /* We got here, assuming everything went OK */
        $ugc_updated=true;
        break;
    }
    break; // otherwise we get stuck in an infinite loop! lol.
}