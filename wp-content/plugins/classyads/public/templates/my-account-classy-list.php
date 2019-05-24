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

        $classyad = new Classyad(get_the_ID());

        $main_img = $classyad->main_image_url;
        if(empty($main_img)) $main_img = get_bloginfo('stylesheet_directory') .  '/images/default-classy-ad-centered.png';

        $primary_cat = get_term_by('slug', $classyad->primary_cat, 'adcat');
        $primary_cat_name = !empty($primary_cat) ? $primary_cat->name : "Uncategorized";

        $excerpt = $classyad->custom_fields['ad_mag_text'];
        if(empty($excerpt)) {

            ob_start();

            if(function_exists('the_advanced_excerpt')) {
                the_advanced_excerpt('length=140&length_type=characters&no_custom=0&finish=sentence&no_shortcode=1&ellipsis=&add_link=0&exclude_tags=p,div,img,b,figure,figcaption,strong,em,i,ul,li,a,ol,h1,h2,h3,h4');
            } else {
                the_excerpt();
            }

            $excerpt = ob_get_contents();
            $excerpt = wp_strip_all_tags($excerpt);
            ob_end_clean();
            // it's an online ad only.
        }

        echo "<div class='ad'>";
        echo "  <div class='img'><img src='$main_img'></div>";
        echo "  <div class='info'>";
        echo "    <div class='category'>$primary_cat_name</div>";
        echo "    <div class='title'><a href='". get_the_permalink() . "'>$classyad->title</a></div>";
        echo "    <div class='desc'>$excerpt</div>";
        echo "  </div><div class='options'>";
        echo "    <div class='price'>" . $classyad->custom_fields['ad_asking_price']  . "</div>";
        echo "    <div class='status'>Expires:&nbsp;" . $classyad->key_dates['expiry']->format('F j, Y') . "</div>";
        echo "    <div class=''><a class='btn small' href=''>Renew</a> <a class='btn small secondary' href=''>Remove</a></div>";
        echo "  </div>";
        echo "</div>";
    }
} else {
    echo "You have no Classified Ads. Post one now! It'll make you feel great!";
}

// echo "<p style='font-size:90%; margin-top:20px; font-style:italic;'>* Ads on automatic renewal that are not canceled by the 15th of the month at 5 p.m. will be charged with the credit card on file and appear in the next issue of our monthly magazine. Ads not renewed by this time will expire and not appear in the next issue. Ads remain online until the following issue is published. Business ads do not appear online.</p>";
wp_reset_postdata();

?>