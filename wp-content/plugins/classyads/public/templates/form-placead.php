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

    <?php
    if (!is_user_logged_in()) {
        echo "<h3 class='title'>Please Log In...</h3>";
        echo "<p>Please <a href='/login/'>log in</a>, or <a href='/register/'>create an account</a> to place your classified ad. ";
    } else { ?>



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
                <p>This description will appear in the classifieds section of the magazine. No need to add a title, price, contact information etc. We'll do that for you.  </p>
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

                $output = '<div class="fieldset" id="choose_payment_fields">';

                // Admin override to allow non-payment.
                if(current_user_can('edit_posts')) {
                    $output .= "<input type='radio' name='cim_payment_profile_id' value='admin_override'> <span class='admin_note'>Admin Override: Don't Charge.. you can do manually through Authorize.Net interface<br>";
                }

                if(!empty($profiles)) {
                    // Owner already has a payment profile. Give him the options.
                    $output .= "<p>Choose a card:</p>";

                    $first = " checked";
                    foreach($profiles as $profile) {
                        $output .=  "<input type='radio' name='cim_payment_profile_id' " . $first . " value='" . $profile['id'] . "'> xxxx xxxx xxxx " . $profile['last4'] . " (" . $profile['expires'] . ")<br>";
                        $first = "";
                    }
                    $output .= "<input type='radio' name='cim_payment_profile_id' value='new_payment_method'> <a class='add-payment-link'>Add a New Payment Method</a>";

                }

                $output .= "</div>";
                echo $output;

                ?>

                <div class="fieldset" id="create_add_payment_fields">
                    <div class="field">
                        <label for="card_number">Credit Card Number</label>
                        <input type="text" name="card_number">
                    </div>

                    <div class="field one-third">
                        <label for="expires">Expires</label>
                        <div class="inputgroup">
                            <select name="card_month" id="" required>
                                <option value="">Month</option><option value="1">01</option><option value="2">02</option><option value="3">03</option><option value="4">04</option><option value="5">05</option><option value="6">06</option><option value="7">07</option><option value="8">08</option><option value="9">09</option><option value="10">10</option><option value="11">11</option><option value="12">12</option>
                            </select>
                            <select name="card_year" id="year" required>
                                <option value="">Year</option><option value="2019">2019</option><option value="2020">2020</option><option value="2021">2021</option><option value="2022">2022</option><option value="2023">2023</option><option value="2024">2024</option><option value="2025">2025</option><option value="2026">2026</option><option value="2027">2027</option><option value="2028">2028</option><option value="2029">2029</option><option value="2030">2030</option><option value="2031">2031</option><option value="2032">2032</option><option value="2033">2033</option><option value="2034">2034</option><option value="2035">2035</option><option value="2036">2036</option><option value="2037">2037</option><option value="2038">2038</option>
                            </select>
                        </div>
                    </div>
                    <div class="field one-third">
                        <label for="cvv2">Security Code</label>
                        <input type="text" name="cvv2" value="" required autocomplete="off" pattern="[0-9]*" title="Only digits are allowed" placeholder="CVV">
                    </div>
                    <div class="field">
                        <label for="cardholder">Cardholder Name</label>
                        <input type="text" name="cardholder" required>
                    </div>
                    <div class="fieldset">
                        <legend>Cardholder Billing Address (Needed for Payment)</legend>
                        <div class="field">
                            <label for="address1">Address 1</label>
                            <input type="text" name="address1" >
                        </div>
                        <div class="field">
                            <label for="address2">Address 2</label>
                            <input type="text" name="address2" >
                        </div>
                        <div class="one-third field">
                            <label for="city">City</label>
                            <input type="text" name="city" >
                        </div>
                        <div class="one-third field">
                            <label for="state">State</label>
                            <select name="state" id="state" ><option value="">State / Province</option><option value="Alabama">Alabama</option><option value="Alaska">Alaska</option><option value="Arizona">Arizona</option><option value="Arkansas">Arkansas</option><option value="California" selected="selected">California</option><option value="Colorado">Colorado</option><option value="Connecticut">Connecticut</option><option value="Delaware">Delaware</option><option value="District of Columbia">District of Columbia</option><option value="Florida">Florida</option><option value="Georgia">Georgia</option><option value="Hawaii">Hawaii</option><option value="Idaho">Idaho</option><option value="Illinois">Illinois</option><option value="Indiana">Indiana</option><option value="Iowa">Iowa</option><option value="Kansas">Kansas</option><option value="Kentucky">Kentucky</option><option value="Louisiana">Louisiana</option><option value="Maine">Maine</option><option value="Maryland">Maryland</option><option value="Massachusetts">Massachusetts</option><option value="Michigan">Michigan</option><option value="Minnesota">Minnesota</option><option value="Mississippi">Mississippi</option><option value="Missouri">Missouri</option><option value="Montana">Montana</option><option value="Nebraska">Nebraska</option><option value="Nevada">Nevada</option><option value="New Hampshire">New Hampshire</option><option value="New Jersey">New Jersey</option><option value="New Mexico">New Mexico</option><option value="New York">New York</option><option value="North Carolina">North Carolina</option><option value="North Dakota">North Dakota</option><option value="Ohio">Ohio</option><option value="Oklahoma">Oklahoma</option><option value="Oregon">Oregon</option><option value="Pennsylvania">Pennsylvania</option><option value="Rhode Island">Rhode Island</option><option value="South Carolina">South Carolina</option><option value="South Dakota">South Dakota</option><option value="Tennessee">Tennessee</option><option value="Texas">Texas</option><option value="Utah">Utah</option><option value="Vermont">Vermont</option><option value="Virginia">Virginia</option><option value="Washington">Washington</option><option value="West Virginia">West Virginia</option><option value="Wisconsin">Wisconsin</option><option value="Wyoming">Wyoming</option><option value="Armed Forces Americas">Armed Forces Americas</option><option value="Armed Forces Europe">Armed Forces Europe</option><option value="Armed Forces Pacific">Armed Forces Pacific</option></select>
                        </div>
                        <div class="one-third field">
                            <label for="zip">Zip</label>
                            <input type="text" name="zip" >
                        </div>
                    </div>
                </div>

            </section>
            <p style="text-align:right; margin-top:30px;" class="submit_container"><input type="submit" value="Submit"></p>
            <?php wp_nonce_field( 'create_classyad', '_create_classyad_nonce' ) ?>
            <input type="hidden" name="ad_subscription_level">
            <input type="hidden" name="post_id" value="0">
            <input type="hidden" name="action" value="create_classyad">
            <input type="hidden" name="primary_adcat" value="<?= $primary_adcat_id ?>">
        </form>
    <?php } // end the is_loggedin check ?>
    </div>
</div>
