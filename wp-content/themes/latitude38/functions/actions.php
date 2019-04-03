<?php

/* Because we want to show posts based on the ACF sort order, not time published. */
function L38_change_day_sort_order($query){
    if(is_day() && $query->is_main_query()):
        //Set the order ASC or DESC
        $query->set( 'order', 'ASC' );
        $query->set( 'orderby', 'meta_value_num' );
    	$query->set( 'meta_key', 'sort_order');
    endif;
};
add_action( 'pre_get_posts', 'L38_change_day_sort_order');
