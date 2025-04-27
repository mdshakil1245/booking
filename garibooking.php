<?php
/*
Plugin Name: GariBooking - Car Booking System
Plugin URI:  https://yourwebsite.com/garibooking
Description: A powerful and user-friendly car booking system plugin for WordPress.
Version:     1.0.0
Author:      Your Name
Author URI:  https://yourwebsite.com
Text Domain: garibooking
Domain Path: /languages
License:     GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'GARIBOOKING_VERSION', '1.0.0' );
define( 'GARIBOOKING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GARIBOOKING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load text domain for translations
function garibooking_load_textdomain() {
    load_plugin_textdomain( 'garibooking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'garibooking_load_textdomain' );

// Include required files
require_once GARIBOOKING_PLUGIN_DIR . 'includes/class-garibooking-roles.php';
require_once GARIBOOKING_PLUGIN_DIR . 'includes/class-garibooking-cpt.php';
require_once GARIBOOKING_PLUGIN_DIR . 'includes/class-garibooking-api.php';
require_once GARIBOOKING_PLUGIN_DIR . 'includes/class-garibooking-notifications.php';
require_once GARIBOOKING_PLUGIN_DIR . 'includes/class-garibooking-bid-system.php';
require_once GARIBOOKING_PLUGIN_DIR . 'includes/class-garibooking-routes.php';
require_once GARIBOOKING_PLUGIN_DIR . 'includes/class-garibooking-bookings.php';
require_once GARIBOOKING_PLUGIN_DIR . 'includes/class-garibooking-admin.php';

// Main plugin class
final class GariBooking {

    /**
     * Plugin version
     */
    public $version = GARIBOOKING_VERSION;

    /**
     * Singleton instance
     */
    protected static $_instance = null;

    /**
     * Main GariBooking Instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
            self::$_instance->init_hooks();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Define any initial setup if needed here
    }

    /**
     * Initialize hooks and filters
     */
    public function init_hooks() {
        // Register custom post types, roles, admin menu etc.
        add_action( 'init', array( 'Garibooking_CPT', 'register_post_types' ) );
        add_action( 'init', array( 'Garibooking_Roles', 'register_roles' ) );
        add_action( 'admin_menu', array( 'Garibooking_Admin', 'add_admin_menus' ) );

        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( 'Garibooking_Admin', 'enqueue_admin_assets' ) );

        // Register REST API routes
        add_action( 'rest_api_init', array( 'Garibooking_API', 'register_routes' ) );
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'garibooking-style', GARIBOOKING_PLUGIN_URL . 'assets/css/garibooking-style.css', array(), $this->version );
        wp_enqueue_script( 'garibooking-script', GARIBOOKING_PLUGIN_URL . 'assets/js/garibooking-scripts.js', array( 'jquery' ), $this->version, true );
        
        // Localize script for ajax URLs etc.
        wp_localize_script( 'garibooking-script', 'GaribookingData', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'rest_url' => esc_url_raw( rest_url( 'garibooking/v1/' ) ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
        ));
    }

    /**
     * Activation hook
     */
    public static function activate() {
        // Register roles and post types for fresh activation
        Garibooking_Roles::register_roles();
        Garibooking_CPT::register_post_types();

        // Flush rewrite rules to avoid 404 errors
        flush_rewrite_rules();
    }

    /**
     * Deactivation hook
     */
    public static function deactivate() {
        // Remove roles if needed (optional)
        Garibooking_Roles::remove_roles();

        // Flush rewrite rules on deactivation
        flush_rewrite_rules();
    }
}

// Plugin activation and deactivation hooks
register_activation_hook( __FILE__, array( 'GariBooking', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GariBooking', 'deactivate' ) );

// Initialize the plugin
function garibooking() {
    return GariBooking::instance();
}
garibooking();
