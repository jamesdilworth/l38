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

    // Get current page title
    $crunchifyTitle = htmlspecialchars(urlencode(html_entity_decode(get_the_title(), ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8');
    // $crunchifyTitle = str_replace( ' ', '%20', get_the_title());

    // Get Post Thumbnail for pinterest
    $crunchifyThumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );

    // Construct sharing URL without using any script
    $twitterURL = 'https://twitter.com/intent/tweet?text='.$crunchifyTitle.'&amp;url='.$crunchifyURL;
    $facebookURL = 'https://www.facebook.com/sharer/sharer.php?u='.$crunchifyURL;

    // Add sharing button at the end of page/page content
    $content .= '<div class="social-buttons">';
    $content .= '<span class="intro">SHARE ON</span> <a class="social-link social-twitter" href="'. $twitterURL .'" target="_blank"><i class="fa fa-twitter"></i> Twitter</a>';
    $content .= '<a class="social-link social-facebook" href="'.$facebookURL.'" target="_blank"><i class="fa fa-twitter"></i> Facebook</a>';
    $content .= '</div>';

    echo $content;

}