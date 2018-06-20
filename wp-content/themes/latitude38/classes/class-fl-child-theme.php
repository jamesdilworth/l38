<?php

/**
 * Helper class for child theme functions.
 *
 * @class FLChildTheme
 */
final class FLChildTheme {
    
    /**
	 * Enqueues scripts and styles.
	 *
     * @return void
     */
    static public function enqueue_scripts()
    {
	    wp_enqueue_style( 'fl-child-theme', FL_CHILD_THEME_URL . '/css/base.css' );
    }

    static public function archive_page_header() {
        // Category
        if ( is_category() ) {
            $page_title = single_cat_title( '', false );
        } elseif ( is_tag() ) {
            $page_title = sprintf( _x( 'Posts Tagged &#8216;%s&#8217;', 'Archive title: tag.', 'fl-automator' ), single_tag_title( '', false ) );
        } // Day
        elseif ( is_day() ) {
            $page_title = sprintf( _x( '%s', 'Archive title: day.', 'fl-automator' ), get_the_date() );
        } // Month
        elseif ( is_month() ) {
            $page_title = sprintf( _x( 'Archive for %s', 'Archive title: month.', 'fl-automator' ), single_month_title( ' ', false ) );
        } // Year
        elseif ( is_year() ) {
            $page_title = sprintf( _x( 'Archive for %s', 'Archive title: year.', 'fl-automator' ), get_the_time( 'Y' ) );
        } // Author
        elseif ( is_author() ) {
            $page_title = sprintf( _x( 'Posts by %s', 'Archive title: author.', 'fl-automator' ), get_the_author() );
        } // Search
        elseif ( is_search() ) {
            $page_title = sprintf( _x( 'Search results for: %s', 'Search results title.', 'fl-automator' ), get_search_query() );
        } // Paged
        elseif ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) {
            $page_title = _x( 'Archives', 'Archive title: paged archive.', 'fl-automator' );
        } // Index
        else {
            $page_title = '';
        }

        if ( ! empty( $page_title ) ) {
            include locate_template( 'includes/archive-header.php' );
        }
    }
}