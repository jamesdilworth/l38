<?php

class Classyad {

    public $post_id;
    public $post; // Holds the post object.
    public $owner; // ID of the classy owner?
    public $primary_cat; // Type of Object we're looking at.

    public $errors; // Holds array of 'field' => 'error' codes made while creating or updating an ad.

    public $title;
    public $main_image_url;
    public $custom_fields;

    public $status; // 'loaded', 'not a classy', 'doesnt exist', empty?

    public function __construct($post_id = null)
    {
        $this->errors = array();
        $default_values = array(
            'boat_year' => '1970',
            'boat_model' => 'Unknown',
            'boat_length' => 0,
            'ad_mag_text' => '',
            'as_asking_price' => '',
            'ad_external_url' => '',
        );
        $this->custom_fields = $default_values;

        if(isset($post_id)) {
            $this->load($post_id);
        } else {
            $this->status = "empty";
        }
    }

    // Load Data Based on
    private function load($post_id) {

        // Load the Post Obj.
        $this->post = get_post($post_id);

        if(!empty($this->post)) {

            $this->post_id = $post_id; // Set the Post ID since we use this a lot.
            $this->owner = $this->post->post_author; // And the owner ID since we also use this a lot.
            $loaded_fields = get_fields($post_id); // ACF Function - https://www.advancedcustomfields.com/resources/get_fields/

            foreach($loaded_fields as $field => $value) {
                $loaded_fields[$field] = $this->prepareOutput($field, $value);
            }

            $this->custom_fields = array_merge($this->custom_fields, $loaded_fields);
            $this->status = 'loaded';
        } else {
            $this->status = "failed to load classy";
        }
    }

    public function prepareOutput($field, $value) {
        // Filters used to clean or prepare output.... for example preparing dates, currency, etc.

        switch($field) {
            case 'ad_asking_price' :
                return money_format('%.0n', $value);
                break;
            default:
                // didn't find any custom stuff, so just return $fieldname;
                break;
        }

        // If it didn't match anything, just return it.
        return $value;
    }

    /**
     * Create new classyad based on a submitted form.
     */
    public function create($data) {

        $primary_cat = get_term_by('id', $data['primary_adcat'], 'adcat');

        if($primary_cat->slug == 'boats') {
            // Required Defaults
            $data['boat_year'] = isset($data['boat_year']) ? $data['boat_year'] : $this->custom_fields['boat_year'];
            $data['boat_length'] = isset($data['boat_length']) ? $data['boat_length'] : $this->custom_fields['boat_length'];
            $data['boat_model'] = isset($data['boat_model']) ? $data['boat_model'] : $this->custom_fields['boat_model'];

            $title = $data['boat_length'] . 'ft ' . $data['boat_model'] . ', ' . $data['boat_year'];
        } else {
            $title = $data['title'];
        }

        $new_post_args = array(
            'post_title'    => $title,
            'post_content'  => $data['maintext'],
            'post_status'   => 'draft', // until we've charged the card and everything.
            'post_type' => 'classy'
        );

        // Insert the post into the database.
        require_once( ABSPATH . 'wp-admin/includes/post.php' );
        $new_post_id = wp_insert_post( $new_post_args );

        if(!empty($new_post_id)) {
            $this->post_id = $new_post_id;
            $this->status = "Saved";
        } else {
            PC::debug("Uh-oh... it failed (" . $new_post_id . ")");
            $this->status = "Failed to Create";
            return false;
        }

        // From this point on, the post has been created, so any further errors should not cause a complete failure, but instead add to $this->errors

        // Set the Categories
        $data['adcats'] = Array();
        $data['adcats'] = isset($data['secondary_adcats']) ?  $data['secondary_adcats'] : Array();
        $data['adcats'][] = $data['primary_adcat']; // Add Primary Adcat in with Secondary Adcat.... now that we've controlled for user behavior
        $data['adcats'] = array_map('intval', $data['adcats']); // Make sure that the adcats are all integers.
        $terms = wp_set_object_terms($new_post_id, $data['adcats'], 'adcat');
        if( is_wp_error($terms)) {
            PC::debug($terms->get_error_message());
        }

        // Now let's start updating the meta.
        if(isset($data['ad_asking_price'])) $this->custom_fields['ad_asking_price'] = update_field('ad_asking_price', preg_replace('/[^0-9]/', '', $data['ad_asking_price']), $new_post_id); // <!-- NEED TO FILTER THIS!
        if(isset($data['ad_external_url'])) $this->custom_fields['ad_external_url'] = update_field('ad_external_url', $data['external_url'], $new_post_id);
        if(isset($data['ad_sale_terms'])) $this->custom_fields['ad_sale_terms'] = update_field('ad_sale_terms', $data['ad_sale_terms'], $new_post_id); // TODO Decide if we're going to use this!
        if(isset($data['boat_location'])) $this->custom_fields['boat_location'] = update_field('boat_location', $data['boat_location'], $new_post_id); // Not really a boat only field.

        // Print Meta
        if(isset($data[''])) $this->custom_fields['ad_mag_text'] = update_field('ad_mag_text', $data['ad_mag_text'], $new_post_id);

        // Featured Image
        if(isset($data['featured_image'])) {
            $featured_image_id = $this->uploadImage($data['featured_image']);
            set_post_thumbnail($new_post_id, $featured_image_id);
        }

        // Handle Additional Images...

        // Boats
        if(isset($data['boat_year'])) $this->custom_fields['boat_year'] =  update_field('boat_year', $data['boat_year'], $new_post_id);
        if(isset($data['boat_length'])) $this->custom_fields['boat_length'] = update_field('boat_length', $data['boat_length'], $new_post_id);
        if(isset($data['boat_model'])) $this->custom_fields['boat_model'] =  update_field('boat_model', $data['boat_model'], $new_post_id);

        /*
        if(isset($data[''])) update_field('ad_mag_title', $data['ad_header'], $new_post_id);
        if(isset($data[''])) update_field('ad_mag_show_photo', $photo_option, $new_post_id);
        update_field('ad_expires', $mag_show_to, $new_post_id);
        update_field('ad_subscription_level', $ad_subscription_level, $new_post_id);
        update_field('ad_auto_renew', $mag_auto_renew, $new_post_id);
        */

        return $new_post_id;

    }

