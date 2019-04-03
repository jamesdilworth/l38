<?php
/**
 * Widget embeds a Classy Ad Sign Up form into a page.
 */

if ( ! function_exists( 'wp_terms_checklist' ) ) {
    include ABSPATH . 'wp-admin/includes/template.php';
}

class classyads_placead_widget extends WP_Widget {
    private $plugin_path;
    private $plugin_url;

    function __construct() {
        // process
        $this->plugin_path = plugin_dir_path( dirname(__DIR__ ));
        $this->plugin_url = plugin_dir_url( dirname(__DIR__ ));
        $widget_ops = array(
            'classname' => 'classy_ads',
            'description' => 'Shows a listing of Classified Ads'
        );
        parent::__construct('classyasd_placead_widget','L38: Place Classy Ad', $widget_ops);
    }

    public function form($instance) {
        // widget form in dashboard
        $defaults = array(
            'title' => 'Place a Classy Ad',
            'primary_adcat' => '',
            'options' => array('')
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $title = $instance['title'];
        $primary_adcat = $instance['primary_adcat'];
        $options = (array) $instance['options'];

        ?>
        <p>Title: <input class="widefat"
                         name="<?php echo $this->get_field_name('title'); ?>"
                         type="text" value="<?php echo esc_attr($title); ?>" /></p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'primary_adcat' ) ); ?>"><?php _e( 'Ad Category:' ); ?></label>
            <?php wp_dropdown_categories(
                    array(
                        'show_option_all' => 'All',
                        'depth' => 1,
                        'hierarchical' => 1,
                        'hide_empty'=> 0,
                        'taxonomy' => 'adcat',
                        'name' => $this->get_field_name("primary_adcat"),
                        'value_field' => 'id',
                        'selected' => $primary_adcat
                    ));
            ?>
        </p>

        <label for="<?php echo esc_attr( $this->get_field_id( 'options' )); ?>"><?php _e( 'Options:' ); ?></label>
        <ul class="nodots">
            <?php
                $valid_options = array(
                    'an_option' => 'Dummy Option',
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
        $instance['primary_adcat'] = $new_instance['primary_adcat'];
        $instance['options'] = $new_instance['options'];

        return $instance;
    }

    public function widget($args, $instance) {
        extract($args);

        Classyads_Public::enqueue_view_scripts();
        Classyads_Public::enqueue_form_scripts();

        $title = (empty($instance['title'])) ? "" : apply_filters('widget_title', $instance['title']);
        $primary_adcat_id = (empty($instance['primary_adcat'])) ? 0 : $instance['primary_adcat']; // this should be an integer ID
        $primary_adcat_obj = get_term_by( 'id', $primary_adcat_id, 'adcat' );
        $primary_adcat = $primary_adcat_obj->slug;

        $secondary_adcats = get_term_children($primary_adcat_id, 'adcat');

        // TODO... only show the form if the user is logged in!
        // include... login form

        // Embed the form HTML.... which we'll put in a seperate partials file.
        include(CLASSYADS_PATH . 'public/templates/placead.php');

    }
}
