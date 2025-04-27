<?php
// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete custom database tables (যদি থাকে)
global $wpdb;
$table_bookings = $wpdb->prefix . 'garibooking_bookings';
$table_bids     = $wpdb->prefix . 'garibooking_bids';
$table_routes   = $wpdb->prefix . 'garibooking_routes';

// Check and drop tables if exist
$wpdb->query( "DROP TABLE IF EXISTS $table_bookings" );
$wpdb->query( "DROP TABLE IF EXISTS $table_bids" );
$wpdb->query( "DROP TABLE IF EXISTS $table_routes" );

// Delete plugin options
delete_option( 'garibooking_settings' );
delete_option( 'garibooking_version' );

// Remove user roles created by plugin
remove_role( 'garibooking_driver' );
remove_role( 'garibooking_user' );

// You can add more cleanup here if needed
