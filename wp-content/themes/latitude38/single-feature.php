<?php get_header(); ?>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article <?php post_class( 'fl-post' ); ?> id="fl-post-<?php the_ID(); ?>" itemscope itemtype="https://schema.org/BlogPosting">

    <div class="feature-header" style="background-image:url(<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large' ); ?>)">
        <div class="shadow">
            <div class="container">
                <div class="row">
                    <div class="fl-content <?php FLTheme::content_class(); ?>">
                        <div class="fl-post-thumb">
                            <?php
                                // Shown for mobile users...
                                the_post_thumbnail( 'large', array('itemprop' => 'image',));
                            ?>
                        </div>

                        <?php if(!is_category()) the_category(); ?>
                        <h1 class="fl-post-title" itemprop="headline"><?php the_title(); ?></h1>
                        <?php FLTheme::post_top_meta(); ?>
                        <?php edit_post_link( _x( 'Edit', 'Edit post link text.', 'fl-automator' ) ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="fl-content <?php FLTheme::content_class(); ?>">
               <div class="fl-post-content clearfix" itemprop="text">
                    <?php the_content(); ?>
                    <?php
                        wp_link_pages( array(
                            'before'         => '<div class="fl-post-page-nav">' . _x( 'Pages:', 'Text before page links on paginated post.', 'fl-automator' ),
                            'after'          => '</div>',
                            'next_or_number' => 'number',
                        ) );
                    ?>
                    <?php display_social_sharing_buttons(); ?>
                    <?php if(!in_category(199)) comments_template(); ?>
               </div><!-- .fl-post-content -->
            </div>
        </div>
    </div>

    </article>
<?php endwhile;
endif; ?>

<?php get_footer();