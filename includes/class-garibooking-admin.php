<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Garibooking_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_garibooking_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_garibooking_add_route', array($this, 'add_route'));
        add_action('wp_ajax_garibooking_delete_route', array($this, 'delete_route'));
        add_action('wp_ajax_garibooking_assign_driver', array($this, 'assign_driver'));
        add_action('wp_ajax_garibooking_change_booking_status', array($this, 'change_booking_status'));
    }

    public function add_admin_menus() {
        add_menu_page(
            __('Garibooking', 'garibooking'),
            __('Garibooking', 'garibooking'),
            'manage_options',
            'garibooking-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-car',
            6
        );

        add_submenu_page(
            'garibooking-dashboard',
            __('Settings', 'garibooking'),
            __('Settings', 'garibooking'),
            'manage_options',
            'garibooking-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'garibooking-dashboard',
            __('Routes', 'garibooking'),
            __('Routes', 'garibooking'),
            'manage_options',
            'garibooking-routes',
            array($this, 'render_routes_page')
        );

        add_submenu_page(
            'garibooking-dashboard',
            __('Bookings', 'garibooking'),
            __('Bookings', 'garibooking'),
            'manage_options',
            'garibooking-bookings',
            array($this, 'render_bookings_page')
        );

        add_submenu_page(
            'garibooking-dashboard',
            __('Drivers', 'garibooking'),
            __('Drivers', 'garibooking'),
            'manage_options',
            'garibooking-drivers',
            array($this, 'render_drivers_page')
        );

        add_submenu_page(
            'garibooking-dashboard',
            __('Bids', 'garibooking'),
            __('Bids', 'garibooking'),
            'manage_options',
            'garibooking-bids',
            array($this, 'render_bids_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'garibooking') === false) {
            return;
        }
        wp_enqueue_style('garibooking-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css', array(), '1.0');
        wp_enqueue_script('garibooking-admin-script', plugin_dir_url(__FILE__) . 'js/admin-dashboard.js', array('jquery'), '1.0', true);
        wp_localize_script('garibooking-admin-script', 'garibooking_ajax_obj', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('garibooking_nonce'),
        ));
    }

    public function render_dashboard() {
        include plugin_dir_path(__FILE__) . 'views/admin-dashboard.php';
    }

    public function render_settings_page() {
        include plugin_dir_path(__FILE__) . 'views/admin-settings.php';
    }

    public function render_routes_page() {
        include plugin_dir_path(__FILE__) . 'views/admin-routes.php';
    }

    public function render_bookings_page() {
        include plugin_dir_path(__FILE__) . 'views/admin-bookings.php';
    }

    public function render_drivers_page() {
        include plugin_dir_path(__FILE__) . 'views/admin-drivers.php';
    }

    public function render_bids_page() {
        include plugin_dir_path(__FILE__) . 'views/admin-bids.php';
    }

    public function save_settings() {
        check_ajax_referer('garibooking_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }

        $settings = isset($_POST['settings']) ? $_POST['settings'] : array();

        if (!is_array($settings)) {
            wp_send_json_error('Invalid settings data');
        }

        update_option('garibooking_settings', $settings);

        wp_send_json_success('Settings saved.');
    }

    public function add_route() {
        check_ajax_referer('garibooking_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }

        $route_name = sanitize_text_field($_POST['route_name'] ?? '');
        $start_point = sanitize_text_field($_POST['start_point'] ?? '');
        $end_point = sanitize_text_field($_POST['end_point'] ?? '');

        if (empty($route_name) || empty($start_point) || empty($end_point)) {
            wp_send_json_error('All fields are required');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'garibooking_routes';

        $inserted = $wpdb->insert($table, array(
            'route_name' => $route_name,
            'start_point' => $start_point,
            'end_point' => $end_point,
            'created_at' => current_time('mysql'),
        ));

        if ($inserted) {
            wp_send_json_success('Route added.');
        } else {
            wp_send_json_error('Failed to add route.');
        }
    }

    public function delete_route() {
        check_ajax_referer('garibooking_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }

        $route_id = intval($_POST['route_id'] ?? 0);

        if (!$route_id) {
            wp_send_json_error('Invalid route ID');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'garibooking_routes';

        $deleted = $wpdb->delete($table, array('id' => $route_id));

        if ($deleted) {
            wp_send_json_success('Route deleted.');
        } else {
            wp_send_json_error('Failed to delete route.');
        }
    }

    public function assign_driver() {
        check_ajax_referer('garibooking_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }

        $booking_id = intval($_POST['booking_id'] ?? 0);
        $driver_id = intval($_POST['driver_id'] ?? 0);

        if (!$booking_id || !$driver_id) {
            wp_send_json_error('Invalid booking or driver ID');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'garibooking_bookings';

        $updated = $wpdb->update($table, array(
            'driver_id' => $driver_id,
            'status' => 'Assigned',
            'assigned_at' => current_time('mysql'),
        ), array('id' => $booking_id));

        if ($updated !== false) {
            wp_send_json_success('Driver assigned.');
        } else {
            wp_send_json_error('Failed to assign driver.');
        }
    }

    public function change_booking_status() {
        check_ajax_referer('garibooking_nonce', 'security');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized user');
        }

        $booking_id = intval($_POST['booking_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');

        $allowed_statuses = ['Pending', 'Assigned', 'Completed', 'Cancelled'];

        if (!$booking_id || !in_array($status, $allowed_statuses)) {
            wp_send_json_error('Invalid booking ID or status');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'garibooking_bookings';

        $updated = $wpdb->update($table, array(
            'status' => $status,
            'updated_at' => current_time('mysql'),
        ), array('id' => $booking_id));

        if ($updated !== false) {
            wp_send_json_success('Booking status updated.');
        } else {
            wp_send_json_error('Failed to update status.');
        }
    }
}
