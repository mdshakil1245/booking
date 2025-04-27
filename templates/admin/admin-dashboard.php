<?php
// admin-dashboard.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! current_user_can( 'manage_options' ) ) {
    echo '<p>You do not have permission to access this page.</p>';
    return;
}

// Handle any POST actions (assign driver, update status, add/delete routes) - বা AJAX এ হ্যান্ডেল করতে পারো

// Get all bookings
$bookings = Garibooking_Bookings::get_all_bookings();

// Get all drivers
$drivers = Garibooking_Admin::get_all_drivers();

// Get all routes
$routes = Garibooking_Routes::get_all_routes();

?>

<div class="garibooking-admin-dashboard">
    <h1>Admin Dashboard</h1>

    <section class="garibooking-bookings">
        <h2>Bookings</h2>
        <?php if ( empty( $bookings ) ) : ?>
            <p>No bookings found.</p>
        <?php else : ?>
            <table class="garibooking-bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Date</th>
                        <th>Passengers</th>
                        <th>Driver</th>
                        <th>Status</th>
                        <th>Assign Driver</th>
                        <th>Change Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $bookings as $booking ) : ?>
                        <tr>
                            <td><?php echo esc_html( $booking->id ); ?></td>
                            <td><?php echo esc_html( $booking->user_name ); ?></td>
                            <td><?php echo esc_html( $booking->pickup_location_name ); ?></td>
                            <td><?php echo esc_html( $booking->drop_location_name ); ?></td>
                            <td><?php echo esc_html( date( 'd M Y', strtotime( $booking->travel_date ) ) ); ?></td>
                            <td><?php echo esc_html( $booking->passengers ); ?></td>
                            <td><?php echo esc_html( $booking->driver_name ?: 'Not Assigned' ); ?></td>
                            <td><?php echo esc_html( ucfirst( $booking->status ) ); ?></td>
                            <td>
                                <select class="garibooking-assign-driver" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
                                    <option value="">Select Driver</option>
                                    <?php foreach ( $drivers as $driver ) : ?>
                                        <option value="<?php echo esc_attr( $driver->ID ); ?>" <?php selected( $booking->driver_id, $driver->ID ); ?>>
                                            <?php echo esc_html( $driver->display_name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select class="garibooking-change-status" data-booking-id="<?php echo esc_attr( $booking->id ); ?>">
                                    <option value="pending" <?php selected( $booking->status, 'pending' ); ?>>Pending</option>
                                    <option value="in_progress" <?php selected( $booking->status, 'in_progress' ); ?>>In Progress</option>
                                    <option value="completed" <?php selected( $booking->status, 'completed' ); ?>>Completed</option>
                                    <option value="cancelled" <?php selected( $booking->status, 'cancelled' ); ?>>Cancelled</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="garibooking-routes">
        <h2>Routes Management</h2>
        <button id="garibooking-add-route-btn">Add New Route</button>
        <table class="garibooking-routes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $routes as $route ) : ?>
                    <tr>
                        <td><?php echo esc_html( $route->id ); ?></td>
                        <td><?php echo esc_html( $route->start_location ); ?></td>
                        <td><?php echo esc_html( $route->end_location ); ?></td>
                        <td>
                            <button class="garibooking-delete-route" data-route-id="<?php echo esc_attr( $route->id ); ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <!-- Add modal or hidden form for adding routes (handled by JS) -->

</div>
