<?php
/**
 * Widget API: Lectronic Archives
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

/**
 * Core class used to implement the Archives widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
class lectronic_archive_widget extends WP_Widget {

    public function __construct() {
        $widget_ops = array(
            'classname' => 'lectronic_archive',
            'description' => __( 'A monthly archive of L38 Posts.' ),
            'customize_selective_refresh' => true,
        );
        parent::__construct('lectronic_archive_widget', __('L38: \'Lectronic Archives'), $widget_ops);
    }

    /**
     * Outputs the content for the current Archives widget instance.
     * @since 2.8.0
     * @param array $args     Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
     * @param array $instance Settings for the current Archives widget instance.
     */
    public function widget( $args, $instance ) {
        global $wpdb;

        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Archives' );

        echo $args['before_widget'];
        if ( $title ) {
            // echo $args['before_title'] . $title . $args['after_title'];
        }

        $year_prev    = '';
        $year_current = '';

        $where = apply_filters( 'getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND post_date <= now()" );
        $join = apply_filters( 'getarchives_join', '' );

        $months = $wpdb->get_results( "SELECT YEAR(post_date) AS year, MONTH(post_date) AS numMonth, DATE_FORMAT(post_date, '%M') AS month, count(ID) as post_count FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date ASC" );

        $output = '<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Archives &nbsp; <span class="caret"></span></button>';
        $output .= '<ul class="dropdown-menu">';

        $html = array();

        // Add Old Latitudes!
        $year = 2006;
        $html[$year] = "</ul></li>";
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Dec2006Stories.html'>December</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Nov2006Stories.html'>November</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Oct2006Stories.html'>October</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Sept2006Stories.html'>September</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Aug2006Stories.html'>August</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/July2006Stories.html'>July</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/June2006Stories.html'>June</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/May2006Stories.html'>May</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/April2006Stories.html'>April</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/March2006Stories.html'>March</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Feb2006Stories.html'>February</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Jan2006Stories.html'>January</a></li>" . $html[$year];
        $html[$year] = "<ul  class='arc-list dropdown-menu'>" . $html[$year];
        $html[$year] = "<li class='year dropdown-submenu'><a href='#' class='year'>2006</a>". $html[$year];

        $year = 2004;
        $html[$year] = "</ul></li>";
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Dec2004Stories.html'>December</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Nov2004Stories.html'>November</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Oct2004Stories.html'>October</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Sept2004Stories.html'>September</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Aug2004Stories.html'>August</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/July2004Stories.html'>July</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/June2004Stories.html'>June</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/May2004Stories.html'>May</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/April2004Stories.html'>April</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/March2004Stories.html'>March</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Feb2004Stories.html'>February</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Jan2004Stories.html'>January</a></li>" . $html[$year];
        $html[$year] = "<ul  class='arc-list dropdown-menu'>" . $html[$year];
        $html[$year] = "<li class='year dropdown-submenu'><a href='#' class='year'>2004</a>". $html[$year];;

        $year = 2002;
        $html[$year] = "</ul></li>";
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Dec2002Stories.html'>December</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Nov2002Stories.html'>November</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Oct2002Stories.html'>October</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Sept2002Stories.html'>September</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Aug2002Stories.html'>August</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/July2002Stories.html'>July</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/June2002Stories.html'>June</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/May2002Stories.html'>May</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/April2002Stories.html'>April</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/March2002Stories.html'>March</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Feb2002Stories.html'>February</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Jan2002Stories.html'>January</a></li>" . $html[$year];
        $html[$year] = "<ul  class='arc-list dropdown-menu'>" . $html[$year];
        $html[$year] = "<li class='year dropdown-submenu'><a href='#' class='year'>2002</a>". $html[$year];;

        $year = 2000;
        $html[$year] = "</ul></li>";
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Dec2000Stories.html'>December</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Nov2000Stories.html'>November</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Oct2000Stories.html'>October</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Sept2000Stories.html'>September</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Aug2000Stories.html'>August</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/July2000Stories.html'>July</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/June2000Stories.html'>June</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/April-May2000Stories.html'>April-May</a></li>" . $html[$year];
        $html[$year] = "<ul  class='arc-list dropdown-menu'>" . $html[$year];
        $html[$year] = "<li class='year dropdown-submenu'><a href='#' class='year'>2000</a>". $html[$year];

        $year = 2007;
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Aug2007Stories.html'>August</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/July2007Stories.html'>July</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/June2007Stories.html'>June</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/May2007Stories.html'>May</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/April2007Stories.html'>April</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/March2007Stories.html'>March</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Feb2007Stories.html'>February</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Jan2007Stories.html'>January</a></li>" . $html[$year];
        $html[$year] = "<ul  class='arc-list dropdown-menu'>" . $html[$year];
        $html[$year] = "<li class='year dropdown-submenu'><a href='#' class='year'>2007</a>". $html[$year];

        $year = 2005;
        $html[$year] = "</ul></li>";
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Dec2005Stories.html'>December</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Nov2005Stories.html'>November</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Oct2005Stories.html'>October</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Sept2005Stories.html'>September</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Aug2005Stories.html'>August</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/July2005Stories.html'>July</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/June2005Stories.html'>June</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/May2005Stories.html'>May</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/April2005Stories.html'>April</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/March2005Stories.html'>March</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Feb2005Stories.html'>February</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Jan2005Stories.html'>January</a>" . $html[$year];
        $html[$year] = "<ul  class='arc-list dropdown-menu'>" . $html[$year];
        $html[$year] = "<li class='year dropdown-submenu'><a href='#' class='year'>2005</a>". $html[$year];;

        $year = 2003;
        $html[$year] = "</ul></li>";
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Dec2003Stories.html'>December</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Nov2003Stories.html'>November</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Oct2003Stories.html'>October</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Sept2003Stories.html'>September</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Aug2003Stories.html'>August</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/July2003Stories.html'>July</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/June2003Stories.html'>June</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/May2003Stories.html'>May</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/April2003Stories.html'>April</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/March2003Stories.html'>March</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Feb2003Stories.html'>February</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Jan2003Stories.html'>January</a></li>" . $html[$year];
        $html[$year] = "<ul  class='arc-list dropdown-menu'>" . $html[$year];
        $html[$year] = "<li class='year dropdown-submenu'><a href='#' class='year'>2003</a>". $html[$year];

        $year = 2001;
        $html[$year] = "</ul></li>";
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Dec2001Stories.html'>December</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Nov2001Stories.html'>November</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Oct2001Stories.html'>October</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Sept2001Stories.html'>September</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Aug2001Stories.html'>August</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/July2001Stories.html'>July</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/June2001Stories.html'>June</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/May2001Stories.html'>May</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/April2001Stories.html'>April</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/March2001Stories.html'>March</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Feb2001Stories.html'>February</a></li>" . $html[$year];
        $html[$year] = "<li><a href='/oldtronicLat/StoryIndices/Jan2001Stories.html'>January</a></li>" . $html[$year];
        $html[$year] = "<ul  class='arc-list dropdown-menu'>" . $html[$year];
        $html[$year] = "<li class='year dropdown-submenu'><a href='#' class='year'>2001</a>". $html[$year];


        foreach($months as $month) :
            $old = $year_current;
            $y = $year_current = $month->year;
            if ($year_current != $year_prev){
                if ($year_prev != null ){
                    $html[$old] .= '</li></ul>';
                }
                if(empty($html[$y])) {
                    $html[$y] = '<li class="year dropdown-submenu"><a href="#" class="year">'.$month->year.'</a>';
                    $html[$y] .= '<ul class="arc-list dropdown-menu">';
                }
            }
            $html[$y] .= '<li>';
            $html[$y] .= '<a href="'.get_bloginfo('url').'/lectronic/'.$month->year.'/'.date("m", strtotime($month->month)).'">';
            $html[$y] .= '<span class="arc-month">'.$month->month.'</span>';
            $html[$y] .= '</a>';
            $html[$y] .= '<span class="arc-count">('.$month->post_count.')</span>';
            $html[$y] .= '</li>';
            $year_prev = $year_current;

        endforeach;
        $html[$year_current] .= '</li></ul>';


        echo $output;
        krsort($html);
        foreach($html as $year) {
            echo $year;
        }
        echo "</ul>";

        echo $args['after_widget'];
    }

    /**
     * Handles updating settings for the current Archives widget instance.
     *
     * @since 2.8.0
     *
     * @param array $new_instance New settings for this instance as input by the user via
     *                            WP_Widget_Archives::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Updated settings to save.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
        $instance['title'] = sanitize_text_field( $new_instance['title'] );

        return $instance;
    }

    /**
     * Outputs the settings form for the Archives widget.
     *
     * @since 2.8.0
     *
     * @param array $instance Current settings.
     */
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
        $title = sanitize_text_field( $instance['title'] );
        ?>

        <?php
    }
}
