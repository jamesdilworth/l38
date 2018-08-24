<?php get_header(); ?>

<div class="container">
	<div class="row">

        <header class="fl-archive-header">
            <div class="lectronic-logo"><img src="/wp-content/themes/latitude38/images/lectronic-title-bg.png"></div>
        </header>

        <?php FLTheme::sidebar( 'left' ); ?>
		
		<div class="fl-content <?php FLTheme::content_class(); ?>">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', 'single' ); ?>
			<?php endwhile;
            endif; ?>
		</div>
		
		<?php FLTheme::sidebar( 'right' ); ?>
		
	</div>
</div>

<?php get_footer(); ?>
