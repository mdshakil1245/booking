<?php
/**
 * Class to handle custom user roles for Garibooking plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Garibooking_Roles {

    public function __construct() {
        // Add roles on plugin activation
        register_activation_hook( GARIBOOKING_PLUGIN_FILE, array( $this, 'add_custom_roles' ) );

        // Remove roles on plugin deactivation
        register_deactivation_hook( GARIBOOKING_PLUGIN_FILE, array( $this, 'remove_custom_roles' ) );
    }

    /**
     * Add custom roles: Driver and Customer(User)
     */
    public function add_custom_roles() {
        add_role(
            'garibooking_driver',
            __( 'Garibooking Driver', 'garibooking' ),
            array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'upload_files' => true,
                // Add more capabilities as needed
            )
        );

        add_role(
            'garibooking_customer',
            __( 'Garibooking Customer', 'garibooking' ),
            array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                // Add more capabilities as needed
            )
        );
    }

    /**
     * Remove custom roles on plugin deactivation
     */
    public function remove_custom_roles() {
        remove_role( 'garibooking_driver' );
        remove_role( 'garibooking_customer' );
    }

    /**
     * Utility to check if user is driver
     */
    public static function is_driver( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return false;
        }
        return in_array( 'garibooking_driver', (array) $user->roles );
    }

    /**
     * Utility to check if user is customer
     */
    public static function is_customer( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return false;
        }
        return in_array( 'garibooking_customer', (array) $user->roles );
    }

}

new Garibooking_Roles();
