<?php

$show_thumbs = FLTheme::get_setting( 'fl-archive-show-thumbs' );
$show_full   = apply_filters( 'fl_archive_show_full',  FLTheme::get_setting( 'fl-archive-show-full' ) );
$more_text   = FLTheme::get_setting( 'fl-archive-readmore-text' );
$thumb_size   = FLTheme::get_setting( 'fl-archive-thumb-size' );


if(is_day()) {
    $show_full = 1;
    $show_thumbs = 0;
}

do_action( 'fl_before_post' );

?>

<!-- inner template is content.php -->
<article <?php post_class( 'fl-post story' ); ?> id="fl-post-<?php the_ID(); ?>" itemscope="itemscope" itemtype="https://schema.org/BlogPosting">

	<?php if ( ! empty( $show_thumbs ) ) : ?>

		<?php if ( 'above-title' == $show_thumbs ) : ?>
		<div class="fl-post-thumb">
			<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">

                <?php if ( has_post_thumbnail()) : ?>
                    <div class='image'>
                        <?php the_post_thumbnail( 'large', array( 'itemprop' => 'image',) ); ?>
                    </div>
                <?php else : ?>
                    <div class='image'><img src='/wp-content/uploads/2018/06/default_thumb.jpg' alt='Default Thumbnail'></div>
                <?php endif; ?>

			</a>
		</div>
		<?php endif; ?>
	<?php endif; ?>

	<header class="fl-post-header">
        <?php echo "<div class='alt_header'>" . get_field('alt_header') . "</div>"; ?>
		<h2 class="fl-post-title" itemprop="headline" id="<?php echo get_post_field( 'post_name'); ?>">
			<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
			<?php edit_post_link( _x( 'Edit', 'Edit post link text.', 'fl-automator' ) ); ?>
		</h2>
        <?php if(!is_category()) the_category(); ?>
        <?php if(!is_day()) the_date(); ?>
		<?php if(is_day()) FLTheme::post_top_meta(); ?>
	</header><!-- .fl-post-header -->

	<?php if ( has_post_thumbnail() && ! empty( $show_thumbs ) ) : ?>
		<?php if ( 'above' == $show_thumbs ) : ?>
		<div class="fl-post-thumb">
			<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">

                <?php if ( has_post_thumbnail()) : ?>
                    <div class='image'>
                        <?php the_post_thumbnail( 'large', array( 'itemprop' => 'image',) ); ?>
                    </div>
                <?php else : ?>
                    <div class='image'><img src='/wp-content/uploads/2018/06/default_thumb.jpg' alt='Default Thumbnail'></div>
                <?php endif; ?>

            </a>
		</div>
		<?php endif; ?>

		<?php if ( 'beside' == $show_thumbs ) : ?>
		<div class="row">
			<div class="fl-post-image-<?php echo $show_thumbs; ?>">
				<div class="fl-post-thumb">
					<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">

                        <?php if ( has_post_thumbnail()) : ?>
                            <div class='image'>
                                <?php the_post_thumbnail( 'large', array( 'itemprop' => 'image',) ); ?>
                            </div>
                        <?php else : ?>
                            <div class='image'><img src='/wp-content/uploads/2018/06/default_thumb.jpg' alt='Default Thumbnail'></div>
                        <?php endif; ?>

                    </a>
				</div>
			</div>
			<div class="fl-post-content-<?php echo $show_thumbs; ?>">
		<?php endif; ?>
	<?php endif; ?>
	<?php do_action( 'fl_before_post_content' ); ?>
	<div class="fl-post-content clearfix story" itemprop="text">
		<?php

		if ( is_search() || ! $show_full ) {
			if(function_exists('the_advanced_excerpt')) {
                the_advanced_excerpt('length=30&length_type=words&no_custom=0&finish=sentence&no_shortcode=1&ellipsis=&add_link=1&exclude_tags=p,div,img,b,figure,figcaption,strong,em,i,ul,li,a,ol,h1,h2,h3,h4');
            } else {
                the_excerpt();
                echo '<a class="fl-post-more-link" href="' . get_permalink() . '">' . $more_text . '</a>';
            }
		} else {
            the_content( '<span class="fl-post-more-link">' . $more_text . '</span>' );
		}

		?>
	</div><!-- .fl-post-content -->

	<?php FLTheme::post_bottom_meta(); ?>
	<?php do_action( 'fl_after_post_content' ); ?>
	<?php if ( has_post_thumbnail() && 'beside' == $show_thumbs && 0 == $show_full) : ?>
		</div>
	</div>
	<?php endif; ?>

</article>
<?php do_action( 'fl_after_post' ); ?>
<!-- .fl-post -->
