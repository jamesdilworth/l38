<?php

$ugc_validation = false;
$ugc_updated = false;

/* Recheck if user is logged in just to be sure, this should have been done already */
if( !is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}

while ( $_SERVER['REQUEST_METHOD'] == 'POST' && !empty( $_POST['action'] ) && $_POST['action'] == 'update-user' ) {
    $current_user = wp_get_current_user();
    /* Check nonce first to see if this is a legit request */
    if( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'update-user' ) ) {
        $ugc_validation = "unknown";
        break;
    }
    /* Check honeypot for autmated requests */
    if( !empty($_POST['honey-name']) ) {
        $ugc_validation = "unknown";
        break;
    }
    /* Update profile fields */
    if ( !empty( $_POST['email'] ) ){
        $posted_email = esc_attr( $_POST['email'] );
        if ( !is_email( $posted_email ) ) {
            $ugc_validation = "emailnotvalid";
            break;
        } elseif( email_exists( $posted_email ) && ( email_exists( $posted_email ) != $current_user->ID ) ) {
            $ugc_validation = "emailexists";
            break;
        } else{
            wp_update_user( array ('ID' => $current_user->ID, 'user_email' => $posted_email ) );
        }
    }
    if ( !empty($_POST['pass1'] ) || !empty( $_POST['pass2'] ) ) {
        if ( $_POST['pass1'] == $_POST['pass2'] ) {
            wp_update_user( array( 'ID' => $current_user->ID, 'user_pass' => esc_attr( $_POST['pass1'] ) ) );
        }
        else {
            $ugc_validation = "passwordmismatch";
            break;
        }
    }
    if ( !empty( $_POST['first-name'] ) ) {
        $display_name = esc_attr( $_POST['first-name'] );
        update_user_meta( $current_user->ID, 'first_name', esc_attr( $_POST['first-name'] ) );
    }
    if ( !empty( $_POST['last-name'] ) ) {
        $display_name .= ' ' . esc_attr( $_POST['last-name'] );
        update_user_meta( $current_user->ID, 'last_name', esc_attr( $_POST['last-name'] ) );
    }
    if ( $display_name ) {
        wp_update_user( array ('ID' => $current_user->ID, 'display_name' => esc_attr( $display_name ) ) );
    }
    if ( !empty( $_POST['phone'] ) ) {
        update_user_meta( $current_user->ID, 'phone', esc_attr( $_POST['phone'] ) );
    }
    if ( !empty( $_POST['user_location'] ) ) {
        update_user_meta( $current_user->ID, 'user_location', esc_attr( $_POST['user_location'] ) );
    }
    /* Let plugins hook in, like ACF who is handling the profile picture all by itself. Got to love the Elliot */
    do_action('edit_user_profile_update', $current_user->ID);

    /* We got here, assuming everything went OK */
    $ugc_updated=true;
    break;
}