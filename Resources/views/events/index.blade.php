@extends('cnxevents::layouts.app')

@section('title', 'Events')

@section('content')
<div class="container">
    <h1>Events</h1>
    <button class="btn btn-primary" data-toggle="modal" data-target="#eventModal" data-backdrop="false">Add Event</button>

    <!-- Filters -->
    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="request" {{ request('status') == 'request' ? 'selected' : '' }}>Request</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
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
                    <td>{{ $event->venue->name }}</td>
                    <td>{{ $event->start_datetime->format('Y-m-d H:i') }}</td>
                    <td>{{ $event->end_datetime->format('Y-m-d H:i') }}</td>
                    <td>{{ ucfirst($event->status) }}</td>
                    <td>
                        <button class="btn btn-sm btn-info edit-event-btn" data-toggle="modal" data-target="#eventModal" data-backdrop="false" data-event-id="{{ $event->id }}">Edit</button>
                        <form method="POST" action="{{ route('cnxevents.events.destroy', $event->id) }}" style="display:inline;">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <input type="hidden" name="_method" value="DELETE" />
                            <button type="submit" class="btn btn-sm btn-danger delete-event-btn">Delete</button>
                        </form>
                        @if($event->status == 'request')
                            <form method="POST" action="{{ route('cnxevents.events.confirm', $event->id) }}" style="display:inline;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <button type="submit" class="btn btn-sm btn-success confirm-event-btn">Confirm</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $events->links() }}
</div>

<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" data-backdrop="false" style="z-index: 100000;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="eventForm" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Event</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Venue</label>
                        <select name="venue_id" class="form-control" required>
                            @foreach($venues ?? [] as $venue)
                                <option value="{{ $venue->id }}">{{ $venue->name }}{{ $venue->capacity ? ' (' . $venue->capacity . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="all_day" class="form-check-input" id="all_day">
                        <label class="form-check-label" for="all_day">All Day</label>
                    </div>
                    <div class="row" id="datetimeFields">
                        <div class="col-md-6">
                            <label>Start Datetime</label>
                            <input type="datetime-local" name="start_datetime" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>End Datetime</label>
                            <input type="datetime-local" name="end_datetime" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Setup Datetime</label>
                            <input type="datetime-local" name="setup_datetime" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Venue Release Datetime</label>
                            <input type="datetime-local" name="venue_release_datetime" class="form-control">
                        </div>
                    </div>                    
                    <!-- Client Fields -->
                    <div class="row" id="clientFields">
                    <div class="col-md-6 form-group">
                        <label>Client Name</label>
                        <input type="text" name="client_name" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Client Email</label>
                        <input type="email" name="client_email" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Client Phone</label>
                        <input type="text" name="client_phone" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Client Company</label>
                        <input type="text" name="client_company" class="form-control">
                    </div>
                    </div>
                    <!-- Custom Fields -->
                    @foreach($customFields ?? [] as $field)
                        <div class="form-group">
                            <label>{{ $field->name }} @if($field->is_required)*@endif</label>
                            @if($field->type == 'text')
                                <input type="text" name="custom_field_{{ $field->id }}" class="form-control" @if($field->is_required) required @endif>
                            @elseif($field->type == 'select')
                                <select name="custom_field_{{ $field->id }}" class="form-control" @if($field->is_required) required @endif>
                                    <option value="">Select</option>
                                    @foreach($field->options ?? [] as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            @elseif($field->type == 'multiselect')
                                <select name="custom_field_{{ $field->id }}[]" class="form-control" multiple style="height: auto; min-height: 120px;" @if($field->is_required) required @endif>
                                    @foreach($field->options ?? [] as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Hold Ctrl (Cmd on Mac) to select multiple options</small>
                            @elseif($field->type == 'date')
                                <input type="date" name="custom_field_{{ $field->id }}" class="form-control" @if($field->is_required) required @endif>
                            @elseif($field->type == 'integer')
                                <input type="number" step="1" name="custom_field_{{ $field->id }}" class="form-control" @if($field->is_required) required @endif>
                            @elseif($field->type == 'decimal')
                                <input type="number" step="0.01" name="custom_field_{{ $field->id }}" class="form-control" @if($field->is_required) required @endif>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ \Module::asset('cnxevents:js/events.js') }}"></script>
@endsection