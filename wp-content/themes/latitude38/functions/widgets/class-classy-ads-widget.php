<?php
if ( ! function_exists( 'wp_terms_checklist' ) ) {
    include ABSPATH . 'wp-admin/includes/template.php';
}




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
        global $wp_query; // To enable pagination?

        extract($args);

        // Enqueue assoc classy script.
        wp_enqueue_script( 'classys', get_stylesheet_directory_uri(). '/js/classys.js', array('plugins','scripts'), filemtime( FL_CHILD_THEME_DIR . '/js/classys.js'), true ); // load scripts in footer

        // Config Vars
        $outerpost_id = $post->ID;
        $title = (empty($instance['title'])) ? "" : apply_filters('widget_title', $instance['title']);
        $qty = (empty($instance['qty'])) ? -1 : $instance['qty'];

        $adcats = get_terms( 'adcat', array('hide_empty' => false));
        $primary_cats = array();

        // Output
        echo $before_widget;

        echo '<div class="classy_widget">';

        // Get the top categories... put them into a checkbox.
        echo '<ul class="primary-filters">';
        foreach($adcats as $adcat) {
            if($adcat->parent == 0) {
                $primary_cats[] = $adcat;
                echo "<li><input type='radio' value='$adcat->term_id' name='primary_cats'> $adcat->name</li>";
            }
        }
        echo  '</ul><!-- /primary-filters -->';

        // Secondary Filters
        echo '<div class="secondary-filters">';
        echo '    <h3>Filters</h3>';

        // Search
        echo '  <div class="search-filter filter">';
        echo "    <label for='search'>Search</label><input class='search' type='text' name='search' placeholder='e.g. Catalina, Moore'><i class='fa fa-search'></i>";
        echo '  </div>';

        // Secondary Filters
        echo '    <div class="secondary-cats filter">';
        echo '      <label>Types</label>';
        wp_terms_checklist( 0, array('taxonomy' => 'adcat') );
        echo '    </div>';

        // Max and Min Length... show only if it's a boat!
        $output = "<div class='max-min-filters filter'>";
        $output .= "    <label>Length (ft)</label>";
        $output .= "    <div class='inputs'><input class='small' type='text' name='min_length' id='min_length' value='' placeholder='Min'> to <input class='small' type='text' name='max_length' id='max_length' value='' placeholder='Max'></div>";
        $output .= "</div>";

        $output .= "</div><!-- /secondary-filters -->";

        $output .= "<div id='classyad_listing' class='classyads'>";
        $output .= get_the_classys(); // This is the heavy lifting right here
        $output .= "</div>";

        $output .= "</div><!-- /classywidget -->";

        echo $output;

        echo $after_widget;
    }
}
