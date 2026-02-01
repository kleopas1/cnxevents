// Events page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Check if we need to reopen the modal after validation error
    const container = document.querySelector('[data-reopen-modal="true"]');
    if (container) {
        setTimeout(function() {
            if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                jQuery('#eventModal').modal('show');
            }
        }, 300);
    }
    
    // Add Event button
    const addEventBtn = document.querySelector('button[data-target="#eventModal"]');
    console.log('Add event button:', addEventBtn);
    if (addEventBtn) {
        addEventBtn.addEventListener('click', function() {
            resetForm();
        });
    }

    // Edit Event buttons (from event list)
    const editButtons = document.querySelectorAll('.edit-event-btn');
    console.log('Found edit buttons:', editButtons.length);
    editButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const eventId = this.getAttribute('data-event-id');
            console.log('Edit button clicked, event ID:', eventId);
            editEvent(eventId);
        });
    });

    // View Event buttons (from calendar)
    const viewButtons = document.querySelectorAll('.view-event-btn');
    console.log('Found view buttons:', viewButtons.length);
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const eventId = this.getAttribute('data-event-id');
            console.log('View button clicked, event ID:', eventId);
            editEvent(eventId);
        });
    });

    // Delete confirmation
    document.querySelectorAll('.delete-event-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this event?')) {
                e.preventDefault();
            }
        });
    });

    // Confirm event buttons
    document.querySelectorAll('.confirm-event-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to confirm this event?')) {
                e.preventDefault();
            }
        });
    });

    // Cancel event buttons
    document.querySelectorAll('.cancel-event-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to cancel this event?')) {
                e.preventDefault();
            }
        });
    });

    // Form submit handler
    const eventForm = document.getElementById('eventForm');
    if (eventForm) {
        eventForm.addEventListener('submit', function(e) {
            const allDayCheckbox = document.getElementById('all_day');
            if (allDayCheckbox && allDayCheckbox.checked) {
                // Copy date values to datetime fields for submission
                const startDate = document.querySelector('input[name="start_date"]');
                const endDate = document.querySelector('input[name="end_date"]');
                const startDatetime = document.querySelector('input[name="start_datetime"]');
                const endDatetime = document.querySelector('input[name="end_datetime"]');
                
                if (startDate && startDate.value && startDatetime) {
                    startDatetime.value = startDate.value;
                    startDatetime.removeAttribute('required');
                }
                if (endDate && endDate.value && endDatetime) {
                    endDatetime.value = endDate.value;
                    endDatetime.removeAttribute('required');
                }
                
                // Remove validation from hidden fields
                document.querySelectorAll('.datetime-field').forEach(field => {
                    field.removeAttribute('required');
                });
            }
        });
    }
});

function resetForm() {
    const form = document.getElementById('eventForm');
    if (!form) return;

    // Reset form action
    form.action = '/cnxevents/events';

    // Remove method input if exists
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.remove();
    }

    // Set redirect URL to current page
    const redirectInput = document.getElementById('redirectToInput');
    if (redirectInput) {
        redirectInput.value = window.location.href;
    }

    // Reset modal title
    document.getElementById('modalTitle').textContent = 'Add Event';

    // Reset form
    form.reset();

    // Clear any error messages
    const errorElements = form.querySelectorAll('.text-danger');
    errorElements.forEach(el => el.remove());

    // Reset datetime fields visibility
    toggleDatetimeFields();
}

