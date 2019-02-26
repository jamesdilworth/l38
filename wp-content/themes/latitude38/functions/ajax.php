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
            'post_author'   => $current_user
        );

        $old_thumbnail_id = $current_user->user_avatar;
        if (false === wp_delete_attachment( $old_thumbnail_id)) {
            error_log('Wasn\'t able to delete the old attachment after ajax upload of the new one');
        }

        // Insert the attachment.
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



add_action('wp_ajax_add_classy_from_lasso', 'add_classy_from_lasso');
add_action('wp_ajax_nopriv_add_classy_from_lasso', 'add_classy_from_lasso');

function add_classy_from_lasso($lasso_id) {
    global $wpdb;
    $output = "";

    if(empty($lasso_id)) {
        $lasso_id = $_REQUEST['lasso_id'];

    }
    error_log('starting add_classy_from_lasso with ' . $lasso_id);

    require_once( ABSPATH . 'wp-admin/includes/file.php' );

    // First check this post doesn't already exist!
    $post_exists = get_post_from_lasso_id($lasso_id);
    if($post_exists) {
        $output .= "<div class='warning'>This classy already exists in Wordpress as " . $post_exists . "</div>";
        echo $output;
        die();
    }

    // Go through all the active classies
    $db = new mysqli('localhost', 'root', 'jaymz0', 'l38_munge');
    if($db->connect_errno > 0){
        die('Unable to connect to database [' . $db->connect_error . ']');
    }

    $sql = "SELECT * FROM class_start WHERE id = " . $lasso_id; // Classy Query.

    if(!$result = $db->query($sql)){
        die('There was an error running the query [' . $db->error . ']');
    }

    while($row = $result->fetch_assoc()){

        // User info
        $lasso_user_id = $row['customerid'];

        // Core Post Info
        $title = $row['ad_header'];
        $maintext = $row['ad_text'];
        $featured_image_url = '';

        $sale_terms = 'sale';
        if($row['category'] == 'Partnerships') $sale_terms = 'partnership';

        // Magazine Specific
        $issue_month = $row['issue_monthnum'];
        if(strlen($row['issue_monthnum'] == 1)) $issue_month = '0' . $issue_month;
        $mag_show_to = $row['issue_year'] .  $issue_month . '01'; // Print up to this date.
        if($row['renewal'] == 'Run Ad Every Month Until Cancelled') $mag_auto_renew = true;

        // Fit the old model into the new model.
        $ad_subscription_level = 'free';
        if($row['status'] == 'Live on Website') {
            $ad_subscription_level = 'basic';
            if($row['pictureid01'] > 0) $ad_subscription_level = 'premium';
        }

        // External URL's are often poorly formed.
        $external_url = $row['field03'];
        $proto_scheme = parse_url($external_url,PHP_URL_SCHEME);
        if((!stristr($proto_scheme,'http')) || (!stristr($proto_scheme,'https'))){
            $webAddress = 'http://'.$webAddress;
        }



        // Payment History
        $product_type = $row['renewal']; // 1 Month, 3 Month, 6 Month etc.
        $cim_customerid = $row['cim_customerid'];
        $cim_transactionid = $row['cim_transactionid'];

        // Admin fields
        $admin_notes = ""; // There is not currently a section for this.

        // Create the right user... some have default entries in the class_table.
        $firstname = $row['cc_firstname'];
        $lastname = $row['cc_lastname'];
        $user_email = $row['ad_email'];
        $user_row = array();

        if(!empty($row['customerid'])) {
            // Check there is a customer associated in the latitude system.
            $user_sql = "SELECT * FROM lat_customers WHERE id = " . $lasso_user_id;

            if (!$user_result = $db->query($user_sql)) {
                die('There was an error running the query [' . $db->error . ']');
            }

            while ($user_row = $user_result->fetch_assoc()) {
                // Check to see if this user already exists in our system?
                $firstname =  $user_row['first'] ?  $user_row['first'] : $user_row['bill_firstname'];
                $lastname = $user_row['last'] ? $user_row['last'] : $user_row['bill_lastname'];
                if($user_row['email']) $user_email = $user_row['email'];
            }
            $user_result->free();
        }

        if($user_email) {
            $wp_user = get_user_by('email', $user_email); // Check this user doesn't already exist..

            $username = sanitize_text_field(strtolower($firstname) . strtolower($lastname));

            if(!$wp_user) {
                $wp_user_id = wp_insert_user(array(
                        'user_login' => $username,
                        'user_pass' => $user_row['password'],
                        'user_email' => $user_email,
                        'first_name' => ucwords(strtolower($firstname)),
                        'last_name' => ucwords(strtolower($lastname)),
                        'display_name' => ucwords(strtolower($firstname)) . ' ' . ucwords(strtolower($lastname)),
                        'role' => 'subscriber'
                    )
                );

                if( is_wp_error($wp_user_id)) {
                    $output .= $wp_user_id->get_error_message();
                    $wp_user_id = 1; // Just create it with admin user.
                }
            } else {
                $wp_user_id = $wp_user->ID;
            }

            // Sort out phone numbers and store them as plain numbers.
            $phone = $row['areacode'] . $row['phone'];
            if($user_row['areacode']) {
                // We'll take that.
                $phone = $user_row['areacode'] .  $user_row['phone'];
            }
            if(empty($phone)) {
                // Last try with billing data
                $phone = $user_row['bill_areacode'] . $user_row['bill_phone'];
            }
            $phone = sanitize_purpose_phone_input($phone);

            update_user_meta($wp_user_id, 'sex', $user_row['sex']);
            update_user_meta($wp_user_id, 'birthdate', strtotime($user_row['created']) - ($row['age'] * 31557600));
            update_user_meta($wp_user_id, 'phone', $phone);
            // update_user_meta($wp_user_id, 'address1', $row['address']);
            // update_user_meta($wp_user_id, 'address2', $row['address2']);
            // update_user_meta($wp_user_id, 'city', $row['city']);
            // update_user_meta($wp_user_id, 'state', $row['state']);
            // update_user_meta($wp_user_id, 'zip', $row['zip']);
            update_user_meta($wp_user_id, 'othercontact', $user_row['othercontact']);


        } else {
            $wp_user_id = 1;
        }


        // Look up the image
        if(!empty($row['pictureid01'])) {
            $image_sql = "SELECT * FROM class_pix WHERE id = " . $row['pictureid01'];

            if (!$image_result = $db->query($image_sql)) {
                die('There was an error running the query [' . $db->error . ']');
            }
            while ($image_row = $image_result->fetch_assoc()) {
                $featured_image_url = 'http://www.latitude38.com/classifieds/uploads/img_classy_576/' . $image_row['this_file'];
            }

            $image_result->free();
        }

        // Now let's add the post into Wordpress
        $new_post_args = array(
            'post_title'    => $title,
            'post_content'  => $maintext,
            'post_status'   => 'publish',
            'post_date' => $row['created'],
            'post_author'   => $wp_user_id,
            'post_modified' => $row['modified'],
            'post_type' => 'classy'
        );

        // Figure out the categories
        switch($row['category']) {
            case 'Dinghies, Liferafts & Rowboats':
                $adcat = array('dinks');
                break;
            case 'Multihull':
                $adcat = array('multihull', 'sail');
                break;
            case 'Power & Houseboats':
                $adcat = array('power');
                break;
            default:
                $adcat = array('sail');
        }

        // Insert the post into the database.
        require_once( ABSPATH . 'wp-admin/includes/post.php' );
        $new_post = wp_insert_post( $new_post_args );
        if(!empty($new_post)) {
            $output .= "<div class='success'>Inserted new Ad (<a href='/?p=$new_post'>$new_post</a>) successfully</div>";
        } else {
            $output .= "<span style='color:red'>Uh-oh... it failed ($new_post)</span><br>";
        }

        // Set the Categories
        $terms = wp_set_object_terms($new_post, $adcat, 'adcat');
        if( is_wp_error($terms)) {
            $output .= $terms->get_error_message();
        }

        // Now let's start updating the meta.
        update_field('location', $row['location'], $new_post);

        // Core Meta
        update_field('lasso_classy_id', $row['id'], $new_post);
        update_field('ad_asking_price', preg_replace('/[^0-9]/', '', $row['boat_price']), $new_post); // <!-- NEED TO FILTER THIS!
        update_field('ad_external_url', $external_url, $new_post);
        update_field('ad_sale_terms', $sale_terms, $new_post);

        // Boat Meta
        update_field('boat_year', $row['boat_year'], $new_post);
        update_field('boat_length', $row['boat_length'], $new_post);
        update_field('boat_model', $row['boat_model'], $new_post);
        update_field('boat_location', $row['field01'], $new_post);

        // Print Meta
        update_field('ad_mag_title', $row['ad_header'], $new_post);
        update_field('ad_mag_text', $maintext, $new_post);

        $photo_option = $row['photo_option'] == 'yes_photo' ? true : false;
        update_field('ad_mag_show_photo', $photo_option, $new_post);
        update_field('ad_mag_run_to', $mag_show_to, $new_post);
        update_field('ad_subscription_level', $ad_subscription_level, $new_post);

        // Transaction Meta
        update_field('ad_auto_renew', $mag_auto_renew, $new_post);

        /* Add featured image */
        if(isset($featured_image_url)) {

            // URL to the WordPress logo
            $timeout_seconds = 10;

            // Download file to temp dir
            $temp_file = download_url($featured_image_url, $timeout_seconds );
            $filetype = wp_check_filetype( $temp_file , null );

            if ( !is_wp_error( $temp_file ) ) {

                // Array based on $_FILE as seen in PHP file uploads
                $file = array(
                    'name'     => basename($featured_image_url), // ex: wp-header-logo.png
                    'type'     => $filetype['type'],
                    'tmp_name' => $temp_file,
                    'error'    => 0,
                    'size'     => filesize($temp_file),
                );

                $overrides = array(
                    'test_form' => false, // Tells WordPress to not look for the POST form
                    'test_size' => true, // Do not allow empty files.
                );

                // Move the temporary file into the uploads directory
                $results = wp_handle_sideload( $file, $overrides );

                if ( !empty( $results['error'] ) ) {
                    $output .= $results['error'];
                } else {

                    $filename  = $results['file']; // Full path to the file
                    $local_url = $results['url'];  // URL to the file in the uploads dir
                    $type      = $results['type']; // MIME type of the file

                    $attachment = array(
                        'post_mime_type' => $type,
                        'post_title'     => 'For Sale: ' . $title,
                        'post_excerpt'   => $maintext,
                        'post_content'   => '',
                        'post_status'    => 'inherit',
                        'post_author'   => $wp_user_id
                    );

                    // Insert the attachment.
                    $attach_id = wp_insert_attachment( $attachment, $filename, $new_post );

                    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
                    require_once( ABSPATH . 'wp-admin/includes/image.php' );

                    // Generate the metadata for the attachment, and update the database record.
                    $attach_data = wp_generate_attachment_metadata( $attach_id, ABSPATH . $filename );
                    wp_update_attachment_metadata( $attach_id, $attach_data );
                    set_post_thumbnail( $new_post, $attach_id );

                    // Update the copyright
                    update_field('source', 'external', $attach_id);
                    update_field('credit', ucwords($firstname . ' ' . $lastname), $attach_id);

                }

            } else {
                $output .= "<div class='notice'>" . $temp_file->get_error_message();
                $output .= " - No image could be attached</div>";
            }
        }
    }
    echo $output;
    die();
}
