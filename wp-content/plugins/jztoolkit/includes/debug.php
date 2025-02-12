<?php
/* Produces a dump on the state of WordPress when a not found error occurs
 * useful when debugging permalink issues, rewrite rule trouble, place inside functions.php
*/

if (current_user_can('manage_options')) {

    ini_set( 'error_reporting', -1 );
    ini_set( 'display_errors', 'On' );

    add_action( 'parse_request', 'debug_404_rewrite_dump' );
	add_action( 'template_redirect', 'debug_404_template_redirect', 99999 );
	add_filter ('template_include', 'debug_404_template_dump' );
}

function debug_404_template_dump( $template ) {
    echo '<pre>';
    echo '<h2>Template file selected</h2>';
    echo var_export( $template, true );
    echo '</pre>';
    exit();
}

function debug_404_template_redirect() {
    global $wp_filter;
    echo '<pre>';
    echo '<h2>Template redirect filters</h2>';
    echo var_export( $wp_filter[current_filter()], true );
    echo '</pre>';
}


function debug_404_rewrite_dump( &$wp ) {
    global $wp_rewrite;

    echo '<pre>';
    echo '<h2>Rewrite rules</h2>';
    echo var_export( $wp_rewrite->wp_rewrite_rules(), true );

    echo '<h2>permalink structure</h2>';
    echo var_export( $wp_rewrite->permalink_structure, true );

    echo '<h2>page permastruct</h2>';
    echo var_export( $wp_rewrite->get_page_permastruct(), true );

    echo '<h2>matched rule and query</h2>';
    echo var_export( $wp->matched_rule, true );

    echo '<h2>matched query</h2>';
    echo var_export( $wp->matched_query, true );

    echo '<h2>request</h2>';
    echo var_export( $wp->request, true );

    global $wp_the_query;
    echo '<h2>the query</h2>';
    echo var_export( $wp_the_query, true );
    echo '</pre>';
}
