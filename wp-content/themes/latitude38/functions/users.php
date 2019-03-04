<?php
/* Adjustments to make the site work for subscriber logins! */

function ajax_login_init(){

    wp_enqueue_script( 'ugc', get_stylesheet_directory_uri(). '/js/ugc.js', array('plugins','scripts'), filemtime( FL_CHILD_THEME_DIR . '/js/ugc.js'), true ); // load scripts in footer

    /*
    wp_localize_script( 'ajax-login-script', 'ajax_login_object', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Sending user info, please wait...')
    ));
    */

    // Enable the user with no privileges to run ajax_login() in AJAX
    add_action( 'wp_ajax_nopriv_ajaxlogin', 'ajax_login' );
}

// Execute the action only if the user isn't logged in
if (!is_user_logged_in()) {
    add_action('init', 'ajax_login_init');
}

function ajax_login(){

    // First check the nonce, if it fails the function will break
    check_ajax_referer( 'ajax-login-nonce', 'security' );

    // Nonce is checked, get the POST data and sign user on
    $info = array();
    $info['user_login'] = $_POST['email'];
    $info['user_password'] = $_POST['password'];
    $info['remember'] = true;

    $user_signon = wp_signon( $info, false );
    if ( is_wp_error($user_signon) ){
        echo json_encode(array('loggedin'=>false, 'message'=>__('Wrong username or password.')));
    } else {
        echo json_encode(array('loggedin'=>true, 'message'=>__('Login successful, redirecting...')));
    }

    die();
}


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
            if(stripos($request,'wp-admin'))
                $url = '/my-account/';
            else
                $url = $request;
        }
    }
    return $url;
}
add_filter('login_redirect', 'kin_login_redirect', 10, 3 );


// Auto Generates Usernames instead of asking the user to choose one.
add_filter( 'gform_user_registration_username', 'auto_username', 10, 4 );
function auto_username( $username, $feed, $form, $entry ) {

    $username = strtolower( rgar( $entry, '2.3' ) . rgar( $entry, '2.6' ) );

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

// Add post state to the projects page
add_filter( 'display_post_states', 'ecs_add_post_state', 10, 2 );
function ecs_add_post_state( $post_states, $post ) {
    if( $post->post_name == 'edit-profile' ) {
        $post_states[] = 'Profile edit page';
    }
    if( $post->post_name == 'my-account' ) {
        $post_states[] = 'User Account Dashboard';
    }
    return $post_states;
}

// Remove editor from the profile page.

//======================================================================
// Add notice to the profile edit page
//======================================================================
add_action( 'admin_notices', 'ecs_add_post_notice' );
function ecs_add_post_notice() {
    global $post;
    if( isset( $post->post_name ) && ( $post->post_name == 'edit-profile' ) ) {
        /* Add a notice to the edit page */
        add_action( 'edit_form_after_title', 'ecs_add_page_notice', 1 );
        /* Remove the WYSIWYG editor */
        remove_post_type_support( 'page', 'editor' );
    }
}
function ecs_add_page_notice() {
    echo '<div class="notice notice-warning inline"><p>' . __( 'You are currently editing the profile edit page. Do not edit the title or slug of this page!', 'textdomain' ) . '</p></div>';
}
