<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
	<div id="comment-<?php comment_ID(); ?>" class="comment-body clearfix">
        <div class="comment-avatar"><?php echo get_avatar( $comment, 80 ); ?></div>
		<div class="comment-meta">
            <span class="comment-author">
                <?php echo get_comment_author_link(); ?>
                <?php
                    $extras = array();
                    if(get_field('boat_name')) $extras[] = get_field('boat_name');
                    if(get_field('boat_type')) $extras[] = get_field('boat_type');
                    if(get_field('hailing_port')) $extras[] = get_field('hailing_port');

                    if(!empty($extras)) echo '<span class="comment-author-extras">' . implode(", ", $extras) . '</span>';

                ?>
            </span>
            <span class="time-since">
                <?php echo human_time_diff((int) get_comment_time('U'), current_time('U')) . ' ago'; ?>
            </span>

		</div><!-- .comment-meta -->

		<div class="comment-content clearfix">
			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'fl-automator' ) ?></p>
			<?php endif; ?>
			<?php comment_text(); ?>
        </div><!-- .comment-content -->

        <div class="comment-options">
            <?php
            $comment_reply_link = get_comment_reply_link(array_merge($args, array(
                'reply_text' => esc_attr__( 'Reply', 'fl-automator' ),
                'depth'      => (int) $depth,
                'max_depth'  => (int) $args['max_depth'],
            )));

            if ( $comment_reply_link ) {
                echo '<span class="comment-reply-link">' . $comment_reply_link . '</span>';
            }

            ?>
            <span class="comment-edit-link"><?php edit_comment_link( esc_html_x( 'Edit', 'Comment edit link text.', 'fl-automator' ), ' ' ); ?> </span>
        </div>

	</div><!-- .comment-body -->
