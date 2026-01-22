function resetDepartmentForm() {
    const form = document.getElementById('departmentForm');

    form.action = '/cnxevents/departments';

    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.remove();
    }

    document.getElementById('departmentModalTitle').textContent = 'Add Department';
    form.reset();
}

function editDepartment(id) {
    fetch(`/cnxevents/departments/${id}`, {
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
            document.getElementById('departmentForm').action = `/cnxevents/departments/${id}`;
            let methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            document.getElementById('departmentForm').appendChild(methodInput);
            document.getElementById('departmentModalTitle').textContent = 'Edit Department';
            document.querySelector('#departmentModal [name="name"]').value = data.name;
            document.querySelector('#departmentModal [name="description"]').value = data.description || '';
        })
        .catch(error => {
            console.error('Error fetching department:', error);
            alert('Error loading department data. Please try again.');
        });
}

// Event listeners for departments
document.addEventListener('DOMContentLoaded', function() {
    const addDepartmentBtn = document.getElementById('addDepartmentBtn');
    if (addDepartmentBtn) {
        addDepartmentBtn.addEventListener('click', resetDepartmentForm);
    }

    document.querySelectorAll('.edit-department-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            editDepartment(this.getAttribute('data-department-id'));
        });
    });

    // Delete confirmation
    document.querySelectorAll('.delete-department-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const departmentName = this.getAttribute('data-department-name');
            if (!confirm(`Are you sure you want to delete the department "${departmentName}"?`)) {
                e.preventDefault();
            }
        });
    });
});