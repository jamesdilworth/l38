<?php
    check_page_security(); /* Redirects people to the home page if they're not logged in. */
    require_once('includes/update-profile.php');

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
                <?php if( !empty( $_GET['updated'] ) ): ?>
                    <div class="response success"><?php _e('Profile successfully updated', 'textdomain'); ?></div>
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
                        <div class="img"><?= $user_img_tag; ?></div>
                        <div class="name"><?= $current_user->display_name; ?></div>
                        <div class="location"><?= $current_user->user_location; ?></div>
                        <div class="desc"></div>
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
                            $img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'thumbnail') : get_bloginfo('stylesheet_url') .  '/images/default-classy-ad.png';

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

                    wp_reset_postdata();
                    echo '</div>';
                    ?>

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
