<?php

class magazine_archive_widget extends WP_Widget {
    function __construct() {
        // process
        $widget_ops = array(
            'classname' => 'magazine-archive', // Added as a class to the widget
            'description' => 'Adds in magazine archives'
        );
        parent::__construct('magazine_archive_widget','L38 : Magazine Archive', $widget_ops);
    }

    public function form($instance) {
        // widget form in dashboard
        $defaults = array(
            'title' => 'Magazine Contents',
            'qty' => 12,
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $title = $instance['title'];
        $qty = $instance['qty'];

        ?>
        <p>Title: <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
        <p>Number of Issues: <input class="widefat" name="<?php echo $this->get_field_name('qty'); ?>"  type="number" value="<?php echo $qty; ?>" /></p>


        <?php
    }

    public function update($new_instance, $old_instance) {
        // save widget options
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['qty'] = intval($new_instance['qty']);
        $instance['options'] = $new_instance['options'];
        return $instance;
    }

    public function widget($args, $instance) {
        // display
        extract($args);

        $title = (empty($instance['title'])) ? "" : apply_filters('widget_title', $instance['title']);
        $qty = (empty($instance['qty'])) ? -1 : $instance['qty'];

        $args = array(
            'post_type' => 'magazine',
            'posts_per_page' => $qty,
            'post_status' => 'publish',
            'orderby' => "date",
            'order' => 'DESC'
        );
        $magazines = new WP_Query($args);

        echo $before_widget;
        $output = "";

        if ( $magazines->have_posts() ) {
            while ($magazines->have_posts()) {
                $magazines->the_post();

                $output .= '<div class="magazine-cover">';
                if ( has_post_thumbnail()) {
                    $output .= "<div class='cover'><a href='" . get_the_permalink() . "'>" . get_the_post_thumbnail(get_the_ID(),'large') . "</a></div>";
                } else {
                    $output .= "<div class='cover'><a href='" . get_the_permalink() . "'><img src='/wp-content/uploads/2018/06/default_cover.jpg' alt=''></div>";
                }
                $output .= '    <div class="title">' . get_the_title() . '</div>';
                $output .= '</div>';
            }
        } else {
            $output = "No Magazines Found?";
        }
        echo $output;
        wp_reset_postdata();

        echo $after_widget;
    }
}
