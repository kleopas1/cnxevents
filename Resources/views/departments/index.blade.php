@extends('cnxevents::layouts.app')

@section('title', 'Departments')

@section('content')
<div class="container">
    <h1>Departments</h1>
    <a href="{{ route('cnxevents.departments.create') }}" class="btn btn-primary mb-3">Add Department</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $department)
                <tr>
                    <td>{{ $department->name }}</td>
                    <td>{{ $department->description }}</td>
                    <td>
                        <a href="{{ route('cnxevents.departments.show', $department->id) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('cnxevents.departments.edit', $department->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form method="POST" action="{{ route('cnxevents.departments.destroy', $department->id) }}" style="display:inline;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $departments->links() }}
</div>
@endsection