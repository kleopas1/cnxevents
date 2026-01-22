@extends('cnxevents::layouts.app')

@section('title', 'Edit Department')

@section('content')
<div class="container">
    <h1>Edit Department</h1>

    <form method="POST" action="{{ route('cnxevents.departments.update', $department->id) }}">
        @csrf @method('PUT')

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" class="form-control" value="{{ $department->name }}" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" class="form-control">{{ $department->description }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Department</button>
        <a href="{{ route('cnxevents.departments.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection