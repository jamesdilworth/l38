<?php
    Classyads_Public::enqueue_view_scripts();
    Classyads_Public::enqueue_form_scripts();

    get_header();

    global $post;

    $classyad = new Classyad($post->ID);
    $owner = new JZ_User($classyad->owner); //Note for payments sake, we need to be clear that THE USER IS NOT NECESSARILY THE OWNER.

    $key_dates = $classyad->key_dates;

?>

<div class="container">
    <div class="row">
        <header class="jz-header">
            <div class="lectronic-logo"><img src="/wp-content/themes/latitude38/images/classy_headline.png"></div>

            <?php if(is_user_logged_in() && $current_user->ID == $post->post_author && isset($_REQUEST['created']) && $_REQUEST['created'] == 'new') :?>
                <div class="success response">Your ad has been successfully created.</div>
            <?php endif; ?>

            <a href="/classyads/">&laquo; Back to Classies</a>
        </header>

        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <article <?php post_class( 'fl-post' ); ?> id="fl-post-<?php the_ID(); ?>" itemscope itemtype="https://schema.org/BlogPosting">

            <?php
                // Seller Info
                $seller = get_user_by('id', get_the_author_meta('ID'));
                $ad_subscription_level = get_field('ad_subscription_level');

                // Main Image
                $main_img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'medium') : get_bloginfo('stylesheet_directory') .  '/images/default-classy-ad-centered.png';

                // Technical items
                $ad_sale_terms_obj = get_field_object('ad_sale_terms');
                $ad_sale_terms_value = $ad_sale_terms_obj['value'];
                $ad_sale_terms_label = $ad_sale_terms_obj['choices'][$ad_sale_terms_value];

                // Meta
                $ad_external_url = get_field('ad_external_url');

                // Title... it's going to need some more logic for the gear.
                $ad_title = get_field('boat_length') . "' " . get_field('boat_model') . ", " . get_field('boat_year');
            ?>

            <div class="main-photo editable">
                <div class="main-photo-preview"><img src="<?= $main_img ?>" alt="For Sale: <?php the_title(); ?>"></div>
                <?php if(is_user_logged_in() && ($current_user->ID == $post->post_author || current_user_can('edit_posts'))) : ?>

                    <div class="main-image-fields">
                        <?php // This is still handled through JZUGC JS ?>
                        <form id="main-photo-form" action="/" method="POST">
                            <label for="main_photo_input" class="">Change Main Photo (Max 2MB)</label>
                            <input type="file" id="main_photo_input" name="main_photo_input" accept="image/*" />
                            <input type="hidden" id="post_id" name="classy_id" value="<?= $post->ID; ?>">
                            <input type="hidden" id="ajax_action" name="ajax_action" value="update_classy_mainphoto">
                            <?php wp_nonce_field( 'update-mainphoto', '_mainphoto_nonce' ) ?>
                        </form>
                     </div>

                <?php endif; ?>
            </div>

            <div class="main-content">
                <?php if(is_user_logged_in() && ($current_user->ID == $post->post_author || current_user_can('edit_posts'))) : ?>
                    <div class="public edit_link"><a href="" class="switch_public_edit_mode">Edit</a></div>
                <?php endif; ?>
                <div class="sale-terms" id="_view_sale_terms_label"><?= $ad_sale_terms_label ?></div>
                <h1><?= $ad_title ?>  </h1>
                <div class="price" id="_view_ad_asking_price"><?= $classyad->custom_fields['ad_asking_price'] ?></div>
                <div class="location" id="_view_boat_location"><?= $classyad->custom_fields['boat_location']; ?></div>
                <div class="content" id="_view_maintext"><?php the_content(); ?></div>

                <?php if($classyad->custom_fields['ad_external_url']) : ?>
                    <div class="external_url" id="_view_ad_external_url">More info at: <a href="<?= $classyad->custom_fields['ad_external_url']; ?>"><?= $classyad->custom_fields['ad_external_url']; ?></a></div>
                <?php endif; ?>

                <div class="seller_info">
                    <div class="contact_name"><?= $seller->first_name; ?> <?= $seller->last_name; ?> </div>
                    <?php
                        $phone = $seller->phone;
                        if(!empty($phone)) {
                            if(strlen($phone) == 10)
                                echo '('.substr($phone, 0, 3).') '.substr($phone, 3, 3).'-'.substr($phone,6);
                            else
                                echo $phone;
                        }
                     ?>
                    <div class="contact_email"><a href="javascript:alert('functionality coming soon....')">Send a Message</a></div>
                    <?php
                    $othercontact = $seller->othercontact; // This needs to be pulled verbosely as it is set through __GET
                    if(!empty($othercontact)) {
                        echo '<div class="contact_other">' . $seller->othercontact . '</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="update-classy-ad" >
                <form class="jz-form"  id="update_classy_public" name="update_classy_public" method="post">
                    <a href="" class="edit_link switch_public_edit_mode">Leave Edit Mode</a>

                    <!-- <div class="field">
                        <?php
                            $field = get_field_object('ad_sale_terms');
                            if( $field['choices'] ): ?>
                                <ul class="horizontal radio-buttons">
                                    <?php foreach( $field['choices'] as $value => $label ): ?>
                                        <li><input class="text-input" name="ad_sale_terms" type="radio" id="edit_sale_terms" <?php if($value == $field['value']) echo 'checked' ?> value="<?= $value ?>"  /> <?= $label ?></li>
                                    <?php endforeach; ?>
                                </ul>
                        <?php endif; ?>
                    </div>
                    -->
                    <div class="sale-terms"><?= $ad_sale_terms_label ?></div>
                    <h1><?= get_field('boat_length') ?>' <?= get_field('boat_model') ?>, <?= get_field('boat_year') ?>  </h1>
                    <div class="field field_ad_asking_price">
                        <label for="ad_asking_price">Asking Price</label>
                        <div class="currencyinput dollar"><input class="text-input" name="ad_asking_price" type="text" id="edit_ad_asking_price" value="<?php the_field('ad_asking_price') ?>" /></div>
                    </div>
                    <div class="field">
                        <label for="boat_location">Location</label>
                        <input class="text-input" name="boat_location" type="text" id="edit_boat_location" value="<?php the_field('boat_location'); ?>" />
                    </div>
                    <div class="field">
                        <label for="maintext">Description</label>
                        <textarea name="maintext" id="edit_maintext"><?php echo get_the_content(); ?></textarea>
                    </div>
                    <div class="field">
                        <label for="ad_external_url">External URL (optional)</label>
                        <input class="text-input" name="ad_external_url" type="url" id="edit_ad_external_url" value="<?php the_field('ad_external_url'); ?>" />
                    </div>
                    <div class="form-submit">
                        <input type="submit" id="updateclassy" class="submit button" value="Update Ad" />
                        <?php wp_nonce_field( 'update_classyad', '_update_classyad_nonce' ) ?>
                        <input name="post_id" value="<?=$post->ID ?>" type="hidden" >
                        <input name="action" type="hidden" id="action" value="update_classyad" />
                    </div><!-- .form-submit -->
                </form>
            </div>

            <?php
                // DATES
                if(is_user_logged_in() && ($current_user->ID == $post->post_author || current_user_can('edit_posts'))) :
             ?>
            <div class="subscription">
                <?php if($ad_subscription_level != 'free') : ?>
                    <div class="magazine-preview">
                        <div class="mag-section"><?php echo $classyad->getMagazineCat(); ?></div>
                        <div class="mag-img" style="background-image:url(<?= $main_img ?>);"></div>
                        <div class="mag-body">
                            <span class="title"><?= $ad_title ?></span>
                            <span class="ad-mag-text" id="_view_ad_mag_text"><?= get_field('ad_mag_text'); ?></span>
                            <?php if($key_dates['can_make_print_changes']) : ?>
                                <div><a href="" class="edit_link switch_magad_edit_mode">Edit Magazine Copy</a></div>
                            <?php endif; ?>
                        </div>
                        <?php if($key_dates['can_make_print_changes']) : ?>
                            <form class="jz-form" action="<?php the_permalink(); ?>" id="update_magad" name="update_magad" method="post">
                                <span class="title"><?= $ad_title ?></span>
                                <textarea name="ad_mag_text" maxlength="200" ><?= get_field('ad_mag_text'); ?></textarea>
                                <div class="form-submit">
                                    <input type="submit" class="submit button" value="Update Magazine Copy" />
                                    <input name="post_id" value="<?=$post->ID ?>" type="hidden" >
                                    <?php wp_nonce_field( 'update_classyad', '_update_classyad_nonce' ) ?>
                                    <input name="action" type="hidden" id="action" value="update_classyad" />
                                </div>
                                <div><a href="" class="edit_link switch_magad_edit_mode">Undo Edit</a></div>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="desc">

                    <h3>Your <?= ucfirst($classyad->custom_fields['ad_subscription_level']); ?> Ad</h3>

                    <!-- <p>Your ad was first placed on <?= $key_dates['ad_placed_on']->format('F j, Y'); ?>. -->

                    <?php if($ad_subscription_level != 'free') : ?>

                        <?php if($key_dates['today'] < $key_dates['ad_edition']) : ?>
                            <?php // TODO!!!! - If they're three months out, it'll appear in May, June, and July issues. ?>
                            Your print ad will appear in our <?= $key_dates['ad_edition']->format('F Y'); ?> Issue.</p>
                        <?php else : ?>
                            Your print ad appears in our <?= $key_dates['ad_edition']->format('F Y'); ?> Issue.</p>
                        <?php endif; ?>

                        <?php if($key_dates['can_make_print_changes']) : ?>
                            <p>Last day to make changes for print is <?= $key_dates['cutoff']->format('F jS, Y'); ?> at 5pm. Questions: 415.383.8200 x 104 or <a href="">email us</a>.</p>
                        <?php endif;  ?>

                        <?php if($key_dates['has_expired']) : ?>
                            This online ad expired on <?= $key_dates['expiry']->format('F jS, Y'); ?></p>
                        <?php else : ?>
                            This online ad will expire on <?= $key_dates['expiry']->format('F jS, Y'); ?></p>
                        <?php endif; ?>

                        <p>Renew before <?= $key_dates['renewal_deadline']->format('F jS, Y'); ?> to get it into the <?= $key_dates['next_ad_edition']->format('F'); ?> issue.</p>

                        <p><a data-mfp-src='#renew_popup' class="renew-modal btn">Renew for 1 Month</a>
                            <!-- <a class='btn' href=''>Renew for 3 Months - $80</a> -->

                   <?php else : ?>
                       <?php if($key_dates['has_expired']) : ?>
                           This online ad expired on <?= $key_dates['expiry']->format('F jS, Y'); ?></p>
                       <?php else : ?>
                           This online ad will expire on <?= $key_dates['expiry']->format('F jS, Y'); ?></p>
                       <?php endif; ?>

                        <p><a class='upgradeplan-modal btn' data-mfp-src='#upgradeplan_popup' >Upgrade to Print Ad</a>

                    <?php endif; ?>

                     <a style="background-color:#4d948c;" class="remove-ad btn">Take down this Ad</a></p>
                </div>
            </div>
            <?php endif; ?>

            <div id="renew_popup" class="mfp-hide jz-modal">
                <div class="wrapper">
                    <h3 class="title">Renew Ad</h3>
                    <form name="renew_classyad" id="renew_classyad">
                    <!--
                    <div class="plan_options">
                        <div class="plan">
                            <div class="title">Online</div>
                            <div class="price">Free</div>
                            <ul class="features">
                                <li>Option 1</li>
                                <li>Option 2</li>
                                <li>Option 3</li>
                            </ul>
                            <div class="select"><input type="radio" name="plan_choice" value="free"></div>
                        </div>
                        <div class="plan">
                            <div class="title">Basic</div>
                            <div class="price">$20</div>
                            <ul class="features">
                                <li>Option 1</li>
                                <li>Option 2</li>
                                <li>Option 3</li>
                            </ul>
                            <div class="select"><input type="radio" name="plan_choice" value="free"></div>
                        </div>
                        <div class="plan">
                            <div class="title">Premium</div>
                            <div class="price">$40</div>
                            <ul class="features">
                                <li>Option 1</li>
                                <li>Option 2</li>
                                <li>Option 3</li>
                            </ul>
                            <div class="select"><input type="radio" name="plan_choice" value="free"></div>
                        </div>
                    </div>
                    -->

                    <?php if($classyad->is_print_ad()) : ?>
                        <p>This will extend your <?php echo $classyad->plan['name'] ?> ad for publication in our July 2019 Issue. This online ad will remain live until July 30, 2019</p>
                    <?php endif; ?>

                    <?php
                       if(count($owner->cim_payment_profiles) > 1 ) {
                           echo "<p>Choose a card:</p>";
                            foreach($owner->cim_payment_profiles as $profile) {
                                echo "<input type='radio' name='cim_payment_profile_id' value='" . $profile['id'] . "'> XXXX XXXX XXXX " . $profile['last4'] . "(" . $profile['expires'] . ")<br>";
                            }
                        } else if(count($owner->cim_payment_profiles) == 1) {
                            $profile = $owner->cim_payment_profiles[0];
                            echo "<p>We will charge your card (XXXX-XXXX-XXXX-" . $profile['last4'] . " (" . $profile['expires'] . ")) $" . $classyad->plan['amount'] . "</p>";
                            echo '<input type="hidden" name="cim_payment_profile_id" value="' . $profile['id'] . '">';
                        } else {
                            echo "You have no saved payment methods. Please add some credit card info";
                        }
                    ?>

                    <p style="text-align:center;"><a href="" class="btn ok-renew">OK</a> <a href="javascript:jQuery.magnificPopup.close();" class="secondary btn">Cancel</a></p>
                    <p style="text-align:center;" class="alt-options"><a href="" class="show-alt-plans">Change Plan</a> | <a href="" class="show-alt-payment">Change Payment Method</a></p>
                    <input type="hidden" name="plan_level" value="<?= $classyad->plan['type'] ?>
                    <input type="hidden" name="post_id" value="<?= $classyad->post_id; ?>">
                    <?php wp_nonce_field( 'renew_classyad', '_renew_classyad_nonce' ) ?>
                    <input type="hidden" name="action" value="renew_classyad">

                    </form>
                </div>
            </div>


            <div id="upgradeplan_popup" class="mfp-hide jz-modal">
                <div class="wrapper">
                    <h3 class="title">Change Plan</h3>

                    <p>The following plans are available for you to upgrade your ad.</p>
                    <!--

                    // Choose from one of the pricing options.
                        - If it's a free ad... he can upgrade to the print ad for $40, and the ad will appear in the next publication.
                        - If it's a paid ad... he could renew at the current level.
                        - If it's a basic ad... he could upgrade to premium and get more features. Yes.
                        - If it's expired... he could renew at any level.

                    // If there is a CIM record, grab that, and offer to use that.
                        - Also show the name on the card, last four digits etc.

                    // Else, we'll need to get the cc info again from scratch.
                    -->


                    <p>This will charge your card $XX</p>

                    <p style="text-align:center;"><a href="" class="renew-ok btn">OK</a> <a href="" class="mfp-close btn">Cancel</a></p>
                </div>
            </div>


            <div class="admin-updates">

                <?php // acf_form(); ?>

            </div>
        </article>
    <?php endwhile;
    endif; ?>
    </div>
</div>

<?php get_footer();