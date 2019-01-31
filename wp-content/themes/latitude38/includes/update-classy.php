<?php
if ( $_SERVER['REQUEST_METHOD'] == 'POST' && !empty( $_POST['action'] ) && $_POST['action'] == 'update-classy' ) {
    /* Recheck if user is logged in just to be sure, this should have been done already */
    if( !is_user_logged_in() ) {
        wp_redirect( home_url() );
        exit;
    }

    $current_user = wp_get_current_user();

    /* Check nonce first to see if this is a legit request */
    if( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'update-classy' ) ) {
        wp_redirect( get_permalink() . '?validation=unknown' );
        exit;
    }
    /* Check honeypot for autmated requests */
    if( !empty($_POST['honey-name']) ) {
        wp_redirect( get_permalink() . '?validation=unknown' );
        exit;
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
        update_post_meta( $post->ID, 'ad_asking_price', $ad_asking_price );
    }
    if ( !empty( $_POST['boat_location'] ) ) {
        $boat_location = esc_attr( $_POST['boat_location'] ); // Probably want to do some cleaning here as it's a searchable field.
        update_post_meta( $post->ID, 'boat_location', $boat_location );
    }

    /* Let plugins hook in, like ACF who is handling the profile picture all by itself. Got to love the Elliot */
    // do_action('edit_user_profile_update', $current_user->ID);

    /* We got here, assuming everything went OK */
    wp_redirect( get_permalink() . '?updated=true' );
    exit;
}