<?php
class Classyads_Ajax {

    public function __construct(  ) {

    }

    /**
     * Receives the AJAX Classy Create Post
     * Validate, Filter, Sanitize the Arguments... and then pass to create the Ad,
     */
    public function create_classyad() {
        global $current_user;

        if (!is_user_logged_in()) {
            wp_die();
        }
        check_ajax_referer('create_classyad', '_create_classyad_nonce');

        $posted_data =  isset( $_POST ) ? $_POST : array();
        $file_data = isset( $_FILES ) ? $_FILES : array();

        // This now holds a whole bunch of stuff!
        $data = array_merge( $posted_data, $file_data );

        if($data['post_id'] > 0) {
            // If the post_id has been set, we're updating the ad after errors rather than saving the original.
            // and he should be the owner.
            $classyad = new Classyad($data['post_id']);
            $classyad->update($data);
        } else {
            // it's a new ad... create it.
            $classyad = new Classyad();
            $data['post_id'] = $classyad->create($data);
        }

        $json_response = array();

        if($classyad->post_id > 0)  $json_response['post_id'] = $classyad->post_id;

        if(empty($classyad->errors)) {
            // Then if the CC is authorized, it'll go live.
            $amount = $classyad->get_plan_amount($data['ad_subscription_level']);

            $override_payment = (isset($data['cim_payment_profile_id']) && $data['cim_payment_profile_id'] == "admin_override") ? true : false;
            if($amount > 0 && !$override_payment) {
                // Augment the $data array with outstanding fields.
                $data['amount'] = $amount;
                $data['payment_description'] = "Latitude 38: New Classified Ad ($classyad->post_id) for $classyad->title, expiring " . $classyad->lookupExpiry('F jS, Y' );

                // Start up the payment system
                $payment = new Jzugc_Payment($current_user->ID);

                // PC::debug($data['cim_payment_profile_id']);
                if(isset($data['cim_payment_profile_id'])) {
                    // PC::debug('charging profile');
                    // They've chosen to bill an existing card.
                    $payment_success = $payment->chargeCustomerProfile($data, $data['cim_payment_profile_id']);

                } else {
                    // PC::debug('charge card.');
                    // Validate card details and bill directly
                    $ready = $payment->validateFields($data);
                    if($ready) {
                        $payment_success = $payment->chargeCreditCard($data);
                    } else { // FAILED CARD INITIAL VALIDATION
                        $json_response['errors'] = $payment->errors;
                        $json_response['msg'] = "Your payment information does not seem to be valid. Please try again";
                        wp_send_json_error($json_response);
                    }
                }

                if($payment_success) {
                    // SUCCESS!!!!
                    $classyad->publish();
                    $json_response['msg'] = "Your Classy Ad has been added successfully.";
                    $json_response['url'] = get_permalink($classyad->post_id);
                    wp_send_json_success($json_response);
                } else { // PAYMENT DECLINED AT PROCESSOR
                    $json_response['msg'] = "Your payment information wasn't accepted by our processor. Please try again\n " . $payment->response;
                    wp_send_json_error($json_response);
                }

            } else {
                // NO PAYMENT NECESSARY. SUCCESS
                $classyad->publish();
                $json_response['msg'] = "Your Classy Ad has been added successfully. ";
                $json_response['url'] = get_permalink($classyad->post_id);
                wp_send_json_success($json_response);
            }
        } else { // REQUIRED FIELDS FAILED VALIDATION
            $json_response['errors'] = $classyad->errors;
            $json_response['msg'] = "We had problems creating your classified ad. Your card has not yet been charged. Please correct the following problems: ";
            wp_send_json_error($json_response);
        }
    }

