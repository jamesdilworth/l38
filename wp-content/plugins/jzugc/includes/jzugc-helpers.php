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