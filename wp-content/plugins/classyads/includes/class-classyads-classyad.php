<?php

class Classyad {
    public $id;
    public $title;
    public $main_image_url;
    public

    public function __construct($classy_id)
    {
        // Look up a classy ad
        // Object if true

        // Return false if not yet made.
        return false;
    }

    public function create($args) {


        return $post_id;
    }

    public function update() {

    }

    public function renew() {

    }

    public function expire() {

    }

    public function update_classy_mainphoto() {
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
        check_ajax_referer( 'update-mainphoto', '_mainphoto_nonce' );

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
            $attach_data = wp_generate_attachment_metadata( $attach_id,  $filename );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            set_post_thumbnail( $post->ID, $attach_id );

            // Update the copyright
            update_field('source', 'external', $attach_id);
            update_field('credit', ucwords($current_user->first_name . ' ' . $current_user->last_name), $attach_id);

            echo "Success!";
        }

        wp_die();
    }

    /**
     * Calculate the expiry date of a classified ad based on the post date, and the number of months it is supposed to run.
     *
     * @param $months
     * @param $post_date
     * @return string
     * @throws Exception
     */
    public function calc_expiry($months, $post_date) {

        $ad_placed_on = new DateTime($post_date);

        $cutoff = clone $ad_placed_on;
        $cutoff->setDate($cutoff->format('Y'), $cutoff->format('m'), 15);
        if($cutoff < $ad_placed_on) {
            $cutoff->modify('+1 month');
        }
        $months = absint($months);
        $expirydate = clone $cutoff;

        if($months < 12 ) {
            $expirydate->modify('+' . $months . 'month');
        } else {
            $expirydate->modify('+1 month');
        }
        $expirydate->modify('last day of ' . $expirydate->format('F'));

        return $expirydate->format('U');
    }

    public function prepare_reminders() {
        // Called by....
        // add_action('l38_classy_expiring_soon', 'l38_classy_reminder');
        // $args = array('classy_id' => $post->ID); // needs to be in an array. If multiple items, put them in an array of arrays... https://wordpress.stackexchange.com/questions/15475/using-wp-schedule-single-event-with-arguments-to-send-email
        // wp_schedule_single_event(time(), 'l38_classy_expiring_soon', $args);
    }


}
