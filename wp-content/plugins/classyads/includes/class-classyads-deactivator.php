<?php

/**
 * Fired during plugin deactivation
 *
 * @link       www.jamesdilworth.com
 * @since      0.1.0
 *
 * @package    Classyads
 * @subpackage Classyads/includes
 */

class Classyads_Deactivator {

	public static function deactivate() {

	    // deactivate scheduled events.
        $timestamp = wp_next_scheduled( 'classy_cleanup_hook' );
        wp_unschedule_event( $timestamp, 'classy_cleanup_hook' );
	}

}
