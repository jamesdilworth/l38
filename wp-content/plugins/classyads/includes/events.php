<?php
/**
 * Expires ads
 *
 * Find Classys that have expired (value in ad_expires meta field is lower then current timestamp) and
 * changes their status to 'expired'.
 *
 * @since 0.1
 * @return void
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
