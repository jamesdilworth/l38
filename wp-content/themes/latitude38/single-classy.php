<?php
    acf_form_head();
    require_once('includes/update-classy.php');
    get_header();
    wp_enqueue_script( 'ugc', get_stylesheet_directory_uri(). '/js/ugc.js', array('plugins','scripts'), filemtime( FL_CHILD_THEME_DIR . '/js/ugc.js'), true ); // load scripts in footer
    $current_user = wp_get_current_user();

?>

<div class="container">
    <div class="row">

        <header class="jz-header">
            <div class="lectronic-logo"><img src="/wp-content/themes/latitude38/images/classy_headline.png"></div>
            <a href="/classyads/">&laquo; Back to Classies</a>

            <!-- STATUS NOTICES -->
            <?php if( !empty( $_GET['updated'] ) ): ?>
                <div class="response success">Classy Successfully Updated!</div>
            <?php endif; ?>

            <?php if( !empty( $_GET['validation'] ) ): ?>
                <?php if( $_GET['validation'] == 'emailnotvalid' ): ?>
                    <div class="response fail"><?php _e('The given email address is not valid', 'textdomain'); ?></div>
                <?php elseif( $_GET['validation'] == 'emailexists' ): ?>
                    <div class="response fail"><?php _e('The given email address already exists', 'textdomain'); ?></div>
                <?php elseif( $_GET['validation'] == 'passwordmismatch' ): ?>
                    <div class="response failr"><?php _e('The given passwords did not match', 'textdomain'); ?></div>
                <?php elseif( $_GET['validation'] == 'unknown' ): ?>
                    <div class="response fail"><?php _e('An unknown error occurred, please try again or contact the website administrator', 'textdomain'); ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </header>

        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <article <?php post_class( 'fl-post' ); ?> id="fl-post-<?php the_ID(); ?>" itemscope itemtype="https://schema.org/BlogPosting">

            <?php
                // Seller Info
                global $post;
                $seller = get_user_by('id', get_the_author_meta('ID'));

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
                <div class="sale-terms"><?= $ad_sale_terms_label ?></div>
                <h1><?= $ad_title ?>  </h1>
                <div class="price"><?php echo money_format('%.0n', get_field('ad_asking_price')); ?></div>
                <div class="location"><?php echo get_field('boat_location'); ?></div>

                <div class="content"><?php the_content(); ?></div>

                <?php if($ad_external_url) : ?>
                    <div class="external_url">More info at: <a href="<?= $ad_external_url; ?>"><?= $ad_external_url; ?></a></div>
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
                    <div class="contact_email"><a href=''>Send a Message</a></div>
                    <?php
                    $othercontact = $seller->othercontact; // This needs to be pulled verbosely as it is set through __GET
                    if(!empty($othercontact)) {
                        echo '<div class="contact_other">' . $seller->othercontact . '</div>';
                    }
                    ?>
                </div>
            </div>

            <div class="update-classy-ad" >
                <div class="edit_link"><a href="" class="switch_public_edit_mode">Leave Edit Mode</a></div>
                <form class="jz-form" action="<?php the_permalink(); ?>" id="update_classy_public" name="update_classy_public" method="post">

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
                    <div class="field" class="field_ad_asking_price">
                        <label for="ad_asking_price">Asking Price</label>
                        <div class="currencyinput dollar"><input class="text-input" name="ad_asking_price" type="text" id="edit_ad_asking_price" value="<?php the_field('ad_asking_price') ?>" /></div>
                    </div>
                    <div class="field">
                        <label for="boat_location">Location</label>
                        <input class="text-input" name="boat_location" type="text" id="edit_boat_location" value="<?php the_field('boat_location'); ?>" />
                    </div>
                    <div class="field">
                        <label for="boat_location">Description</label>
                        <textarea name="main_content" id="edit_main_content"><?php echo strip_tags(get_the_content(), '<p>'); ?></textarea>
                    </div>
                    <div class="form-submit">
                        <input type="submit" id="updateclassy" class="submit button" value="Update Ad" />
                        <?php wp_nonce_field( 'update-classy' ) ?>
                        <input name="honey-name" value="" type="text" style="display:none;"></input>
                        <input name="action" type="hidden" id="action" value="update-classy" />
                    </div><!-- .form-submit -->
                </form>
            </div>

            <?php if(is_user_logged_in() && ($current_user->ID == $post->post_author || current_user_can('edit_posts'))) : ?>
            <div class="subscription">
                <div class="desc">
                    <h3>Your &lt;PURCHASE LEVEL&gt; Ad</h3>

                    <p>Your print ad will appear in our December 2018 Issue. </p>
                    <p>Last day to make changes for print is November 15th at 5pm. Questions: 415.383.8200 x 104 or <a href="">email us</a>.</p>
                    <p>This online ad will expire on December 31, 2018.</p>
                    <p><a class='btn' style="background-color:#4d948c;">Mark as SOLD</a> <a class='btn' href=''>Renew for 1 Month - $40</a> <a class='btn' href=''>Renew for 3 Months - $80</a></p>

                </div>

                <div class="magazine-preview">
                    <div class="mag-section">&lt; SECTION &gt;</div>
                    <div class="mag-img" style="background-image:url(<?= $main_img ?>);"></div>
                    <div class="mag-body">
                        <span class="title"><?= $ad_title ?></span>
                        <span class="ad-mag-text"><?= get_field('ad_mag_text'); ?></span>
                    </div>
                    <a href="" class="edit_link">Edit Magazine Ad</a>
                </div>

            </div>
            <?php endif; ?>




            <div class="admin-updates">

                <?php // acf_form(); ?>

            </div>
        </article>
    <?php endwhile;
    endif; ?>
    </div>
</div>

<?php get_footer();