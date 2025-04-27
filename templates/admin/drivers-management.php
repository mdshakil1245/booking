<?php
// drivers-management.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Direct access not allowed
}

if ( ! current_user_can( 'manage_options' ) ) {
    echo '<p>You do not have permission to access this page.</p>';
    return;
}

require_once 'class-garibooking-drivers.php'; // ড্রাইভার ক্লাস ফাইল ইমপোর্ট

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ( isset( $_POST['delete_driver'] ) && !empty( $_POST['driver_id'] ) ) {
        $driver_id = intval( $_POST['driver_id'] );
        $deleted = Garibooking_Drivers::delete_driver( $driver_id );

        if ( $deleted ) {
            echo '<div class="notice notice-success"><p>Driver deleted successfully.</p></div>';
            wp_redirect( $_SERVER['REQUEST_URI'] );
            exit;
        } else {
            echo '<div class="notice notice-error"><p>Failed to delete driver.</p></div>';
        }
    }

    if ( isset( $_POST['name'], $_POST['email'], $_POST['phone'] ) ) {
        $name  = sanitize_text_field( $_POST['name'] );
        $email = sanitize_email( $_POST['email'] );
        $phone = sanitize_text_field( $_POST['phone'] );

        if ( !is_email($email) ) {
            echo '<div class="notice notice-error"><p>Invalid email address.</p></div>';
        } else {
            $added = Garibooking_Drivers::add_driver( $name, $email, $phone );

            if ( $added ) {
                echo '<div class="notice notice-success"><p>Driver added successfully.</p></div>';
                wp_redirect( $_SERVER['REQUEST_URI'] );
                exit;
            } else {
                echo '<div class="notice notice-error"><p>Failed to add driver.</p></div>';
            }
        }
    }
}

// Fetch all drivers
$drivers = Garibooking_Drivers::get_all_drivers();

?>

<div class="garibooking-drivers-management">
    <h1>Manage Drivers</h1>

    <section class="add-driver">
        <h2>Add New Driver</h2>
        <form id="garibooking-add-driver-form" method="post" action="">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required>

            <button type="submit">Add Driver</button>
        </form>
    </section>

    <section class="existing-drivers">
        <h2>Existing Drivers</h2>
        <?php if ( empty( $drivers ) ) : ?>
            <p>No drivers found.</p>
        <?php else : ?>
            <table class="garibooking-drivers-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $drivers as $driver ) : ?>
                        <tr>
                            <td><?php echo esc_html( $driver->id ); ?></td>
                            <td><?php echo esc_html( $driver->name ); ?></td>
                            <td><?php echo esc_html( $driver->email ); ?></td>
                            <td><?php echo esc_html( $driver->phone ); ?></td>
                            <td>
                                <form method="post" style="display:inline-block;">
                                    <input type="hidden" name="driver_id" value="<?php echo esc_attr( $driver->id ); ?>">
                                    <button type="submit" name="delete_driver" onclick="return confirm('Are you sure you want to delete this driver?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
