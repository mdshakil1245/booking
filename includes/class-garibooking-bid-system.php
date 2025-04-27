<?php
/**
 * Garibooking Bid System Class
 * Handles bid creation, retrieval, and management for driver bids on bookings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Garibooking_Bid_System {

    public function __construct() {
        // AJAX hooks for bid operations
        add_action( 'wp_ajax_garibooking_place_bid', array( $this, 'ajax_place_bid' ) );
        add_action( 'wp_ajax_nopriv_garibooking_place_bid', array( $this, 'ajax_place_bid' ) );

        add_action( 'wp_ajax_garibooking_get_bids', array( $this, 'ajax_get_bids' ) );
        add_action( 'wp_ajax_nopriv_garibooking_get_bids', array( $this, 'ajax_get_bids' ) );
    }

    /**
     * Place a new bid for a booking by a driver
     * @param int $booking_id
     * @param int $driver_id
     * @param float $amount
     * @param string $message
     * @return int|false Bid ID or false on failure
     */
    public function place_bid( $booking_id, $driver_id, $amount, $message = '' ) {
        if ( empty( $booking_id ) || empty( $driver_id ) || empty( $amount ) ) {
            return false;
        }

        // Prepare bid data as post (custom post type 'garibooking_bid')
        $bid_post = array(
            'post_title'  => "Bid for Booking #{$booking_id} by Driver #{$driver_id}",
            'post_type'   => 'garibooking_bid',
            'post_status' => 'publish',
            'post_author' => $driver_id,
            'post_content'=> sanitize_textarea_field( $message ),
        );

        $bid_id = wp_insert_post( $bid_post );

        if ( is_wp_error( $bid_id ) ) {
            return false;
        }

        // Save bid meta data
        update_post_meta( $bid_id, '_booking_id', intval( $booking_id ) );
        update_post_meta( $bid_id, '_driver_id', intval( $driver_id ) );
        update_post_meta( $bid_id, '_bid_amount', floatval( $amount ) );
        update_post_meta( $bid_id, '_bid_status', 'pending' ); // pending, accepted, rejected
        update_post_meta( $bid_id, '_bid_placed_at', current_time( 'mysql' ) );

        return $bid_id;
    }

    /**
     * Get bids for a specific booking
     * @param int $booking_id
     * @param string $status Optional filter by bid status (pending, accepted, rejected)
     * @return array
     */
    public function get_bids( $booking_id, $status = '' ) {
        if ( empty( $booking_id ) ) {
            return array();
        }

        $meta_query = array(
            array(
                'key'   => '_booking_id',
                'value' => intval( $booking_id ),
                'compare' => '=',
            ),
        );

        if ( $status ) {
            $meta_query[] = array(
                'key'   => '_bid_status',
                'value' => sanitize_text_field( $status ),
                'compare' => '=',
            );
        }

        $args = array(
            'post_type'      => 'garibooking_bid',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value',
            'meta_key'       => '_bid_placed_at',
            'order'          => 'DESC',
            'meta_query'     => $meta_query,
        );

        $query = new WP_Query( $args );
        $bids = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                $bids[] = array(
                    'bid_id'      => get_the_ID(),
                    'driver_id'   => get_post_meta( get_the_ID(), '_driver_id', true ),
                    'amount'      => get_post_meta( get_the_ID(), '_bid_amount', true ),
                    'message'     => get_the_content(),
                    'status'      => get_post_meta( get_the_ID(), '_bid_status', true ),
                    'placed_at'   => get_post_meta( get_the_ID(), '_bid_placed_at', true ),
                    'driver_name' => get_the_author_meta( 'display_name', get_post_meta( get_the_ID(), '_driver_id', true ) ),
                );
            }
            wp_reset_postdata();
        }

        return $bids;
    }

    /**
     * Update bid status (accept/reject)
     * @param int $bid_id
     * @param string $status
     * @return bool
     */
    public function update_bid_status( $bid_id, $status ) {
        if ( empty( $bid_id ) || ! in_array( $status, array( 'pending', 'accepted', 'rejected' ) ) ) {
            return false;
        }

        return update_post_meta( $bid_id, '_bid_status', $status );
    }

    /**
     * AJAX handler for placing bid
     */
    public function ajax_place_bid() {
        // Check nonce, user capability etc. (implement as needed)

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'User not logged in' );
        }

        $booking_id = isset( $_POST['booking_id'] ) ? intval( $_POST['booking_id'] ) : 0;
        $amount     = isset( $_POST['amount'] ) ? floatval( $_POST['amount'] ) : 0;
        $message    = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';
        $driver_id  = get_current_user_id();

        if ( ! $booking_id || ! $amount ) {
            wp_send_json_error( 'Missing required parameters' );
        }

        $bid_id = $this->place_bid( $booking_id, $driver_id, $amount, $message );

        if ( $bid_id ) {
            wp_send_json_success( array( 'bid_id' => $bid_id, 'message' => 'Bid placed successfully.' ) );
        } else {
            wp_send_json_error( 'Failed to place bid.' );
        }
    }

    /**
     * AJAX handler for fetching bids for a booking
     */
    public function ajax_get_bids() {
        // Check permissions (implement as needed)

        $booking_id = isset( $_GET['booking_id'] ) ? intval( $_GET['booking_id'] ) : 0;

        if ( ! $booking_id ) {
            wp_send_json_error( 'Missing booking ID' );
        }

        $bids = $this->get_bids( $booking_id );

        wp_send_json_success( $bids );
    }
}

new Garibooking_Bid_System();
