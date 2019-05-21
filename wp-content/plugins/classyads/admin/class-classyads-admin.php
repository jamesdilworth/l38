<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.jamesdilworth.com
 * @since      0.1.0
 *
 * @package    Classyads
 * @subpackage Classyads/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Classyads
 * @subpackage Classyads/admin
 * @author     James D <james@jamesdilworth.com>
 */
class Classyads_Admin {

	private $plugin_name;
	private $version; // This is carried down so that the enqueues can cachebust.
    private $localize_vars;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->localize_vars = array(
		    'is_classy' => false,
		    'is_expired' => false,
            'test' => 'hello'
        );
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, CLASSYADS_URL . 'admin/css/classyads-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
	    wp_enqueue_script( 'classyads-admin', CLASSYADS_URL . 'admin/js/classyads-admin.js', array( 'jquery' ), filemtime( CLASSYADS_PATH . 'admin/js/classyads-admin.js'), true );
	}

	public function localize_footer_scripts() {
	    wp_localize_script( 'classyads-admin', 'localized',  $this->localize_vars );
    }

    public function add_plugin_admin_menu() {
        /**
         * Add a settings page for this plugin to the Settings menu.  http://codex.wordpress.org/Administration_Menus
         */
        add_submenu_page(
            'edit.php?post_type=classy',
            'L38 Classifieds: Settings',
            'Settings',
            'manage_options',
            'settings',
            array($this, 'display_settings_page')
        );

        add_submenu_page(
            'edit.php?post_type=classy',
            'L38 Classifieds: Transactions',
            'Transactions',
            'edit_pages',
            'transactions',
            array($this, 'display_transactions_page')
        );

        add_submenu_page(
            'edit.php?post_type=classy',
            'L38 Classifieds: Export',
            'Export',
            'edit_pages',
            'export',
            array($this, 'display_export_page')
        );

        add_submenu_page(
            'edit.php?post_type=classy',
            'L38 Classifieds: Import from Lasso',
            'Import',
            'edit_pages',
            'import',
            array($this, 'display_import_page')
        );
    }

    public function add_action_links( $links ) {
        /**
        *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
        */
        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
        );
        return array_merge(  $settings_link, $links );

    }

    public function add_post_status_list() {
	    /**
         * We add the post status to the admin area with Javascript. Here, we're checking the post status and then
         * passing the variables into the localized version of the Javascript.
         */
        global $post;
        if($post->post_type == 'classy'){
            if($post->post_status == 'expired'){
                $this->localize_vars['is_classy_expired'] = true;
            }
            if($post->post_status == 'removed'){
                $this->localize_vars['is_classy_removed'] = true;
            }
            $this->localize_vars['is_classy_post_page'] = true;
        }
    }

    public function display_expired_state( $states ) {
	    /**
         * This is hooked into the main dashboard view of the classies to show the expired state.
         */
        global $post;
        $arg = get_query_var( 'post_status' );

        if($arg != 'expired'){ // if we haven't searched by
            if($post->post_status == 'expired'){
                return array('Expired');
            }
        }

        if($arg != 'removed'){ // if we haven't searched by
            if($post->post_status == 'removed'){
                return array('Removed');
            }
        }

        return $states;
    }

    public function display_transactions_page() {
        include_once( 'partials/classyads-admin-transactions.php' );
    }

    public function display_export_page() {
        include_once( 'partials/classyads-admin-export.php' );
    }

    public function display_settings_page() {
        include_once( 'partials/classyads-admin-settings.php' );
    }

    public function display_import_page() {
        include_once( 'partials/classyads-admin-import.php' );
    }

    public function options_update() {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

    public function handle_acf_saved($post_id) {
        // This gets fired after the post is saved through ACF.

        // bail early if no ACF data
        if( empty($_POST['acf']) ) {
            return;
        }

        // If the expired post has an expire date after today, unexpire it.
        /* Actually, this is not really necessary, as it is obvious.
        if(get_post_status($post_id) == 'expired') {
            $date = get_field('ad_expires', $post_id);
            $date = strtotime($date);
            if($date > time()) {
                // PC::debug('Post should no longer be expired');
            }
        }
        */

        // array of field values
        // $fields = $_POST['acf'];

        // specific field value
        // $field = $_POST['acf']['field_abc123'];
    }

    function output_classyads() {
        $args = array(
            'post_type' => 'classy',
            'post_status' => 'publish',
            'posts_per_page' => -1
        );

        // We want an ordered list, so let's initialize the array with everything in order :)
        // This needs to match with what is coming in, so it's a little brittle.
        $ads_by_cat = array(
            "Dinghies, Liferafts and Rowboats" => array(),
            "24 Feet and Under" => array(),
            "25 to 28 Feet" => array(),
            "29 to 31 Feet" => array(),
            "32 to 34 Feet" => array(),
            "35 to 39 Feet" => array(),
            "40 to 50 Feet" => array(),
            "51 Feet and Over" => array(),
            "Power & Houseboats" => array(),
            "Classic Boats" => array(),
            "Multihulls" => array(),
            "Partnerships" => array(),
        );

        $active_classies = new WP_Query($args);
        if ( $active_classies->have_posts() ) {
            while ($active_classies->have_posts() ) {
                $active_classies->the_post();

                $classyad = new Classyad(get_the_ID());


                if(!($classyad->is_print_ad())) {
                    // Don't include if this isn't a print ad.
                    continue;
                }

                // It also needs to be valid into next month!!!! So not just alive today, but alive for the following month!
                $expiry = $classyad->key_dates['expiry'];

                // So... the expiry date should be at least a month away.
                $check_date = new DateTime('first day of next month');
                if($expiry < $check_date) {
                    // It's not valid for next months print run.
                    continue;
                }

                $item = array(
                    'title' => $classyad->title . ", ",
                    'location' => $classyad->custom_fields['boat_location'],
                    'price' => $classyad->custom_fields['ad_asking_price'],
                    'ad_mag_text' => $classyad->custom_fields['ad_mag_text'],
                    'img' => "",
                    'external_url' => isset($classyad->custom_fields['external_url']) ? $classyad->custom_fields['external_url'] : null,
                    'owner_deets' => $classyad->owner_deets,
                    'url' => get_the_permalink(),
                    'id' => get_the_ID()
                );

                if($item['owner_deets']['phone'])
                    $item['owner_deets']['phone'] = JZUGC_format_phone($item['owner_deets']['phone']);

                if($classyad->plan['print_photo']) {
                    $item['img'] = $classyad->main_image_url;
                }

                $mag_cat = $classyad->getMagazineCat();
                $ads_by_cat[$mag_cat][] = $item;
            }
            wp_reset_postdata();
        }

        // Now let's iterate through the categories in the order that we want?
        $output = "<table border='1' style='padding:3px;' class='export_table'>";
        foreach($ads_by_cat as $cat_name => $list) {
            $output .= "<tr><td class='section_title' colspan='3'>" . $cat_name . "</td></tr>";
            if(is_array($list)) {
                foreach($list as $ad) {
                    $output .= "<tr><td class='selectable'>";
                    $output .= "<b style='text-transform:uppercase'>{$ad['title']}</b>";
                    if(!empty($ad['price'])) $output .= " " . $ad['price'] . ".";
                    if(!empty($ad['location'])) $output .= " " . $ad['location'] . ".";
                    if(!empty($ad['ad_mag_text'])) $output .= " " . $ad['ad_mag_text'] . ".";
                    $output .= " Contact " . $ad['owner_deets']['firstname'] . " at " . $ad['owner_deets']['email'];
                    if($ad['owner_deets']['phone']) $output .= " or " . $ad['owner_deets']['phone'];
                    if($ad['owner_deets']['other']) $output .= " or " . $ad['owner_deets']['other'];
                    if(isset($ad['external_url'])) $output .= "See more at " . $ad['external_url'];
                    if(!empty($ad['img']))
                        $output .= "</td><td class='unselectable'><img src='" . $ad['img'] . "' style='width:200px;'>";
                    else
                        $output .= "</td><td class='unselectable'>";
                    $output .= "</td>";
                    $output .= "<td class='unselectable'><a href='" .$ad['url'] . "' target='_blank'>" . $ad['id'] . "</a>";
                }
            }
        }
        $output .= "</table>";
        echo $output;
    }

    public function validate($input) {
        // All checkboxes inputs
        $valid = array();

        //Cleanup
        $valid['show_on_home'] = (isset($input['show_on_home']) && !empty($input['show_on_home'])) ? 1 : 0;
        $valid['another_option'] = (isset($input['another_option']) && !empty($input['another_option'])) ? 1 : 0;
        $valid['another_text'] = esc_url($input['another_text']);

        return $valid;
    }

}
