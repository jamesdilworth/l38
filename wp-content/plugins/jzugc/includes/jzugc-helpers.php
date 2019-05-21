<?php

/**
 * Automatically check and generate a username.
 *
 * @param $username - preferred username. You might make this up from a first . last.
 */
function JZUGC_suggest_username($username) {

    if ( empty( $username ) ) {
        return $username;
    }

    if ( ! function_exists( 'username_exists' ) ) {
        require_once( ABSPATH . WPINC . '/registration.php' );
    }

    if ( username_exists( $username ) ) {
        $i = 2;
        while ( username_exists( $username . $i ) ) {
            $i++;
        }
        $username = $username . $i;
    };

    return $username;
}

/* Check if a slug exists.. used in activation before we create a page. */
function JZUGC_slug_exists($post_name) {
    global $wpdb;
    if($wpdb->get_row("SELECT post_name FROM wp_posts WHERE post_name = '" . $post_name . "'", 'ARRAY_A')) {
        return true;
    } else {
        return false;
    }
}

function JZUGC_format_phone($number) {
    // first strip out extraneous formatting.
    $number = preg_replace( "/[^0-9]/", "", $number );
    return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $number). "\n";
}