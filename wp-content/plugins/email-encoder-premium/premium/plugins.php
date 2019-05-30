<?php

defined( 'ABSPATH' ) || exit;

$eaeSearchIn = get_option( 'eae_search_in', 'filters' );

if ( $eaeSearchIn === 'output' ) {

    /**
     * WooCommerce
     * https://woocommerce.com/
     */
    if ( class_exists( 'WooCommerce' ) ) {
        add_filter( 'woocommerce_structured_data_product', function ( $markup ) {
            return eae_encode_json_recursive( $markup );
        }, EAE_FILTER_PRIORITY );
    }

    /**
     * Schema Pro
     * https://wpschema.com/
     */
    if ( class_exists( 'BSF_AIOSRS_Pro' ) ) {
        require_once __DIR__ . '/../plugins/schema-pro.php';
    }

    /**
     * WPSSO Schema JSON-LD
     * https://wpsso.com/
     */
    if ( class_exists( 'WpssoJson' ) && defined( 'WPSSO_HEAD_PRIORITY' ) ) {
        require_once __DIR__ . '/../plugins/wpsso.php';
    }

    /**
     * Rank Math SEO
     * https://rankmath.com/wordpress/plugin/seo-suite/
     */
    if ( class_exists( 'RankMath\RichSnippet\JsonLD' ) ) {
        add_filter( 'rank_math/json_ld', function ( $data ) {
            return eae_encode_json_recursive( $data );
        }, EAE_FILTER_PRIORITY );
    }

    /**
     * Thrive Architect
     * https://thrivethemes.com/architect/
     */
    add_action( 'tcb_landing_head', 'eae_buffer', EAE_FILTER_PRIORITY );

    /**
     * Ginger (EU Cookie Law)
     * http://www.ginger-cookielaw.com/
     */
    if ( function_exists( 'ginger_run' ) ) {
        add_filter( 'eae_buffer_action', '__return_false' );
        add_filter( 'final_output', 'eae_buffer_callback', EAE_FILTER_PRIORITY );
    }

}

/**
 * Register plugin filters when full-page scanning is not an option.
 */
if ( $eaeSearchIn === 'filters' ) {

    // Advanced Custom Fields
    add_filter( 'acf/load_value', function ( $value ) {
        return eae_encode_emails( $value );
    }, EAE_FILTER_PRIORITY );

    // Jetpack
    add_filter( 'jetpack_open_graph_tags', function ( $tags ) {
        return array_map( function ( $tag ) {
            return eae_encode_emails( $tag );
        }, $tags );
    }, EAE_FILTER_PRIORITY );

    // Webdados’ Open Graph
    add_filter( 'fb_og_output', function ( $html ) {
        return eae_encode_emails( $html );
    }, 100 );
}
