<?php
/**
 * LearnDash Notifications - Add-on for 'Not Completed Course' Condition.
 *
 * @version 1.0.0
 * @author  Your Name
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add the 'Not Completed Course' condition to the list of available conditions.
 *
 * @param array $conditions Existing conditions.
 * @return array Modified conditions.
 */
function add_not_completed_course_condition( $conditions ) {
	$conditions['not_complete_course'] = __( 'User has NOT completed a course', 'learndash-notifications' );
	return $conditions;
}
add_filter( 'learndash_notifications_conditions', 'add_not_completed_course_condition' );

/**
 * Process the 'Not Completed Course' condition before sending the notification.
 *
 * @param bool  $send           Whether to send the notification.
 * @param array $shortcode_data Data associated with the notification.
 * @return bool Whether to send the notification.
 */
function process_not_completed_course_condition( $send, $shortcode_data ) {
	// Get the notification ID from the shortcode data.
	$notification_id = isset( $shortcode_data['notification_id'] ) ? $shortcode_data['notification_id'] : 0;

	if ( empty( $notification_id ) ) {
		return $send;
	}

	// Get the conditions for this notification.
	$conditions = get_post_meta( $notification_id, '_ld_notifications_conditions', true );

	if ( ! empty( $conditions ) ) {
		foreach ( $conditions as $condition ) {
			// Check for the correct condition type
			if ( isset( $condition['condition_type'] ) && 'not_complete_course' === $condition['condition_type'] ) {
				// The course ID is in an array
				$course_id = isset( $condition['course_id'][0] ) ? absint( $condition['course_id'][0] ) : 0;
				$user_id   = isset( $shortcode_data['user_id'] ) ? absint( $shortcode_data['user_id'] ) : 0;

				if ( ! empty( $course_id ) && ! empty( $user_id ) ) {
					// If the user has completed the course, don't send the notification.
					if ( learndash_course_completed( $user_id, $course_id ) ) {
						return false;
					}
				}
			}
		}
	}

	return $send;
}
add_filter( 'learndash_notifications_send_notification', 'process_not_completed_course_condition', 10, 2 );

/**
 * Add 'course_id' to the object fields for the 'not_complete_course' condition.
 *
 * @param array $object_fields Existing object fields.
 * @return array Modified object fields.
 */
function add_course_id_to_not_completed_course_object_fields( $object_fields ) {
	if ( isset( $object_fields['course_id']['parent'] ) ) {
		$object_fields['course_id']['parent'][] = 'not_complete_course';
	}
	return $object_fields;
}
add_filter( 'learndash_notifications_object_fields', 'add_course_id_to_not_completed_course_object_fields' );
