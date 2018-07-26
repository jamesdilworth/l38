<?php

class lectronic_stories_widget extends WP_Widget {
    function __construct() {
        // process
        $widget_ops = array(
            'classname' => 'lectronic_stories',
            'description' => 'Adds a listing of Latitude stories to the page'
        );
        parent::__construct('lectronic_stories_widget','L38 : Lectronic Stories', $widget_ops);
    }

    public function form($instance) {
        // widget form in dashboard
        $defaults = array(
            'title' => 'Lectronic Stories',
            'qty' => 1,
            'start_date' => NULL
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $title = $instance['title'];
        $qty = $instance['qty'];
        $start_date = $instance['start_date'];
        ?>
        <p>Title: <input class="widefat"
                         name="<?php echo $this->get_field_name('title'); ?>"
                         type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <p>Number of Days: <input class="widefat"
                                    name="<?php echo $this->get_field_name('qty'); ?>"
                                    type="number" value="<?php echo $qty; ?>" /></p>

        <p>Start Date : Set Programmatically</p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        // save widget options
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['qty'] = intval($new_instance['qty']);
        $instance['start_date'] = $new_instance['start_date'];
        return $instance;
    }

    public function widget($args, $instance) {
        // display
        global $post;
        extract($args);

        echo $before_widget;

        $outerpost_id = $post->ID;
        $title = (empty($instance['title'])) ? "" : apply_filters('widget_title', $instance['title']);
        $qty = (empty($instance['qty'])) ? -1 : $instance['qty'];
        $type = (empty($instance['type'])) ? array() : $instance['type'];
        $start_date = (empty($instance['start_date'])) ? NULL : $instance['start_date'];

        $dayswithposts = days_with_posts($qty, $start_date);

        foreach($dayswithposts as $pubdate) {

            $args = array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'year' => $pubdate[0],
                'monthnum' => $pubdate[1],
                'day' => $pubdate[2],
                'meta_key' => 'sort_order',
                'orderby' => "meta_value_num",
                'order' => 'ASC'
            );

            $output = "";
            $posts = new WP_Query($args);

            if ( $posts->have_posts() ) {

                $output .= '<div class="lectronic-edition">';

                if(is_page('lectronic')) {
                    $output .= "<div class='day section-heading'><a class='title' href='". get_day_link( $pubdate[0], $pubdate[1], $pubdate[2] ) . "'  rel='nofollow'>" . date('D, F j', mktime(0, 0, 0, $pubdate[1], $pubdate[2], $pubdate[0])) . "</a></div>";
                } else {
                    $output .= "<div class='day section-heading'><a class='title' style='width:300px;' href='/lectronic/'>'Lectronic Latitude: ". date('D, F j', mktime(0, 0, 0, $pubdate[1], $pubdate[2], $pubdate[0])) . "</a></div>";
                }

                $x = 0;
                while ($posts->have_posts()) {
                    $posts->the_post();

                        $main = ($x > 0) ? 'normal' : 'main';
                        $post_id = get_the_ID();

                        if($post_id == $outerpost_id) continue; // Don't display a link to the page we're already on!

                        $url = get_day_link( $pubdate[0], $pubdate[1], $pubdate[2] ) . '#' . get_post_field( 'post_name');

                        $escaped_img_url = str_replace(" ","%20", get_the_post_thumbnail_url($post_id,'large'));

                        $output .= "<article class='$main story'>";

                        if ( has_post_thumbnail()) {
                            $output .= "<div class='image' style='background-image:url(" . $escaped_img_url . ")'>" . get_the_post_thumbnail($post_id,'large') . "</div>";
                        } else {
                            $output .= "<div class='image'><img src='/wp-content/uploads/2018/06/default_thumb.jpg' alt=''></div>";
                        }

                        $output .= get_the_category_list();
                        $output .= "<div class='alt_header'>" . get_field('alt_header') . "</div>";
                        $output .= "<h2 class='title'><a href='$url'>" . get_the_title() . "</a></h2>";

                        // Get the excerpt
                        $output .= "<div class='desc'>";
                        ob_start();

                        if(function_exists('the_advanced_excerpt')) {
                            the_advanced_excerpt('length=12&length_type=words&no_custom=0&finish=sentence&no_shortcode=1&ellipsis=&add_link=1&exclude_tags=p,div,img,b,figure,figcaption,strong,em,i,ul,li,a,ol,h1,h2,h3,h4');
                        } else {
                            the_excerpt();
                        }

                        $output .= ob_get_contents() . "</div>";
                        ob_end_clean();

                        if (current_user_can('edit_posts'))
                            $output .= '<div class="edit_link"><a href="' . get_edit_post_link() . '">Edit Story</a></div>';

                        $output .= '</article>';
                        $x++;
                }
                $output .= '   </div>';
            } else {
                $output = "No posts for this day?.";
            }

            echo $output;
            wp_reset_postdata();
        }

        echo $after_widget;
    }
}
