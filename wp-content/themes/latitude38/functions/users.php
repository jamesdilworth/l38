<?php
/* Adjustments to make the site work for subscriber logins! */

/* LOGIN PAGE FILTERS... I think this redirects the user back to their original starting point? */
function L38_login_logo_url() { return get_bloginfo( 'url' ); }
add_filter( 'login_headerurl', 'L38_login_logo_url' );

/* Change Title */
function L38_login_logo_url_title() { return 'Latitude 38: Subscribers & Community'; }
add_filter( 'login_headertitle', 'L38_login_logo_url_title' );

/* Change Title */
function L38_login_message() { return '<div class="header_title"><i class="fa fa-sign-in" aria-hidden="true"></i> Login</div>'; }
add_filter( 'login_message', 'L38_login_message' );

// Auto Generates Usernames instead of asking the user to choose one.
add_filter( 'gform_user_registration_username', 'auto_username', 10, 4 );
function auto_username( $username, $feed, $form, $entry ) {
    $username = strtolower( rgar( $entry, '2.3' ) . rgar( $entry, '2.6' ) );
    $username = JZUGC_suggest_username($username); // From JZUGC
    return $username;
}

<?php
/**
 * Override the GF User Activation Template.
 * http://gravitywiz.com/customizing-gravity-forms-user-registration-activation-page
 */
add_action('wp', 'custom_maybe_activate_user', 9);
function custom_maybe_activate_user() {

    $template_path = STYLESHEETPATH . '/activate.php';
    $is_activate_page = isset( $_GET['page'] ) && $_GET['page'] == 'gf_activation';

    if( ! file_exists( $template_path ) || ! $is_activate_page  )
        return;

    require_once( $template_path );

    exit();
}
