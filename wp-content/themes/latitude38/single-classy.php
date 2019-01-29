<?php acf_form_head(); ?>
<?php get_header(); ?>

<div class="container">
    <div class="row">

        <header class="fl-archive-header">
            <div class="lectronic-logo"><img src="/wp-content/themes/latitude38/images/classy_headline.png"></div>
            <a href="/classyads/">&laquo; Back to Classies</a>
        </header>

        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <article <?php post_class( 'fl-post' ); ?> id="fl-post-<?php the_ID(); ?>" itemscope itemtype="https://schema.org/BlogPosting">

            <?php

                // Seller Info
                $seller = get_user_by('id', get_the_author_meta('ID'));

                // Main Image
                $main_img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'medium') : get_bloginfo('stylesheet_directory') .  '/images/default-classy-ad.png';

                // Technical items
                $ad_sale_terms_obj = get_field_object('ad_sale_terms');
                $ad_sale_terms_value = $ad_sale_terms_obj['value'];
                $ad_sale_terms_label = $ad_sale_terms_obj['choices'][$ad_sale_terms_value];

                //
                $ad_external_url = get_field('ad_external_url');



            ?>

            <div class="photo-gallery">
                <div class="main_img"><img src="<?= $main_img ?>" alt="For Sale: <?php the_title(); ?>"></div>
            </div>

            <div class="main-content">
                <div class="sale-terms"><?= $ad_sale_terms_label ?></div>
                <h1><?= get_field('boat_length') ?>' <?= get_field('boat_model') ?>, <?= get_field('boat_year') ?>  </h1>
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

            <div class="acf-form-container">
            <?php


                echo '<form id="acf-form" class="acf-form" action="" method="post">';
                if (( is_user_logged_in() && $current_user->ID == $post->post_author ) || current_user_can('edit_posts')) {


                   echo '<form id="acf-form" class="acf-form" action="" method="post">';
                   $settings = array(
                            'post_content' => true,
                            'field_groups' => array('group_5c35239d60ff9')
                   );
                   acf_form($settings);

                }

                if (current_user_can('edit_posts')) {
                    echo "<h3>Admin Settings</h3>";
                    $settings = array(
                        'field_groups' => array('group_5c3e24898c23a')
                    );
                    acf_form($settings);
                    echo '<a id="edit-post" href="#edit">Edit Post</a>';
                }
                echo '</form>';


            ?>
            </div>
        </article>
    <?php endwhile;
    endif; ?>
    </div>
</div>

<?php get_footer();