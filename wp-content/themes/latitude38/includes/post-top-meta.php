<?php

// Wrapper
if ( $show_author || $show_date || $comments ) {

	echo '<div class="fl-post-meta fl-post-meta-top">';

	do_action( 'fl_post_top_meta_open' );
}

// Author
if ( $show_author ) {
	echo '<span class="fl-post-author">';

	if(!empty(get_field('outside_author'))) {
        printf( _x( 'By %s', 'Post meta info: author.', 'fl-automator' ), '<span>' . get_field('outside_author') . '</span>');
    } else {
        printf( _x( 'By %s', 'Post meta info: author.', 'fl-automator' ), '<span>' . get_the_author_meta( 'display_name', get_the_author_meta( 'ID' ) ) . '</span>' );
    }
	// printf( _x( 'By %s', 'Post meta info: author.', 'fl-automator' ), '<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '"><span>' . get_the_author_meta( 'display_name', get_the_author_meta( 'ID' ) ) . '</span></a>' );
	echo '</span>';
}

// Date
if ( $show_date ) {

	if ( $show_author ) {
		echo '<span class="fl-sep"> | </span>';
	}

	echo '<span class="fl-post-date">' . get_the_date() . '</span>';
}

if (get_field('location')) {

    if ( $show_author || $show_date ) {
        echo '<span class="fl-sep"> | </span>';
    }
    echo '<span class="location">' . get_field('location') . '</span>';
}

// Comments
if ( $comments && $comment_count ) {

	if ( $show_author || $show_date ) {
		echo '<span class="fl-sep"> | </span>';
	}

	echo '<span class="fl-comments-popup-link">';
	comments_popup_link( '0 <i class="fa fa-comment"></i>', '1 <i class="fa fa-comment"></i>', '% <i class="fa fa-comment"></i>' );
	echo '</span>';
}

// Close Wrapper
if ( $show_author || $show_date || $comments ) {

	do_action( 'fl_post_top_meta_close' );

	echo '</div>';
}

// Schema Meta
FLTheme::post_schema_meta();
