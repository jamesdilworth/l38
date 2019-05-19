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
            'primary_adcat' => ''
        );
        $instance = wp_parse_args((array) $instance, $defaults);

        $title = $instance['title'];
        $primary_adcat = $instance['primary_adcat'];

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
        <?php
    }

    public function update($new_instance, $old_instance) {
        // save widget options
        $instance = $old_instance;

        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['primary_adcat'] = $new_instance['primary_adcat'];

        return $instance;
    }

    public function widget($args, $instance) {
        $current_jzuser = new JZ_User(get_current_user_id());
        extract($args);

        Classyads_Public::enqueue_view_scripts();
        Classyads_Public::enqueue_form_scripts();

        $title = (empty($instance['title'])) ? "" : apply_filters('widget_title', $instance['title']);
        $primary_adcat_id = (empty($instance['primary_adcat'])) ? 0 : $instance['primary_adcat']; // this should be an integer ID
        $primary_adcat_obj = get_term_by( 'id', $primary_adcat_id, 'adcat' );
        $primary_adcat = $primary_adcat_obj->slug;

        $secondary_adcats = get_term_children($primary_adcat_id, 'adcat');

        // TODO... only show the form if the user is logged in!

        // Embed the form HTML.... which we'll put in a seperate partials file.
        include(CLASSYADS_PATH . 'public/templates/form-placead.php');

    }
}
