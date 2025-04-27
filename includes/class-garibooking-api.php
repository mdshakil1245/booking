<?php
/**
 * Class to handle REST API endpoints for Garibooking plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Garibooking_API {

    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register custom REST API routes
     */
    public function register_routes() {
        $namespace = 'garibooking/v1';

        // Example: Get all routes
        register_rest_route( $namespace, '/routes', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_routes' ),
            'permission_callback' => function () {
                return current_user_can( 'read' );
            }
        ));

        // Example: Get all bookings for current user
        register_rest_route( $namespace, '/bookings', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_bookings' ),
            'permission_callback' => function () {
                return is_user_logged_in();
            }
        ));

        // Example: Create a new booking
        register_rest_route( $namespace, '/bookings', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'create_booking' ),
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args' => $this->get_booking_endpoint_args(),
        ));

        // Additional routes for bidding, notifications, drivers, users can be added similarly
    }

    /**
     * Get list of routes
     */
    public function get_routes( WP_REST_Request $request ) {
        $args = array(
            'post_type'      => 'garibooking_route',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );
        $query = new WP_Query( $args );

        $routes = array();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $routes[] = array(
                    'id'          => get_the_ID(),
                    'title'       => get_the_title(),
                    'description' => get_the_content(),
                    // Add custom fields if any
                );
            }
            wp_reset_postdata();
        }

        return rest_ensure_response( $routes );
    }

    /**
     * Get bookings of the current logged-in user
     */
    public function get_bookings( WP_REST_Request $request ) {
        $user_id = get_current_user_id();

        $args = array(
            'post_type'      => 'garibooking_booking',
            'post_status'    => 'publish',
            'author'         => $user_id,
            'posts_per_page' => -1,
        );

        $query = new WP_Query( $args );

        $bookings = array();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $bookings[] = array(
                    'id'          => get_the_ID(),
                    'title'       => get_the_title(),
                    'content'     => get_the_content(),
                    // Include meta data if needed
                );
            }
            wp_reset_postdata();
        }

        return rest_ensure_response( $bookings );
    }

    /**
     * Create new booking
     */
    public function create_booking( WP_REST_Request $request ) {
        $params = $request->get_params();

        $user_id = get_current_user_id();

        // Basic validation
        if ( empty( $params['title'] ) || empty( $params['route_id'] ) ) {
            return new WP_Error( 'missing_data', 'Required fields are missing', array( 'status' => 400 ) );
        }

        // Create new booking post
        $booking_post = array(
            'post_title'   => sanitize_text_field( $params['title'] ),
            'post_content' => sanitize_textarea_field( $params['description'] ?? '' ),
            'post_type'    => 'garibooking_booking',
            'post_status'  => 'publish',
            'post_author'  => $user_id,
        );

        $post_id = wp_insert_post( $booking_post );

        if ( is_wp_error( $post_id ) ) {
            return new WP_Error( 'booking_failed', 'Failed to create booking', array( 'status' => 500 ) );
        }

        // Save meta data (like route_id, date, seats, etc)
        if ( isset( $params['route_id'] ) ) {
            update_post_meta( $post_id, '_route_id', intval( $params['route_id'] ) );
        }
        if ( isset( $params['date'] ) ) {
            update_post_meta( $post_id, '_booking_date', sanitize_text_field( $params['date'] ) );
        }
        if ( isset( $params['seats'] ) ) {
            update_post_meta( $post_id, '_seats', intval( $params['seats'] ) );
        }

        // Return booking ID and success
        return rest_ensure_response( array(
            'success'   => true,
            'booking_id'=> $post_id,
        ) );
    }

    /**
     * Define arguments for create_booking endpoint
     */
    private function get_booking_endpoint_args() {
        return array(
            'title' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'Booking title',
            ),
            'description' => array(
                'required' => false,
                'type' => 'string',
                'description' => 'Booking description',
            ),
            'route_id' => array(
                'required' => true,
                'type' => 'integer',
                'description' => 'ID of the selected route',
            ),
            'date' => array(
                'required' => false,
                'type' => 'string',
                'description' => 'Booking date',
            ),
            'seats' => array(
                'required' => false,
                'type' => 'integer',
                'description' => 'Number of seats to book',
            ),
        );
    }
}

new Garibooking_API();
