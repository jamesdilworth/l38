<?php

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
    ob_start( '_eae_wpsso_buffer_callback' );
}, WPSSO_HEAD_PRIORITY - 1 );

add_action( 'wp_head', function () {
    ob_end_flush();
}, WPSSO_HEAD_PRIORITY + 1 );

function _eae_wpsso_buffer_callback( $html ) {
    $start = mb_strpos( $html, 'wpsso:mark:begin' );
    $end = mb_strpos( $html, 'wpsso:mark:end' );
    $wpsso = mb_substr( $html, $start, $end - $start );

    return str_replace(
        $wpsso,
        preg_replace_callback(
            '/<script type="application\/ld\+json">(.+?)<\/script>\v/s',
            '_eae_wpsso_json_callback',
            $wpsso
        ),
        $html
    );
}

function _eae_wpsso_json_callback( $matches ) {
    $json = eae_encode_json_recursive(
        json_decode( $matches[ 1 ], true )
    );

    return sprintf(
        '<script type="application/ld+json">%s</script>' . "\n",
        Wpsso::get_instance()->util->json_format( $json )
    );
}
