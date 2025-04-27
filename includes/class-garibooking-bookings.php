<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Garibooking_Bookings {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'garibooking_bookings';

        // Hook to create table on plugin activation
        register_activation_hook( GARIBOOKING_PLUGIN_FILE, array( $this, 'create_booking_table' ) );

        // AJAX handlers
        add_action('wp_ajax_garibooking_create_booking', array($this, 'ajax_create_booking'));
        add_action('wp_ajax_nopriv_garibooking_create_booking', array($this, 'ajax_create_booking'));

        add_action('wp_ajax_garibooking_get_bookings', array($this, 'ajax_get_bookings'));
        add_action('wp_ajax_garibooking_update_booking_status', array($this, 'ajax_update_booking_status'));
    }

    /**
     * Create bookings table
     */
    public function create_booking_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            driver_id bigint(20) unsigned DEFAULT NULL,
            route_id bigint(20) unsigned NOT NULL,
            booking_date datetime NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'pending',
            bid_amount decimal(10,2) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY driver_id (driver_id),
            KEY route_id (route_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Create a booking entry
     */
    public function create_booking( $data ) {
        global $wpdb;

        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'user_id' => intval($data['user_id']),
                'driver_id' => isset($data['driver_id']) ? intval($data['driver_id']) : null,
                'route_id' => intval($data['route_id']),
                'booking_date' => sanitize_text_field($data['booking_date']),
                'status' => sanitize_text_field($data['status']),
                'bid_amount' => isset($data['bid_amount']) ? floatval($data['bid_amount']) : null,
                'created_at' => current_time('mysql'),
            ),
            array(
                '%d','%d','%d','%s','%s','%f','%s'
            )
        );

        if ( $inserted ) {
            return $wpdb->insert_id;
        }
        return false;
    }

    /**
     * Get bookings list with optional filters
     */
    public function get_bookings( $args = array() ) {
        global $wpdb;

        $defaults = array(
            'user_id' => 0,
            'driver_id' => 0,
            'status' => '',
            'limit' => 20,
            'offset' => 0,
        );
        $args = wp_parse_args($args, $defaults);

        $where = " WHERE 1=1 ";
        $params = array();

        if ( $args['user_id'] ) {
            $where .= $wpdb->prepare(" AND user_id = %d ", $args['user_id']);
        }
        if ( $args['driver_id'] ) {
            $where .= $wpdb->prepare(" AND driver_id = %d ", $args['driver_id']);
        }
        if ( $args['status'] ) {
            $where .= $wpdb->prepare(" AND status = %s ", $args['status']);
        }

        $limit = intval($args['limit']);
        $offset = intval($args['offset']);

        $sql = "SELECT * FROM {$this->table_name} {$where} ORDER BY booking_date DESC LIMIT %d OFFSET %d";
        $prepared_sql = $wpdb->prepare( $sql, $limit, $offset );

        $results = $wpdb->get_results( $prepared_sql );

        return $results;
    }

    /**
     * Update booking status
     */
    public function update_booking_status( $booking_id, $status ) {
        global $wpdb;

        $updated = $wpdb->update(
            $this->table_name,
            array(
                'status' => sanitize_text_field($status),
                'updated_at' => current_time('mysql'),
            ),
            array('id' => intval($booking_id)),
            array('%s','%s'),
            array('%d')
        );

        return $updated !== false;
    }

    /**
     * AJAX handler to create booking
     */
    public function ajax_create_booking() {
        check_ajax_referer('garibooking_nonce', 'nonce');

        $data = $_POST;

        // Basic validation
        if ( empty($data['user_id']) || empty($data['route_id']) || empty($data['booking_date']) ) {
            wp_send_json_error( array('message' => 'Missing required fields') );
        }

        $data['status'] = 'pending';

        $booking_id = $this->create_booking($data);

        if ( $booking_id ) {
            wp_send_json_success( array('booking_id' => $booking_id) );
        } else {
            wp_send_json_error( array('message' => 'Failed to create booking') );
        }
    }

    /**
     * AJAX handler to get bookings
     */
    public function ajax_get_bookings() {
        check_ajax_referer('garibooking_nonce', 'nonce');

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $driver_id = isset($_POST['driver_id']) ? intval($_POST['driver_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        $bookings = $this->get_bookings( array(
            'user_id' => $user_id,
            'driver_id' => $driver_id,
            'status' => $status,
            'limit' => 50,
            'offset' => 0,
        ));

        wp_send_json_success( $bookings );
    }

    /**
     * AJAX handler to update booking status
     */
    public function ajax_update_booking_status() {
        check_ajax_referer('garibooking_nonce', 'nonce');

        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        if ( ! $booking_id || ! in_array($status, array('pending', 'confirmed', 'cancelled', 'completed')) ) {
            wp_send_json_error( array('message' => 'Invalid booking ID or status') );
        }

        $updated = $this->update_booking_status( $booking_id, $status );

        if ( $updated ) {
            wp_send_json_success( array('message' => 'Booking status updated') );
        } else {
            wp_send_json_error( array('message' => 'Failed to update booking status') );
        }
    }
}
    