    /**
     * Used for renewing and upgrading a classyad.
     */
    public function renew_classyad() {
        global $current_user;

        if (!is_user_logged_in()) {
            // Not a valid user to perform this operation.
            wp_die();
        }
        check_ajax_referer('renew_classyad', '_renew_classyad_nonce');

        $data =  isset( $_POST ) ? $_POST : array();

        $classyad = new Classyad($data['post_id']);
        $owner = new JZ_User($classyad->owner);

        if($current_user->ID == $classyad->owner || $current_user->has_cap('edit_pages')) {

            $plan = $classyad->custom_fields['ad_subscription_level'];
            if(isset($data['ad_subscription_level'])) $plan = $data['ad_subscription_level'];

            $data['amount'] = $classyad->get_plan_amount($plan);

            $override_payment = (isset($data['cim_payment_profile_id']) && $data['cim_payment_profile_id'] == "admin_override") ? true : false;
            if($data['amount'] > 0 && !$override_payment ) {
                // NO PAYMENT NECESSARY. SUCCESS
                if ( function_exists( 'SimpleLogger' ) ) {
                    SimpleLogger()->info( 'User {username} renewed this classyad {title} (No Charge)',
                        array(
                            'username' => $current_user->user_nicename,
                            'title' => $classyad->title,
                            '_initiator' => SimpleLoggerLogInitiators::WP_USER,
                            '_user_id' => $current_user->ID,
                            '_user_email' => $current_user->user_email
                        ));
                }

                $classyad->renew($data['plan_level']);
                $json_response['msg'] = "Your Classy Ad has been Renewed. No Charge ";
                wp_send_json_success($json_response);

            } else {
                // Attempt Payment Processing.
                // Augment the $data array with outstanding fields.
                $data['post_author'] = $owner->ID;
                $data['payment_description'] = "Latitude 38: Renew Classified Ad ($classyad->post_id) for $classyad->title";

                $payment = new Jzugc_Payment($owner);
                if(isset($data['cim_payment_profile_id'])) {
                    // They've chosen to bill an existing card.
                    $payment_success = $payment->chargeCustomerProfile($data);
                } else {
                    // For now they should do that from their payment profile page.
                    $payment_success = $payment->chargeCreditCard($data);
                }

                if($payment_success) {
                    // SUCCESS!!!!
                    $classyad->renew($data['plan_level']);
                    $json_response['msg'] = "Your Classy Ad has been renewed successfully.";
                    $json_response['expires'] = $classyad->key_dates['expiry']->format('F jS, Y');
                    $json_response['ad_edition'] = $classyad->key_dates['expiry']->format('F Y');

                    if ( function_exists( 'SimpleLogger' ) ) {
                        SimpleLogger()->info( 'User {username} renewed this classyad {title} for ${amount}',
                            array(
                                'username' => $current_user->user_nicename,
                                'title' => $classyad->title,
                                'amount' => $classyad->plan['amount'],
                                '_initiator' => SimpleLoggerLogInitiators::WP_USER,
                                '_user_id' => $current_user->ID,
                                '_user_email' => $current_user->user_email
                            ));
                    }
                    wp_send_json_success($json_response);

                } else { // PAYMENT DECLINED AT PROCESSOR
                    $json_response['msg'] = "Your payment information wasn't accepted by our processor. Please try again\n " . $payment->response;
                    wp_send_json_error($json_response);
                }
            }

        } else {
            // USER DOES NOT HAVE PERMISSIONS TO DO THIS?
            wp_die();
        }
    }



    public function update_classyad() {

        // Check this is legit.
        if(false == check_ajax_referer('update_classyad', '_update_classyad_nonce', false)) {
            wp_send_json_error('Sorry. Something went wrong. Please refresh the page and try again.');
        };

        $current_user = wp_get_current_user();
        $classyad = new Classyad($_REQUEST['post_id']);

        if (!is_user_logged_in() || !($current_user->ID == $classyad->owner || current_user_can('edit_posts'))) {
            // Not a valid user to perform this operation.
            wp_send_json_error('Sorry. Something went wrong. Please log back in and try again.');
        }

        $new_data = $_REQUEST;

        // strip the post related fields
        unset($new_data['action'], $new_data['_wp_http_referer'], $new_data['_update_classyad_nonce'], $new_data['post_id']);

        $classyad->update($new_data);

        if(empty($classyad->errors)) {
            $json_response['fields'] = array();
            foreach($new_data as $key=>$value) {
                $json_response['fields'][$key] = $classyad->custom_fields[$key];
            }
            wp_send_json_success($json_response);
        } else {
            $json_response['errors'] = $classyad->errors;
            $json_response['msg'] = "We had problems updating your classified ad. Please correct the following problems: ";
            wp_send_json_error($json_response);
        }

    }


