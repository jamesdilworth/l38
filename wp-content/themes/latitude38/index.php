<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}
?>
<?php get_header(); ?>

<div class="fl-archive container">
	<div class="row">

        <?php FLChildTheme::archive_page_header(); ?>

		<?php // FLTheme::sidebar( 'left' ); ?>

        <div class="lectronic-edition fl-content <?php FLTheme::content_class(); ?>" itemscope="itemscope" itemtype="https://schema.org/Blog">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'content', get_post_format() ); ?>
				<?php endwhile; ?>
                <?php
                    if(is_day()) {
                        // Get the next day's posts
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

<?php get_footer(); ?>
