<?php

class classy_ads_widget extends WP_Widget {
    function __construct() {
        // process
        $widget_ops = array(
            'classname' => 'classy_ads',
            'description' => 'Shows a listing of Classified Ads'
        );
        parent::__construct('classy_ads_widget','L38: Classy Classifieds', $widget_ops);
    }

    public function form($instance) {
        // widget form in dashboard
        $defaults = array(
            'title' => 'Classy Classifieds',
            'qty' => -1,
            'start_date' => NULL
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $title = $instance['title'];
        $qty = $instance['qty'];

        ?>
        <p>Title: <input class="widefat"
                         name="<?php echo $this->get_field_name('title'); ?>"
                         type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <p>Number of Ads: <input class="widefat"
                                    name="<?php echo $this->get_field_name('qty'); ?>"
                                    type="number" value="<?php echo $qty; ?>" /></p>

         <?php
    }

    public function update($new_instance, $old_instance) {
        // save widget options
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['qty'] = intval($new_instance['qty']);
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

        // Enable Pagination
        global $wp_query;

        $args = array(
            'post_type' => 'classy',
            'posts_per_page' => -1
        );

        $output = "<div class='classyads'>";

        $posts = new WP_Query($args);
        if ( $posts->have_posts() ) {
            // Run the loop first, because calls in the loop might change the number of posts in the edition.
            while ($posts->have_posts()) {
                $posts->the_post();

                $img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'medium') : get_bloginfo('stylesheet_url') .  '/images/default-classy-ad.png';

                $output .= "<div class='ad' style='background-image:url($img)'>";
                $output .= "  <div class='meta'>";
                $output .= "    <div class='title'><a href='". get_the_permalink() ."'>" . get_the_title() . "</a></div>";
                $output .= "    <div class='price'>" . money_format('%.0n',get_field('ad_asking_price')) . "</div>";
                $output .= "    <div class='location'>" . get_field('boat_location') . "</div>";
                $output .= "</div></div>";

            }
        }
        $output .= "</div>";
        echo $output;

        wp_reset_postdata();

        echo $after_widget;
    }
}
