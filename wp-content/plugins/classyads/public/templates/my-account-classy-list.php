<!-- Classies... partial to be included in JZUGC My Account Page.  -->
<?php

$args = array(
    'post_type' => 'classy',
    'author' => $current_user->ID,
    'posts_per_page' => -1
);

$classies_by_me = new WP_Query( $args );

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

?>