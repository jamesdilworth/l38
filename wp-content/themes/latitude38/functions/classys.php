<?php
/* Functions, Filters and Actions related to Classified Ads */

add_action('wp_ajax_update_classy_mainphoto', 'update_classy_mainphoto');
function update_classy_mainphoto() {
    // Built with the help of : https://www.ibenic.com/wordpress-file-upload-with-ajax/

    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    $posted_data =  isset( $_POST ) ? $_POST : array();
    $file_data = isset( $_FILES ) ? $_FILES : array();
    $data = array_merge( $posted_data, $file_data );

    $current_user = wp_get_current_user();
    $post = get_post($data['post_id']);

    $output = "";

    // Does the guy have the right to update this picture?
    check_ajax_referer( 'update-mainphoto', '_wpnonce' );

    if(!is_user_logged_in() || !($current_user->ID == $post->post_author || current_user_can('edit_posts'))) {
        // Not a valid user to perform this operation.
        wp_die();
    }

    $fileErrors = array(
        0 => "There is no error, the file uploaded with success",
        1 => "The uploaded file exceeds the upload_max_files in server settings",
        2 => "The uploaded file exceeds the MAX_FILE_SIZE from html form",
        3 => "The uploaded file uploaded only partially",
        4 => "No file was uploaded",
        6 => "Missing a temporary folder",
        7 => "Failed to write file to disk",
        8 => "A PHP extension stoped file to upload" );

    $response = array();

    // This should be called multiple times if multiple files in an array.
    $uploaded_file = wp_handle_upload( $data['main_photo'], array( 'test_form' => false ) );

    if ( !empty( $uploaded_file['error'] ) ) {
        echo $uploaded_file['error'];
    } else {

        $filename  = $uploaded_file['file']; // Full path to the file
        $local_url = $uploaded_file['url'];  // URL to the file in the uploads dir
        $type      = $uploaded_file['type']; // MIME type of the file

        $attachment = array(
            'post_mime_type' => $type,
            'post_title'     => 'For Sale: ' . $post->title,
            'post_excerpt'   => get_field('ad_mag_text', $post->ID),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_author'   => $current_user
        );

        $old_thumbnail_id = get_post_thumbnail_id( $post->ID );
        if (false === wp_delete_attachment( $old_thumbnail_id)) {
            error_log('Wasn\'t able to delete the old attachment after ajax upload of the new one');
        }

        // Insert the attachment.
        $attach_id = wp_insert_attachment( $attachment, $filename, $post->ID );

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, ABSPATH . $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        set_post_thumbnail( $post->ID, $attach_id );

        // Update the copyright
        update_field('source', 'external', $attach_id);
        update_field('credit', ucwords($current_user->first_name . ' ' . $current_user->last_name), $attach_id);

        echo "Success!";
    }

    wp_die();
}


add_action('wp_ajax_update_classy_list', 'update_classy_list');
add_action('wp_ajax_nopriv_update_classy_list', 'update_classy_list');

// AJAX Handler for the Classified Listing
function update_classy_list() {

    if(!empty($_REQUEST['adcat'])) $adcat = $_REQUEST['adcat'];
    $custom = array();
    if($_REQUEST['search']) $custom['search'] = $_REQUEST['search'];
    if($_REQUEST['min_length']) $custom['min_length'] = intval($_REQUEST['min_length']);
    if($_REQUEST['max_length']) $custom['max_length'] = intval($_REQUEST['max_length']);

    if(!empty($adcat)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'adcat',
                'field' => 'id',
                'terms' => $adcat
            )
        );
    }

    if(!empty($custom)) {
        $args['meta_query'] = array();

        if($custom['min_length'] > 0) {
            $args['meta_query'][] = array(
                'key'     => 'boat_length',
                'value'   => $custom['min_length'],
                'compare' => '>='
            );
        }
        if($custom['max_length'] > 0) {
            $args['meta_query'][] = array(
                'key'     => 'boat_length',
                'value'   => $custom['max_length'],
                'compare' => '<='
            );
        }
        if(isset($custom['search'])) {
            $args['meta_query'][] = array(
                'key'     => 'boat_model',
                'value'   => $custom['search'],
                'compare' => 'LIKE'
            );
        }
    }

    $output = get_the_classys($args);

    // Fire Simple History Event.

    echo $output;
    wp_die();
}

// Core Function to get the Classified Listings.....
function get_the_classys($instance = array()) {

    $defaults = array(
        'post_type' => 'classy',
        'posts_per_page' => -1
    );
    $args = wp_parse_args((array) $instance, $defaults);

    $output = "";

    $ads = new WP_Query($args);
    if ( $ads->have_posts() ) {
        // Run the loop first, because calls in the loop might change the number of posts in the edition.
        while ($ads->have_posts()) {
            $ads->the_post();

            $img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'medium') : get_bloginfo('stylesheet_directory') .  '/images/default-classy-ad.png';
            $title = get_field('boat_length') . "' " . get_field('boat_model') . ", " . get_field('boat_year');

            $output .= "<div class='ad' style='background-image:url($img)'>";
            $output .= "  <div class='meta'>";
            $output .= "    <div class='title'><a href='". get_the_permalink() ."'>" . get_field('boat_length') . "' " . get_field('boat_model') . ", " . get_field('boat_year') . "</a></div>";
            $output .= "    <div class='price'>" . money_format('%.0n',get_field('ad_asking_price')) . "</div>";
            $output .= "    <div class='location'>" . get_field('boat_location') . "</div>";
            $output .= "</div></div>";
        }
    } else {
        $output = "<div class='no-results'>There are no results that matched your search. Sorry. </div>";
    }
    wp_reset_postdata();

    return $output;

}