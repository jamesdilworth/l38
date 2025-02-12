<?php

if ( ! comments_open() && '0' == get_comments_number() ) {
	return;
}
if ( post_password_required() ) {
	return;
}

?>
<div class="fl-comments">

	<?php do_action( 'fl_comments_open' ); ?>

	<?php if ( have_comments() ) : ?>
	<div class="fl-comments-list">

		<h3 class="fl-comments-list-title"><?php

		$num_comments = get_comments_number();

		if ( $num_comments ) {

			printf(
				esc_html( _nx( '%1$s Comment', '%1$s Comments', get_comments_number(), 'Comments list title.', 'fl-automator' ) ),
				number_format_i18n( $num_comments )
			);

		} else {

			_e( 'No Comments', 'fl-automator' );

		}

		?></h3>

		<ol id="comments">
		<?php wp_list_comments( array(
			'callback' => 'FLTheme::display_comment',
		) ); ?>
		</ol>

		<?php if ( get_comment_pages_count() > 1 ) : ?>
		<nav class="fl-comments-list-nav clearfix">
			<div class="fl-comments-list-prev"><?php previous_comments_link() ?></div>
			<div class="fl-comments-list-next"><?php next_comments_link() ?></div>
		</nav>
		<?php endif; ?>

	</div>
	<?php endif; ?>
    <!-- Start form -->
	<?php

	comment_form( array(
	    'id_form'               => 'fl-comment-form',
		'class_form'            => 'fl-comment-form',
		'id_submit'             => 'fl-comment-form-submit',
		'class_submit'          => 'btn btn-primary',
		'name_submit'           => 'submit',
		'label_submit'          => __( 'Submit Comment', 'fl-automator' ),
		'title_reply'           => _x( 'Leave a Comment', 'Respond form title.', 'fl-automator' ),
		'title_reply_to'        => __( 'Leave a Reply', 'fl-automator' ),
		'cancel_reply_link'     => __( 'Cancel Reply', 'fl-automator' ),
		'cancel_reply_before'   => '<div class="cancel-reply-link">',
        'cancel_reply_after'    => '</div>',
		'format'                => 'xhtml',
		'comment_notes_before'  => '',
		'comment_notes_after'   => '',

		'comment_field'         => '<div class="comment_field field_comment"><label for="comment" class="required">' . _x( 'Comment', 'Comment form label: comment content.', 'fl-automator' ) . '</label><textarea name="comment" class="form-control" cols="60" rows="4" tabindex="4" placeholder="Please keep it friendly."></textarea></div>',

		'must_log_in'           => '<p>' . sprintf( _x( 'You must be <a%s>logged in</a> to post a comment.', 'Please, keep the HTML tags.', 'fl-automator' ), ' href="' . esc_url( home_url( '/wp-login.php' ) ) . '?redirect_to=' . urlencode( get_permalink() ) . '"' ) . '</p>',

		'logged_in_as'          => '<p>' . sprintf( __( 'Logged in as %s.', 'fl-automator' ), '<a href="' . esc_url( home_url( '/wp-admin/profile.php' ) ) . '">' . $user_identity . '</a>' ) . ' <a href="' . wp_logout_url( get_permalink() ) . '" title="' . __( 'Log out of this account', 'fl-automator' ) . '">' . __( 'Log out &raquo;', 'fl-automator' ) . '</a></p>',

		'fields'                => apply_filters( 'comment_form_default_fields', array(

			'author'                 => '<div class="comment_field field_author"><label for="author" class="required">' . _x( 'Name', 'Comment form label: comment author name.', 'fl-automator' )  . '</label>
										<input type="text" name="author" class="form-control author" placeholder="Your Name (Required)" value="' . $comment_author . '" tabindex="5"' . ( $req ? ' aria-required="true"' : '' ) . ' /></div>',

			'email'                  => '<div class="comment_field field_email"><label for="email" class="required">' . _x( 'Email (will not be published)', 'Comment form label: comment author email.', 'fl-automator' )  . '</label>
										<input type="text" name="email" class="form-control email" placeholder="Your Email (Required)" value="' . $comment_author_email . '" tabindex="6"' . ( $req ? ' aria-required="true"' : '' ) . ' /></div>'


		) ),
	) );

	// 'url'                    => '<div class="comment_field field_website"><label for="url">' . _x( 'Website', 'Comment form label: comment author website.', 'fl-automator' ) . '</label>
    //								<input type="text" name="url" class="form-control website" placeholder="Your website" value="' . $comment_author_url . '" tabindex="7" /></div>',

	?>

	<?php do_action( 'fl_comments_close' ); ?>
</div>
