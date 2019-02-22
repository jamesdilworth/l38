<?php
    check_page_security(); /* Redirects people to the home page if they're not logged in. */
    require_once('includes/update-profile.php');
    wp_enqueue_script( 'ugc', get_stylesheet_directory_uri(). '/js/ugc.js', array('plugins','scripts'), filemtime( FL_CHILD_THEME_DIR . '/js/ugc.js'), true ); // load scripts in footer

    get_header();

    $current_user = wp_get_current_user();
    $user_img = $current_user->user_avatar;
    $user_img_tag = wp_get_attachment_image($user_img, 'thumbnail');
?>

<div class="container">
	<div class="row">

		<div class="fl-content col-md-12 ?>">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

                <!-- STATUS NOTICES --> 
                <?php if( $ugc_updated ): ?>
                    <div class="response success"><?php _e('Profile successfully updated', 'textdomain'); ?></div>
                <?php endif; ?>

                <?php if( $ugc_validation  ): ?>
                    <?php if( $ugc_validation == 'emailnotvalid' ): ?>
                        <div class="response fail"><?php _e('The given email address is not valid', 'textdomain'); ?></div>
                    <?php elseif( $ugc_validation == 'emailexists' ): ?>
                        <div class="response fail"><?php _e('The given email address already exists', 'textdomain'); ?></div>
                    <?php elseif( $ugc_validation == 'passwordmismatch' ): ?>
                        <div class="response failr"><?php _e('The given passwords did not match', 'textdomain'); ?></div>
                    <?php elseif( $ugc_validation == 'unknown' ): ?>
                        <div class="response fail"><?php _e('An unknown error occurred, please try again or contact the website administrator', 'textdomain'); ?></div>
                    <?php endif; ?>
                <?php endif; ?>

                <h1>Hello <?= $current_user->first_name; ?>!</h1>

                <ul class="tabs">
                    <li><a href="#profile">My Account</a></li>
                    <li><a href="#edit-profile">Edit Profile</a></li>
                    <li><a href="#classies">My Classy Ads</a></li>
                </ul>

                <!-- List out the fields in a small box -->
                <div class="tabbed">

                    <!-- Profile Page -->
                    <div id="profile" class="my-profile acct-widget">

                        <div class="main-photo editable">
                            <div class="main-photo-preview"><?= $user_img_tag; ?></div>

                            <div class="main-image-fields">
                                <form id="main-photo-form" action="handler.php" method="POST">
                                    <label for="main_photo_input" class="">Change Main Photo (Max 2MB)</label>
                                    <input type="file" id="main_photo_input" name="main_photo_input" accept="image/*" />
                                    <input type="hidden" id="ajax_action" name="ajax_action" value="update_profile_mainphoto">
                                    <?php wp_nonce_field( 'update-mainphoto', '_mainphoto_nonce' ) ?>
                                </form>
                            </div>
                        </div>
                        <div class="main-content">
                            <div class="name"><?= $current_user->display_name; ?></div>
                            <div class="location"><?= $current_user->user_location; ?></div>
                            <div class="desc"></div>
                        </div>

                    </div>

                    <!-- Edit Stuff -->
                    <div id="edit-profile">

                        <form class="jz-form" method="post" id="adduser" action="<?php the_permalink(); ?>">
    
                            <h3><?php _e('Personal info', 'textdomain'); ?></h3>

                            <div class="left-half ">
                                <label for="first-name"><?php _e('First name', 'textdomain'); ?></label>
                                <input class="text-input" name="first-name" type="text" id="first-name" value="<?php the_author_meta( 'first_name', $current_user->ID ); ?>" />
                            </div>
                            <div class="right-half">
                                <label for="last-name"><?php _e('Last name', 'textdomain'); ?></label>
                                <input class="text-input" name="last-name" type="text" id="last-name" value="<?php the_author_meta( 'last_name', $current_user->ID ); ?>" />
                            </div>

                            <p>
                                <label for="email"><?php _e('E-mail *', 'textdomain'); ?></label>
                                <input class="text-input" name="email" type="text" id="email" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>" />
                            </p>
    
                            <p>
                                <label for="phone"><?php _e('Phone', 'textdomain'); ?></label>
                                <input class="text-input" name="phone" type="text" id="phone" value="<?php the_author_meta( 'phone', $current_user->ID ); ?>" />
                            </p>
                            <p>
                                <label for="user_location">Where do you sail from (primarily)?</label>
                                <input class="text-input" name="user_location" type="text" id="user_location" value="<?php the_author_meta( 'user_location', $current_user->ID ); ?>" />
                            </p>
    
                            <?php
                            // action hook for plugin and extra fields
                            // do_action('edit_user_profile', $current_user);
                            ?>
    
                            <h3><?php _e('Change password', 'textdomain'); ?></h3>
    

                            <div class="left-half form-password">
                                <label for="pass1"><?php _e('Password *', 'profile'); ?> </label>
                                <input class="text-input" name="pass1" type="password" id="pass1" />
                            </div><!-- .form-password -->
                            <div class="right-half form-password">
                                <label for="pass2"><?php _e('Repeat password *', 'profile'); ?></label>
                                <input class="text-input" name="pass2" type="password" id="pass2" />
                            </div><!-- .form-password -->
                            <p><?php _e('If both password fields are left empty, your password will not change', 'textdomain'); ?></p>

                            <p class="form-submit">
                                <input name="updateuser" type="submit" id="updateuser" class="submit button" value="<?php _e('Update profile', 'textdomain'); ?>" />
                                <?php wp_nonce_field( 'update-user' ) ?>
                                <input name="honey-name" value="" type="text" style="display:none;"></input>
                                <input name="action" type="hidden" id="action" value="update-user" />
                            </p><!-- .form-submit -->
    
                        </form><!-- #adduser -->
                    </div>

                    <!-- Classies -->
                    <?php

                    $args = array(
                        'post_type' => 'classy',
                        'author' => $current_user->ID,
                        'posts_per_page' => -1
                    );

                    $classies_by_me = new WP_Query( $args );

                    echo '<div id="classies">';

                    if ( $classies_by_me->have_posts() ) {

                        echo '<h2>My Classy Ads</h2>';
                        echo '<div class="my-classies acct-widget">';
                        while ($classies_by_me->have_posts()) {

                            $classies_by_me->the_post();

                            $status = 'live'; // Calculate this from the last day of the ad.
                            $img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'thumbnail') : get_bloginfo('stylesheet_directory') .  '/images/default-classy-ad-centered.png';


                            echo "<div class='ad'>";
                            echo "  <div class='img'><img src='$img'></div>";
                            echo "  <div class='info'>";
                            echo "    <div class='title'><a href='". get_the_permalink() . "'>" . get_field('boat_length') . "' " . get_field('boat_model') . ", " . get_field('boat_year') . "</a></div>";
                            echo "    <div class='price'>$" . get_field('ad_asking_price') . "</div>";
                            echo "  </div><div class='options'>";
                            echo "    <div class='status'>Ad Runs to:<br>End " . get_field('ad_mag_run_to') . "</div>";
                            echo "    <div class=''><a class='btn small' href=''>Renew for 1 Month - $40</a></div>";
                            echo "  </div>";
                            echo "</div>";
                        }
                    } else {
                        echo "You have no Classified Ads. Post one now! It'll make you feel great!";
                    }

                    echo "<p style='font-size:90%; margin-top:20px; font-style:italic;'>* Ads on automatic renewal that are not canceled by the 15th of the month at 5 p.m. will be charged with the credit card on file and appear in the next issue of our monthly magazine. Ads not renewed by this time will expire and not appear in the next issue. Ads remain online until the following issue is published. Business ads do not appear online.</p>";

                    wp_reset_postdata();
                    echo '</div>';
                    ?>

                    <div style="background-color:#FFD9D7; padding:5px 10px; font-size:90%; "><strong>FRAUD
                            WARNING</strong> - If you use an email address
                        in your ad, please be EXTREMELY WARY of offers by distant strangers
                        to send you a cashier's check with a higher value than the item
                        they are purchasing, and then have you wire the balance to them.
                        These offers are sometimes emailed to classified advertisers.
                        Banks will cash these counterfeit checks, but then hold you responsible
                        for the funds when the check fails to clear! If you suspect such
                        a scam, or have been victimized, you should contact your local
                        Secret Service field office and/or the FTC toll-free at 1-877-FTC-HELP
                        (1-877-382-4357) or use the complaint form at <a href="http://www.ftc.gov/" target="_blank">www.ftc.gov</a>, or call the Canadian PhoneBusters
                        hotline toll-free at 1-888-495-8501. Craig's List has more info
                        about such cons on their <a href="http://www.craigslist.org/about/scams.html" target="_blank">current scams page</a>.</td>
                    </div>
                </div>


                <!-- Has the user written in any stories? -->
                <?php
                    /*
                    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
                    $args = array(
                        'post_type' => 'post',
                        'author' => $current_user->ID,
                        'posts_per_page' => 10,
                        'paged' => $paged
                    );

                    $posts_by_me = new WP_Query( $args );

                    // Pagination fix... https://wordpress.stackexchange.com/questions/120407/how-to-fix-pagination-for-custom-loops/120408#120408
                    $temp_query = $wp_query;
                    $wp_query   = NULL;
                    $wp_query   = $posts_by_me;

                    if ( $posts_by_me->have_posts() ) {
                        echo '<h2>My Contributed Stories</h2>';
                        echo '<div class="my-stories acct-widget">';
                        while ($posts_by_me->have_posts()) {

                            $posts_by_me->the_post();


                            $img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'thumbnail') : get_bloginfo('stylesheet_url') .  '/wp-content/uploads/2018/06/default_thumb.jpg';

                            echo "<div class='story'>";
                            echo "    <div class='img'><img src='$img'></div>";
                            echo "    <div class='title'><a href='". get_the_permalink() . "'>" . get_the_title() . "</a></div>";
                            echo "    <div class='publish_date'>" . get_the_date() . "</div>";
                            echo "</div>";

                        }
                    }
                    wp_reset_postdata();

                    $big = 999999999; // ???
                    echo paginate_links( array(
                        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                        'format' => '?paged=%#%',
                        'current' => max( 1, get_query_var('paged') ),
                        'mid_size' => 5,
                        'total' => $posts_by_me->max_num_pages
                    ) );

                    // Reset main query object
                    $wp_query = NULL;
                    $wp_query = $temp_query;
                    */
                ?>


                <!-- Does the user have any classified Ads -->



			<?php endwhile;
            endif; ?>
		</div>

	</div>
</div>

<?php get_footer(); ?>
