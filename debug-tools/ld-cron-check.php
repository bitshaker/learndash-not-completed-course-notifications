<?php
/**
 * LearnDash Notifications - Standalone Cron Check Tool.
 *
 * To use, upload this file to your WordPress root directory and visit:
 * your-site.com/ld-cron-check.php
 *
 * IMPORTANT: Delete this file after use.
 *
 * @version 1.0.0
 * @author  Gemini
 */

// Load the WordPress environment.
if ( file_exists( 'wp-load.php' ) ) {
	require_once( 'wp-load.php' );
} else {
	die( 'WordPress environment not found. Please make sure this file is in your WordPress root directory.' );
}

// Check for user capabilities.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'You do not have permission to access this page.' );
}

header( 'Content-Type: text/plain' );
echo "LearnDash Notifications - Cron Check Tool\n";
echo "=======================================\n\n";

$timestamp = wp_next_scheduled( 'learndash_notifications_cron' );

if ( $timestamp ) {
	$gmt_offset = get_option( 'gmt_offset' );
	$local_time = $timestamp + ( $gmt_offset * HOUR_IN_SECONDS );
	echo "The next hourly cron event is scheduled to run at:\n";
	echo date( 'Y-m-d H:i:s', $local_time ) . " (Your site's local time)\n";
} else {
	echo "The hourly cron event is not currently scheduled. It may have just run or there might be an issue with the cron system.";
}

