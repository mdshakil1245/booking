<?php
// Booking form - front end template part

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>

<form id="garibooking-booking-form" method="post" action="">
    <?php wp_nonce_field( 'garibooking_booking_action', 'garibooking_booking_nonce' ); ?>

    <div class="garibooking-form-group">
        <label for="pickup_route">From:</label>
        <select name="pickup_route" id="pickup_route" required>
            <option value="">Select Pickup Location</option>
            <?php
            // Fetch all pickup locations from DB (you'll implement get_all_pickup_routes())
            $pickup_routes = Garibooking_Routes::get_all_pickup_routes();
            if ( $pickup_routes ) {
                foreach ( $pickup_routes as $route ) {
                    echo '<option value="' . esc_attr( $route->id ) . '">' . esc_html( $route->start_location ) . '</option>';
                }
            }
            ?>
        </select>
    </div>

    <div class="garibooking-form-group">
        <label for="drop_route">To:</label>
        <select name="drop_route" id="drop_route" required>
            <option value="">Select Destination</option>
            <?php
            // Fetch all drop locations (get_all_drop_routes() or similar)
            $drop_routes = Garibooking_Routes::get_all_drop_routes();
            if ( $drop_routes ) {
                foreach ( $drop_routes as $route ) {
                    echo '<option value="' . esc_attr( $route->id ) . '">' . esc_html( $route->end_location ) . '</option>';
                }
            }
            ?>
        </select>
    </div>

    <div class="garibooking-form-group">
        <label for="travel_date">Travel Date:</label>
        <input type="date" id="travel_date" name="travel_date" required min="<?php echo date('Y-m-d'); ?>" />
    </div>

    <div class="garibooking-form-group">
        <label for="passengers">Number of Passengers:</label>
        <input type="number" id="passengers" name="passengers" min="1" max="10" value="1" required />
    </div>

    <div class="garibooking-form-group">
        <label for="additional_notes">Additional Notes:</label>
        <textarea id="additional_notes" name="additional_notes" rows="3" placeholder="Any special requests?"></textarea>
    </div>

    <div class="garibooking-form-group">
        <button type="submit" name="garibooking_submit_booking">Book Now</button>
    </div>
</form>
