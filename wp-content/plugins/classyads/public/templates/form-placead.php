<?php
/**
 * Form for creating a new ad on the site.
 *
 */

global $classyads_config; //

?>

<div class="choose_plan">
        <h3>Choose a Plan</h3>

        <?php
        $output = "<div class='plan_options equalize'>";
        foreach($classyads_config['plans'] as $key => $plan) {
            if(in_array($primary_adcat, $plan['categories']) && $plan['status'] == 'active') {
                $output .= "<div class='plan'>";
                $output .= "    <div class='title'>" . $plan['name'] . "</div>";
                $output .= "    <div class='price'>$" . $plan['amount'] . "<span class='per-month'>/month</span></div>";
                $output .= "    <div class='body'>";
                $output .= "    <ul class='features'>";
                if(isset($plan['features'])) {
                    foreach($plan['features'] as $feature) {
                        $output .= "<li>$feature</li>";
                    }
                }
                $output .= "</ul><div class='cta'><a href='' class='btn choose_plan' data-plan='" . $plan['type'] . "'>Start</a></div>";
                $output .= "</div>"; // end body
                $output .= "</div>"; // end plan
            }
        }
        $output .= "</div>"; // end plans
        echo $output;
        ?>
</div>

<div id="create_classyad_container" class="mfp-hide jz-modal">
    <div class="wrapper">
        <h3 class="title">Create Your Classy Ad
            <div class="subtitle">(You'll be able to edit your ad after placing it.)</div>
        </h3>
        <div class="notice"></div>
        <form id="create_classyad" class="create_classyad jz-form" enctype="multipart/form-data" method="post"><?php // TODO! - We need a non-ajax backup submit! ?>

            <section class="online_listing">
                <h3>Online Listing</h3>
                <?php if( $primary_adcat == 'boats') : ?>
                    <div>
                        <div class="one-third field">
                            <label>Boat Model</label>
                            <input type="text" name="boat_model" placeholder="example: Catalina 30" required>
                        </div>
                        <div class="one-third field">
                            <label>Boat Length (ft)</label>
                            <input type="text" name="boat_length" required>
                        </div>
                        <div class="one-third field">
                            <label>Manufacture Year</label>
                            <input type="text" name="boat_year" required>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($primary_adcat != 'boats') : ?>
                    <div class="field">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" required>
                    </div>
                <?php endif; ?>

                <div class="field">
                    <label for="maintext">Description</label>
                    <textarea name="maintext" id="maintext" required></textarea>
                </div>

                <div class="field">
                    <label for="ad_asking_price">Price</label>
                    <div class="currencyinput dollar"><input type="text" name="ad_asking_price" id="ad_asking_price" required></div>
                </div>

                <div class="field ">
                    <label for="adcats">Categories (Choose 2 or 3 max)</label>
                    <ul class="list-3col">
                        <?php
                        foreach($secondary_adcats as $adcat) {
                            $term = get_term_by( 'id', $adcat, 'adcat' );
                            echo "<li><input type='checkbox' name='secondary_adcats[]' value='$adcat'> $term->name </li>";
                        }
                        ?>
                    </ul>
                </div>
                <div>
                    <div class="one-half field">
                        <label>Location</label>
                        <input type="text" name="boat_location" placeholder="Example: Sausalito, CA or La Paz, Mexico">
                    </div>
                    <div class="one-half field">
                        <label>External URL</label>
                        <input type="url" name="ad_external_url" data-msg="Enter a valid URL (Start with http://)" placeholder="http://www....">
                    </div>
                </div>
            </section>


            <section class="magazine_listing">
                <h3>Magazine Listing</h3>
                <p>Your online listing can be accompanied by a <span class="_no_chars">200</span> character listing in the classifieds section of the magazine.  </p>
                <div class="field">
                    <label for="ad_mag_text">Copy for the magazine:</label>
                    <textarea name="ad_mag_text"></textarea>
                    <div class="counter-container"><span class="counter"></span> characters remaining</div>
                </div>
            </section>


            <section class="upload_images">
                <h3>Images</h3>
                <div class="field">
                    <label for="photos">Upload Featured Image</label>
                    <input type="file" name="featured_image" class="jzugc_image">
                </div>

                <!--
                <div class="section_upload_multiple">
                    <div class="field">
                        <label for="photos">Additional Images</label>
                        <input type="file" name="additional_images[]" class="jzugc_image" multiple>
                    </div>

                    <div class="field photo-upload">
                        <label for="photos">Additional Images</label>
                        <input type="file" class="filepond" name="photos[]" multiple data-max-file-size="3MB" data-max-files="3">
                    </div>
                </div>
                -->
            </section>

            <section class="contact_info">
                <h3>Contact Info</h3>
                <p>Only change if needed. Your full name and email will be masked from spammers.</p>

                <?php // Admin override to allow non-payment.

                if(current_user_can('edit_posts')) {
                ?>
                    <input type='checkbox' name='override_owner' id="owner_admin_override" value="1"> <span class='admin_note'> Admin Override? Force new contact info (versus your own contact details)<br>
                    <div class="field">
                        <label for="email">Contact Name</label>
                        <input type="text" name="override_name"  type="text" placeholder="<?= $current_jzuser->first_name ?>" >
                    </div>
                    <?php
                }
                ?>

                <!--
                <div class="field">
                    <label for="email">Available Contact Method(s)</label>
                    <input type="checkbox" name="preferred_contact_method" value="email"> Email
                    <input type="checkbox" name="preferred_contact_method" value="phone"> Phone
                </div>
                -->
                <div class="field">
                    <label for="override_email">Email</label>
                    <input type="text" name="override_email"  type="email" placeholder="<?= $current_jzuser->user_email ?>" >
                </div>
                <div class="field">
                    <label for="override_phone">Phone</label>
                    <input type="text" name="override_phone" type="tel" placeholder="<?= JZUGC_format_phone($current_jzuser->phone); ?>" >
                </div>
                <div class="field">
                    <label for="override_other">Other</label>
                    <input type="text" name="override_other" type="text" placeholder="<?= $current_jzuser->othercontact ?>" >
                </div>
            </section>

            <section class="payment_info">
                <h3>Payment Options</h3>
                <?php
                    if(!isset($current_jzuser))
                        $current_jzuser = new JZ_User($current_user->ID);

                    $profiles = $current_jzuser->cim_payment_profiles;
                    if(empty($profiles)) {
                        // No payment profile... let's add one.
                        include(CLASSYADS_PATH . 'public/templates/section-new-cccard.php');
                    } else {
                        // User has a payment profile... show them that screen.
                        include(CLASSYADS_PATH . 'public/templates/section-choose-payment.php');
                    }
                 ?>
            </section>
            <p style="text-align:right; margin-top:30px;" class="submit_container"><input type="submit" value="Submit"></p>
            <?php wp_nonce_field( 'create_classyad', '_create_classyad_nonce' ) ?>
            <input type="hidden" name="ad_subscription_level">
            <input type="hidden" name="post_id" value="0">
            <input type="hidden" name="action" value="create_classyad">
            <input type="hidden" name="primary_adcat" value="<?= $primary_adcat_id ?>">
        </form>
    </div>
</div>
