<?php
/**
 * Garibooking Routes Class
 * Handles route creation, update, retrieval, and management
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Garibooking_Routes {

    public function __construct() {
        // Register custom post type for routes
        add_action( 'init', array( $this, 'register_route_cpt' ) );

        // AJAX handlers for route operations
        add_action( 'wp_ajax_garibooking_add_route', array( $this, 'ajax_add_route' ) );
        add_action( 'wp_ajax_garibooking_get_routes', array( $this, 'ajax_get_routes' ) );
        add_action( 'wp_ajax_garibooking_delete_route', array( $this, 'ajax_delete_route' ) );
        add_action( 'wp_ajax_garibooking_update_route', array( $this, 'ajax_update_route' ) );
    }

    /**
     * Register custom post type for Routes
     */
    public function register_route_cpt() {
        $labels = array(
            'name'               => __( 'Routes', 'garibooking' ),
            'singular_name'      => __( 'Route', 'garibooking' ),
            'menu_name'          => __( 'Routes', 'garibooking' ),
            'name_admin_bar'     => __( 'Route', 'garibooking' ),
            'add_new'            => __( 'Add New Route', 'garibooking' ),
            'add_new_item'       => __( 'Add New Route', 'garibooking' ),
            'edit_item'          => __( 'Edit Route', 'garibooking' ),
            'new_item'           => __( 'New Route', 'garibooking' ),
            'view_item'          => __( 'View Route', 'garibooking' ),
            'search_items'       => __( 'Search Routes', 'garibooking' ),
            'not_found'          => __( 'No routes found.', 'garibooking' ),
            'not_found_in_trash' => __( 'No routes found in Trash.', 'garibooking' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => array( 'title' ),
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-location-alt',
            'exclude_from_search'=> true,
            'publicly_queryable' => false,
            'show_in_admin_bar'  => true,
        );

        register_post_type( 'garibooking_route', $args );
    }

    /**
     * Add a new route
     * @param string $from_location
     * @param string $to_location
     * @return int|false Route post ID or false on failure
     */
    public function add_route( $from_location, $to_location ) {
        if ( empty( $from_location ) || empty( $to_location ) ) {
            return false;
        }

        $route_title = sanitize_text_field( $from_location ) . ' to ' . sanitize_text_field( $to_location );

        $post_data = array(
            'post_title'  => $route_title,
            'post_type'   => 'garibooking_route',
            'post_status' => 'publish',
        );

        $route_id = wp_insert_post( $post_data );

        if ( is_wp_error( $route_id ) ) {
            return false;
        }

        // Save route meta data
        update_post_meta( $route_id, '_from_location', sanitize_text_field( $from_location ) );
        update_post_meta( $route_id, '_to_location', sanitize_text_field( $to_location ) );

        return $route_id;
    }

    /**
     * Get all routes
     * @return array
     */
    public function get_routes() {
        $args = array(
            'post_type'      => 'garibooking_route',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $query = new WP_Query( $args );
        $routes = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                $routes[] = array(
                    'route_id'     => get_the_ID(),
                    'from'         => get_post_meta( get_the_ID(), '_from_location', true ),
                    'to'           => get_post_meta( get_the_ID(), '_to_location', true ),
                    'route_title'  => get_the_title(),
                );
            }
            wp_reset_postdata();
        }

        return $routes;
    }

    /**
     * Delete a route by ID
     * @param int $route_id
     * @return bool
     */
    public function delete_route( $route_id ) {
        if ( empty( $route_id ) ) {
            return false;
        }

        $deleted = wp_delete_post( intval( $route_id ), true );

        return ( $deleted !== false );
    }

    /**
     * Update route details
     * @param int $route_id
     * @param string $from_location
     * @param string $to_location
     * @return bool
     */
    public function update_route( $route_id, $from_location, $to_location ) {
        if ( empty( $route_id ) || empty( $from_location ) || empty( $to_location ) ) {
            return false;
        }

        $route_title = sanitize_text_field( $from_location ) . ' to ' . sanitize_text_field( $to_location );

        $post_data = array(
            'ID'         => intval( $route_id ),
            'post_title' => $route_title,
        );

        $updated_id = wp_update_post( $post_data );

        if ( is_wp_error( $updated_id ) ) {
            return false;
        }

        update_post_meta( $route_id, '_from_location', sanitize_text_field( $from_location ) );
        update_post_meta( $route_id, '_to_location', sanitize_text_field( $to_location ) );

        return true;
    }

    /**
     * AJAX handler to add route
     */
    public function ajax_add_route() {
        // Check permissions, nonce etc. (implement as needed)
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $from = isset( $_POST['from_location'] ) ? sanitize_text_field( $_POST['from_location'] ) : '';
        $to   = isset( $_POST['to_location'] ) ? sanitize_text_field( $_POST['to_location'] ) : '';

        if ( empty( $from ) || empty( $to ) ) {
            wp_send_json_error( 'Missing route data' );
        }

        $route_id = $this->add_route( $from, $to );

        if ( $route_id ) {
            wp_send_json_success( array( 'route_id' => $route_id, 'message' => 'Route added successfully.' ) );
        } else {
            wp_send_json_error( 'Failed to add route.' );
        }
    }

    /**
     * AJAX handler to get routes
     */
    public function ajax_get_routes() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $routes = $this->get_routes();

        wp_send_json_success( $routes );
    }

    /**
     * AJAX handler to delete route
     */
    public function ajax_delete_route() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $route_id = isset( $_POST['route_id'] ) ? intval( $_POST['route_id'] ) : 0;

        if ( ! $route_id ) {
            wp_send_json_error( 'Missing route ID' );
        }

        $deleted = $this->delete_route( $route_id );

        if ( $deleted ) {
            wp_send_json_success( 'Route deleted successfully.' );
        } else {
            wp_send_json_error( 'Failed to delete route.' );
        }
    }

    /**
     * AJAX handler to update route
     */
    public function ajax_update_route() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $route_id     = isset( $_POST['route_id'] ) ? intval( $_POST['route_id'] ) : 0;
        $from         = isset( $_POST['from_location'] ) ? sanitize_text_field( $_POST['from_location'] ) : '';
        $to           = isset( $_POST['to_location'] ) ? sanitize_text_field( $_POST['to_location'] ) : '';

        if ( ! $route_id || empty( $from ) || empty( $to ) ) {
            wp_send_json_error( 'Missing data for update' );
        }

        $updated = $this->update_route( $route_id, $from, $to );

        if ( $updated ) {
            wp_send_json_success( 'Route updated successfully.' );
        } else {
            wp_send_json_error( 'Failed to update route.' );
        }
    }
}

new Garibooking_Routes();
