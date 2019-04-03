<?php
/**
 * Modify WP_Query to support 'meta_or_tax' argument
 * to use OR between meta- and taxonomy query parts.
 * http://wordpress.stackexchange.com/questions/190011/wp-query-to-show-post-from-a-category-or-custom-field/190018#190018
 */
function JZ_meta_or_tax( $where, \WP_Query $q ) {
    // Get query vars
    $tax_args    = isset( $q->query_vars['tax_query'] )
        ? $q->query_vars['tax_query']
        : null;
    $meta_args   = isset( $q->query_vars['meta_query'] )
        ? $q->query_vars['meta_query']
        : null;
    $meta_or_tax = isset( $q->query_vars['_meta_or_tax'] )
        ? wp_validate_boolean( $q->query_vars['_meta_or_tax'] )
        : false;

    // Construct the "tax OR meta" query
    if( $meta_or_tax && is_array( $tax_args ) &&  is_array( $meta_args )  )
    {
        global $wpdb;

        // Primary id column
        $field = 'ID';

        // Tax query
        $sql_tax  = get_tax_sql(  $tax_args,  $wpdb->posts, $field );

        // Meta query
        $sql_meta = get_meta_sql( $meta_args, 'post', $wpdb->posts, $field );

        // Modify the 'where' part
        if( isset( $sql_meta['where'] ) && isset( $sql_tax['where'] ) )
        {
            $where  = str_replace(array($sql_meta['where'], $sql_tax['where']), '', $where );
            $where .= sprintf(
                ' AND ( %s OR  %s ) ',
                substr( trim( $sql_meta['where'] ), 4 ),
                substr( trim( $sql_tax['where']  ), 4 )
            );
        }
    }
    return $where;
}