    public function uploadImage(&$file) {

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $current_user = wp_get_current_user();
        $uploaded_file = wp_handle_upload($file , array('test_form' => false));

        if (!empty($uploaded_file['error'])) {
            echo $uploaded_file['error'];
            return false;
        } else {
            $filename = $uploaded_file['file']; // Full path to the file
            $local_url = $uploaded_file['url'];  // URL to the file in the uploads dir
            $type = $uploaded_file['type']; // MIME type of the file
            $the_excerpt = isset($this->custom_fields['ad_mag_text']) ? $this->custom_fields['ad_mag_text'] : "";

            $attachment = array(
                'post_mime_type' => $type,
                'post_title' => 'For Sale: ' . $this->title,
                'post_excerpt' => $the_excerpt,
                'post_content' => '',
                'post_status' => 'inherit',
                'post_author' => $current_user->ID
            );

            // Insert the attachment.
            $attach_id = wp_insert_attachment($attachment, $filename, $this->post_id);

            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            if (!function_exists('wp_generate_attachment_metadata')) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
            }

            // Generate the metadata for the attachment, and update the database record.
            $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
            wp_update_attachment_metadata($attach_id, $attach_data);

            // Update the copyright
            update_field('source', 'external', $attach_id);
            update_field('credit', ucwords($current_user->first_name . ' ' . $current_user->last_name), $attach_id);

            return $attach_id;
        }
    }

    public function setFeaturedImage($attach_id) {
        $id_or_false = set_post_thumbnail($this->post_id, $attach_id);
        return $id_or_false;
    }

    public function removeImage($attach_id) {
        // TODO... empty it from the array of images

        if (false === wp_delete_attachment($attach_id)) {
            error_log('Wasn\'t able to delete the old attachment after ajax upload of the new one');
        }
    }

    public function validate_field($field, $value) {
        // TODO our validtion functions.

        switch ($field) {
            case 'maintext' :
                // Check it's not empty.
                break;
            case 'ad_asking_price' :
                // It should resolve to an integer or (free)
                break;
        }

        return $value;
    }

    /* Save the value of a field, and return the sanitized version to the user. */
    public function update_field($field, $value) {
        // TODO - Think through the sanitization here
        // TODO - Some fields shouldn't be editable by the user... for example expiry.

        switch ($field) {
            case 'maintext' :
                // TODO... should probably be careful about calling this, as it revises the whole post!
                $post_content = esc_attr($value);
                wp_update_post(array( 'ID' => $this->post_id, 'post_content' => $post_content ), true);
                break;
            case 'ad_asking_price':
                $ad_asking_price = esc_attr($value);
                $ad_asking_price = preg_replace("/[^0-9\.]/", "", $ad_asking_price);
                update_post_meta( $this->post_id, 'ad_asking_price', $ad_asking_price );
                break;
            case 'ad_mag_text' :
                $ad_mag_text = esc_attr($value);
                update_post_meta( $this->post_id, 'ad_mag_text', $ad_mag_text );
                break;
            case 'ad_external_url' :
                $ad_mag_text = esc_attr($value);
                update_post_meta( $this->post_id, 'ad_mag_text', $ad_mag_text );
                break;
            case 'boat_location':
                $boat_location = esc_attr($value);
                update_post_meta( $this->post_id, 'boat_location', $boat_location );
                break;
            default :
                return false; // If this isn't an approved field. Don't save it. It could be a nonce, a self-injected field. Who knows!
                break;
        }

        // And update the view field.
        $this->custom_fields[$field] = $this->prepareOutput($field, $value);
        return $this->custom_fields[$field];
    }

    public function setExpiry($date) {

    }

    /**
     * Calculate the expiry date based on the plan they signed up with, and a placement date.
     */
    public function calculateExpiry($plan, $placement_date = NULL) {

        if($placement_date == NULL) {
            $placement_date = new DateTime(); // By default, treat it as now.
        } else {
            $placement_date = new DateTime($placement_date);
        }

        /**
         * For a print ad, the expiry will be the end of the month that the ad will run. So, for an ad placed on Dec 10,
         * the cutoff is Dec 15, and it'll run on Jan 1st. So the ad will expire Jan 30th.  But if the ad was placed on Dec 20,
         * it missed the cutoff, so will run in Feb's issue, and expires on Feb 28. So calculating the cutoff date is key to
         * calculating the expiry.
         */
        $cutoff = clone $placement_date;
        $cutoff->setDate($cutoff->format('Y'), $cutoff->format('m'), 15);
        if($cutoff < $placement_date) {
            $cutoff->modify('+1 month');
        }

        /* By default, we'll place an ad for one month, but this depends on the plan. */
        switch ($plan) {
            case 'online' :
                break;
            case '3month':
                break;
            default :
                $months = 1; // How many months the ad should run.
                break;
        }

        $expirydate = clone $cutoff;
        if($months > 1 ) {
            $expirydate->modify('+' . $months . ' months');
        } else {
            $expirydate->modify('+1 month');
        }
        $expirydate->modify('last day of ' . $expirydate->format('F'));

        // return $expirydate->format('U');

        return $expirydate;
    }

    /**
     * There are various key dates that the user needs to be aware of in relation to the expiry...
     * - Last day to renew
     * - Which month(s) the ad will be printed in.
     * - Cutoff date for changes for the next issue
     * - Cutoff date to renew for another issue.
     * - etc.
     */
    public function lookupKeyDates() {
        $key_dates = Array();

        return $key_dates;
    }

    public function upgradePlan() {
        // Let's say someone starts with an online only plan, but now wants to upgrade to a paid plan.
    }

    public function getPlanAmount() {
        // Returns the amount the plan costs.
        return 20;
    }

    public function delete() {
        // Delete a Classy Ad... under what circumstances would we do this?
    }

    public function renew() {
        // Depending on the plan, this will require them to
    }

    public function expire() {
        // This will need to be triggered, but when it is... expire the ad, and make it so that it no longer shows up in listings,
        // but still shows up in the dashboard.
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


    public function prepare_reminders() {
        // Called by....
        // add_action('l38_classy_expiring_soon', 'l38_classy_reminder');
        // $args = array('classy_id' => $post->ID); // needs to be in an array. If multiple items, put them in an array of arrays... https://wordpress.stackexchange.com/questions/15475/using-wp-schedule-single-event-with-arguments-to-send-email
        // wp_schedule_single_event(time(), 'l38_classy_expiring_soon', $args);
    }

    public function get_the_ID() {
        return $post_id;
    }

    public function get_the_status() {
        return $this->status;
    }

}
