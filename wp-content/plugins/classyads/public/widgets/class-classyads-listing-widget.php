<?php
if ( ! function_exists( 'wp_terms_checklist' ) ) {
    include ABSPATH . 'wp-admin/includes/template.php';
}


class classyads_listing_widget extends WP_Widget {
    function __construct() {
        // process
        $widget_ops = array(
            'classname' => 'classy_ads',
            'description' => 'Shows a listing of Classified Ads'
        );
        parent::__construct('classy_ads_widget','L38: Classyads Listing', $widget_ops);
    }

    public function form($instance) {
        // widget form in dashboard
        $defaults = array(
            'title' => 'Classy Classifieds',
            'qty' => 0,
            'start_date' => NULL,
            'adcats' => '',
            'options' => array()
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $title = $instance['title'];
        $qty = $instance['qty'];
        $adcats = $instance['adcats'];
        $options = (array) $instance['options'];

        ?>
        <p>Title: <input class="widefat"
                         name="<?php echo $this->get_field_name('title'); ?>"
                         type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <p>Default Number of Ads: <input class="widefat"
                                    name="<?php echo $this->get_field_name('qty'); ?>"
                                    type="number" value="<?php echo $qty; ?>" /></p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'adcats' ) ); ?>"><?php _e( 'Ad Category:' ); ?></label>
            <?php wp_dropdown_categories(
                    array(
                        'show_option_all' => 'All',
                        'depth' => 1,
                        'hierarchical' => 1,
                        'hide_empty'=> 0,
                        'taxonomy' => 'adcat',
                        'name' => $this->get_field_name("adcats"),
                        'value_field' => 'slug',
                        'selected' => $adcats
                    ));
            ?>
        </p>

        <label for="<?php echo esc_attr( $this->get_field_id( 'options' )); ?>"><?php _e( 'Options:' ); ?></label>
        <ul class="nodots">
            <?php
                $valid_options = array(
                    'show_primaries' => "Primary Filters (If 'All' Selected)",
                    'show_filters' => 'Show Filters',
                );
                $fid = $this->get_field_id('options[]') ;
                $fn = $this->get_field_name('options[]') ;
                foreach($valid_options as $opt => $title) {
                    $checked = in_array($opt, $options) ? 'checked' : '' ;
                    echo "<li><input type='checkbox' id='$fid' name='$fn' value='$opt' $checked>$title</li>";
                }
            ?>
        </ul>
         <?php
    }

    public function update($new_instance, $old_instance) {
        // save widget options
        $instance = $old_instance;

        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['qty'] = intval($new_instance['qty']);
        $instance['adcats'] = $new_instance['adcats'];
        $instance['options'] = $new_instance['options'];

        return $instance;
    }

    public function widget($args, $instance) {
        // display
        global $post;
        global $wp_query; // To enable pagination?

        extract($args);

        Classyads_Public::enqueue_view_scripts();

        // Config Vars
        $outerpost_id = $post->ID;
        $title = (empty($instance['title'])) ? "" : apply_filters('widget_title', $instance['title']);
        $qty = (empty($instance['qty'])) ? -1 : $instance['qty'];

        $adcats = (empty($instance['adcats'])) ? array() : $instance['adcats']; // If selected, this should by now be a string.
        $selected_adcat_term_id = "";
        if(empty($adcats)) {
            $adcats = ""; // If all selected, adcats will be an array.
        } else {
            $selected_adcat_term =  get_term_by('slug', $adcats, 'adcat');
            $selected_adcat_term_id = intval($selected_adcat_term->term_id);
        }

        $options = (empty($instance['options'])) ? array() : $instance['options'];

        $adcat_options = get_terms( 'adcat', array('hide_empty' => false));

        $primary_cats = array();

        $args = array(); // We'll send this to get_the_classys() to build the query.

        if($qty) $args['posts_per_page'] = $qty;

        if(!empty($adcats)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'adcat',
                    'field' => 'slug',
                    'terms' => $adcats
                )
            );
        }

        // Output
        echo $before_widget;

        echo '<div class="classy_widget">';

        // Get the top categories... put them into a checkbox.
        if(in_array('show_primaries', $options) && empty($adcats)) {
            echo '<ul class="primary-filters">';
            foreach ($adcat_options as $adcat) {
                if ($adcat->parent == 0) {
                    $primary_cats[] = $adcat;
                    echo "<li><input type='radio' value='$adcat->term_id' name='primary_cats'> $adcat->name</li>";
                }
            }
            echo '</ul><!-- /primary-filters -->';
        }

        if(in_array('show_filters', $options)) {
            // Secondary Filters
            echo '<div class="secondary-filters">';
            echo '    <h3>Filters</h3>';

            // Search
            echo '  <div class="search-filter filter">';
            echo "    <label for='search'>Search</label><input class='search' type='text' name='search' placeholder='e.g. Catalina, Moore'><i class='fa fa-search'></i>";
            echo '  </div>';

            // Secondary Level Categories
            echo '    <div class="secondary-cats filter">';
            echo '      <label>Types</label>';
            wp_terms_checklist(0, array('taxonomy' => 'adcat', 'popular_cats' => array($selected_adcat_term_id))); // This outputs all the categories... we then have to filter that with CSS & Javascript.
            echo '    </div>';

            // Max and Min Length... show only if it's a boat!
            if(stristr($adcats, 'boats')) {
                echo  '    <div class="max-min-filters filter">';
                echo  '        <label>Length (ft)</label>';
                echo  "        <div class='inputs'><input class='small' type='text' name='min_length' id='min_length' value='' placeholder='Min'> to <input class='small' type='text' name='max_length' id='max_length' value='' placeholder='Max'></div>";
                echo  '    </div>';
            }

            echo  '</div><!-- /secondary-filters -->';
        }

        $output = "<div id='classyad_listing' class='classyads $adcats'>";
        $output .= get_the_classys($args); // This is the heavy lifting right here
        $output .= "</div>";

        $output .= "</div><!-- /classywidget -->";

        echo $output;

        echo $after_widget;
    }
}
