<?php
/* Functions, Filters and Actions related to Classified Ads */

// Redirect from new classy ads to home if not an editor, and we're on the live server.
if(WP_ENV != 'dev' && !current_user_can('edit_posts')) {
    define('CLASSIES', false);
    if(stristr($_SERVER['REQUEST_URI'], 'new-classyads')) {
        wp_redirect( '/' );
        exit;
    }
} else {
    define('CLASSIES', true);
}

// Schedule Emails.
add_action('l38_classy_expiring_soon', 'l38_classy_reminder');
function l38_classy_reminder($classy_id) {
    // For an individual, ad send the user a reminder to renew their ad
}

// Called by....
// $args = array('classy_id' => $post->ID); // needs to be in an array. If multiple items, put them in an array of arrays... https://wordpress.stackexchange.com/questions/15475/using-wp-schedule-single-event-with-arguments-to-send-email
// wp_schedule_single_event(time(), 'l38_classy_expiring_soon', $args);

// Pre-render the Gravity forms.
add_filter( 'gform_pre_render_2', 'populate_adcats' );
add_filter( 'gform_pre_validation_2', 'populate_adcats' );
add_filter( 'gform_pre_submission_filter_2', 'populate_adcats' );
// add_filter( 'gform_admin_pre_render_2', 'populate_adcats' );
function populate_adcats( $form ) {
    /* I've set it up so that field (10) is the primary filter that defines the main ad type, then
     * Field (22) does the sub cats, which we'll filter with jQuery
     */

    $adcats = get_terms(array(
        'taxonomy' => 'adcat',
        'hide_empty' => false,
    ));

    // Loop through fields in form and get those attached to
    foreach( $form['fields'] as &$field )  {
        $primary_field = 41;
        $secondary_field = 22;

        // If it's neither of the fields, move on.
        if ( $field->id == $primary_field ) {
            $adcats =  get_terms( array(
                'taxonomy' => 'adcat',
                'hide_empty' => false,
            ));

            $input_id = 1;
            $choices = array();
            $inputs = array();

            foreach( $adcats as $term ) {

                //skipping index that are multiples of 10 (multiples of 10 create problems as the input IDs)
                if ( $input_id % 10 == 0 ) {
                    $input_id++;
                }

                if ($term->parent == 0) {
                    $choices[] = array( 'text' => $term->name, 'value' => $term->slug );
                    $inputs[] = array( 'label' => $term->name, 'id' => "{$primary_field}.{$input_id}" );
                }

                $input_id++;
            }

            $field->choices = $choices;
            $field->inputs = $inputs;

        }

        else if ( $field->id == $secondary_field ) {

            $input_id = 1;
            $choices = array();
            $inputs = array();

            foreach ($adcats as $term) {

                //skipping index that are multiples of 10 (multiples of 10 create problems as the input IDs)
                if ($input_id % 10 == 0) {
                    $input_id++;
                }

                if ($term->parent == 376) {
                    // It's a boat!
                    $choices[] = array('text' => $term->name, 'value' => $term->slug);
                    $inputs[] = array('label' => $term->name, 'id' => "{$secondary_field}.{$input_id}");
                }

                $input_id++;
            }

            $field->choices = $choices;
            $field->inputs = $inputs;
        }

    }

    return $form;
}

add_action( 'gform_pre_submission_2', 'new_classy_pre_submission_handler' );
function new_classy_pre_submission_handler( $form ) {

    if($_POST['input_10'] == '376') {
        // If it's a boat, set the title automatically from boat model.
        $boat_model =  esc_attr(rgpost( 'input_9' ));
        $boat_length = intval(preg_replace("/[^0-9\.]/", "", esc_attr(rgpost( 'input_11' ))));
        $boat_year = preg_replace("/[^0-9\.]/", "", esc_attr(rgpost( 'input_12' )));

        if($boat_length < 3 || $boat_length > 300) {
            $boat_length = 10; // Default 10'
            $_POST['input_11'] = $boat_length;
        }

        $this_year = intval(date ('Y'));
        if($boat_year < 1850 || $boat_year > ($this_year + 2)) {
            // Not a valid year, so set it to 1980?
            $boat_year = 1980;
            $_POST['input_12'] = $boat_year;
        }
        $_POST['input_1'] = "$boat_length' $boat_model, $boat_year";
    }

    // Clean the asking price.
    $ad_asking_price = esc_attr( $_POST['input_8'] ); // Probably want to do some cleaning here as it's a searchable field.
    $ad_asking_price = preg_replace("/[^0-9\.]/", "", $ad_asking_price);
    $_POST['input_8'] = $ad_asking_price;
}

add_action( 'gform_after_create_post_2', 'finish_classy_post', 10, 3 );
function finish_classy_post( $post_id, $entry, $form ) {

    // Set items that are not set automatically by the post.
    $sub_level = stristr(rgar( $entry, '23' ), "|", true);
    update_field('ad_subscription_level', $sub_level , $post_id);

}



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


