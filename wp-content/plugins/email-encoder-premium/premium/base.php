<?php

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/dom.php';
require_once __DIR__ . '/updates.php';

add_action( 'plugins_loaded', function () {
    if ( eae_license_was_revoked() ) {
        return;
    }

    require_once __DIR__ . '/plugins.php';

    $action = apply_filters( 'eae_buffer_action', 'template_include' );

    if ( $action !== false ) {
        add_filter( $action, function ( $argument ) {
            eae_buffer();

            return $argument;
        }, EAE_FILTER_PRIORITY );
    }
} );

add_action( 'wp_head', function () {
    $cssName = EAE_DOM_Encoder::instance()->cssName;

    $styles = <<<HTML
        <style type="text/css">
            .__eae_cssd, .{$cssName} {
                unicode-bidi: bidi-override;
                direction: rtl;
            }
        </style>
HTML;

    printf( "\n%s\n", preg_replace( '/(\v|\s{2,})/', '', $styles ) );
} );

add_action( 'wp_head', function () {
    $script = file_get_contents( __DIR__ . '/../includes/rot.js' );
    $script = str_replace( '__eae_r47', EAE_DOM_Encoder::instance()->jsName, $script );
    $script = preg_replace( '/(\v|\s{2,})/', ' ', $script );
    $script = preg_replace( '/\s+/', ' ', $script );

    printf( "\n<script type=\"text/javascript\">%s</script>\n", $script );
} );

add_action( 'admin_head', function () {
    $screen = get_current_screen();

    if ( ! isset( $screen->id ) || $screen->id !== 'settings_page_email-address-encoder' ) {
        return;
    }

    echo <<<HTML
        <style type="text/css">
            .description .license-success,
            .description .license-success a {
                color: #46b450;
            }
            .description .license-warning,
            .description .license-warning a {
                color: #ff9700;
            }
            .description .license-danger,
            .description .license-danger a {
                color: #dc3232;
            }
        </style>
HTML;
} );
