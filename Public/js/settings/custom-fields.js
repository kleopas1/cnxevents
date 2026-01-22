function resetCustomFieldForm() {
    const form = document.getElementById('customFieldForm');

    form.action = '/cnxevents/custom-fields';

    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.remove();
    }

    document.getElementById('customFieldModalTitle').textContent = 'Add Custom Field';
    form.reset();

    document.querySelector('#customFieldModal [name="type"]').value = 'text';
    document.querySelector('#customFieldModal [name="position"]').value = '0';
    document.getElementById('optionsGroup').style.display = 'none';
}

function editCustomField(id) {
    fetch(`/cnxevents/custom-fields/${id}`, {
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
            document.getElementById('customFieldForm').action = `/cnxevents/custom-fields/${id}`;
            let methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            document.getElementById('customFieldForm').appendChild(methodInput);
            document.getElementById('customFieldModalTitle').textContent = 'Edit Custom Field';
            document.querySelector('#customFieldModal [name="name"]').value = data.name;
            document.querySelector('#customFieldModal [name="type"]').value = data.type;
            document.querySelector('#customFieldModal [name="position"]').value = data.position || 0;
            document.querySelector('#customFieldModal [name="is_required"]').checked = data.is_required;

            // Show/hide options group based on type
            const optionsGroup = document.getElementById('optionsGroup');
            if (data.type === 'select' || data.type === 'multiselect') {
                optionsGroup.style.display = 'block';
                document.querySelector('#customFieldModal [name="options"]').value = data.options ? data.options
                    .join('\n') : '';
            } else {
                optionsGroup.style.display = 'none';
                document.querySelector('#customFieldModal [name="options"]').value = '';
            }
            // Check departments
            data.departments.forEach(dept => {
                let checkbox = document.querySelector(
                    `#customFieldModal input[name="departments[]"][value="${dept.id}"]`);
                if (checkbox) checkbox.checked = true;
            });
        })
        .catch(error => {
            console.error('Error fetching custom field:', error);
            alert('Error loading custom field data. Please try again.');
        });
}

// Show options for select type
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('customFieldModal');
    const typeSelect = modal.querySelector('select[name="type"]');
    const optionsGroup = modal.querySelector('#optionsGroup');

    function updateOptionsVisibility() {
        if (typeSelect.value === 'select' || typeSelect.value === 'multiselect') {
            optionsGroup.style.display = 'block';
        } else {
            optionsGroup.style.display = 'none';
        }
    }

    // Reset form when modal is opened for adding new custom field
    $('#customFieldModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        if (!button.hasClass('edit-custom-field-btn')) {
            // Reset form for new custom field
            customFieldForm.reset();
            customFieldForm.action = '/cnxevents/custom-fields';
            document.getElementById('customFieldModalTitle').textContent = 'Add Custom Field';
            
            // Clear any existing method input
            const methodInput = customFieldForm.querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.remove();
            }
            
            // Clear any error messages
            const errorElements = customFieldForm.querySelectorAll('.text-danger');
            errorElements.forEach(el => el.remove());
            
            // Reset options visibility
            updateOptionsVisibility();
        }
    });

    // Form validation and AJAX submission
    const customFieldForm = document.getElementById('customFieldForm');
    if (customFieldForm) {
        customFieldForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Client-side validation
            const departmentCheckboxes = customFieldForm.querySelectorAll('input[name="departments[]"]:checked');
            if (departmentCheckboxes.length === 0) {
                alert('Please select at least one department.');
                return false;
            }

            // Clear previous errors
            const errorElements = customFieldForm.querySelectorAll('.text-danger');
            errorElements.forEach(el => el.remove());

            // Submit via AJAX
            const formData = new FormData(customFieldForm);

            fetch(customFieldForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success - close modal and reload page
                    $('#customFieldModal').modal('hide');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = customFieldForm.querySelector(`[name="${field}"], [name="${field}[]"]`);
                            if (input) {
                                // For checkboxes, find the parent container
                                let container = input;
                                if (input.type === 'checkbox') {
                                    container = input.closest('.form-group');
                                }
                                
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'text-danger';
                                errorDiv.textContent = data.errors[field][0];
                                container.appendChild(errorDiv);
                            }
                        });
                    } else {
                        alert(data.message || 'An error occurred. Please try again.');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }

    // When type changes
    typeSelect.addEventListener('change', updateOptionsVisibility);

    // When modal opens (important for edit mode)
    $('#customFieldModal').on('shown.bs.modal', function () {
        updateOptionsVisibility();
    });

});

// Event listeners for custom fields
document.addEventListener('DOMContentLoaded', function() {
    const addCustomFieldBtn = document.getElementById('addCustomFieldBtn');
    if (addCustomFieldBtn) {
        addCustomFieldBtn.addEventListener('click', resetCustomFieldForm);
    }

    document.querySelectorAll('.edit-custom-field-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            editCustomField(this.getAttribute('data-custom-field-id'));
        });
    });

    // Delete confirmation
    document.querySelectorAll('.delete-custom-field-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const fieldName = this.getAttribute('data-field-name');
            if (!confirm(`Are you sure you want to delete the custom field "${fieldName}"?`)) {
                e.preventDefault();
            }
        });
    });
});