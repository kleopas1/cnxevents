<h3>Custom Fields</h3>
<button class="btn btn-primary mb-3" data-toggle="modal" data-target="#customFieldModal"
    data-backdrop="false" id="addCustomFieldBtn">Add Custom Field</button>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Order</th>
            <th>Name</th>
            <th>Type</th>
            <th>Required</th>
            <th>Departments</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($customFields as $field)
            <tr>
                <td>{{ $field->position }}</td>
                <td>{{ $field->name }}</td>
                <td>{{ $field->getTypeLabel() }}</td>
                <td>{{ $field->is_required ? 'Yes' : 'No' }}</td>
                <td>{{ $field->departments->pluck('name')->implode(', ') }}</td>
                <td>
                    <button class="btn btn-sm btn-info edit-custom-field-btn" data-toggle="modal"
                        data-target="#customFieldModal" data-backdrop="false"
                        data-custom-field-id="{{ $field->id }}">Edit</button>
                    <form method="POST" action="{{ route('cnxevents.custom-fields.destroy', $field->id) }}"
                        style="display:inline;" class="delete-custom-field-form">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="_method" value="DELETE" />
                        <button type="submit" class="btn btn-sm btn-danger delete-custom-field-btn"
                            data-field-name="{{ $field->name }}">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Custom Field Modal -->
<div class="modal fade" id="customFieldModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customFieldModalTitle">Add Custom Field</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customFieldForm" method="POST" action="{{ route('cnxevents.custom-fields.store') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" class="form-control" required>
                            <option value="text">Text</option>
                            <option value="select">Single Select</option>
                            <option value="multiselect">Multi Select</option>
                            <option value="date">Date</option>
                            <option value="integer">Integer Number</option>
                            <option value="decimal">Decimal Number</option>
                        </select>
                    </div>
                    <div class="form-group" id="optionsGroup" style="display: none;">
                        <label>Options (one per line)</label>
                        <textarea name="options" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_required" class="form-check-input" id="is_required">
                        <label class="form-check-label" for="is_required">Required</label>
                    </div>
                    <div class="form-group">
                        <label>Position</label>
                        <input type="number" name="position" class="form-control" min="0" value="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                    <div class="form-group">
                        <label>Departments <span style="color: #dc3545; font-weight: bold;">*</span></label>
                        <div>
                            @foreach ($departments ?? [] as $department)
                                <div class="form-check">
                                    <input type="checkbox" name="departments[]" value="{{ $department->id }}"
                                        id="department-{{ $department->id }}" class="form-check-input department-checkbox">
                                    <label class="form-check-label" for="department-{{ $department->id }}">{{ $department->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Select at least one department</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" form="customFieldForm" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>