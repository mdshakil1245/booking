(function($) {
    "use strict";

    // Document ready
    $(document).ready(function() {

        // Booking form submission via AJAX
        $('#garibooking-form').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: garibooking_ajax_obj.ajax_url,
                type: 'POST',
                data: form.serialize() + '&action=garibooking_submit_booking',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Booking successful!');
                        form[0].reset();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Submit Booking');
                }
            });
        });


        // Bidding form submission (example)
        $('.garibooking-bid-form').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Sending Bid...');

            $.ajax({
                url: garibooking_ajax_obj.ajax_url,
                type: 'POST',
                data: form.serialize() + '&action=garibooking_submit_bid',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Bid sent successfully!');
                        form[0].reset();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Something went wrong while sending the bid.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Send Bid');
                }
            });
        });


        // Notification example: Poll for new notifications every 30 seconds
        function checkNotifications() {
            $.ajax({
                url: garibooking_ajax_obj.ajax_url,
                type: 'POST',
                data: { action: 'garibooking_check_notifications' },
                dataType: 'json',
                success: function(response) {
                    if(response.success && response.data.count > 0) {
                        // Show notification count badge
                        $('#notification-badge').text(response.data.count).fadeIn();
                    } else {
                        $('#notification-badge').fadeOut();
                    }
                }
            });
        }

        // Start polling notifications
        setInterval(checkNotifications, 30000);
        checkNotifications();


        // Simple UI enhancements (toggle menus, etc.)
        $('.garibooking-toggle-menu').on('click', function() {
            $('.garibooking-sidebar').toggleClass('active');
        });

    });

})(jQuery);
