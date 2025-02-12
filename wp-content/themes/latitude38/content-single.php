<?php

$show_thumbs = FLTheme::get_setting( 'fl-posts-show-thumbs' );
$thumb_size   = FLTheme::get_setting( 'fl-posts-thumb-size' );
$show_cats = 0;
?>
<?php do_action( 'fl_before_post' ); ?>

<!-- inner template is content-single.php -->

<article <?php post_class( 'fl-post' ); ?> id="fl-post-<?php the_ID(); ?>" itemscope itemtype="https://schema.org/BlogPosting">

	<?php if ( has_post_thumbnail() && ! empty( $show_thumbs ) ) : ?>
		<?php if ( 'above-title' == $show_thumbs ) : ?>
		<div class="fl-post-thumb">
			<?php the_post_thumbnail( 'large', array(
				'itemprop' => 'image',
			) ); ?>
		</div>
		<?php endif; ?>
	<?php endif; ?>

	<header class="fl-post-header">
        <?php // echo "<div class='alt_header'>" . get_field('alt_header') . "</div>"; ?>
        <?php if(!is_category()) the_category(); ?>
        <h1 class="fl-post-title" itemprop="headline">
            <?php the_title(); ?>
			<?php edit_post_link( _x( 'Edit', 'Edit post link text.', 'fl-automator' ) ); ?>
		</h1>
		<?php FLTheme::post_top_meta(); ?>
	</header><!-- .fl-post-header -->

	<?php if ( has_post_thumbnail() && ! empty( $show_thumbs ) ) : ?>
		<?php if ( 'above' == $show_thumbs ) : ?>
		<div class="fl-post-thumb">
			<?php the_post_thumbnail( 'large' ); ?>
		</div>
		<?php endif; ?>

		<?php if ( 'beside' == $show_thumbs ) : ?>
			<div class="row">
				<div class="fl-post-image-<?php echo $show_thumbs; ?>">
					<div class="fl-post-thumb">
						<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
							<?php the_post_thumbnail( $thumb_size ); ?>
						</a>
					</div>
				</div>
				<div class="fl-post-content-<?php echo $show_thumbs; ?>">
		<?php endif; ?>
	<?php endif; ?>

	<?php do_action( 'fl_before_post_content' ); ?>

	<div class="fl-post-content clearfix" itemprop="text">
		<?php
		    the_content();
        ?>

        <?php
            wp_link_pages( array(
                'before'         => '<div class="fl-post-page-nav">' . _x( 'Pages:', 'Text before page links on paginated post.', 'fl-automator' ),
                'after'          => '</div>',
                'next_or_number' => 'number',
            ) );
		?>
	</div><!-- .fl-post-content -->

	<?php if ( has_post_thumbnail() && 'beside' == $show_thumbs ) : ?>
		</div>
	</div>
	<?php endif; ?>

	<?php FLTheme::post_bottom_meta(); ?>
	<?php // FLTheme::post_navigation(); ?>
	<?php do_action( 'fl_after_post_content' ); ?>

</article>

<?php if(!in_category(199)) comments_template(); ?>

<div class="subscribe-notice">Receive emails when 'Lectronic Latitude is updated. <a href="https://secure.campaigner.com/CSB/Public/Form.aspx?fid=1721230" class="btn" target="_blank">SUBSCRIBE</a></div>

<?php
    // Other Pages from this day.
    $start_date = date("Y-m-d", get_the_time('U'));
    the_widget('lectronic_stories_widget',"qty=1&start_date=$start_date");
?>

<?php do_action( 'fl_after_post' ); ?>

<!-- .fl-post -->
