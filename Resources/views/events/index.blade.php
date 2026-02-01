@extends('cnxevents::layouts.app')

@section('title', 'Events')

@section('stylesheets')
<style>
.table tbody tr {
    transition: background-color 0.2s ease;
}
.table tbody tr:hover {
    background-color: #f0f8ff !important;
}
</style>
@endsection

@section('content')
<div class="container" @if(old('title')) data-reopen-modal="true" @endif>
    <h1>Events</h1>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif
    
    <button class="btn btn-primary" data-toggle="modal" data-target="#eventModal" data-backdrop="false" style="margin-bottom: 20px;">Add Event</button>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="">Active Events</option>
                    <option value="request" {{ request('status') == 'request' ? 'selected' : '' }}>Request</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="venue_id" class="form-control">
                    <option value="">All Venues</option>
                    @foreach($venues as $venue)
                        <option value="{{ $venue->id }}" {{ request('venue_id') == $venue->id ? 'selected' : '' }}>{{ $venue->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Events Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Client</th>
                <th>Venue</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
                <tr>
                    <td>{{ $event->title }}</td>
                    <td>{{ $event->client_name }}</td>
                    <td>
                        <span class="label" style="background-color: {{ $event->venue->color ?? '#3c8dbc' }}; color: white;">
                            {{ $event->venue->name }}
                        </span>
                    </td>
                    <td>{{ $event->start_datetime->format('d-m-Y H:i') }}</td>
                    <td>{{ $event->end_datetime->format('d-m-Y H:i') }}</td>
                    <td>
                        @php
                            $labelClass = $event->status === 'confirmed' ? 'success' : ($event->status === 'cancelled' ? 'default' : 'warning');
                        @endphp
                        <span class="label label-{{ $labelClass }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info edit-event-btn" data-event-id="{{ $event->id }}">Edit</button>
                        
                        @if($event->status === 'cancelled')
                            {{-- Activate button for cancelled events --}}
                            <form method="POST" action="{{ route('cnxevents.events.activate', $event->id) }}" style="display:inline;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <button type="submit" class="btn btn-sm btn-success">Activate</button>
                            </form>
                        @else
                            {{-- Cancel button for active events --}}
                            <form method="POST" action="{{ route('cnxevents.events.cancel', $event->id) }}" style="display:inline;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <button type="submit" class="btn btn-sm btn-warning cancel-event-btn">Cancel</button>
                            </form>
                            
                            {{-- Confirm button for request status --}}
                            @if($event->status == 'request')
                                <form method="POST" action="{{ route('cnxevents.events.confirm', $event->id) }}" style="display:inline;">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                    <button type="submit" class="btn btn-sm btn-success confirm-event-btn">Confirm</button>
                                </form>
                            @endif
                        @endif
                        
                        {{-- Delete button only for admins --}}
                        @if(Auth::user()->isAdmin())
                            <form method="POST" action="{{ route('cnxevents.events.destroy', $event->id) }}" style="display:inline;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <input type="hidden" name="_method" value="DELETE" />
                                <button type="submit" class="btn btn-sm btn-danger delete-event-btn">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $events->links() }}
</div>

@include('cnxevents::modals.event-modal')

@endsection
