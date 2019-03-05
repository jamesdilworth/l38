<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}
?>
<?php get_header(); ?>

<div class="lectronic">
    <div class="fl-archive container">
        <div class="row">

            <?php FLChildTheme::archive_page_header(); ?>

            <?php // FLTheme::sidebar( 'left' ); ?>

            <div class="fl-content <?php FLTheme::content_class(); ?>" itemscope="itemscope" itemtype="https://schema.org/Blog">

                <?php if (is_month()) : ?>
                    <?php
                        // Get the next day's posts
                        $start_date = date("Y-m-d", strtotime( '-1 days', get_the_time('U')));
                        the_widget('lectronic_stories_widget',"qty=12&start_date=$start_date");
                    ?>
                <?php elseif ( have_posts() ) : ?>
                    <?php
                        $count = 1;
                        $ad_before = false;

                        while ( have_posts() ) : the_post();
                            // Add mid-panel advertising every two stories.
                            if($count %2 == 0 && function_exists('the_ad_placement') && !in_category('advertisment') && !$ad_before) {
                                echo '<div class="ad_midway">';
                                the_ad_placement('lectronic-day-midway');
                                echo '</div>';
                            }

                            get_template_part( 'content', get_post_format() );

                            if(!in_category('advertisment')) $count++;

                            // Only increment if this wasn't an ad, and there wasn't an ad before.
                            if(in_category('advertisment')) {
                                $ad_before = true;
                            } else {
                                $ad_before = false;
                            }




                         ?>
                    <?php endwhile; ?>

                    <div class="subscribe-notice">Receive emails when 'Lectronic Latitude is updated. <a href="https://secure.campaigner.com/CSB/Public/Form.aspx?fid=1721230" class="btn" target="_blank">SUBSCRIBE</a></div>

                    <?php
                        if(is_day()) {
                            // Show the next day's posts as a follow up.
                            $start_date = date("Y-m-d", strtotime( '-1 days', get_the_time('U')));
                            the_widget('lectronic_stories_widget',"qty=1&start_date=$start_date");
                        } else {
                            // Get the next lectronic
                            FLTheme::archive_nav();
                        }
                    ?>
                <?php else : ?>
                    <?php get_template_part( 'content', 'no-results' ); ?>
                <?php endif; ?>

            </div>

            <?php // FLTheme::sidebar( 'right' ); ?>

        </div>
    </div>
</div>
<?php get_footer(); ?>
