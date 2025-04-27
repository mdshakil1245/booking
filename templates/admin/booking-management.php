<?php
// booking-management.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Direct access not allowed
}

if ( ! current_user_can( 'manage_options' ) ) {
    echo '<p>You do not have permission to access this page.</p>';
    return;
}

require_once 'class-garibooking-bookings.php'; // বুকিং ক্লাস ফাইল ইমপোর্ট

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete booking
    if ( isset($_POST['delete_booking']) && !empty($_POST['booking_id']) ) {
        $booking_id = intval($_POST['booking_id']);
        $deleted = Garibooking_Bookings::delete_booking($booking_id);

        if ($deleted) {
            echo '<div class="notice notice-success"><p>Booking deleted successfully.</p></div>';
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="notice notice-error"><p>Failed to delete booking.</p></div>';
        }
    }

    // Update booking status
    if ( isset($_POST['update_status']) && !empty($_POST['booking_id']) && isset($_POST['status']) ) {
        $booking_id = intval($_POST['booking_id']);
        $status = sanitize_text_field($_POST['status']);
        $updated = Garibooking_Bookings::update_booking_status($booking_id, $status);

        if ($updated) {
            echo '<div class="notice notice-success"><p>Booking status updated.</p></div>';
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="notice notice-error"><p>Failed to update booking status.</p></div>';
        }
    }

    // Assign driver to booking
    if ( isset($_POST['assign_driver']) && !empty($_POST['booking_id']) && !empty($_POST['driver_id']) ) {
        $booking_id = intval($_POST['booking_id']);
        $driver_id = intval($_POST['driver_id']);
        $assigned = Garibooking_Bookings::assign_driver($booking_id, $driver_id);

        if ($assigned) {
            echo '<div class="notice notice-success"><p>Driver assigned successfully.</p></div>';
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="notice notice-error"><p>Failed to assign driver.</p></div>';
        }
    }
}

// Fetch all bookings
$bookings = Garibooking_Bookings::get_all_bookings();

// Fetch all drivers for assignment dropdown
require_once 'class-garibooking-drivers.php';
$drivers = Garibooking_Drivers::get_all_drivers();

?>

<div class="garibooking-booking-management">
    <h1>Manage Bookings</h1>

    <?php if (empty($bookings)) : ?>
        <p>No bookings found.</p>
    <?php else : ?>
        <table class="garibooking-bookings-table" border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Status</th>
                    <th>Driver</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking) : ?>
                    <tr>
                        <td><?php echo esc_html($booking->id); ?></td>
                        <td><?php echo esc_html($booking->user_name); ?></td>
                        <td><?php echo esc_html($booking->route_from); ?></td>
                        <td><?php echo esc_html($booking->route_to); ?></td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="booking_id" value="<?php echo esc_attr($booking->id); ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?php selected($booking->status, 'pending'); ?>>Pending</option>
                                    <option value="confirmed" <?php selected($booking->status, 'confirmed'); ?>>Confirmed</option>
                                    <option value="completed" <?php selected($booking->status, 'completed'); ?>>Completed</option>
                                    <option value="cancelled" <?php selected($booking->status, 'cancelled'); ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="booking_id" value="<?php echo esc_attr($booking->id); ?>">
                                <select name="driver_id" onchange="this.form.submit()">
                                    <option value="">Select Driver</option>
                                    <?php foreach ($drivers as $driver) : ?>
                                        <option value="<?php echo esc_attr($driver->id); ?>" <?php selected($booking->driver_id, $driver->id); ?>>
                                            <?php echo esc_html($driver->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="assign_driver" value="1">
                            </form>
                        </td>
                        <td>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this booking?');" style="display:inline-block;">
                                <input type="hidden" name="booking_id" value="<?php echo esc_attr($booking->id); ?>">
                                <button type="submit" name="delete_booking">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
