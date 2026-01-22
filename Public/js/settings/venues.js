function resetVenueForm() {
    const form = document.getElementById('venueForm');

    form.action = '/cnxevents/venues';

    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) {
        methodInput.remove();
    }

    document.getElementById('venueModalTitle').textContent = 'Add Venue';
    form.reset();
}

function editVenue(id) {
    fetch(`/cnxevents/venues/${id}`, {
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
            document.getElementById('venueForm').action = `/cnxevents/venues/${id}`;
            let methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            document.getElementById('venueForm').appendChild(methodInput);
            document.getElementById('venueModalTitle').textContent = 'Edit Venue';
            document.querySelector('#venueModal [name="name"]').value = data.name;
            document.querySelector('#venueModal [name="capacity"]').value = data.capacity;
        })
        .catch(error => {
            console.error('Error fetching venue:', error);
            alert('Error loading venue data. Please try again.');
        });
}

// Event listeners for venues
document.addEventListener('DOMContentLoaded', function() {
    const addVenueBtn = document.getElementById('addVenueBtn');
    if (addVenueBtn) {
        addVenueBtn.addEventListener('click', resetVenueForm);
    }

    document.querySelectorAll('.edit-venue-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            editVenue(this.getAttribute('data-venue-id'));
        });
    });

    document.querySelectorAll('.delete-venue-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const venueName = this.getAttribute('data-venue-name');
            if (!confirm(`Are you sure you want to delete the venue "${venueName}"?`)) {
                e.preventDefault();
            }
        });
    });
});