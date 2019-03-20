<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       www.jamesdilworth.com
 * @since      1.0.0
 *
 * @package    Classyads
 * @subpackage Classyads/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Classyads
 * @subpackage Classyads/public
 * @author     James D <james@jamesdilworth.com>
 */
class Classyads_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name; // The name of the plugin.
		$this->version = $version; // The version... used for enqueuing.
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/classyads-public.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/classyads-public.js', array( 'jquery' ), $this->version, false );
	}

	public function register_classyads_listing_widget() {
        require_once plugin_dir_path( dirname( __FILE__ )) . 'public/widgets/class-classyads-listing-widget.php';
        register_widget('classyads_listing_widget');
    }

    public function define_classy_template($template) {
	    global $post;
        if ( $post->post_type == 'classy' ) {
            return plugin_dir_path( dirname( __FILE__ )) . 'public/templates/single-classy.php';
        }
	    return $template;
    }

    public function update_classy_mainphoto() {
        // Built with the help of : https://www.ibenic.com/wordpress-file-upload-with-ajax/

        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        $posted_data =  isset( $_POST ) ? $_POST : array();
        $file_data = isset( $_FILES ) ? $_FILES : array();
        $data = array_merge( $posted_data, $file_data );

        $current_user = wp_get_current_user();
        $post = get_post($data['post_id']);

        $output = "";

        // Does the guy have the right to update this picture?
        check_ajax_referer( 'update-mainphoto', '_mainphoto_nonce' );

        if(!is_user_logged_in() || !($current_user->ID == $post->post_author || current_user_can('edit_posts'))) {
            // Not a valid user to perform this operation.
            wp_die();
        }

        $fileErrors = array(
            0 => "There is no error, the file uploaded with success",
            1 => "The uploaded file exceeds the upload_max_files in server settings",
            2 => "The uploaded file exceeds the MAX_FILE_SIZE from html form",
            3 => "The uploaded file uploaded only partially",
            4 => "No file was uploaded",
            6 => "Missing a temporary folder",
            7 => "Failed to write file to disk",
            8 => "A PHP extension stoped file to upload" );

        $response = array();

        // This should be called multiple times if multiple files in an array.
        $uploaded_file = wp_handle_upload( $data['main_photo'], array( 'test_form' => false ) );

        if ( !empty( $uploaded_file['error'] ) ) {
            echo $uploaded_file['error'];
        } else {

            $filename  = $uploaded_file['file']; // Full path to the file
            $local_url = $uploaded_file['url'];  // URL to the file in the uploads dir
            $type      = $uploaded_file['type']; // MIME type of the file

            $attachment = array(
                'post_mime_type' => $type,
                'post_title'     => 'For Sale: ' . $post->title,
                'post_excerpt'   => get_field('ad_mag_text', $post->ID),
                'post_content'   => '',
                'post_status'    => 'inherit',
                'post_author'   => $current_user
            );

            $old_thumbnail_id = get_post_thumbnail_id( $post->ID );
            if (false === wp_delete_attachment( $old_thumbnail_id)) {
                error_log('Wasn\'t able to delete the old attachment after ajax upload of the new one');
            }

            // Insert the attachment.
            $attach_id = wp_insert_attachment( $attachment, $filename, $post->ID );

            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once( ABSPATH . 'wp-admin/includes/image.php' );

            // Generate the metadata for the attachment, and update the database record.
            $attach_data = wp_generate_attachment_metadata( $attach_id,  $filename );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            set_post_thumbnail( $post->ID, $attach_id );

            // Update the copyright
            update_field('source', 'external', $attach_id);
            update_field('credit', ucwords($current_user->first_name . ' ' . $current_user->last_name), $attach_id);

            echo "Success!";
        }

        wp_die();
    }

    function update_classy_list() {

        if(!empty($_REQUEST['adcat'])) $adcat = $_REQUEST['adcat'];
        $custom = array();
        if(isset($_REQUEST['search'])) $custom['search'] = $_REQUEST['search'];
        if(isset($_REQUEST['paged'])) $custom['paged'] = $_REQUEST['paged'];
        if(isset($_REQUEST['min_length'])) $custom['min_length'] = intval($_REQUEST['min_length']);
        if(isset($_REQUEST['max_length'])) $custom['max_length'] = intval($_REQUEST['max_length']);

        if(!empty($adcat)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'adcat',
                    'field' => 'id',
                    'terms' => $adcat
                )
            );
        }

        if(!empty($custom)) {
            $args['meta_query'] = array();

            if(isset($custom['paged'])) {
                $args['paged'] = $custom['paged'];
            }
            if(isset($custom['min_length']) && $custom['min_length'] > 0) {
                $args['meta_query'][] = array(
                    'key'     => 'boat_length',
                    'value'   => $custom['min_length'],
                    'compare' => '>='
                );
            }
            if(isset($custom['max_length']) && $custom['max_length'] > 0) {
                $args['meta_query'][] = array(
                    'key'     => 'boat_length',
                    'value'   => $custom['max_length'],
                    'compare' => '<='
                );
            }
            if(isset($custom['search'])) {
                $args['meta_query'][] = array(
                    'key'     => 'boat_model',
                    'value'   => $custom['search'],
                    'compare' => 'LIKE'
                );
            }
        }

        $output = get_the_classys($args);

        echo $output;
        wp_die();
    }

}
