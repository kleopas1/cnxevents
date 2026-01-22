<h3>Venues</h3>
<button class="btn btn-primary mb-3" data-toggle="modal" data-target="#venueModal" data-backdrop="false"
    id="addVenueBtn">Add Venue</button>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Color</th>
            <th>Capacity</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($venues as $venue)
            <tr>
                <td>{{ $venue->name }}</td>
                <td>
                    <span style="display: inline-block; width: 30px; height: 20px; background-color: {{ $venue->color }}; border: 1px solid #ccc; border-radius: 3px;"></span>
                    {{ $venue->color }}
                </td>
                <td>{{ $venue->capacity }}</td>
                <td>
                    <button class="btn btn-sm btn-info edit-venue-btn" data-toggle="modal"
                        data-target="#venueModal" data-backdrop="false"
                        data-venue-id="{{ $venue->id }}">Edit</button>
                    <form method="POST" action="{{ route('cnxevents.venues.destroy', $venue->id) }}"
                        style="display:inline;">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="_method" value="DELETE" />
                        <button type="submit" class="btn btn-danger btn-sm delete-venue-btn"
                            data-venue-name="{{ $venue->name }}">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Venue Modal -->
<div class="modal fade" id="venueModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="venueModalTitle">Add Venue</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="venueForm" method="POST" action="{{ route('cnxevents.venues.store') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <input type="color" name="color" class="form-control" value="#3c8dbc" style="height: 40px;">
                    </div>
                    <div class="form-group">
                        <label>Capacity</label>
                        <input type="number" name="capacity" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" form="venueForm" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>