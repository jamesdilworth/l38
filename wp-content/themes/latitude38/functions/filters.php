<?php

// We only want the admin bar shown to in-house editors.
if (!current_user_can('edit_others_posts')) {
    add_filter('show_admin_bar', '__return_false');
}

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
function l38_img_caption_shortcode( $a , $attr, $content = null) {

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
        // $html .= '<!-- source is ' . $source . '-->';
        if(!empty($source)) {
            if ($source == 'external') {
                $html .= "<div class='source'>&copy; $year $credit</div>";
            } elseif ($source == 'latitude38') {
                $html .= "<div class='source'>&copy; $year Latitude 38 Media LLC  / $credit</div>";
            }
            if (!empty($external_link)) {
                $html .= "<div class='external_link'><a href='$external_link' target='_blank'>$external_link</a></div>";
            }
            if (current_user_can('edit_posts'))
                $html .= "<div class='edit_link' style='display:block;'><a href='" . get_edit_post_link($id) . "'>Edit Image</a></div>";
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
add_filter( 'img_caption_shortcode', 'l38_img_caption_shortcode', 10, 3 );

/* When a classy ad acf is saved, make sure it is also added to featured image
function acf_set_featured_image( $value, $post_id, $field  ){

    if($value != ''){
        //Add the value which is the image ID to the _thumbnail_id meta data for the current post
        add_post_meta($post_id, '_thumbnail_id', $value);
    }
    return $value;
}

// acf/update_value/name={$field_name} - filter for a specific field based on it's name
add_filter('acf/update_value/name=foo', 'acf_set_featured_image', 10, 3);
*/
