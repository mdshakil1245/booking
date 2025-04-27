<?php
// routes-settings.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Direct access not allowed
}

if ( ! current_user_can( 'manage_options' ) ) {
    echo '<p>You do not have permission to access this page.</p>';
    return;
}

// Handle POST requests for add/edit/delete route here or use AJAX

// Fetch all routes
$routes = Garibooking_Routes::get_all_routes();

?>

<div class="garibooking-routes-settings">
    <h1>Manage Routes</h1>

    <section class="add-route">
        <h2>Add New Route</h2>
        <form id="garibooking-add-route-form" method="post" action="">
            <label for="start_location">Start Location:</label>
            <input type="text" id="start_location" name="start_location" required>

            <label for="end_location">End Location:</label>
            <input type="text" id="end_location" name="end_location" required>

            <button type="submit">Add Route</button>
        </form>
    </section>

    <section class="existing-routes">
        <h2>Existing Routes</h2>
        <?php if ( empty( $routes ) ) : ?>
            <p>No routes found.</p>
        <?php else : ?>
            <table class="garibooking-routes-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Start Location</th>
                        <th>End Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $routes as $route ) : ?>
                        <tr>
                            <td><?php echo esc_html( $route->id ); ?></td>
                            <td><?php echo esc_html( $route->start_location ); ?></td>
                            <td><?php echo esc_html( $route->end_location ); ?></td>
                            <td>
                                <form method="post" style="display:inline-block;">
                                    <input type="hidden" name="route_id" value="<?php echo esc_attr( $route->id ); ?>">
                                    <button type="submit" name="delete_route" onclick="return confirm('Are you sure you want to delete this route?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ( isset( $_POST['delete_route'] ) && !empty( $_POST['route_id'] ) ) {
        $route_id = intval( $_POST['route_id'] );
        $deleted = Garibooking_Routes::delete_route( $route_id );

        if ( $deleted ) {
            echo '<div class="notice notice-success"><p>Route deleted successfully.</p></div>';
            // Redirect to avoid resubmission
            wp_redirect( $_SERVER['REQUEST_URI'] );
            exit;
        } else {
            echo '<div class="notice notice-error"><p>Failed to delete route.</p></div>';
        }
    }

    if ( isset( $_POST['start_location'], $_POST['end_location'] ) ) {
        $start = sanitize_text_field( $_POST['start_location'] );
        $end = sanitize_text_field( $_POST['end_location'] );

        $added = Garibooking_Routes::add_route( $start, $end );

        if ( $added ) {
            echo '<div class="notice notice-success"><p>Route added successfully.</p></div>';
            wp_redirect( $_SERVER['REQUEST_URI'] );
            exit;
        } else {
            echo '<div class="notice notice-error"><p>Failed to add route.</p></div>';
        }
    }
}
?>
