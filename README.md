# LearnDash Notification & Debugging Tools

A collection of standalone tools for debugging LearnDash Notifications and adding custom notification triggers.

Why? Because there need to be more useful triggers. This is a decent template to get you going.

## Included Files

*   `ld-debug.php`: Simulates the cron job for the 'Not Logged In' trigger. It provides a snapshot of which users are currently eligible to receive a 'Not Logged In' notification.
*   `ld-check.php`: A detailed diagnostic tool to check why a specific notification may not have been sent to a specific user. It requires a `user_id` and `notification_id` to run.
*   `ld-cron-check.php`: A simple script to verify that the main LearnDash Notifications hourly cron event is properly scheduled in the WordPress cron system.
*   `ld-notification-not-completed-course.php`: A custom snippet to trigger notifications for users who have not completed a course after a specific time.

## Installation

You can use these tools in one of the following ways.

### 1. Code Snippets Plugin (Recommended)

1.  Install and activate the [Code Snippets](https://wordpress.org/plugins/code-snippets/) plugin.
2.  Go to **Snippets > Add New** in your WordPress dashboard.
3.  Open the content of the desired `.php` file from this repository.
4.  Copy the entire content and paste it into the snippet editor.
5.  Give the snippet a title and save and activate it.

### 2. Must-Use Plugin

1.  Connect to your server via FTP or your hosting control panel's File Manager.
2.  Navigate to the `/wp-content/` directory.
3.  If it doesn't already exist, create a folder named `mu-plugins`.
4.  Upload the desired `.php` files directly into the `/wp-content/mu-plugins/` directory.

Files in this directory are automatically activated on your site.

## Usage Instructions

### Debug and Check Tools

**IMPORTANT:** These are standalone tools and should NOT be installed as plugins or snippets. They are designed for temporary use and should be deleted from your server immediately after you are finished debugging.

To use any of these tools:
1.  Upload the specific `.php` file to the **root directory** of your WordPress installation (the same folder that contains `wp-config.php`).
2.  Log in to your WordPress admin dashboard as an administrator.
3.  In a new browser tab, navigate to the URL specified for the tool below.
4.  **Delete the file from your server when you are done.**

---

#### `ld-cron-check.php`

*   **Purpose:** Verifies that the main LearnDash Notifications hourly cron event is properly scheduled.
*   **URL:** `https://your-site.com/ld-cron-check.php`
*   **Output:** Shows the exact date and time the next cron job is scheduled to run in your site's local time.

---

#### `ld-debug.php`

*   **Purpose:** Simulates the "Not Logged In" trigger and provides a snapshot of which users are currently eligible to receive a notification.
*   **URL:** `https://your-site.com/ld-debug.php`
*   **Output:** A plain-text list of pending notifications and the users who would receive them if the cron were to run at that moment. If the list is empty, no notifications of this type are currently due.

---

#### `ld-check.php`

*   **Purpose:** Performs a detailed, step-by-step diagnosis to see why a specific notification was or was not sent to a specific user.
*   **URL:** `https://your-site.com/ld-check.php?user_id=X&notification_id=Y`
    *   Replace `X` with the ID of the user you want to check.
    *   Replace `Y` with the ID of the `ld-notification` post you want to check.
*   **Output:** A step-by-step evaluation showing whether the user passed or failed the conditions for that specific notification.

### Custom Notification: Not Completed Course

**Prerequisite:** You must have the official **LearnDash Notifications** plugin installed and activated to use this snippet.

This snippet adds a powerful new condition to the LearnDash Notifications editor.

**What it does:**
It adds a new condition type called **"User has NOT completed a course"**. This allows you to create notifications that are only sent if a user has not finished a specific course.

**How to use it:**
1.  Install the `ld-notification-not-completed-course.php` file using one of the methods described in the **Installation** section (the Code Snippets or MU-Plugins method is recommended).
2.  In your WordPress dashboard, go to **LearnDash LMS > Notifications**.
3.  Create a new notification or edit an existing one.
4.  Select any trigger (e.g., "After course enrollment", "User has not logged in for a number of days", etc.).
5.  Scroll down to the **Conditions** section and click **Add Condition**.
6.  In the dropdown menu for the condition type, you will now find a new option: **User has NOT completed a course**.
7.  Select this option and then choose the course you wish to check against from the course selector that appears.

**Example Use Case:**
You can create a reminder email that sends 7 days after a user hasn't logged into "Beginner's Course", but only if they have NOT completed the "Beginner Course" course yet.
