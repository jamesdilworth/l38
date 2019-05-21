<?php

/**
 * Class to hold all the common functions and variables associated with a Classyad
 * It'd be nice to just extend WP_Post, but this can't be done since WP_Post has been declared as final
 */

class Classyad {

    public $post_id;
    public $post; // Holds the post object.
    public $owner; // ID of the classy owner?

    public $errors; // Holds array of 'field' => 'error' codes made while creating or updating an ad.

    public $title;
    public $primary_cat; // Type of object we're looking at.
    public $custom_fields; // Will hold the rest of the custom meta fields.

    public $key_dates; // Will hold an array of key dates.
    public $plan; // This holds the relevant plan array of items as listed in classyads_config

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
            'ad_subscription_level' => 'free'
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
            $loaded_fields = get_post_meta($post_id);

            foreach($loaded_fields as $field => $value) {
                $loaded_fields[$field] = $this->prepareOutput($field, $value[0]);
            }

            $this->title = $this->post->post_title;
            $this->primary_cat = wp_get_post_terms( $post_id, 'adcat', array("fields" => "slugs", "parent" => 0));
            $this->custom_fields = array_merge($this->custom_fields, $loaded_fields);

            $this->key_dates = $this->lookupKeyDates();
            $this->plan = $this->lookup_plan();

