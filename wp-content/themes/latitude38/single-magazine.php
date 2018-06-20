<?php get_header(); ?>

<div class="magazine container">
	<div class="row">

		<?php // FLTheme::sidebar( 'left' ); ?>

		<div class="fl-content <?php FLTheme::content_class(); ?>">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

                <header class="fl-post-header">
                    <h1 class="fl-post-title" itemprop="headline">
                        <?php the_title(); ?>

                        <?php edit_post_link( _x( 'Edit', 'Edit post link text.', 'fl-automator' ) ); ?>
                    </h1>
                </header><!-- .fl-post-header -->

                <div class="fl-post-content clearfix" itemprop="text">
                    <?php the_widget( 'magazine_contents_widget'); ?>
               </div><!-- .fl-post-content -->

            <?php endwhile;
            endif; ?>
		</div>

		<?php // FLTheme::sidebar( 'right' ); ?>

	</div>
</div>

<?php get_footer(); ?>