function editEvent(id) {
    console.log('editEvent called with ID:', id);
    fetch(`/cnxevents/events/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Event data received:', data);
        const form = document.getElementById('eventForm');

        // Update form action
        form.action = `/cnxevents/events/${id}`;

        // Add method input
        let methodInput = form.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            form.appendChild(methodInput);
        }
        methodInput.value = 'PUT';

        // Set redirect URL to current page
        const redirectInput = document.getElementById('redirectToInput');
        if (redirectInput) {
            redirectInput.value = window.location.href;
        }

        // Update modal title
        document.getElementById('modalTitle').textContent = 'Edit Event';

        // Populate form fields
        Object.keys(data).forEach(key => {
            const element = form.querySelector(`[name="${key}"]`);
            console.log(`Field ${key}:`, element, 'Value:', data[key]);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = data[key];
                } else {
                    element.value = data[key] || '';
                }
            }
        });

        // Handle custom fields
        if (data.custom_fields) {
            Object.keys(data.custom_fields).forEach(fieldId => {
                const element = form.querySelector(`[name="custom_field_${fieldId}"]`);
                const multiElement = form.querySelector(`[name="custom_field_${fieldId}[]"]`);

                if (multiElement && multiElement.multiple) {
                    // Handle multiselect
                    const values = Array.isArray(data.custom_fields[fieldId]) ? data.custom_fields[fieldId] : [];
                    Array.from(multiElement.options).forEach(option => {
                        option.selected = values.includes(option.value);
                    });
                } else if (element) {
                    if (element.type === 'checkbox') {
                        element.checked = data.custom_fields[fieldId];
                    } else {
                        element.value = data.custom_fields[fieldId];
                    }
                }
            });
        }

        // Update datetime fields visibility
        toggleDatetimeFields();

        // Show modal
        $('#eventModal').modal('show');
    })
    .catch(error => {
        console.error('Error fetching event:', error);
        alert('Error loading event data. Please try again.');
    });
}

function toggleDatetimeFields() {
    const allDayCheckbox = document.getElementById('all_day');
    const datetimeFields = document.querySelectorAll('.datetime-field');
    const dateFields = document.querySelectorAll('.date-field');
    const setupReleaseFields = document.querySelector('.setup-release-fields');

    if (allDayCheckbox && allDayCheckbox.checked) {
        // Show date inputs, hide datetime inputs
        datetimeFields.forEach(field => {
            field.style.display = 'none';
            field.removeAttribute('required');
            field.disabled = false; // Keep enabled for form submission
        });
        dateFields.forEach(field => {
            field.style.display = 'block';
            field.setAttribute('required', 'required');
            field.disabled = false;
            // Copy value from datetime field if exists
            const datetimeField = field.previousElementSibling;
            if (datetimeField && datetimeField.value) {
                field.value = datetimeField.value.split('T')[0];
            }
        });
        // Hide setup/release fields for all-day events
        if (setupReleaseFields) {
            setupReleaseFields.style.display = 'none';
        }
    } else {
        // Show datetime inputs, hide date inputs
        datetimeFields.forEach(field => {
            field.style.display = 'block';
            field.setAttribute('required', 'required');
            field.disabled = false;
        });
        dateFields.forEach(field => {
            field.style.display = 'none';
            field.removeAttribute('required');
            field.disabled = true; // Disable so it doesn't submit
            // Copy value to datetime field if exists
            const datetimeField = field.previousElementSibling;
            if (field.value && datetimeField) {
                const currentTime = datetimeField.value ? datetimeField.value.split('T')[1] : '';
                datetimeField.value = field.value + (currentTime ? 'T' + currentTime : '');
            }
        });
        // Show setup/release fields
        if (setupReleaseFields) {
            setupReleaseFields.style.display = 'flex';
        }
    }
}

// Initialize datetime fields toggle on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Events.js: DOM Content Loaded');
    
    const allDayCheckbox = document.getElementById('all_day');
    if (allDayCheckbox) {
        allDayCheckbox.addEventListener('change', toggleDatetimeFields);
        toggleDatetimeFields(); // Initial state
    }

    // Check if we need to reopen the modal after validation error
    // This is set by the controller when redirecting back with errors
    const container = document.querySelector('[data-show-event-modal]');
    console.log('Events.js: Looking for modal flag container:', container);
    
    if (container) {
        console.log('Events.js: Container found, dataset:', container.dataset);
        console.log('Events.js: showEventModal value:', container.dataset.showEventModal);
        
        if (container.dataset.showEventModal === 'true') {
            console.log('Events.js: Opening modal due to validation errors');
            
            // Use setTimeout to ensure modal is ready
            setTimeout(function() {
                if (typeof $ !== 'undefined' && $.fn.modal) {
                    $('#eventModal').modal('show');
                    console.log('Events.js: Modal show command executed');
                    // Update datetime fields visibility based on all_day checkbox state
                    toggleDatetimeFields();
                } else {
                    console.error('Events.js: jQuery or Bootstrap modal not available');
                }
            }, 100);
        }
    } else {
        console.log('Events.js: No modal flag container found');
    }
});