<?php
/**
 * Class to register Custom Post Types for Garibooking plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Garibooking_CPT {

    public function __construct() {
        add_action( 'init', array( $this, 'register_post_types' ) );
    }

    /**
     * Register custom post types: routes, bookings
     */
    public function register_post_types() {
        // Register Route CPT
        $labels_route = array(
            'name'               => _x( 'Routes', 'post type general name', 'garibooking' ),
            'singular_name'      => _x( 'Route', 'post type singular name', 'garibooking' ),
            'menu_name'          => _x( 'Routes', 'admin menu', 'garibooking' ),
            'name_admin_bar'     => _x( 'Route', 'add new on admin bar', 'garibooking' ),
            'add_new'            => _x( 'Add New', 'route', 'garibooking' ),
            'add_new_item'       => __( 'Add New Route', 'garibooking' ),
            'new_item'           => __( 'New Route', 'garibooking' ),
            'edit_item'          => __( 'Edit Route', 'garibooking' ),
            'view_item'          => __( 'View Route', 'garibooking' ),
            'all_items'          => __( 'All Routes', 'garibooking' ),
            'search_items'       => __( 'Search Routes', 'garibooking' ),
            'parent_item_colon'  => __( 'Parent Routes:', 'garibooking' ),
            'not_found'          => __( 'No routes found.', 'garibooking' ),
            'not_found_in_trash' => __( 'No routes found in Trash.', 'garibooking' )
        );

        $args_route = array(
            'labels'             => $labels_route,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'garibooking_admin_menu', // Custom menu slug
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'garibooking_route' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-location-alt',
            'supports'           => array( 'title', 'editor' ),
        );

        register_post_type( 'garibooking_route', $args_route );

        // Register Booking CPT
        $labels_booking = array(
            'name'               => _x( 'Bookings', 'post type general name', 'garibooking' ),
            'singular_name'      => _x( 'Booking', 'post type singular name', 'garibooking' ),
            'menu_name'          => _x( 'Bookings', 'admin menu', 'garibooking' ),
            'name_admin_bar'     => _x( 'Booking', 'add new on admin bar', 'garibooking' ),
            'add_new'            => _x( 'Add New', 'booking', 'garibooking' ),
            'add_new_item'       => __( 'Add New Booking', 'garibooking' ),
            'new_item'           => __( 'New Booking', 'garibooking' ),
            'edit_item'          => __( 'Edit Booking', 'garibooking' ),
            'view_item'          => __( 'View Booking', 'garibooking' ),
            'all_items'          => __( 'All Bookings', 'garibooking' ),
            'search_items'       => __( 'Search Bookings', 'garibooking' ),
            'parent_item_colon'  => __( 'Parent Bookings:', 'garibooking' ),
            'not_found'          => __( 'No bookings found.', 'garibooking' ),
            'not_found_in_trash' => __( 'No bookings found in Trash.', 'garibooking' )
        );

        $args_booking = array(
            'labels'             => $labels_booking,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => 'garibooking_admin_menu', // Custom menu slug
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'garibooking_booking' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 21,
            'menu_icon'          => 'dashicons-clipboard',
            'supports'           => array( 'title', 'editor' ),
        );

        register_post_type( 'garibooking_booking', $args_booking );
    }
}

new Garibooking_CPT();
