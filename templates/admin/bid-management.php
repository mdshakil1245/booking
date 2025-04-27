<?php
// bid-management.php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Direct access not allowed
}

if ( ! current_user_can( 'manage_options' ) ) {
    echo '<p>You do not have permission to access this page.</p>';
    return;
}

require_once 'class-garibooking-bid-system.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete bid
    if ( isset($_POST['delete_bid']) && !empty($_POST['bid_id']) ) {
        $bid_id = intval($_POST['bid_id']);
        $deleted = Garibooking_Bid_System::delete_bid($bid_id);

        if ($deleted) {
            echo '<div class="notice notice-success"><p>Bid deleted successfully.</p></div>';
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="notice notice-error"><p>Failed to delete bid.</p></div>';
        }
    }

    // Update bid status
    if ( isset($_POST['update_bid_status']) && !empty($_POST['bid_id']) && isset($_POST['status']) ) {
        $bid_id = intval($_POST['bid_id']);
        $status = sanitize_text_field($_POST['status']);
        $updated = Garibooking_Bid_System::update_bid_status($bid_id, $status);

        if ($updated) {
            echo '<div class="notice notice-success"><p>Bid status updated.</p></div>';
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        } else {
            echo '<div class="notice notice-error"><p>Failed to update bid status.</p></div>';
        }
    }
}

// Fetch all bids
$bids = Garibooking_Bid_System::get_all_bids();

?>

<div class="garibooking-bid-management">
    <h1>Manage Bids</h1>

    <?php if (empty($bids)) : ?>
        <p>No bids found.</p>
    <?php else : ?>
        <table class="garibooking-bids-table" border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th>Bid ID</th>
                    <th>Booking ID</th>
                    <th>Driver</th>
                    <th>Bid Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bids as $bid) : ?>
                    <tr>
                        <td><?php echo esc_html($bid->id); ?></td>
                        <td><?php echo esc_html($bid->booking_id); ?></td>
                        <td><?php echo esc_html($bid->driver_name); ?></td>
                        <td><?php echo esc_html($bid->amount); ?></td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="bid_id" value="<?php echo esc_attr($bid->id); ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="pending" <?php selected($bid->status, 'pending'); ?>>Pending</option>
                                    <option value="accepted" <?php selected($bid->status, 'accepted'); ?>>Accepted</option>
                                    <option value="rejected" <?php selected($bid->status, 'rejected'); ?>>Rejected</option>
                                </select>
                                <input type="hidden" name="update_bid_status" value="1">
                            </form>
                        </td>
                        <td>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this bid?');" style="display:inline-block;">
                                <input type="hidden" name="bid_id" value="<?php echo esc_attr($bid->id); ?>">
                                <button type="submit" name="delete_bid">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