            $this->status = 'loaded';
        } else {
            $this->status = "failed to load classy";
        }

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

            $this->title = $data['boat_length'] . 'ft ' . $data['boat_model'] . ', ' . $data['boat_year'];
        } else {
            $this->title = $data['title'];
        }

        $new_post_args = array(
            'post_title'    => $this->title,
            'post_content'  => $data['maintext'],
            'post_status'   => 'draft', // until we've charged the card and everything.
            'post_type' => 'classy'
        );

        if(isset($data['wp_user_id'])) {
            $new_post_args['post_author'] = $data['wp_user_id'];
        }

        // Insert the post into the database.
        require_once( ABSPATH . 'wp-admin/includes/post.php' );
        $new_post_id = wp_insert_post( $new_post_args );

        if(!empty($new_post_id)) {
            $this->post_id = $new_post_id;
            $this->status = "Saved";
        } else {
            PC::debug("Uh-oh... it failed (" . $new_post_id . ")");
            $this->status = "Failed to create";
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
        if(isset($data['ad_asking_price'])) $this->update_field('ad_asking_price', $data['ad_asking_price']);
        if(isset($data['ad_external_url'])) $this->update_field('ad_external_url', $data['ad_external_url']);
        if(isset($data['ad_sale_terms'])) $this->update_field('ad_sale_terms', $data['ad_sale_terms']); // TODO Decide if we're going to use this!
        if(isset($data['boat_location'])) $this->update_field('boat_location', $data['boat_location']); // Not really a boat only field.

        // Print Meta
        if(isset($data['ad_mag_text'])) $this->update_field('ad_mag_text', $data['ad_mag_text']);

        // Featured Image
        if(isset($data['featured_image']) && $data['featured_image']['size'] > 0) {
            $featured_image_id = $this->uploadImage($data['featured_image']);
            set_post_thumbnail($new_post_id, $featured_image_id);
        } else if(isset($data['external_image_url'])) {
            // TODO!!! The import script has a sideload of an image.
        }

        // TODO!!! Handle Additional Images...

        // Preferred Contact Details... TODO - Add these to ACF.
        if(isset($data['override_owner']) &&  $data['override_owner'] = 1) $this->update_field('override_owner', true);
        if(isset($data['override_name'])) $this->update_field('override_name', $data['override_name']);
        if(isset($data['override_phone'])) $this->update_field('override_phone', $data['override_phone']);
        if(isset($data['override_other'])) $this->update_field('override_other', $data['override_other']);

        // Boats
        if(isset($data['boat_year'])) $this->update_field('boat_year', $data['boat_year']);
        if(isset($data['boat_length'])) $this->update_field('boat_length', $data['boat_length']);
        if(isset($data['boat_model'])) $this->update_field('boat_model', $data['boat_model']);

        // Set the subscription level
        if(isset($data['ad_subscription_level'])) $this->update_field('ad_subscription_level', $data['ad_subscription_level']);

        // Check overrides.
        if(isset($data['boat_model'])) $this->update_field('boat_model', $data['boat_model']);

        // Set Expiry Dates
        $this->setExpiry($this->calculateExpiry($data['ad_subscription_level']));

        return $new_post_id;
    }

    /**
     * Updates the Classy with data submitted from a form.
     */
    public function update($data) {
        foreach($data as $field => $value) {
            $result = $this->update_field($field, $value);
        }
    }

    public function sideloadImage($url) {
        // For sideloading an image from the old site.
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

    /**
     * Centralized validation function. Returns true if no problems, and the error if not.
     */
    public function validate_field($field, $value) {
        // TODO ... a lot of these still need to be defined.

        switch ($field) {
            case 'maintext' :
                // TODO! Check it's not empty... or has the min/.
                break;
            case 'ad_asking_price' :
                if (!preg_match("/^[0-9]+(\.[0-9]{2})?$/", $value)) {
                    return "Your asking price should be a number (Enter zero if you don't want to set a price)";
                }
                // TODO! It should resolve to an integer or (free)
                break;
            case 'boat_year' :
                // TODO! Check it's a year!
                break;
            case 'boat_length' :
                // TODO! Should boil down to an integer.
                break;
        }
        // No validation errors, return false.
        return false;
    }

    /* Save the value of a field, and return the sanitized version to the user. */
    public function update_field($field, $value) {
        // TODO - Think through the sanitization here
        // TODO - Some fields shouldn't be editable by the user... for example expiry.

        // first validate the field.
        $validation_error = $this->validate_field($field, $value);
        if($validation_error) {
            // Didn't validate. Log the error and return false.
            $this->errors[$field] = $validation_error;
            return false;
        }

        // Now we can filter, sanitize and save.
        switch ($field) {
            case 'title' :
                sanitize_text_field($value);
                break;
            case 'maintext' :
                // Main Content is an exception as it's not a meta field....
                $value = wp_kses_data($value);
                wp_update_post(array( 'ID' => $this->post_id, 'post_content' => $value ), true);
                // ....so we'll return right away.
                return $this->custom_fields[$field] = $this->prepareOutput($field, $value);
                break;
            case 'ad_asking_price':
                // TODO!!! - We should really allow any user input for the asking price, and then have a shadow field
                $value = preg_replace("/[^0-9\.]/", "", $value);
                break;
            case 'ad_mag_text' :
                $value = sanitize_textarea_field(wp_kses($value, array()));
                break;
            case 'ad_subscription_level':
                break;
            case 'ad_external_url' :
                $value = esc_url($value);
                break;
            case 'boat_length' :
                $value = intval($value);
                break;
            case 'boat_year':
                $value = intval($value);
                break;
            case 'boat_model':
                $value = wp_strip_all_tags($value);
                break;
            case 'boat_location':
                $value = wp_strip_all_tags($value);
                break;
            default :
                // PC::debug('I don\'t think we want to save ' . $field . ' , correct?');
                return false; // Since this isn't an approved field. Don't save it. It could be a nonce, a self-injected field. Who knows!
                break;
        }

        // Save the field.
        update_post_meta( $this->post_id, $field, $value );

        // Update the custom_field and return that value.
        return $this->custom_fields[$field] = $this->prepareOutput($field, $value);
    }

    public function prepareOutput($field, $value) {
        // Filters used to clean or prepare output.... for example preparing dates, currency, etc.

        switch($field) {
            case 'title' :
                $value = esc_html($value);
                break;
            case 'maintext' :
                $value = wp_rel_nofollow( balanceTags( $value, true));
                break;
            case 'ad_asking_price' :
                $value = money_format('%.0n', $value);
                break;
            case 'ad_external_url' :
                $value = esc_url($value);
                break;
            case 'phone' :
                $value = JZUGC_format_phone($value);
                break;
            default:
                // didn't find any custom stuff, so just return $fieldname;
                break;
        }

        // If it didn't match anything, just return it.
        return $value;
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

        $months = $this->get_plan_duration($plan);

        if($this->is_print_ad()) {
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

            $expirydate = clone $cutoff;
            if($months > 1 ) {
                $expirydate->modify('+' . $months . ' months');
            } else {
                $expirydate->modify('+1 month');
            }
            $expirydate->modify('last day of ' . $expirydate->format('F'));

        } else {

            $expirydate = clone $placement_date;
            if($months > 1 ) {
                $expirydate->modify('+' . $months . ' months');
            } else {
                $expirydate->modify('+1 month');
            }
        }
        return $expirydate->format('Ymd');
    }

    public function setExpiry($date) {
        // This is currently held in an ACF field as yyyymmdd
        update_post_meta( $this->post_id, 'ad_expires', $date );
        $this->custom_fields['ad_expires'] = $date;
        $this->lookupKeyDates(); // Recalculates everything.

    }

    public function lookupExpiry($format = 'Ymd') {
        // First check to see if the expire date is loaded into the custom fields...
        $ad_expires = get_post_meta($this->post_id, 'ad_expires', true);

        $expire_obj = new DateTime($ad_expires);
        $ad_expires = $expire_obj->format($format);

        return $ad_expires;
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
        $ad_placed = new DateTime(get_the_date('', $this->post_id));

        $today = new DateTime();
        $day = $today->format('d');

        $expirydate = new DateTime($this->lookupExpiry());

        // The cutoff is the next magazine cutoff date... this relates to the magazine, not this classyad!!!
        $cutoff = new DateTime('15 ' . $today->format('F Y'));
        if($cutoff < $today) {
            $cutoff->modify('+1 month');
        }

        // If you placed an ad today, this would be the next magazine it would get into!
        $next_magazine_for_print = clone $cutoff;
        $next_magazine_for_print->modify('last day of next month');

        // Initially the renewal deadline is before the next cutoff.
        $renewal_deadline = new DateTime('15 ' . $expirydate->format('F Y'));

        // But if it's already passed, we still want to convince them to renew for the following month.
        if($today > $renewal_deadline) {
            $renewal_deadline = clone $today;
            if(absint($day) < 15) {
                $renewal_deadline->setDate($today->format('Y'), $today->format('m'), 15);
            } else {
                $renewal_deadline->modify('next month');
                $renewal_deadline->modify($renewal_deadline->format('Y') . $renewal_deadline->format('m') . "-15");
            }
        }

        // This would be the first day of the last month that this ad is ready for.
        $last_ad_edition = clone $expirydate;
        $last_ad_edition->modify('first day of this month');

        // If they were to extend for a month, this would be the edition.
        $next_ad_edition = clone $renewal_deadline;
        $next_ad_edition->modify('first day of next month');

        $today = new DateTime(); // Fake it with new DateTime('December 17, 2018');

        $key_dates = array();
        // KEY DATES
        $key_dates['today'] = $today;
        $key_dates['ad_placed_on'] = $ad_placed;
        $key_dates['expiry'] = $expirydate;
        $key_dates['cutoff'] = $cutoff;
        $key_dates['next_magazine_for_print'] = $next_magazine_for_print;
        $key_dates['renewal_deadline'] = $renewal_deadline;
        $key_dates['next_ad_edition'] = $next_ad_edition; // The edition after that.

        // BOOLS...
        $key_dates['can_make_print_changes'] = $cutoff < $last_ad_edition  ? true : false;
        $key_dates['can_renew'] = $today < $key_dates['renewal_deadline'] ? true : false;
        $key_dates['has_expired'] = $today > $key_dates['expiry'] ? true : false;

        $this->key_dates = $key_dates;
        return $key_dates;
    }

    public function lookupMainImageURL() {
        if(!isset($this->main_image_url)) {
            $this->main_image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'medium') : false;
        }
        return $this->main_image_url;
    }

    public function lookup_plan() {
        global $classyads_config;

        $plan_name = get_post_meta($this->post_id, 'ad_subscription_level', true);
        if(!empty($plan_name)) {
           return $classyads_config['plans'][$plan_name];
        } else {
            return false;
        }
    }

    public function lookupOwnerDetails() {
        // If this ad is created by an admin who overrides the user, or imported without a user id, we'll set user_id of 1.
        if(isset($this->custom_fields['override_owner']) || $this->owner == 1) {
            $owner_details = array(
                'firstname' => "",
                'lastname' => "",
                'display_name'  => isset($this->custom_fields['override_name']) ? $this->custom_fields['override_name'] : null,
                'email' => isset($this->custom_fields['override_email']) ? $this->custom_fields['override_email'] : null,
                'phone' => isset($this->custom_fields['override_phone']) ? $this->custom_fields['override_phone'] : null,
                'other' => isset($this->custom_fields['override_other']) ? $this->custom_fields['override_other'] : null
            );
            if(!empty($owner_details['display_name']))
                $owner_details['firstname'] = substr($owner_details['display_name'], 0, strpos($owner_details['display_name'], ' '));

        } else {
            $owner = new JZ_User($this->owner);
            $owner_details = array(
                'firstname' => $owner->first_name,
                'lastname' => $owner->last_name,
                'display_name'  => isset($this->custom_fields['override_name']) ? $this->custom_fields['override_name'] : $owner->display_name,
                'email' => isset($this->custom_fields['override_email']) ? $this->custom_fields['override_email'] : $owner->user_email,
                'phone' => isset($this->custom_fields['override_phone']) ? $this->custom_fields['override_phone'] : isset($owner->phone) ? $owner->phone : null,
                'other' => isset($this->custom_fields['override_other']) ? $this->custom_fields['override_other'] : isset($owner->other) ? $owner->other : null
            );
        }
        $this->owner_deets = $owner_details;
        return $owner_details;

    }

    public function get_plan_amount($plan) {
        global $classyads_config;
        $plan = $classyads_config['plans'][$plan];
        if (isset($plan)) {
            return $plan['amount'];
        } else {
            $this->errors['plan'] = 'Could not find plan in the system';
            return 0;
        }
    }

    private function get_plan_duration($plan)
    {
        global $classyads_config;
        $plan = $classyads_config['plans'][$plan];
        if (isset($plan)) {
            return $plan['months'];
        } else {
            $this->errors['plan'] = 'Could not find plan in the system';
            return 1;
        }
    }

    public function is_print_ad() {
        global $classyads_config;
        $plan_name = $this->custom_fields['ad_subscription_level'];
        $plan = $classyads_config['plans'][$plan_name];
        if(isset($plan) && $plan['in_print'])
            return true;
        else
            return false;
    }

    public function publish() {
        // We could have used wp_publish_post(), but this doesn't setup a permalink, so this is more robust. http://alexking.org/blog/2011/09/19/wp_publish_post-does-not-set-post_
        wp_update_post(array(
            'ID' => $this->post_id,
            'post_status' => 'publish'
            )
        );
    }

    /* Output the Category that this ad should appear under in the magazine? */
    public function getMagazineCat() {

        // Load $adcats with a list of slugs that this item falls into.
        $adcats = wp_get_post_terms( $this->post_id, 'adcat', array("fields" => "slugs"));

        $section = "Uncategorized";
        if(in_array('boats', $adcats)) {
            // It's a boat....
            if(in_array('dinks', $adcats)) {
                $section = "Dinghies, Liferafts and Rowboats";
            }
            else if(has_term(array('power','rib','houseboat'), 'adcat', $this->post_id)) {
                $section = "Power & Houseboats";
            }
            else if(in_array('classic', $adcats)) {
                $section = "Classic Boats";
            }
            else if(in_array('multihull', $adcats)) {
                $section = "Multihulls";
            }
            else {
                $length = $this->custom_fields['boat_length'];
                if($length >= 51) {
                    $section = "51 Feet and Over";
                } else if ($length >= 40 ) {
                    $section = "40 to 50 Feet";
                } else if ($length >= 35 ) {
                    $section = "35 to 39 Feet";
                } else if ($length >= 32 ) {
                    $section = "32 to 34 Feet";
                } else if ($length >= 29 ) {
                    $section = "29 to 31 Feet";
                } else if ($length >= 25 ) {
                    $section = "25 to 28 Feet";
                } else if ($length < 25) {
                    $section = "24 Feet and Under";
                }
            }
        } else if(in_array('jobs', $adcats)) {
            $section = "Jobs";
        } else if(in_array('gear', $adcats)) {
            $section = "Gear";
        } else if(in_array('property', $adcats)) {
            $section = "Property";
        } else if(in_array('other', $adcats)) {
            $section = "Other";
        } else {
            // We don't know what it is??? Let's use the cat that we have.
            $adcat = wp_get_post_terms( $this->post_id, 'adcat');
            if(!empty($adcat)) {
                $section = $adcat[0]->name;
            }
        }
        return $section;
    }

    public function getUpgradeOptions() {
        // Return an array of qualifying plans for this post type.
        $options = $this->getPlanOptions();
    }

    public function getPlanOptions() {
        // Assemble an array of Plan options for this type of ad.
        return array('');
    }

    public function upgradePlan() {
        // Let's say someone starts with an online only plan, but now wants to upgrade to a paid plan.
    }

    public function delete() {
        // Delete a Classy Ad... under what circumstances would we do this?
    }

    public function renew($plan = null) {
        global $classyads_config;

        if(!isset($plan)) {
            $plan = $this->custom_fields['ad_subscription_level'];
        } else {
            // We might be changing the plan... so make sure this gets set.
            update_field('ad_subscription', 'plan');
        }

        $expirydate = $this->key_dates['expiry'];
        $today = new DateTime();
        if($today > $expirydate) {
            $expirydate = $today;
            // Already Expired.
            $update = wp_update_post( array(
                "ID" => $this->ID,
                "post_status" => "publish"
            ));

        }
        $is_print_ad = $classyads_config['plans'][$plan]['in_print'];
        $plan_extension_in_months = $classyads_config['plans'][$plan]['months'];

        // Knock it up x months... making sure to keep clear of some weird behavior when adding months. - https://stackoverflow.com/questions/3602405/php-datetimemodify-adding-and-subtracting-months
        $day = $expirydate->format('j');
        $expirydate->modify('first day of +' . $plan_extension_in_months . ' month');
        $expirydate->modify('+' . (min($day, $expirydate->format('t')) - 1) . ' days');

        // If it's a print ad... the expiry date should be at least the end of the next printable month.
        if($is_print_ad && $expirydate < $this->key_dates['next_magazine_for_print']) {
            $expirydate = $this->key_dates['next_magazine_for_print'];
        }

        $this->setExpiry($expirydate->format('Ymd'));

    }

    public function expire() {
        wp_update_post(array(
            'ID'           => $this->post_id,
            'post_status'   => 'expired'
        ));
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


    // Magic method to detect variables.
    function __get($key) {
        switch ($key) {
            case 'key_dates' :
                return $this->lookupKeyDates();
            case 'main_image_url' :
                return $this->lookupMainImageURL();
            case 'owner_deets' :
                return $this->lookupOwnerDetails();
        }
    }

}
