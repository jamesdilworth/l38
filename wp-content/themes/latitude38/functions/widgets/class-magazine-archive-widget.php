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
            'magtype' => ''
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $title = $instance['title'];
        $qty = $instance['qty'];
        $magtype = $instance['magtype'];

        ?>
        <p>Title: <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
        <p>Number of Issues: <input class="widefat" name="<?php echo $this->get_field_name('qty'); ?>"  type="number" value="<?php echo $qty; ?>" /></p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'magtype' ) ); ?>"><?php _e( 'Magazine Type:' ); ?></label>
            <?php
                wp_dropdown_categories(
                        array(
                            'show_option_all' => 'All',
                            'hide_empty'=> 0,
                            'taxonomy' => 'magtype',
                            'name' => $this->get_field_name("magtype"),
                            'value_field' => 'slug',
                            'selected' => $magtype[0]
                        ));
             ?>
        </p>

        <?php
    }

    public function update($new_instance, $old_instance) {
        // save widget options
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['qty'] = intval($new_instance['qty']);
        $instance['magtype'] = $new_instance['magtype'];
        return $instance;
    }

    public function widget($args, $instance) {
        // display
        extract($args);

        $title = (empty($instance['title'])) ? "" : apply_filters('widget_title', $instance['title']);
        $qty = (empty($instance['qty'])) ? -1 : $instance['qty'];
        $magtype = (empty($instance['magtype'])) ? "" : $instance['magtype'];

        $args = array(
            'post_type' => 'magazine',
            'posts_per_page' => $qty,
            'post_status' => 'publish',
            'orderby' => "date",
            'order' => 'DESC'
        );

        if(!empty($magtype)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'magtype',
                    'field'    => 'slug',
                    'terms'    => $magtype,
                ),
            );
        }

        $magazines = new WP_Query($args);

        echo $before_widget;
        $output = "";
        $tabs = array();

        if ( $magazines->have_posts() ) {
            while ($magazines->have_posts()) {
                $magazines->the_post();

                $features = get_field('features');
                $core_url = get_field('magazine_url');
                $cover = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(),'medium') : '/wp-content/uploads/2018/06/default_cover.jpg';
                $pdf = get_field('upload_pdf');
                if(strlen($pdf) == 5) {
                    // This ACF field got mangled, and some of the data will be output as ID's not, URL's.
                    $pdf = wp_get_attachment_url( $pdf );
                }

                $year = get_the_date('Y');

                $output .= "<div class='magazine-cover $year'>";

                $output .= "    <div class='cover' style='background-image:url($cover);'><div class='links'>";
                if($features)
                    $output .= "<a href='" . get_the_permalink() . "'><i class='fa fa-list'></i> Contents</a>";
                if($core_url)
                    $output .= "<a " . get_pdf_link($core_url,1) . "'><i class='fas fa-book-open'></i> Read Online</a>";
                if($pdf)
                    $output .= "<a href='" . $pdf . "' target='_blank'><i class='fa fa-download'></i> Download (PDF)</a>";
                $output .= "</div></div>";
                $output .= '    <div class="title">' . get_the_title() . '</div>';
                if (current_user_can('edit_posts')) $output .= '<div class="share edit_link"><a href="' . get_the_permalink() . '">Sharable URL</a></div>';
                $output .= '</div>';

                $tabs[$year] .= $output;
            }
        } else {
            $output = "No Magazines Found?";
        }

        // Output tabs
        echo "<ul class='tabs'>";
        foreach($tabs as $key => $value) {
            echo "<li class='tab'><a href='#$key' data-targets='$key'>$key</a></li>";
        }
        echo "</ul>";


        // Output content
        echo "<div class='tabbed'>$output</div>";

        wp_reset_postdata();

        echo $after_widget;
    }
}
