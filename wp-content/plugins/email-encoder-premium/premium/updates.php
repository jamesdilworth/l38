<?php

defined( 'ABSPATH' ) || exit;

add_action( 'eae_validate_license', 'eae_has_valid_license' );

add_action( 'admin_init', function () {
    if ( ! wp_next_scheduled( 'eae_validate_license' ) ) {
        wp_schedule_event( time(), 'daily', 'eae_validate_license' );
    }
} );

add_action( 'in_plugin_update_message-' . EAE_PLUGIN_BASENAME, function ( $plugin_data, $response ) {
    if ( ! eae_has_valid_license() ) {
        printf(
            '<br />'. __( 'To enable updates, please <a href="%1$s">enter your license key</a>. If you don’t have a license key, please see <a href="%2$s">details & pricing</a>.', 'email-address-encoder' ),
            admin_url( 'options-general.php?page=email-address-encoder' ),
            'https://encoder.till.im/download?utm_source=wp-plugin&amp;utm_medium=update-msg'
        );
    }
}, 10, 2 );

add_action( 'pre_update_option_eae_license_key', function ( $value ) {
    $license = get_option( 'eae_license', (object) [
        'key' => null,
    ] );

    if ( $license->key !== $value ) {
        delete_option( 'eae_license' );
    }

    return $value;
} );

add_filter( 'pre_option_eae_search_in', function ( $value ) {
    return eae_license_was_revoked() ? 'filters' : false;
} );

add_filter( 'pre_option_eae_technique', function ( $value ) {
    return eae_license_was_revoked() ? 'entities' : false;
} );

add_filter( 'plugins_api', function ( $result, $action = null, $args = null ) {
    if ( $action === 'plugin_information' && $args->slug === dirname( EAE_PLUGIN_BASENAME ) ) {
        return eae_fetch_plugin_info();
    }

    return $result;
}, 10, 3 );

add_filter( 'pre_set_site_transient_update_plugins', function ( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    $update = eae_fetch_plugin_update();

    if ( is_wp_error( $update ) ) {
        return $transient;
    }

    if ( version_compare( $update->version, eae_plugin_version(), '>' ) ) {
        $transient->response[ EAE_PLUGIN_BASENAME ] = (object) [
            'slug' => dirname( EAE_PLUGIN_BASENAME ),
            'new_version' => $update->version,
            'package' => $update->package,
            'icons' => [
                'default' => "https://ps.w.org/email-address-encoder/assets/icon-256x256.jpg?v={$update->version}",
            ],
        ];
    }

    return $transient;
} );

function eae_has_valid_license() {
    $key = get_option( 'eae_license_key' );

    if ( empty( $key ) ) {
        if ( $key !== false ) {
            delete_option( 'eae_license_key' );
        }

        if ( get_option( 'eae_license' ) !== false ) {
            delete_option( 'eae_license' );
        }

        return false;
    }

    return eae_validate_license_key( $key );
}

function eae_license_was_revoked() {
    static $license = null;

    if ( $license === null ) {
        $license = get_option( 'eae_license', (object) [
            'state' => null,
        ] );
    }

    return $license->state === 'revoked';
}

function eae_sanitize_license_key( $value ) {
    $value = sanitize_text_field( $value );

    if ( empty( $value ) ) {
        return $value;
    }

    $license = eae_validate_license_key( $value );

    if ( ! is_wp_error( $license ) ) {
        return $value;
    }

    $message = $license->get_error_message();

    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        $message .= sprintf(': %s', $license->get_error_data());
    }

    add_settings_error( 'eae_license_key', 'invalid', $message, 'error' );
}

function eae_plugin_version() {
    $data = get_plugin_data( realpath(
        sprintf( '%s/../%s', __DIR__, basename( EAE_PLUGIN_BASENAME ) )
    ) );

    return $data[ 'Version' ];
}

function eae_remote_request( $path, $license = null ) {
    $dev = defined( 'EAE_DEBUG_API' ) && EAE_DEBUG_API;

    $url = sprintf(
        'https://%s/api/%s',
        $dev ? 'encoder.test' : 'encoder.till.im',
        $path
    );

    return wp_remote_post( $url, [
        'headers' => [
            'Accept' => 'application/json',
        ],
        'body' => [
            'url' => get_home_url(),
            'version' => eae_plugin_version(),
            'email' => get_bloginfo( 'admin_email' ),
            'license' => $license ?: get_option( 'eae_license_key' ),
        ],
        'sslverify' => ! $dev,
    ] );
}

function eae_fetch_plugin_info() {
    $response = eae_remote_request( 'plugin/info' );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $responseCode = $response[ 'response' ][ 'code' ];
    $responseMessage = $response[ 'response' ][ 'message' ];
    $json = (object) json_decode( $response[ 'body' ], true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        return new WP_Error( json_last_error(), json_last_error_msg(), $response[ 'body' ] );
    }

    if ( $responseCode !== 200 ) {
        return new WP_Error(
            $responseCode,
            empty( $json->message ) ? $responseMessage : $json->message
        );
    }

    return $json;
}

function eae_fetch_plugin_update() {
    $response = eae_remote_request( 'plugin/update' );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $responseCode = $response[ 'response' ][ 'code' ];
    $responseMessage = $response[ 'response' ][ 'message' ];
    $update = (object) json_decode( $response[ 'body' ], true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        return new WP_Error( json_last_error(), json_last_error_msg(), $response[ 'body' ] );
    }

    if ( $responseCode !== 200 ) {
        return new WP_Error(
            $responseCode,
            empty( $update->message ) ? $responseMessage : $update->message
        );
    }

    return $update;
}

function eae_validate_license_key( $key ) {
    $license = get_option( 'eae_license', (object) [
        'key' => null,
        'active' => null,
        'lastCheck' => current_time( 'timestamp', 1 ),
    ] );

    if (
        $license->key === $key &&
        $license->state === 'active' &&
        $license->lastCheck + DAY_IN_SECONDS > current_time( 'timestamp', 1 )
    ) {
        return true;
    }

    $response = eae_remote_request( 'plugin/license', $key );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $responseCode = $response[ 'response' ][ 'code' ];
    $responseMessage = $response[ 'response' ][ 'message' ];
    $json = json_decode( $response[ 'body' ] );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        return new WP_Error( json_last_error(), json_last_error_msg(), $response[ 'body' ] );
    }

    if ( $responseCode !== 200 ) {
        return new WP_Error(
            $responseCode,
            empty( $json->message ) ? $responseMessage : $json->message
        );
    }

    if ( $json->state === 'invalid' ) {
        return new WP_Error( 404, __( 'Invalid license key. Make sure you copied your license key exactly as it appears on your receipt.', 'email-address-encoder' ) );
    }

    update_option( 'eae_license', (object) array_filter( [
        'key' => $key,
        'state' => $json->state,
        'lastCheck' => current_time( 'timestamp', 1 ),
        'plan' => empty( $json->plan ) ? null : $json->plan,
        'licensee' => empty( $json->licensee ) ? null : $json->licensee,
    ] ) );

    return in_array( $json->state, [ 'active', 'past-due' ] );
}
