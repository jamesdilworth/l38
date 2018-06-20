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
function days_with_posts($qty = 1, $date = NULL) {
    global $wpdb;

    if(!isset($date))
        $date = date('Y-m-d');

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
