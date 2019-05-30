<?php

defined( 'ABSPATH' ) || exit;

$wp_schema_pro_filters = [
    'wp_schema_pro_schema_article',
    'wp_schema_pro_schema_book',
    'wp_schema_pro_schema_event',
    'wp_schema_pro_schema_person',
    'wp_schema_pro_schema_product',
    'wp_schema_pro_schema_review',
    'wp_schema_pro_schema_recipe',
    'wp_schema_pro_schema_job_posting',
    'wp_schema_pro_schema_course',
    'wp_schema_pro_schema_local_business',
    'wp_schema_pro_schema_software_application',
    'wp_schema_pro_schema_service',
    'wp_schema_pro_schema_video_object',
    'wp_schema_pro_global_schema_about_page',
    'wp_schema_pro_global_schema_breadcrumb',
    'wp_schema_pro_global_schema_person',
    'wp_schema_pro_global_schema_contact_page',
    'wp_schema_pro_global_schema_site_navigation_element',
    'wp_schema_pro_global_schema_sitelink_search_box',
    'wp_schema_pro_global_schema_organization',
];

foreach ( $wp_schema_pro_filters as $filter ) {
    add_filter( $filter, function ( $schema ) {
        return eae_encode_json_recursive( $schema );
    }, EAE_FILTER_PRIORITY );
}
