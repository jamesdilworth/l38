<?php

if (!current_user_can('edit_posts')) {
    add_filter('show_admin_bar', '__return_false');
}

//
function s4o_body_classes( $classes ) {
    $insider = get_query_var( 'insider', 0 );
    if($insider)
        $classes[] = 'insider';

    $path_array = array_filter(explode('/', $_SERVER['REQUEST_URI']));
    $length = count($path_array);
    for ($i = 1; $i <= $length; $i++) {
        if($i < $length) {
            $classes[] = 'path-' . $path_array[$i];
        } else {
            $classes[] = 'page-' . $path_array[$i];
        }
    }
    return $classes;
}
add_filter( 'body_class','s4o_body_classes' );


/* LOGIN PAGE FILTERS */
function kin_login_logo_url() { return get_bloginfo( 'url' ); }
add_filter( 'login_headerurl', 'kin_login_logo_url' );

/* Change Title */
function kin_login_logo_url_title() { return 'Kinetica : Community & Forums'; }
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

/* Adding custom query variables... */
add_filter('query_vars', function ($qvars) {
    $qvars[] = 'start_date'; // Used for the lectronic stories widget.
    return $qvars;
});

/* Customize the caption shortcode to output copyrights, etc. */
function L38_img_caption_shortcode( $a , $attr, $content = null) {

    $atts = shortcode_atts( array(
        'id'      => '',
        'align'   => 'alignnone',
        'width'   => '',
        'caption' => '',
        'class'   => '',
    ), $attr, 'caption' );

    // We need to pull the image info.
    // 1. Pull the ID out of the id tag.
    // 2. If the ID is successful, look up the image details... get the copy, the kit and caboodle.
    // 3. Print out the custom formatted figcaption.
    // 4. Else go through it as normal.

    global $post;
    $year = date('Y', strtotime($post->post_date));

    $id = substr($atts['id'], 11 ); // strips out the leading attachment_

    if(is_numeric($id)) {
        $source = get_field('source', $id);
        $credit = get_field('credit', $id);
        $external_link = get_field('external_link', $id);
    }

    $atts['width'] = (int) $atts['width'];
    if ( $atts['width'] < 1 || empty( $atts['caption'] ) )
        return $content;

    if ( ! empty( $atts['id'] ) )
        $atts['id'] = 'id="' . esc_attr( sanitize_html_class( $atts['id'] ) ) . '" ';

    $class = trim( 'wp-caption ' . $atts['align'] . ' ' . $atts['class'] );

    $html5 = current_theme_supports( 'html5', 'caption' );
    // HTML5 captions never added the extra 10px to the image width
    $width = $html5 ? $atts['width'] : ( 10 + $atts['width'] );

    /**
     * Filters the width of an image's caption.
     *
     * By default, the caption is 10 pixels greater than the width of the image,
     * to prevent post content from running up against a floated image.
     *
     * @since 3.7.0
     *
     * @see img_caption_shortcode()
     *
     * @param int    $width    Width of the caption in pixels. To remove this inline style,
     *                         return zero.
     * @param array  $atts     Attributes of the caption shortcode.
     * @param string $content  The image element, possibly wrapped in a hyperlink.
     */
    $caption_width = apply_filters( 'img_caption_shortcode_width', $width, $atts, $content );

    $style = '';
    if ( $caption_width ) {
        $style = '';
    }

    if ( $html5 ) {
        $html = '<figure ' . $atts['id'] . $style . 'class="' . esc_attr( $class ) . '">';
        if(!empty($external_link))
            $html .= "<a href='$external_link' target='_blank'>";
        $html .= do_shortcode( $content );
        if(!empty($external_link))
            $html .= "</a>";
        $html .= '<figcaption class="wp-caption-text">' . $atts['caption'];
        if(!empty($source)) {
            if ($source == 'external') {
                $html .= "<div class='source'>&copy $year $credit</div>";
            } elseif ($source == 'latitude38') {
                $html .= "<div class='source'>&copy $year Latitude38</div>";
            }
            if (!empty($external_link)) {
                $html .= "<div class='external_link'><a href='$external_link' target='_blank'>$external_link</a></div>";
            }
            if (current_user_can('edit_posts'))
                $html .= "<div class='edit_link'><a href='" . get_edit_post_link($id) . "'>Edit Image</a></div>";
        }
        $html .= "</figcaption>";
        $html .= "</figure>";
    } else {
        // Basic caption with no history.
        $html = '<div ' . $atts['id'] . $style . 'class="' . esc_attr( $class ) . '">'
            . do_shortcode( $content ) . '<p class="wp-caption-text">' . $atts['caption'] . '</p></div>';
    }

    return $html;
}
//Add the filter to override the standard shortcode
add_filter( 'img_caption_shortcode', 'L38_img_caption_shortcode', 10, 3 );


/* Add a filter for the nav-menu to build out an autogenerated submenu.
 * This is a nice example of using the DOM library to filter and parse HTML. There are other libs that look easier, but this one is native to PHP. - More info here... http://htmlparsing.com/php.html
 * Seemed like a good idea to build off the Wordpress menu system, but it's just too cumbersome. Instead, How about a different submenu for features, and we'll automate the rest.

add_filter('wp_nav_menu_main-menu_items', function($items, $args) {
    $menu_dom = new DOMDocument;
    $menu_dom->loadHTML($items);

    $frag = $menu_dom->createDocumentFragment();
    $frag_html = '<ul class="sub-menu"><li id="menu-item-1579" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-1579"><a href="/events/">Test</a><ul class="sub-menu"><li id="menu-item-1580" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-580"><a href="http://www.baja-haha.com/">Test</a></li><li id="menu-item-1582" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-582"><a href="http://www.deltadoodah.com/">Test</a></li><li id="menu-item-1581" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-581"><a href="http://www.socaltata.com/">Test</a></li><li id="menu-item-1583" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-583"><a href="http://www.pacificpuddlejump.com/">Test</a></li></ul></li></ul>';
    $frag->appendXML($frag_html);

    $lectronic_menu = $menu_dom->getElementById('menu-item-73');
    $lectronic_menu->appendChild($frag);

    $items = $menu_dom->saveHTML();
    return $items;
}, 10, 2);

*/
