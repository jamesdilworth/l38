<?php
/* Adjustments to make the site work for subscriber logins! */

/* LOGIN PAGE FILTERS... I think this redirects the user back to their original starting point? */
function kin_login_logo_url() { return get_bloginfo( 'url' ); }
add_filter( 'login_headerurl', 'kin_login_logo_url' );

/* Change Title */
function kin_login_logo_url_title() { return 'Latitude 38: Subscribers & Community'; }
add_filter( 'login_headertitle', 'kin_login_logo_url_title' );

/* Change Title */
function kin_login_message() { return '<div class="header_title"><i class="fa fa-sign-in" aria-hidden="true"></i> Login</div>'; }
add_filter( 'login_message', 'kin_login_message' );

/* Change wp registration url  */
function kin_register_url($link){ return str_replace(site_url('wp-login.php?action=register', 'login'),site_url('register'),$link); }
add_filter('register','kin_register_url');

/* Redirect users on login based on user role  */
function kin_login_redirect( $url, $request, $user ){
    if( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
        if( $user->has_cap( 'edit_pages' ) ) {
            // TODO redirect to $request url if requested.
            $url = admin_url();
        } else {
            $url = $request;
        }
    }
    return $url;
}
add_filter('login_redirect', 'kin_login_redirect', 10, 3 );