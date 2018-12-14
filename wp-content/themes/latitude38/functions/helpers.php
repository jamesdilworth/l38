<?php

// Dump $object to error log
function var_error_log( $object=null ){
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log( $contents );        // log contents of the result of var_dump( $object )
}

// Outputs the name of the template file being used for this page.
function get_current_template( $echo = false ) {
    if( !isset( $GLOBALS['current_theme_template'] ) )
        return false;
    if( $echo )
        echo $GLOBALS['current_theme_template'];
    else
        return $GLOBALS['current_theme_template'];
}

// Gets dates for the most recent number of Lectronic Latitudes. By default, the most recent.
// Returns an array of dates?
function days_with_posts($qty = 1, $date = NULL) {
    global $wpdb;

    if(!isset($date)) {
        $date = date('Y-m-d');
    }

    $dayswithposts = $wpdb->get_results("SELECT DISTINCT YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date)
        FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish'
        AND post_date <= '{$date} 23:59:59'
        ORDER BY DATE(post_date) DESC
        LIMIT {$qty} ", ARRAY_N);

    return $dayswithposts;
}

// Assemble lightbox link for opening magazine at a certain page.
function get_pdf_link($core_url, $page) {
    // Style was ..61956248
    // Single Page is 62432579
    $link = ' href="' . $core_url . '/' . $page . '?e=1997181" class="issuu"';
    return $link;
}

// Social Sharing
// Based on code from : https://crunchify.com/how-to-create-social-sharing-button-without-any-plugin-and-script-loading-wordpress-speed-optimization-goal/
function display_social_sharing_buttons() {

    $content = "";

    // Get current page URL
    $crunchifyURL = urlencode(get_permalink());
    $crunchifyTitle = htmlspecialchars(urlencode(html_entity_decode(get_the_title(), ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8');
    $rawTitle = rawurlencode(html_entity_decode(get_the_title()));

    ob_start();
    the_advanced_excerpt('length=12&length_type=words&no_custom=0&finish=sentence&no_shortcode=1&ellipsis=&add_link=0&exclude_tags=p,div,img,b,figure,figcaption,strong,em,i,ul,li,a,ol,h1,h2,h3,h4');
    $excerpt = ob_get_contents();
    ob_end_clean();

    $crunchifyDesc = rawurlencode('A story from Latitude 38:') . '%0A%0A' . rawurlencode(html_entity_decode(strip_tags($excerpt)));

    // $crunchifyTitle = str_replace( ' ', '%20', get_the_title());

    // Get Post Thumbnail for pinterest
    $crunchifyThumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );

    // Construct sharing URL without using any script
    $twitterURL = 'https://twitter.com/intent/tweet?text='.$crunchifyTitle.'&amp;url='.$crunchifyURL;
    $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u='.$crunchifyURL;
    $emailURL = 'mailto:?subject=From%20Latitude%2038: ' . $rawTitle . '&amp;body=' . $crunchifyDesc . '%0A%0A' . $crunchifyURL . rawurlencode('?utm_source=l38_webshare&utm_medium=email');


    // Add sharing button at the end of page/page content
    $content .= '<div class="social-buttons">';
    $content .= '   <span class="intro">SHARE ON</span>';
    $content .= '   <a class="social-link social-twitter" href="'. $twitterURL .'" target="_blank" data-gacategory="Share" data-gatitle="Twitter" data-galabel="' . get_page_uri() . '" ><i class="fa fa-twitter"></i><span class="social-text">Twitter</a>';
    $content .= '   <a class="social-link social-facebook" href="'.$facebookURL.'" target="_blank" data-gacategory="Share" data-gatitle="Facebook" data-galabel="' . get_page_uri() . '" ><i class="fa fa-facebook"></i><span class="social-text">Facebook</a>';
    $content .= '   <a class="social-link social-email" href="' . $emailURL . '" data-gacategory="Share" data-gatitle="Email" data-galabel="' . get_page_uri() . '" ><i class="fa fa-envelope"></i><span class="social-text">Email</a>';
    $content .= '</div>';

    echo $content;

}