add_action('wp_ajax_update_classy_list', 'update_classy_list');
add_action('wp_ajax_nopriv_update_classy_list', 'update_classy_list');

// AJAX Handler for the Classified Listing
function update_classy_list() {

    if(!empty($_REQUEST['adcat'])) $adcat = $_REQUEST['adcat'];
    $custom = array();
    if(isset($_REQUEST['search'])) $custom['search'] = $_REQUEST['search'];
    if(isset($_REQUEST['paged'])) $custom['paged'] = $_REQUEST['paged'];
    if(isset($_REQUEST['min_length'])) $custom['min_length'] = intval($_REQUEST['min_length']);
    if(isset($_REQUEST['max_length'])) $custom['max_length'] = intval($_REQUEST['max_length']);

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

        if(isset($custom['paged'])) {
            $args['paged'] = $custom['paged'];
        }
        if(isset($custom['min_length']) && $custom['min_length'] > 0) {
            $args['meta_query'][] = array(
                'key'     => 'boat_length',
                'value'   => $custom['min_length'],
                'compare' => '>='
            );
        }
        if(isset($custom['max_length']) && $custom['max_length'] > 0) {
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

    echo $output;
    wp_die();
}

// Core Function to get the Classified Listings.....
function get_the_classys($instance = array()) {

    $defaults = array(
        'post_type' => 'classy',
        'posts_per_page' => 20,
        'paged' => 1,
        'orderby' => 'modified',
        'order' => 'DESC'
    );
    $args = wp_parse_args((array) $instance, $defaults);

    $output = "";
    $ads = new WP_Query($args);
    if ( $ads->have_posts() ) {
        // $output = "<div class='totals'>" . $ads->max_num_pages . " ads match your search</div>";
        // Run the loop first, because calls in the loop might change the number of posts in the edition.
        while ($ads->have_posts()) {
            $ads->the_post();

            $img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'medium') : get_bloginfo('stylesheet_directory') .  '/images/default-classy-ad.png';
            $title = get_field('boat_length') . "' " . get_field('boat_model') . ", " . get_field('boat_year');

            $output .= "<div class='ad' style='background-image:url($img)'>";
            $output .= "  <div class='meta'>";
            $output .= "    <div class='title'><a href='". get_the_permalink() ."'>" . get_field('boat_length') . "' " . get_field('boat_model') . ", " . get_field('boat_year') . "</a></div>";
            $output .= "    <div class='price'>" . money_format('%.0n', (int) get_field('ad_asking_price')) . "</div>";
            $output .= "    <div class='location'>" . get_field('boat_location') . "</div>";
            $output .= "</div></div>";
        }
        if($ads->max_num_pages > 1) {
            $output .= "<a href='' class='more-ads' data-paged='" . (max( 1, $args[ 'paged' ]) + 1) . "'>More...</a>";
        }

    } else {
        $output = "<div class='no-results'>There are no results that matched your search. Sorry. </div>";
    }


    wp_reset_postdata();

    return $output;
}

// Determines the expiry date of the ad, based on the type of pricing model, and the date the ad was posted.
function calc_expiry($months, $post_date) {

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

// Returns array of datetime objects about an ad, based on the expiry.
function get_dates_from_expiry($epoch) {

    if(!isset($epoch)) return false();

    $today = new DateTime();
    $day = $today->format('d');


    $expirydate = new DateTime();
    $expirydate->setTimestamp($epoch);

    $cutoff = clone $expirydate;
    $cutoff->setDate($cutoff->format('Y'), ($cutoff->format('m') -1), 15);

    // Initially the renewal deadline is before the next cutoff.
    $renewal_deadline = clone $cutoff;
    $renewal_deadline->modify('+1 month');

    // But if it's already passed, we still want to convince them to renew for the following month.
    if($today > $renewal_deadline) {
        $renewal_deadline = clone $today;
        if(absint($day) < 15) {
            $renewal_deadline->setDate($today->format('Y'), $today->format('m'), 15);
        } else {
            $renewal_deadline->modify('next month');
            $renewal_deadline->modify($renewal_deadline->format('Y'), $renewal_deadline->format('m'), 15);
        }
    }

    $ad_edition = clone $cutoff;
    $ad_edition->modify('+1 month');
    $ad_edition->modify('first day of ' . $ad_edition->format('F'));

    $next_ad_edition = clone $renewal_deadline;
    $next_ad_edition->modify('+1 month');

    $key_dates = array();
    $key_dates['expiry'] = $expirydate;
    $key_dates['cutoff'] = $cutoff;
    $key_dates['renewal_deadline'] = $renewal_deadline;
    $key_dates['ad_edition'] = $ad_edition;
    $key_dates['next_ad_edition'] = $next_ad_edition;

    return $key_dates;
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
        $mag_show_to = $row['issue_year'] .  $issue_month . '28'; // Print up to this date.
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
                $adcat = array('motoryacht');
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
        update_field('ad_expires', $mag_show_to, $new_post);
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