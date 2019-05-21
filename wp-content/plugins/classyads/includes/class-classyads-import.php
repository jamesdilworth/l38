<?php
class Classyads_Import {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name; // The name of the plugin.
        $this->version = $version; // The version... used for enqueuing.
    }

    /**
     * AJAX function to handle importing a classy from the old system.
     *
     * @reauires access to the Lassso DB
     * @param $lasso_id - The original ID of the lasso classy.
     */
    public static function add_classy_from_lasso($lasso_id) {
        global $wpdb;
        $output = "";

        if(empty($lasso_id)) {
            $lasso_id = $_REQUEST['lasso_id'];
        }

        require_once( ABSPATH . 'wp-admin/includes/file.php' );

        // First check this post doesn't already exist!
        $post_exists = Classyads_Import::get_post_from_lasso_id($lasso_id);
        if($post_exists) {
            $output .= "<div class='warning'>This classy already exists in Wordpress as " . $post_exists . "</div>";
            echo $output;
            die();
        }

        // Go through all the active classies
        $db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, LASSO_DB);
        if($db->connect_errno > 0){
            die('Unable to connect to database [' . $db->connect_error . ']');
        }

        $sql = "SELECT * FROM class_start WHERE id = " . $lasso_id; // Classy Query.

        if(!$result = $db->query($sql)){
            die('There was an error running the query [' . $db->error . ']');
        }

        while($row = $result->fetch_assoc()) {

            $data = array();

            // Core Post Info
            $title = $row['ad_header'];
            $maintext = $row['ad_text'];

            // TODO! Figure out our mapping here.
            $sale_terms = 'sale';
            if ($row['category'] == 'Partnerships') $sale_terms = 'partnership';

            // Fit the old model into the new model. - TODO! - Check this still makes sense.
            $ad_subscription_level = 'free';
            if ($row['status'] == 'Live on Website') {
                $ad_subscription_level = 'basic';
                if ($row['pictureid01'] > 0) $ad_subscription_level = 'premium';
            }

            // External URL's are often poorly formed.
            if(!empty($row['field03'])) {
                $external_url = $row['field03'];
                $proto_scheme = parse_url($external_url, PHP_URL_SCHEME);
                if ((!stristr($proto_scheme, 'http')) || (!stristr($proto_scheme, 'https'))) {
                    $external_url = 'http://' . $external_url;
                }
            } else {
                $external_url = "";
            }

            /**
             * =========================================================================================================
             * CREATE THE USER?.... some have default entries in the class_table.
             * =========================================================================================================
             */
            $lasso_user_id = $row['customerid'];
            $firstname = $row['cc_firstname'];
            $lastname = $row['cc_lastname'];
            $email = $row['ad_email'];
            $password = $row['pzwrrd'];

            $user_row = array();

            if (!empty($lasso_user_id)) {
                // Check there is an associated customer row in the latitude system.
                $user_sql = "SELECT * FROM lat_customers WHERE id = " . $lasso_user_id;

                if (!$user_result = $db->query($user_sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                }

                $user_row = array();
                while ($urow = $user_result->fetch_assoc()) {
                    // If so, the customer row will take priority.
                    $firstname = $urow['first'] ? $urow['first'] : $urow['bill_firstname'];
                    $lastname = $urow['last'] ? $urow['last'] : $urow['bill_lastname'];
                    if ($urow['email']) $email = $urow['email'];
                    if ($urow['pwd']) $password = $urow['pwd'];
                    $user_row = $row;
                }
                $user_result->free();
            }

            if ($email) {
                $wp_user = get_user_by('email', $email); // Check if this user already exists in Wordpress..

                // If not... create a new user.
                if (!$wp_user) {
                    $username = sanitize_text_field(strtolower($firstname) . strtolower($lastname));
                    $wp_user_id = wp_insert_user(array(
                            'user_login' => $username,
                            'user_pass' => $password,
                            'user_email' => $email,
                            'first_name' => ucwords(strtolower($firstname)),
                            'last_name' => ucwords(strtolower($lastname)),
                            'display_name' => ucwords(strtolower($firstname)) . ' ' . ucwords(strtolower($lastname)),
                            'role' => 'subscriber'
                        )
                    );

                    if (is_wp_error($wp_user_id)) {
                        $output .= $wp_user_id->get_error_message();
                        $wp_user_id = 1; // Just create it with admin user.
                    }
                    // Else, associate the existing user before we update the other fields.
                } else {
                    $wp_user_id = $wp_user->ID;
                }

                if($wp_user_id != 1) {
                    // Sort out phone numbers and store them as plain numbers.
                    $phone = "";
                    if (isset($user_row['areacode'])) {
                        // We'll take that.
                        $phone = $user_row['areacode'] . $user_row['phone'];
                    } else if(isset($row['areacode'])) {
                        // Just take from the classies?
                        $phone = $row['areacode'] . $row['phone'];
                    } else if(isset($user_row['bill_areacode'])) {
                        $phone = $user_row['bill_areacode'] . $user_row['bill_phone'];
                    }
                    $phone = sanitize_purpose_phone_input($phone);

                    // UPDATE USER META... transaction ID is stored wrongly in lasso.
                    if (isset($user_row['cim_customerid'])) {
                        update_user_meta($wp_user_id, 'cim_profile_id', $user_row['cim_customerid']);

                        $payment_profile = Jzugc_Payment::lookupPaymentProfileDeets($user_row['cim_customerid'], $user_row['cim_transactionid']);
                        if ($payment_profile) add_user_meta($wp_user_id, 'cim_payment_profile', $payment_profile);
                    }

                    if (isset($user_row['sex'])) update_user_meta($wp_user_id, 'sex', $user_row['sex']);
                    if (isset($user_row['age'])) update_user_meta($wp_user_id, 'birthdate', strtotime($user_row['created']) - ($row['age'] * 31557600));

                    update_user_meta($wp_user_id, 'phone', $phone);
                    if(isset($user_row['address']))update_user_meta($wp_user_id, 'address1', $row['address']);
                    if(isset($user_row['address2']))update_user_meta($wp_user_id, 'address2', $row['address2']);
                    if(isset($user_row['city']))update_user_meta($wp_user_id, 'city', $row['city']);
                    if(isset($user_row['state']))update_user_meta($wp_user_id, 'state', $row['state']);
                    if(isset($user_row['zip'])) update_user_meta($wp_user_id, 'zip', $row['zip']);

                    if(isset($user_row['othercontact'])) update_user_meta($wp_user_id, 'othercontact', $user_row['othercontact']);
                    update_user_meta($wp_user_id, 'lasso_user_id', $lasso_user_id);
                }
            } else {
                // No email address associated... no key to create a user, so let's just create it under user 1?
                $wp_user_id = 1;

                // And if we do this, we'll need to set up some override data later...
            }

            /**
             * =========================================================================================================
             * CREATE THE POST
             * TODO... really we should just pass the data array to Classyad class!
             * =========================================================================================================
             */
            $new_post_args = array(
                'post_title' => $title,
                'post_content' => $maintext,
                'post_status' => 'publish',
                'post_date' => $row['date_display'],
                'post_author' => $wp_user_id,
                'post_modified' => $row['modified'],
                'post_type' => 'classy'
            );

            // Insert the post into the database.
            require_once(ABSPATH . 'wp-admin/includes/post.php');
            $new_post = wp_insert_post($new_post_args);
            if (!empty($new_post)) {
                $output .= "<div class='success'>Inserted new Ad (<a href='/?p=$new_post'>$new_post</a>) successfully</div>";
            } else {
                $output .= "<span style='color:red'>Uh-oh... it failed ($new_post)</span><br>";
            }
            $classy_ad = new Classyad($new_post);

            /**
             * =========================================================================================================
             * FIGURE OUT THE CATEGORIES
             * =========================================================================================================
             */
            //
            switch ($row['category']) {
                case 'Dinghies, Liferafts & Rowboats':
                    $adcat = array('boats','dinks');
                    break;
                case 'Multihull':
                    $adcat = array('boats','multihull', 'sail');
                    break;
                case 'Classics':
                    $adcat = array('boats','classic','sail');
                    break;
                case 'Power & Houseboats':
                    $adcat = array('boats','power');
                    break;
                default:
                    $adcat = array('boats','sail');
            }

            $terms = wp_set_object_terms($new_post, $adcat, 'adcat');
            if (is_wp_error($terms)) {
                $output .= $terms->get_error_message();
            }

            /**
             * =========================================================================================================
             * FIGURE OUT AND SET THE EXPIRY DATE
             * =========================================================================================================
             */
            $mag_auto_renew = false;
            $date_display = $row['date_display'];
            $renew = $row['renewal'];
            $ad_expires = "";

            update_field('ad_subscription_level', $ad_subscription_level, $new_post);
            $classy_ad->custom_fields['ad_subscription_level'] = $ad_subscription_level;

            if (isset($renew)) {
                switch ($renew) {
                    case "Run Ad for 1 Month":
                        $ad_expires = $classy_ad->calculateExpiry('premium', $date_display);
                        break;
                    case "Run Ad for 2 Months":
                        $ad_expires = $classy_ad->calculateExpiry('premium2', $date_display);
                        break;
                    case "Run Ad for 3 Months":
                        $ad_expires = $classy_ad->calculateExpiry('premium3', $date_display);
                        break;
                    case "Run Ad Every Month Until Cancelled":
                        $ad_expires = $classy_ad->calculateExpiry('premium', $date_display);
                        $mag_auto_renew = true;
                        break;
                    default:
                        break;
                }
            } else {
                $ad_expires = $classy_ad->calculateExpiry('premium', $date_display);
            }
            update_field('ad_expires', $ad_expires, $new_post);


            /**
             * =========================================================================================================
             * UPDATE META
             * =========================================================================================================
             */
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

            // Transaction Meta
            update_field('ad_auto_renew', $mag_auto_renew, $new_post);

            // Add Contact Override data.
            if($wp_user_id == 1) {
                update_post_meta($new_post, 'override_name', $row['cc_firstname']);
                update_post_meta($new_post, 'override_phone', '(' . $row['ad_areacode'] . ') ' . $row['ad_phone']);
            }


            /**
             * =========================================================================================================
             * Get the external image URL, sideload it, and attach the ID.
             * =========================================================================================================
             */
            if(!empty($row['pictureid01']) && $row['pictureid01'] != 0) {
                $image_sql = "SELECT * FROM class_pix WHERE id = " . $row['pictureid01'];

                if (!$image_result = $db->query($image_sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                }
                while ($image_row = $image_result->fetch_assoc()) {
                    $external_image_url = 'http://www.latitude38.com/classifieds/uploads/img_classy_576/' . $image_row['this_file'];
                }

                $image_result->free();
            }

            if(isset($external_image_url)) {

                // URL to the WordPress logo
                $timeout_seconds = 10;

                // Download file to temp dir
                $temp_file = download_url($external_image_url, $timeout_seconds );
                $filetype = wp_check_filetype( $temp_file , null );

                if ( !is_wp_error( $temp_file ) ) {

                    // Array based on $_FILE as seen in PHP file uploads
                    $file = array(
                        'name'     => basename($external_image_url), // ex: wp-header-logo.png
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

            /**
             * =========================================================================================================
             * LOG THE MOST RECENT TRANSACTION!
             * =========================================================================================================
             */
            // Jzugc_Payment
            if(isset($row['cim_transactionid'])) {
                $tx_data = array(
                    'post_id' => $new_post,
                    'user_id' => $wp_user_id,
                    'gateway' => 'auth-net',
                    'transaction_msg' => "Legacy Payment Record: ",
                    'description' => 'Latitude38: New Classified Ad for (' . $new_post . ')',
                    'created' => $row['date_display'],
                    'amount' => $row['cc_amount'],
                    'transaction_id' => '0', // This is not recorded in the old system.
                    'cim_profile_id' => $row['cim_customerid'],
                    'cim_payment_profile_id' => $row['cim_transactionid']
                );

                $wpdb->insert( $wpdb->prefix . 'l38_transactions', $tx_data );
                $order_id = $wpdb->insert_id;
            }
        }
        echo $output;
        die();
    }

    public static function get_post_from_lasso_id($classy_id) {
        // Look up to see if the Classified exists...
        $wp_id = false;
        $args = array(
            'post_type'		=>	'classy',
            'meta_key'	    =>	'lasso_classy_id',
            'meta_value'    =>  $classy_id
        );

        $wp_posts = new WP_Query( $args );
        if( $wp_posts->have_posts() ) {
            $wp_posts->the_post();
            $wp_id = get_the_ID();
        }
        return $wp_id;
    }

}


