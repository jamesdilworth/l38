<?php

/* Additions to the header */
function JZ_Custom_Header() {
    // INSERT TEMPLATE NAME FOR REFERENCE
    echo "\n<!-- Template is : " .  get_current_template() . " -->\n";
}

/* Additions to the footer */
function JZ_Custom_Footer() {

    // This makes it easy to add custom JS into the page from ACF.
    if(get_field('js_include')) {
        echo "<!--Start Page-specific footer code - Set per page in the ACF field, 'footer_code' -->\n\r";
        echo "<script type='text/javascript' language='javascript'>";
        echo get_field('js_include');
        echo "</script>";
        echo "\n\r<!--/End Page-specific footer code -->";
    }
}
add_action( 'wp_footer', 'JZ_Custom_Footer', 100 );

// Add a custom class to each page... makes it easier to style.
function JZ_body_classes( $classes ) {
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

// Alter the layout of the Private Titles
function JZ_alter_private_in_titles( $format ) {
    return "%s <span class='private_notice'>Private</span>";
}

// This powers the get_current_template() function in helpers.php
function JZ_var_template_include( $t ){
    $GLOBALS['current_theme_template'] = basename($t);
    return $t;
}