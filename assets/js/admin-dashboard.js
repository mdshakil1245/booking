(function($) {
    "use strict";

    $(document).ready(function() {

        // Save settings form submit
        $('#garibooking-admin-settings-form').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: garibooking_ajax_obj.ajax_url,
                type: 'POST',
                data: form.serialize() + '&action=garibooking_save_settings',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Settings saved successfully!');
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Failed to save settings. Try again.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Save Settings');
                }
            });
        });


        // Add new route via modal form
        $('#garibooking-add-route-form').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Adding Route...');

            $.ajax({
                url: garibooking_ajax_obj.ajax_url,
                type: 'POST',
                data: form.serialize() + '&action=garibooking_add_route',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Route added successfully!');
                        form[0].reset();
                        // Optionally reload or append route to the route list
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Failed to add route.');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Add Route');
                }
            });
        });


        // Delete route action
        $('.garibooking-delete-route').on('click', function() {
            if(!confirm('Are you sure you want to delete this route?')) return;

            let routeId = $(this).data('route-id');

            $.ajax({
                url: garibooking_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'garibooking_delete_route',
                    route_id: routeId
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Route deleted successfully!');
                        location.reload();
                    } else {
                        alert('Failed to delete route.');
                    }
                }
            });
        });


        // Assign driver to booking
        $('.garibooking-assign-driver').on('click', function() {
            let bookingId = $(this).data('booking-id');
            let driverId = $('#driver-select-' + bookingId).val();

            if(!driverId) {
                alert('Please select a driver first.');
                return;
            }

            $.ajax({
                url: garibooking_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'garibooking_assign_driver',
                    booking_id: bookingId,
                    driver_id: driverId
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Driver assigned successfully!');
                        location.reload();
                    } else {
                        alert('Failed to assign driver.');
                    }
                }
            });
        });


        // Change booking status dropdown
        $('.garibooking-change-status').on('change', function() {
            let bookingId = $(this).data('booking-id');
            let status = $(this).val();

            $.ajax({
                url: garibooking_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'garibooking_change_booking_status',
                    booking_id: bookingId,
                    status: status
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Booking status updated!');
                    } else {
                        alert('Failed to update booking status.');
                    }
                }
            });
        });


        // Pagination or filtering can be added here similarly

    });

})(jQuery);
