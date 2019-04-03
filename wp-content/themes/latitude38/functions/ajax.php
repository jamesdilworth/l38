<?php
// General AJAX endpoints here

add_action('wp_ajax_update_profile_mainphoto', 'update_profile_mainphoto');
function update_profile_mainphoto() {
    // Built with the help of : https://www.ibenic.com/wordpress-file-upload-with-ajax/

    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    require_once( ABSPATH . 'wp-admin/includes/image.php' );


    $posted_data =  isset( $_POST ) ? $_POST : array();
    $file_data = isset( $_FILES ) ? $_FILES : array();
    $data = array_merge( $posted_data, $file_data );

    $current_user = wp_get_current_user();

    PC::debug($data);

    $output = "";

    // Does the guy have the right to update this picture?
    check_ajax_referer( 'update-mainphoto', '_mainphoto_nonce' );

    if(!is_user_logged_in()) {
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
            'post_title'     => 'User Avatar: ' . $current_user->first_name . ' ' . $current_user->last_name,
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_author'   => $current_user->ID
        );

        $old_thumbnail_id = $current_user->user_avatar;
        if (false === wp_delete_attachment( $old_thumbnail_id)) {
            error_log('Wasn\'t able to delete the old attachment after ajax upload of the new one');
        }

        // Insert the attachment.
        PC::debug($attachment);
        PC::debug($filename);
        $attach_id = wp_insert_attachment( $attachment, $filename );

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id,  $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        update_user_meta($current_user->ID, 'user_avatar', $attach_id);

        // Update the copyright
        update_field('source', 'external', $attach_id);

        echo "Success!";
    }

    wp_die();
}



