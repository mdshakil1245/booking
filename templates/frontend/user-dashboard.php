<?php
// User Dashboard - Show user's bookings

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$current_user_id = get_current_user_id();

if ( ! $current_user_id ) {
    echo '<p>Please log in to view your dashboard.</p>';
    return;
}

// Fetch bookings of the logged-in user
$bookings = Garibooking_Bookings::get_bookings_by_user( $current_user_id );

?>

<div class="garibooking-user-dashboard">
    <h2>My Bookings</h2>

    <?php if ( empty( $bookings ) ) : ?>
        <p>You have no bookings yet.</p>
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $bookings as $booking ) : ?>
                    <tr>
                        <td><?php echo esc_html( $booking->id ); ?></td>
                        <td><?php echo esc_html( $booking->pickup_location_name ); ?></td>
                        <td><?php echo esc_html( $booking->drop_location_name ); ?></td>
                        <td><?php echo esc_html( date( 'd M Y', strtotime( $booking->travel_date ) ) ); ?></td>
                        <td><?php echo esc_html( $booking->passengers ); ?></td>
                        <td><?php echo esc_html( ucfirst( $booking->status ) ); ?></td>
                        <td>
                            <?php if ( $booking->status === 'pending' ) : ?>
                                <a href="<?php echo esc_url( add_query_arg( array( 'cancel_booking' => $booking->id ) ) ); ?>" onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel</a>
                            <?php else : ?>
                                <span>-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
// Handle booking cancellation (simple example)
if ( isset( $_GET['cancel_booking'] ) && is_numeric( $_GET['cancel_booking'] ) ) {
    $booking_id = intval( $_GET['cancel_booking'] );
    
    // Verify user owns this booking
    $booking = Garibooking_Bookings::get_booking_by_id( $booking_id );
    if ( $booking && $booking->user_id == $current_user_id && $booking->status === 'pending' ) {
        Garibooking_Bookings::update_booking_status( $booking_id, 'cancelled' );
        echo '<p>Your booking has been cancelled successfully.</p>';
        // Optionally redirect to avoid resubmission
        wp_redirect( remove_query_arg( 'cancel_booking' ) );
        exit;
    } else {
        echo '<p>Unable to cancel this booking.</p>';
    }
}
?>
