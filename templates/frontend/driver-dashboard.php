<?php
// Driver Dashboard - Show assigned bookings

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$current_user_id = get_current_user_id();

if ( ! $current_user_id ) {
    echo '<p>Please log in to view your dashboard.</p>';
    return;
}

// Check if current user is driver - এখানে তোমার রোল চেক করতে পারো
if ( ! current_user_can( 'driver_role' ) ) {
    echo '<p>You do not have permission to access this page.</p>';
    return;
}

// Fetch bookings assigned to this driver
$assigned_bookings = Garibooking_Bookings::get_bookings_by_driver( $current_user_id );

?>

<div class="garibooking-driver-dashboard">
    <h2>My Assigned Bookings</h2>

    <?php if ( empty( $assigned_bookings ) ) : ?>
        <p>No bookings assigned to you yet.</p>
    <?php else : ?>
        <table class="garibooking-bookings-table">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Travel Date</th>
                    <th>Passengers</th>
                    <th>Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $assigned_bookings as $booking ) : ?>
                    <tr>
                        <td><?php echo esc_html( $booking->id ); ?></td>
                        <td><?php echo esc_html( $booking->pickup_location_name ); ?></td>
                        <td><?php echo esc_html( $booking->drop_location_name ); ?></td>
                        <td><?php echo esc_html( date( 'd M Y', strtotime( $booking->travel_date ) ) ); ?></td>
                        <td><?php echo esc_html( $booking->passengers ); ?></td>
                        <td><?php echo esc_html( ucfirst( $booking->status ) ); ?></td>
                        <td>
                            <form method="post" action="">
                                <?php wp_nonce_field( 'garibooking_update_status_' . $booking->id ); ?>
                                <input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?php selected( $booking->status, 'pending' ); ?>>Pending</option>
                                    <option value="in_progress" <?php selected( $booking->status, 'in_progress' ); ?>>In Progress</option>
                                    <option value="completed" <?php selected( $booking->status, 'completed' ); ?>>Completed</option>
                                    <option value="cancelled" <?php selected( $booking->status, 'cancelled' ); ?>>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
// Handle booking status update by driver
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['booking_id'], $_POST['status'] ) ) {
    $booking_id = intval( $_POST['booking_id'] );
    $new_status = sanitize_text_field( $_POST['status'] );

    if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'garibooking_update_status_' . $booking_id ) ) {
        wp_die( 'Security check failed' );
    }

    // Check if this booking is assigned to this driver
    $booking = Garibooking_Bookings::get_booking_by_id( $booking_id );
    if ( $booking && $booking->driver_id == $current_user_id ) {
        Garibooking_Bookings::update_booking_status( $booking_id, $new_status );
        echo '<p>Status updated successfully.</p>';
        // Redirect to avoid resubmission
        wp_redirect( $_SERVER['REQUEST_URI'] );
        exit;
    } else {
        echo '<p>You are not authorized to update this booking.</p>';
    }
}
?>
