<h3>Departments</h3>
<button class="btn btn-primary mb-3" data-toggle="modal" data-target="#departmentModal"
    data-backdrop="false" id="addDepartmentBtn">Add Department</button>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($departments as $department)
            <tr>
                <td>{{ $department->name }}</td>
                <td>
                    <button class="btn btn-sm btn-info edit-department-btn" data-toggle="modal"
                        data-target="#departmentModal" data-backdrop="false"
                        data-department-id="{{ $department->id }}">Edit</button>
                    <form method="POST"
                        action="{{ route('cnxevents.departments.destroy', $department->id) }}"
                        style="display:inline;" class="delete-department-form">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="_method" value="DELETE" />
                        <button type="submit" class="btn btn-sm btn-danger delete-department-btn"
                            data-department-name="{{ $department->name }}">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Department Modal -->
<div class="modal fade" id="departmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="departmentModalTitle">Add Department</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="departmentForm" method="POST" action="{{ route('cnxevents.departments.store') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" form="departmentForm" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>