<?php
class Classyads_Ajax {

    public function __construct(  ) {

    }

    /**
     * Receives the AJAX Classy Create Post
     * Validate, Filter, Sanitize the Arguments... and then pass to create the Ad,
     */
    public function create_classyad() {

        $current_user = wp_get_current_user();

        if (!is_user_logged_in()) {
            // Not a valid user to perform this operation.
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
        } else {
            // it's a new ad... create it.
            $classyad = new Classyad();
            $classyad->create($data);
        }

        $response_data = array();

        // Then if the CC is authorized, it'll go live.
        if(empty($classyad->errors)) {
            $amount = classyads_get_plan_amount($data['ad_subscription_level']);
            $response_data['payment_response'] = chargeCreditCard($amount, $data);
        }

        if(empty($classyad->errors)) {
            $response_data['msg'] = "Your Classy Ad has been added successfully. It can now be seen at.... ";
            wp_send_json_success($response_data);
        } else {
            $response_data['msg'] = "We had problems updating your classified ad. Your card has not yet been charged. Please correct the following problems: ";
            wp_send_json_error($response_data);
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
        unset($new_data['action'], $new_data['_wp_http_referer'], $new_data['_update_classyad_nonce']);

        foreach($new_data as $field => $value) {
            $result = $classyad->validate_field($field, $value);
        }

        $errors = Array();
        $wins = Array();
        foreach($new_data as $field => $value) {
            $result = $classyad->update_field($field, $value);
            if($result) {
                if($result == 'Saved') {
                    $errors[$field] = $result; // The error message?
                } else {
                    $wins[$field] = $result;
                }
            }
        }

        if(empty($errors)) {
            wp_send_json_success($wins);
        } else {
            wp_send_json_error($errors);
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

        echo "Success";
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


