<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'mb_detect_encoding' ) ) :

function mb_detect_encoding( $str, $encoding_list = null, $strict = null ) {
    return false;
}

endif;

if ( ! function_exists( 'mb_convert_encoding' ) ) :

function mb_convert_encoding( $str, $to_encoding, $from_encoding ) {
    return iconv( $from_encoding, $to_encoding, $str );
}

endif;
