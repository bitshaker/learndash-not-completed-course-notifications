<?php
/**
 * LearnDash Notifications - Standalone Check Tool.
 *
 * To use, upload this file to your WordPress root directory and visit:
 * your-site.com/ld-check.php?user_id=X&notification_id=Y
 *
 * IMPORTANT: Delete this file after use.
 *
 * @version 1.1.0
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

// Get user and notification IDs from the URL.
$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
$notification_id = isset( $_GET['notification_id'] ) ? absint( $_GET['notification_id'] ) : 0;

if ( ! $user_id || ! $notification_id ) {
	wp_die( 'Please provide a user_id and a notification_id in the URL. Example: ld-check.php?user_id=1&notification_id=123' );
}

// Get the user and notification objects.
$user = get_user_by( 'ID', $user_id );
$notification = get_post( $notification_id );

if ( ! $user || ! $notification || 'ld-notification' !== $notification->post_type ) {
	wp_die( 'Invalid user_id or notification_id.' );
}

header( 'Content-Type: text/plain' );
echo "LearnDash Notifications - Check Tool\n";
echo "====================================\n\n";

// --- Notification Details ---
echo "--- Notification Details ---\n";
echo "ID: " . $notification->ID . "\n";
echo "Title: " . $notification->post_title . "\n";
echo "Status: " . $notification->post_status . "\n";

$trigger = get_post_meta( $notification->ID, '_ld_notifications_trigger', true );
echo "Trigger: " . $trigger . "\n";

if ( 'not_logged_in' === $trigger ) {
	$n_days = get_post_meta( $notification->ID, '_ld_notifications_not_logged_in_days', true );
	echo "Days not logged in: " . $n_days . "\n";
}

$notification_course_id = get_post_meta( $notification->ID, '_ld_notifications_course_id', true );
if ( is_array( $notification_course_id ) ) {
	echo "Course attached to notification: \n";
	foreach ( $notification_course_id as $c_id ) {
		echo "  - " . get_the_title( $c_id ) . " (" . $c_id . ")\n";
	}
} elseif ( ! empty( $notification_course_id ) ) {
	echo "Course attached to notification: " . get_the_title( $notification_course_id ) . " (" . $notification_course_id . ")\n";
} else {
    echo "Course attached to notification: All Courses\n";
}

echo "\n--- User Details ---\n";
echo "ID: " . $user->ID . "\n";
echo "Username: " . $user->user_login . "\n";

$last_login = (int) get_user_meta( $user->ID, '_ld_notifications_last_login', true );
if ( $last_login ) {
	echo "Last login: " . date( 'Y-m-d H:i:s', $last_login ) . "\n";
} else {
	echo "Last login: Not recorded.\n";
}

$enrolled_courses = ld_get_mycourses( $user->ID );
if ( $enrolled_courses ) {
	echo "Enrolled courses: \n";
	foreach ( $enrolled_courses as $c_id ) {
		echo "  - " . get_the_title( $c_id ) . " (" . $c_id . ")\n";
	}
} else {
	echo "Enrolled courses: None.\n";
}

echo "\n--- Condition Evaluation ---\n";

// 1. "Not logged in" trigger evaluation
if ( 'not_logged_in' === $trigger ) {
	echo "1. 'Not Logged In' Trigger:\n";
	if ( ! $last_login ) {
		echo "  - RESULT: FAIL (User has no recorded last login time).\n";
	} else {
		$scheduled_time = strtotime( '+' . $n_days . ' days', $last_login );
		echo "  - Last Login: " . date( 'Y-m-d H:i:s', $last_login ) . "\n";
		echo "  - Days Setting: " . $n_days . "\n";
		echo "  - Scheduled Time: " . date( 'Y-m-d H:i:s', $scheduled_time ) . "\n";
		echo "  - Current Time: " . date( 'Y-m-d H:i:s', time() ) . "\n";
		if ( time() >= $scheduled_time ) {
			echo "  - RESULT: PASS (Current time is after scheduled time).\n";
		} else {
			echo "  - RESULT: FAIL (Current time is before scheduled time).\n";
		}
	}
}

// 2. Additional Conditions
$conditions = get_post_meta( $notification->ID, '_ld_notifications_conditions', true );
if ( ! empty( $conditions ) ) {
		echo "\n2. Additional Conditions:\n";
    echo "Raw conditions data:\n";
    print_r($conditions);
    echo "\n";

		foreach ( $conditions as $condition ) {
			if ( isset( $condition['condition_type'] ) ) {
				echo "  - Condition: " . $condition['condition_type'] . "\n";
				if ( 'not_complete_course' === $condition['condition_type'] ) {
					$course_id_to_check = isset( $condition['course_id'][0] ) ? absint( $condition['course_id'][0] ) : 0;
					if ( $course_id_to_check ) {
						echo "    - Course to check: " . get_the_title( $course_id_to_check ) . " (" . $course_id_to_check . ")\n";
						if ( learndash_course_completed( $user->ID, $course_id_to_check ) ) {
							echo "    - RESULT: FAIL (User HAS completed this course).\n";
						} else {
							echo "    - RESULT: PASS (User has NOT completed this course).\n";
						}
					} else {
						echo "    - RESULT: FAIL (No course selected for this condition).\n";
					}
				}
				// Add other condition checks here if needed
			}
		}
	} else {
    echo "\n2. Additional Conditions: None found.\n";
}

echo "\n--- Final Conclusion---\n";
echo "Based on the evaluation above, you can determine why the notification is not being sent.\n";
echo "Make sure all conditions (including the main trigger) pass for the notification to be sent.\n";