    /**
     * Allows user to 'remove' a classified ad listing, by setting it's post-status to 'draft'
     */
    public function remove_classyad() {
        // Check this is legit.
        if(false == check_ajax_referer('remove_classyad', '_remove_classyad_nonce', false)) {
            wp_send_json_error('Sorry. Something went wrong. Please refresh the page and try again.');
        };

        $current_user = wp_get_current_user();
        $classyad = new Classyad($_REQUEST['post_id']);

        if (!is_user_logged_in() || !($current_user->ID == $classyad->owner || current_user_can('edit_posts'))) {
            // Not a valid user to perform this operation.
            wp_send_json_error('Sorry. Something went wrong. Please log back in and try again.');
        }

        $result = $classyad->remove();

        if($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Update the main photo based on an upload from the form.
     * We'll want to change this, so that it receives the file, and
     */
    public function update_classy_mainphoto() {
        // Built with the help of : https://www.ibenic.com/wordpress-file-upload-with-ajax/
        check_ajax_referer('update-mainphoto', '_mainphoto_nonce');

        $posted_data = isset($_POST) ? $_POST : array();
        $file_data = isset($_FILES) ? $_FILES : array();
        $data = array_merge($posted_data, $file_data);

        $current_user = wp_get_current_user();
        $classyad = new Classyad($data['post_id']);

        if($classyad->status != 'loaded') {
            wp_die('Classyad does not exist with that ID');
        }

        if (!is_user_logged_in() || !($current_user->ID == $classyad->owner || current_user_can('edit_posts'))) {
            // Not a valid user to perform this operation.
            wp_die('You do not have the privileges to edit this ad?');
        }

        $new_image_id = $classyad->uploadImage($data['main_photo']);
        $classyad->setFeaturedImage($new_image_id);

        echo "Success!";
        wp_die();
    }


    /**
     *  Returns a list of Classy Ads depending on variables in form submission.
     */
    public function refresh_classy_list() {

        if (!empty($_REQUEST['adcat'])) $adcat = $_REQUEST['adcat'];
        $custom = array();
        if (isset($_REQUEST['search'])) $custom['search'] = $_REQUEST['search'];
        if (isset($_REQUEST['paged'])) $custom['paged'] = $_REQUEST['paged'];
        if (isset($_REQUEST['min_length'])) $custom['min_length'] = intval($_REQUEST['min_length']);
        if (isset($_REQUEST['max_length'])) $custom['max_length'] = intval($_REQUEST['max_length']);

        if (!empty($adcat)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'adcat',
                    'field' => 'id',
                    'terms' => $adcat
                )
            );
        }

        if (!empty($custom)) {
            $args['meta_query'] = array();

            if (isset($custom['paged'])) {
                $args['paged'] = $custom['paged'];
            }
            if (isset($custom['min_length']) && $custom['min_length'] > 0) {
                $args['meta_query'][] = array(
                    'key' => 'boat_length',
                    'value' => $custom['min_length'],
                    'compare' => '>='
                );
            }
            if (isset($custom['max_length']) && $custom['max_length'] > 0) {
                $args['meta_query'][] = array(
                    'key' => 'boat_length',
                    'value' => $custom['max_length'],
                    'compare' => '<='
                );
            }
            if (isset($custom['search'])) {
                $args['meta_query'][] = array(
                    'key' => 'boat_model',
                    'value' => $custom['search'],
                    'compare' => 'LIKE'
                );
            }
        }

        $output = get_the_classys($args);

        echo $output;
        wp_die();
    }


}


