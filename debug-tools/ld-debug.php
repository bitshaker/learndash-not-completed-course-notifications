<?php
/**
 * LearnDash Notifications - Standalone Debug Tool for 'Not Logged In' Trigger.
 *
 * To use, upload this file to your WordPress root directory and visit:
 * your-site.com/ld-debug.php
 *
 * IMPORTANT: Delete this file after use.
 *
 * @version 1.4.0
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

$notifications = learndash_notifications_get_notifications( 'not_logged_in' );
$pending_notifications = [];

foreach ( $notifications as $n ) {
	$n_days = get_post_meta( $n->ID, '_ld_notifications_not_logged_in_days', true );

	if ( ! ( $n_days > 0 ) ) {
		continue;
	}

	$course_id  = get_post_meta( $n->ID, '_ld_notifications_course_id', true );
	$recipients = learndash_notifications_get_recipients( $n->ID );

	$all_users = get_users();
	$users_to_notify = [];

	foreach ( $all_users as $user ) {
		$user_roles = $user->roles;
		$is_recipient = false;

		if ( in_array( 'user', $recipients ) ) {
			if ( ! in_array( 'administrator', $user_roles ) && ! in_array( 'group_leader', $user_roles ) ) {
				$is_recipient = true;
			}
		}

		if ( in_array( 'group_leader', $recipients ) && in_array( 'group_leader', $user_roles ) ) {
			$is_recipient = true;
		}

		if ( in_array( 'admin', $recipients ) && in_array( 'administrator', $user_roles ) ) {
			$is_recipient = true;
		}

		if ( $is_recipient ) {
			$users_to_notify[] = $user;
		}
	}

	foreach ( $users_to_notify as $u ) {
		$last_login = (int) get_user_meta( $u->ID, '_ld_notifications_last_login', true );

		if ( empty( $last_login ) ) {
			continue;
		}

		$courses = ld_get_mycourses( $u->ID );
		$scheduled_time = strtotime( '+' . $n_days . ' days', $last_login );

		if ( time() >= $scheduled_time ) {
			if ( ! empty( $course_id ) && $course_id > 0 ) {
				if ( in_array( $course_id, $courses ) ) {
					$pending_notifications[] = [
						'notification' => $n->post_title,
						'user'         => $u->user_login,
						'course'       => get_the_title( $course_id ),
						'send_time'    => date( 'Y-m-d H:i:s', $scheduled_time ),
					];
				}
			} else {
				foreach ( $courses as $c_id ) {
					$pending_notifications[] = [
						'notification' => $n->post_title,
						'user'         => $u->user_login,
						'course'       => get_the_title( $c_id ),
						'send_time'    => date( 'Y-m-d H:i:s', $scheduled_time ),
					];
				}
			}
		}
	}
}

header( 'Content-Type: text/plain' );
echo "LearnDash Notifications - Debug Output\n";
echo "======================================\n\n";
if ( empty( $pending_notifications ) ) {
	echo "No pending 'not logged in' notifications found.";
} else {
	print_r( $pending_notifications );
}