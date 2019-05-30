<?php

defined( 'ABSPATH' ) || exit;

/**
 * Load the plugin's text domain.
 */
add_action( 'plugins_loaded', 'eae_load_textdomain' );

/**
 * Register the plugin's menu item.
 */
add_action( 'admin_menu', 'eae_register_ui' );

/**
 * Register the plugin's setting fields.
 */
add_action( 'admin_init', 'eae_register_settings' );

/**
 * Register the plugin's action links.
 */
add_filter( 'plugin_action_links', 'eae_plugin_actions_links', 10, 2 );

/**
 * Register callback to transmit email address to remote server.
 */
add_action( 'load-settings_page_email-address-encoder', 'eae_transmit_email' );

/**
 * Register callback to clear page caches.
 */
add_action( 'load-options.php', 'eae_clear_caches' );

/**
 * Callback to load the plugin's text domain.
 *
 * @return void
 */
function eae_load_textdomain() {
    load_plugin_textdomain(
        'email-address-encoder',
        false,
        basename( dirname( __FILE__ ) ) . '/languages'
    );
}

/**
 * Callback to add the plugin's menu item to the "Settings" menu.
 *
 * @return void
 */
function eae_register_ui() {
    add_options_page(
        __( 'Email Address Encoder', 'email-address-encoder' ),
        __( 'Email Encoder ✪', 'email-address-encoder' ),
        'manage_options',
        'email-address-encoder',
        'eae_options_page'
    );
}

/**
 * Register the plugin's setting fields.
 *
 * @return void
 */
function eae_register_settings() {
    register_setting( 'email-address-encoder', 'eae_license_key', [
        'type' => 'string',
        'sanitize_callback' => 'eae_sanitize_license_key',
    ] );

    register_setting( 'email-address-encoder', 'eae_search_in', [
        'type' => 'string',
        'default' => 'filters',
        'sanitize_callback' => 'sanitize_text_field',
    ] );

    register_setting( 'email-address-encoder', 'eae_technique', [
        'type' => 'string',
        'default' => 'entities',
        'sanitize_callback' => 'sanitize_text_field',
    ] );

    register_setting( 'email-address-encoder', 'eae_filter_priority', [
        'type' => 'integer',
        'default' => 1000,
        'sanitize_callback' => 'sanitize_text_field',
    ] );
}

/**
 * Callback that displays the plugin's settings interface.
 *
 * @return void
 */
function eae_options_page() {
    include __DIR__ . '/ui.php';
}

/**
 * Callback to add "Settings" link to the plugin's action links.
 *
 * @param array $links
 * @param string $file
 *
 * @return array
 */
function eae_plugin_actions_links( $links, $file ) {
    if ( strpos( $file, 'email-encoder-premium/' ) !== 0 ) {
        return $links;
    }

    return array_merge( [
        sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'options-general.php?page=email-address-encoder' ),
            __( 'Settings', 'email-address-encoder' )
        ),
    ], $links );
}

/**
 * Transmit email address to remote server.
 *
 * @return void
 */
function eae_transmit_email() {
    if (
        empty( $_POST ) ||
        ! isset( $_POST[ 'action' ], $_POST[ 'eae_notify_email' ] ) ||
        $_POST[ 'action' ] !== 'subscribe'
    ) {
        return;
    }

    $host = parse_url( get_home_url(), PHP_URL_HOST );

    if (
        $host === 'localhost' ||
        filter_var( $host, FILTER_VALIDATE_IP ) ||
        preg_match( '/\.(dev|test|local)$/', $host ) ||
        preg_match( '/^(dev|test|staging)\./', $host )
    ) {
        return add_settings_error(
            'eae_notify_email',
            'invalid',
            sprintf( __( 'Sorry, "%s" doesn’t appear to be a production domain.', 'email-address-encoder' ), $host ),
            'error'
        );
    }

    check_admin_referer( 'subscribe' );

    $response = wp_remote_post( 'https://encoder.till.im/api/subscribe', [
        'headers' => [
            'Accept' => 'application/json',
        ],
        'body' => [
            'url' => get_home_url(),
            'email' => $_POST[ 'eae_notify_email' ],
        ],
    ] );

    if ( is_wp_error( $response ) || $response[ 'response' ][ 'code' ] !== 200 ) {
        return add_settings_error(
            'eae_notify_email',
            'invalid',
            __( 'Whoops, something went wrong. Please try again.', 'email-address-encoder' ),
            'error'
        );
    }

    add_settings_error(
        'eae_notify_email',
        'subscribed',
        __( 'You’ll receive a notification should your site contain unprotected email addresses.', 'email-address-encoder' ),
        'updated'
    );
}

/**
 * Clear page caches caches.
 *
 * @return void
 */
function eae_clear_caches() {
    if (
        empty( $_POST ) ||
        ! isset( $_POST[ 'option_page' ] ) ||
        $_POST[ 'option_page' ] !== 'email-address-encoder'
    ) {
        return;
    }

    // W3 Total Cache
    if ( function_exists( 'w3tc_flush_all' ) ) {
        w3tc_flush_all();
    }

    // WP Rocket
    if ( function_exists( 'rocket_clean_domain' ) ) {
        rocket_clean_domain();
    }

    // WP Super Cache
    if ( function_exists( 'wp_cache_clear_cache' ) ) {
        wp_cache_clear_cache();
    }

    // JCH Optimize
    if ( class_exists( 'JchPlatformCache' ) && method_exists( 'JchPlatformCache', 'deleteCache' ) ) {
        JchPlatformCache::deleteCache( true );
    }

    // LiteSpeed Cache
    if ( class_exists( 'LiteSpeed_Cache_API' ) && method_exists( 'LiteSpeed_Cache_API', 'purge_all' ) ) {
        LiteSpeed_Cache_API::purge_all();
    }

    // Cachify
    if ( class_exists( 'Cachify' ) && method_exists( 'Cachify', 'flush_total_cache' ) ) {
        Cachify::flush_total_cache( true );
    }
}
