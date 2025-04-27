<?php
/**
 * Garibooking Notifications Class
 * Handles notification creation, sending, and retrieval
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Garibooking_Notifications {

    public function __construct() {
        // Ajax hooks for sending and fetching notifications
        add_action( 'wp_ajax_garibooking_send_notification', array( $this, 'ajax_send_notification' ) );
        add_action( 'wp_ajax_nopriv_garibooking_send_notification', array( $this, 'ajax_send_notification' ) );

        add_action( 'wp_ajax_garibooking_get_notifications', array( $this, 'ajax_get_notifications' ) );
        add_action( 'wp_ajax_nopriv_garibooking_get_notifications', array( $this, 'ajax_get_notifications' ) );
    }

    /**
     * Create and store a notification
     * @param int $user_id - Recipient user ID
     * @param string $message - Notification message
     * @param string $type - Notification type (e.g. 'booking', 'bid', 'system')
     * @param array $data - Additional data related to notification
     * @return int|false - Notification ID or false on failure
     */
    public function create_notification( $user_id, $message, $type = 'general', $data = array() ) {
        if ( empty( $user_id ) || empty( $message ) ) {
            return false;
        }

        $notification = array(
            'post_title'   => wp_trim_words( $message, 10, '...' ),
            'post_content' => $message,
            'post_type'    => 'garibooking_notification',
            'post_status'  => 'publish',
            'post_author'  => $user_id,
        );

        $post_id = wp_insert_post( $notification );

        if ( is_wp_error( $post_id ) ) {
            return false;
        }

        // Save meta data
        update_post_meta( $post_id, '_notification_type', sanitize_text_field( $type ) );
        update_post_meta( $post_id, '_notification_data', $data );
        update_post_meta( $post_id, '_notification_read', 0 ); // 0 = unread, 1 = read

        return $post_id;
    }

    /**
     * Mark a notification as read
     * @param int $notification_id
     * @return bool
     */
    public function mark_as_read( $notification_id ) {
        if ( empty( $notification_id ) ) {
            return false;
        }

        return update_post_meta( $notification_id, '_notification_read', 1 );
    }

    /**
     * Get notifications for a user
     * @param int $user_id
     * @param int $limit
     * @param bool $only_unread
     * @return array
     */
    public function get_notifications( $user_id, $limit = 10, $only_unread = false ) {
        if ( empty( $user_id ) ) {
            return array();
        }

        $meta_query = array(
            array(
                'key'     => '_notification_read',
                'value'   => $only_unread ? 0 : array(0,1),
                'compare' => $only_unread ? '=' : 'IN',
                'type'    => 'NUMERIC',
            ),
        );

        $args = array(
            'post_type'      => 'garibooking_notification',
            'post_status'    => 'publish',
            'author'         => $user_id,
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => $meta_query,
        );

        $query = new WP_Query( $args );

        $notifications = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $notifications[] = array(
                    'id'          => get_the_ID(),
                    'message'     => get_the_content(),
                    'type'        => get_post_meta( get_the_ID(), '_notification_type', true ),
                    'data'        => get_post_meta( get_the_ID(), '_notification_data', true ),
                    'read'        => get_post_meta( get_the_ID(), '_notification_read', true ),
                    'date'        => get_the_date(),
                );
            }
            wp_reset_postdata();
        }

        return $notifications;
    }

    /**
     * Ajax handler to send a notification
     */
    public function ajax_send_notification() {
        // Check nonce, permissions etc here if implemented

        $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
        $message = isset( $_POST['message'] ) ? sanitize_text_field( $_POST['message'] ) : '';
        $type    = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'general';

        if ( empty( $user_id ) || empty( $message ) ) {
            wp_send_json_error( 'Missing parameters' );
        }

        $result = $this->create_notification( $user_id, $message, $type );

        if ( $result ) {
            wp_send_json_success( 'Notification sent' );
        } else {
            wp_send_json_error( 'Failed to send notification' );
        }
    }

    /**
     * Ajax handler to get notifications for current user
     */
    public function ajax_get_notifications() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'User not logged in' );
        }

        $user_id = get_current_user_id();
        $only_unread = isset( $_GET['only_unread'] ) && $_GET['only_unread'] == '1' ? true : false;

        $notifications = $this->get_notifications( $user_id, 20, $only_unread );

        wp_send_json_success( $notifications );
    }
}

new Garibooking_Notifications();
