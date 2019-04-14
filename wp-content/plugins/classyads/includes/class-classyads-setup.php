<?php
class Classyads_Setup {

    public static $l38_db_version = '1.2.1';
    public static $transactions_table_sql;

    public static function init() {
        global $wpdb;

        $table_name = $wpdb->prefix . "l38_transactions";
        $charset_collate = $wpdb->get_charset_collate();

        self::$transactions_table_sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          user_id mediumint(9) NOT NULL,
          post_id mediumint(9),
          gateway tinytext NOT NULL,
          transaction_id bigint NOT NULL,
          cim_profile_id bigint,
          cim_payment_profile_id bigint,
          transaction_msg text,
          description text,
          created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          PRIMARY KEY  (id)
        ) $charset_collate;";
    }

    public static function setupDB() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( self::$transactions_table_sql );
        add_option( 'l38_db_version', self::$l38_db_version );
    }

    public static function checkDB() {

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $version_in_db = get_option( 'l38_db_version');

        if ( self::$l38_db_version != $version_in_db ) {
            // Need to upgrade the DB
            dbDelta( self::$transactions_table_sql );
            update_option('l38_db_version', self::$l38_db_version );
        }
    }

    public static function define_classy_cpt() {
        $labels = array(
            'name' => _x('Classies', 'post type general name'),
            'singular_name' => _x('Classified', 'post type singular name'),
            'add_new' => _x('Add New', 'Classified Ad'),
            'add_new_item' => __('Add New Classified Ad'),
            'edit_item' => __('Edit Ad'),
            'new_item' => __('New Ad'),
            'view_item' => __('View Ad'),
            'search_items' => __('Search Classifieds'),
            'not_found' => __('No items found'),
            'not_found_in_trash' => __('No items found in Trash'),
            'parent_item_colon' => ''
        );
        $supports = array('title', 'thumbnail', 'editor', 'author', 'excerpt', 'comments');

        register_post_type('classy',
            array(
                'labels' => $labels,
                'public' => true,
                'hierarchical' => false,
                'has_archive' => false,
                'supports' => $supports,
                'capability_type' => 'post',
                'menu_position' => 8,
                'rewrite' => array(
                    'slug' => 'classyads',
                    'with_front' => false
                ),
                'menu_icon' => 'dashicons-screenoptions'
            )
        );
    }

    /**
     * Adcat taxonomy holds the types of things that might be listed as classyads..
     * Top-level categories... Boats, Job Ads, etc.
     * Second level categories... Schooner, Motoryacht, etc.
     */
    public static function define_adcat_tax() {

        $labels = array(
            'name'				=> _x( 'Ad Cats', 'taxonomy general name' ),
            'singular_name'		=> _x( 'Ad Cat', 'taxonomy singular name' ),
            'search_items'		=>  __( 'Search Boats' ),
            'all_items'			=> __( 'All Ad Types' ),
            'edit_item'			=> __( 'Edit Ad Type' ),
            'update_item'		=> __( 'Update Ad Type' ),
            'add_new_item'		=> __( 'Add New Ad Type' ),
            'new_item_name'		=> __( 'New Ad Type' ),
            'menu_name'			=> __( 'Ad Types' )
        );

        register_taxonomy( 'adcat', array('classy'), array(
            'hierarchical'	=> true,
            'labels'		=> $labels,
            'capabilities' => array(
                'assign_terms' => 'read',
            ),
             // Added to make WP_terms checklist work for non-logged in users. https://stackoverflow.com/questions/36164916/wp-terms-checklist-checkboxes-are-disabled-in-the-subscriber-profile
            'show_ui'	    => true,
            'show_in_quick_edit' => true,
            'query_var'		=> true,
            'rewrite'		=> array(
                'slug'			=> 'adtype',
                'with_front'	=> true
            )
        ));
     }

     public static function define_post_statuses() {
         register_post_status( 'expired', array(
             'label'                    => _x( 'Expired', 'post' ),
             'public'                   => true,
             'internal'                 => false,
             'exclude_from_search'       => true,
             'show_in_admin_all_list'    => false,
             'show_in_admin_status_list' => true,
             'label_count'    => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>' )
         ) );
     }

     public static function classy_cleanup() {
         classyads_expire_ads(); // TODO!!! - Currently in events.php  - Move ehre.
     }

}
Classyads_Setup::init();


