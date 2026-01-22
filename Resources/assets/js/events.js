// Events page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Add Event button
    const addEventBtn = document.querySelector('button[data-target="#eventModal"]');
    if (addEventBtn) {
        addEventBtn.addEventListener('click', function() {
            resetForm();
        });
    }

    // Edit Event buttons
    document.querySelectorAll('.edit-event-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
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
    fetch(`/cnxevents/events/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
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

        // Update modal title
        document.getElementById('modalTitle').textContent = 'Edit Event';

        // Populate form fields
        Object.keys(data).forEach(key => {
            const element = form.querySelector(`[name="${key}"]`);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = data[key];
                } else {
                    element.value = data[key];
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
    const datetimeFields = document.getElementById('datetimeFields');

    if (allDayCheckbox && datetimeFields) {
        if (allDayCheckbox.checked) {
            datetimeFields.style.display = 'none';
        } else {
            datetimeFields.style.display = 'block';
        }
    }
}

// Initialize datetime fields toggle on page load
document.addEventListener('DOMContentLoaded', function() {
    const allDayCheckbox = document.getElementById('all_day');
    if (allDayCheckbox) {
        allDayCheckbox.addEventListener('change', toggleDatetimeFields);
        toggleDatetimeFields(); // Initial state
    }
});