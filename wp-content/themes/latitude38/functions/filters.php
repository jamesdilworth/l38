<?php

if (!current_user_can('edit_posts')) {
    add_filter('show_admin_bar', '__return_false');
}

/* LOGIN PAGE FILTERS */
function kin_login_logo_url() { return get_bloginfo( 'url' ); }
add_filter( 'login_headerurl', 'kin_login_logo_url' );

/* Change Title */
function kin_login_logo_url_title() { return 'Kinetica : Community & Forums'; }
add_filter( 'login_headertitle', 'kin_login_logo_url_title' );

/* Change Title */
function kin_login_message() { return '<div class="header_title"><i class="fa fa-comments-o" aria-hidden="true"></i> Community & Forums</div>'; }
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

// Alter the layout of the Private Titles
function alter_private_in_titles( $format ) {
    return "%s <span class='private_notice'>Private</span>";
}
add_filter( 'private_title_format',   'alter_private_in_titles' );


// Preprocess WYSIWYG text pasted in from Word, etc.... it filters out everything but what's in the whitelist.
function configure_tinymce($in) {
    $in['paste_preprocess'] = "function(plugin, args){
    // Strip all HTML tags except those we have whitelisted
    var whitelist = 'p,h2,h3,h4,h5,h6,ul,li,ol,table,tr,td,th,tbody,thead,img,a';
    var stripped = jQuery('<div>' + args.content + '</div>');
    var els = stripped.find('*').not(whitelist);
    for (var i = els.length - 1; i >= 0; i--) {
      var e = els[i];
      jQuery(e).replaceWith(e.innerHTML);
    }
    // Strip all class and id attributes
    stripped.find('*').removeAttr('id').removeAttr('class');
    // Return the clean HTML
    args.content = stripped.html();
  }";
    return $in;
}
add_filter('tiny_mce_before_init','configure_tinymce');

//use with 'echo get_current_template();'
add_filter( 'template_include', 'var_template_include', 1000 );
function var_template_include( $t ){
    $GLOBALS['current_theme_template'] = basename($t);
    return $t;
}

// Disable 'checked_ontop' for categories... I don't know why WP would even think feature was a good idea! - J
add_filter( 'wp_terms_checklist_args', 'wpcats_no_top_float', 10, 2 );
function wpcats_no_top_float( $args, $post_id ) {
    if ( isset( $args['taxonomy'] ) ) {
        $args['checked_ontop'] = false;
    }
    return $args;
}

/* Add a class for CSS targeting if the translucent nav is needed. */
add_filter('body_class','browser_body_class');
function browser_body_class($classes = '') {
    $translucent_nav = get_field('translucent_nav');
    $topstrp = get_field('top-strip');

    if($translucent_nav == true || is_home() || is_singular(array('post'))) $classes[] = "translucent-nav";
    return $classes;
}

/**
 * Modify WP_Query to support 'meta_or_tax' argument
 * to use OR between meta- and taxonomy query parts.
 * http://wordpress.stackexchange.com/questions/190011/wp-query-to-show-post-from-a-category-or-custom-field/190018#190018
 */
add_filter( 'posts_where', function( $where, \WP_Query $q )
{
    // Get query vars
    $tax_args    = isset( $q->query_vars['tax_query'] )
        ? $q->query_vars['tax_query']
        : null;
    $meta_args   = isset( $q->query_vars['meta_query'] )
        ? $q->query_vars['meta_query']
        : null;
    $meta_or_tax = isset( $q->query_vars['_meta_or_tax'] )
        ? wp_validate_boolean( $q->query_vars['_meta_or_tax'] )
        : false;

    // Construct the "tax OR meta" query
    if( $meta_or_tax && is_array( $tax_args ) &&  is_array( $meta_args )  )
    {
        global $wpdb;

        // Primary id column
        $field = 'ID';

        // Tax query
        $sql_tax  = get_tax_sql(  $tax_args,  $wpdb->posts, $field );

        // Meta query
        $sql_meta = get_meta_sql( $meta_args, 'post', $wpdb->posts, $field );

        // Modify the 'where' part
        if( isset( $sql_meta['where'] ) && isset( $sql_tax['where'] ) )
        {
            $where  = str_replace(array($sql_meta['where'], $sql_tax['where']), '', $where );
            $where .= sprintf(
                ' AND ( %s OR  %s ) ',
                substr( trim( $sql_meta['where'] ), 4 ),
                substr( trim( $sql_tax['where']  ), 4 )
            );
        }
    }
    return $where;
}, PHP_INT_MAX, 2 );

// Add a 'day' class if we're looking at just one day.
add_filter( 'body_class', function( $classes ) {
    if(is_day()) {
        $classes = array_merge( $classes, array( 'day' ));
    }
    return $classes;
} );

function responsive_wp_caption($x = NULL, $attr, $content) {
    extract( shortcode_atts(
            array(
                'id' => '',
                'align' => 'alignnone',
                'width' => '',
                'caption' => '',
            ),
            $attr
        )
    );

    if ( intval( $width ) < 1 || empty( $caption ) ) {
        return $content;
    }

    $id = $id ? ('id="' . $id . '" ') : '';

    $caption .= do_shortcode( $content );

    return $ret;
}
add_filter( 'img_caption_shortcode', 'responsive_wp_caption', 10, 3 );
