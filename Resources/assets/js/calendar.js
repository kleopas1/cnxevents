/**
 * Calendar functionality for CnxEvents module
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing calendar...');
        
        var calendarEl = document.getElementById('calendar');
        
        if (!calendarEl) {
            console.error('Calendar element not found!');
            return;
        }

        console.log('Calendar element found:', calendarEl);
        
        // Check if required dependencies are loaded
        if (typeof FullCalendar === 'undefined') {
            console.error('FullCalendar library not loaded!');
            return;
        }
        
        if (typeof laroute === 'undefined') {
            console.error('laroute not loaded!');
            return;
        }

        console.log('Dependencies loaded, creating calendar...');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: function(info, successCallback, failureCallback) {
                fetch(laroute.route('cnxevents.calendar.events') + '?start=' + info.startStr + '&end=' + info.endStr)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        successCallback(data);
                    })
                    .catch(function(error) {
                        console.error('Error fetching calendar events:', error);
                        failureCallback(error);
                    });
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                
                // Fetch event details
                fetch(laroute.route('cnxevents.events.show', { id: info.event.id }))
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        displayEventDetails(data);
                    })
                    .catch(function(error) {
                        console.error('Error fetching event details:', error);
                    });
            }
        });

        calendar.render();

        /**
         * Display event details in modal
         */
        function displayEventDetails(data) {
            document.getElementById('eventTitle').textContent = data.title;
            
            var details = '';
            details += '<p><strong>Description:</strong> ' + (data.description || 'N/A') + '</p>';
            details += '<p><strong>Venue:</strong> ' + (data.venue ? data.venue.name : 'N/A') + '</p>';
            details += '<p><strong>Start:</strong> ' + new Date(data.start_datetime).toLocaleString() + '</p>';
            details += '<p><strong>End:</strong> ' + new Date(data.end_datetime).toLocaleString() + '</p>';
            
            if (data.setup_datetime) {
                details += '<p><strong>Setup:</strong> ' + new Date(data.setup_datetime).toLocaleString() + '</p>';
            }
            if (data.venue_release_datetime) {
                details += '<p><strong>Release:</strong> ' + new Date(data.venue_release_datetime).toLocaleString() + '</p>';
            }
            
            details += '<p><strong>Status:</strong> <span class="label label-' + (data.status === 'confirmed' ? 'success' : 'info') + '">' + data.status + '</span></p>';
            details += '<p><strong>Client:</strong> ' + (data.client_name || 'N/A') + '</p>';
            
            if (data.client_email) {
                details += '<p><strong>Client Email:</strong> <a href="mailto:' + data.client_email + '">' + data.client_email + '</a></p>';
            }
            if (data.client_phone) {
                details += '<p><strong>Client Phone:</strong> ' + data.client_phone + '</p>';
            }
            if (data.client_company) {
                details += '<p><strong>Client Company:</strong> ' + data.client_company + '</p>';
            }
            
            // Add custom fields if they exist
            if (data.customFieldValues && data.customFieldValues.length > 0) {
                details += '<hr><p><strong>Custom Fields:</strong></p><dl class="dl-horizontal">';
                data.customFieldValues.forEach(function(fieldValue) {
                    if (fieldValue.custom_field) {
                        details += '<dt>' + fieldValue.custom_field.name + ':</dt>';
                        details += '<dd>' + (fieldValue.value || 'N/A') + '</dd>';
                    }
                });
                details += '</dl>';
            }
            
            document.getElementById('eventDetails').innerHTML = details;
            document.getElementById('editEventBtn').href = laroute.route('cnxevents.events.index') + '#event-' + data.id;
            
            // Show modal using jQuery (Bootstrap 3)
            $('#eventDetailsModal').modal('show');
        }
    });
})();
