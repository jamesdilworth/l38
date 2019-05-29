<?php
    check_page_security(); /* Redirects people to the home page if they're not logged in. */
    require_once(JZUGC_PATH . 'public/partials/update-profile.php');

    get_header();

    $current_user = wp_get_current_user();
    $user_img = $current_user->user_avatar;
    $user_img_tag = wp_get_attachment_image($user_img, 'thumbnail');
    if(empty($user_img_tag)) {
        $user_img_tag = '<img src="' . JZUGC_URL . 'public/images/anon-user.png">';
    }

    $jz_user = new JZ_User($current_user->ID);

    $sections = array(
        'My Profile' => JZUGC_PATH . 'public/partials/partial-my-profile.php',
        'Edit Profile' => JZUGC_PATH . 'public/partials/partial-edit-profile.php'
    );

    if(has_filter('jzugc_my_account_sections')) {
        $sections = apply_filters('jzugc_my_account_sections', $sections);
    }


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
                    <?php
                        foreach($sections as $name=>$path) {
                            echo "<li><a href='#" . sanitize_title($name) . "'>$name</a></li>";
                        }
                    ?>
                </ul>

                <!-- List out the fields in a small box -->
                <div class="tabbed">
                <?php
                    foreach($sections as $name=>$path) {
                        $slug = sanitize_title($name);
                        echo "<div id='$slug' class='section $slug'>";
                        include($path);
                        echo "</div>";
                    }
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
