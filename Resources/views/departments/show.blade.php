@extends('cnxevents::layouts.app')

@section('title', 'Department Details')

@section('content')
<div class="container">
    <h1>{{ $department->name }}</h1>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Department Information</h5>
            <p><strong>Name:</strong> {{ $department->name }}</p>
            <p><strong>Description:</strong> {{ $department->description }}</p>
            <p><strong>Created:</strong> {{ $department->created_at->format('Y-m-d H:i') }}</p>
            <p><strong>Updated:</strong> {{ $department->updated_at->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    <a href="{{ route('cnxevents.departments.edit', $department->id) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('cnxevents.departments.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection