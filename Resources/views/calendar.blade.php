@extends('cnxevents::layouts.app')

@section('title', 'Calendar')

@section('stylesheets')
<style>
.calendar-container { margin-top: 20px; }
.calendar-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f5f5f5;
    border-radius: 4px;
}
.calendar-nav { display: flex; gap: 10px; align-items: center; }
.calendar-title { font-size: 20px; font-weight: 600; margin: 0; }
.calendar-view-switcher { display: flex; gap: 5px; }
.calendar-view-switcher .btn { padding: 6px 12px; }
.calendar-grid { 
    display: grid; 
    grid-template-columns: repeat(7, 1fr); 
    gap: 1px; 
    background: #ddd;
    border: 1px solid #ddd;
}
.calendar-day-header {
    background: #3c8dbc;
    color: white;
    padding: 10px;
    text-align: center;
    font-weight: 600;
}
.calendar-day {
    background: white;
    min-height: 120px;
    padding: 8px;
    position: relative;
}
.calendar-day.other-month { background: #f9f9f9; opacity: 0.6; }
.calendar-day.today { background: #e3f2fd; }
.calendar-day-number {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
}
.calendar-event {
    background: #3c8dbc;
    color: white;
    padding: 4px 6px;
    margin: 2px 0;
    border-radius: 3px;
    font-size: 12px;
    cursor: pointer;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.calendar-event:hover { background: #2e6da4; text-decoration: none; color: white; }
.legend { 
    margin-top: 15px; 
    padding: 15px;
    background: #f5f5f5;
    border-radius: 4px;
}
.legend-item { 
    display: inline-block; 
    margin-right: 20px;
    font-size: 13px;
}
.legend-color {
    display: inline-block;
    width: 20px;
    height: 14px;
    margin-right: 5px;
    vertical-align: middle;
    border-radius: 2px;
}
.event-status-confirmed { background: #5cb85c; }
.event-status-request { background: #f0ad4e; }

/* Venue color styles */
.event-venue-color {
    border-radius: 3px;
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.event-venue-color.event-request {
    background-image: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(255,255,255,0.3) 10px,
        rgba(255,255,255,0.3) 20px
    );
}

/* Week and Day Views */
.week-view, .day-view {
    display: table;
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #ddd;
    background: white;
    position: relative;
}
.week-row, .day-row {
    display: table-row;
}
.time-slot-label {
    display: table-cell;
    background: #f5f5f5;
    padding: 8px 12px;
    text-align: right;
    font-size: 11px;
    color: #666;
    border: 1px solid #ddd;
    width: 80px;
    vertical-align: top;
    font-weight: 600;
}
.time-slot-cell {
    display: table-cell;
    background: white;
    height: 60px;
    padding: 0;
    border: 1px solid #ddd;
    position: relative;
    vertical-align: top;
}
.time-slot-cell.today { background: #e3f2fd; }
.week-day-header, .day-header {
    display: table-cell;
    background: #3c8dbc;
    color: white;
    padding: 12px 8px;
    text-align: center;
    font-weight: 600;
    border: 1px solid #2e6da4;
}
.week-day-column, .day-column {
    position: relative;
}
.week-event, .day-event {
    position: absolute;
    left: 4px;
    right: 4px;
    background: #3c8dbc;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    border-left: 4px solid #2e6da4;
    transition: all 0.2s;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    z-index: 10;
}
.week-event:hover, .day-event:hover { 
    filter: brightness(0.9);
    text-decoration: none; 
    color: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    z-index: 20;
}
.event-time { 
    font-weight: 600; 
    display: block; 
    margin-bottom: 2px;
    font-size: 10px;
    white-space: nowrap;
}
.event-title { 
    display: block;
    font-size: 12px;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
}
.event-venue {
    display: block;
    font-size: 9px;
    margin-top: 2px;
    opacity: 0.9;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@endsection

@section('content')
<div class="calendar-container">
    <div class="calendar-header">
        <div class="calendar-nav">
            <a href="{{ route('cnxevents.calendar', array_merge(request()->all(), ['year' => $prevDate->year, 'month' => $prevDate->month, 'day' => $prevDate->day])) }}" class="btn btn-default">
                <i class="glyphicon glyphicon-chevron-left"></i> Previous
            </a>
            <h2 class="calendar-title">
                @if($view === 'month')
                    {{ $currentDate->format('F Y') }}
                @elseif($view === 'week')
                    {{ $currentDate->startOfWeek(Carbon\Carbon::SUNDAY)->format('M j') }} - {{ $currentDate->endOfWeek(Carbon\Carbon::SATURDAY)->format('M j, Y') }}
                @else
                    {{ $currentDate->format('l, F j, Y') }}
                @endif
            </h2>
            <a href="{{ route('cnxevents.calendar', array_merge(request()->all(), ['year' => $nextDate->year, 'month' => $nextDate->month, 'day' => $nextDate->day])) }}" class="btn btn-default">
                Next <i class="glyphicon glyphicon-chevron-right"></i>
            </a>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <div class="calendar-view-switcher">
                <a href="{{ route('cnxevents.calendar', array_merge(request()->except(['year', 'month', 'day']), ['view' => 'month'])) }}" class="btn btn-{{ $view === 'month' ? 'primary' : 'default' }}">
                    Month
                </a>
                <a href="{{ route('cnxevents.calendar', array_merge(request()->except(['year', 'month', 'day']), ['view' => 'week'])) }}" class="btn btn-{{ $view === 'week' ? 'primary' : 'default' }}">
                    Week
                </a>
                <a href="{{ route('cnxevents.calendar', array_merge(request()->except(['year', 'month', 'day']), ['view' => 'day'])) }}" class="btn btn-{{ $view === 'day' ? 'primary' : 'default' }}">
                    Day
                </a>
            </div>
            <a href="{{ route('cnxevents.calendar', ['view' => $view]) }}" class="btn btn-info">Today</a>
            <a href="{{ route('cnxevents.events.create') }}" class="btn btn-success">
                <i class="glyphicon glyphicon-plus"></i> New Event
            </a>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="calendar-filters" style="margin-bottom: 15px; padding: 15px; background: #f5f5f5; border-radius: 4px;">
        <form method="GET" action="{{ route('cnxevents.calendar') }}" id="calendar-filter-form" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <input type="hidden" name="view" value="{{ $view }}">
            <input type="hidden" name="year" value="{{ $currentDate->year }}">
            <input type="hidden" name="month" value="{{ $currentDate->month }}">
            <input type="hidden" name="day" value="{{ $currentDate->day }}">
            
            <div style="display: flex; align-items: center; gap: 8px;">
                <label for="status-filter" style="margin: 0; font-weight: 600;">Status:</label>
                <select name="status" id="status-filter" class="form-control calendar-filter-select" style="width: 150px;">
                    <option value="">All Statuses</option>
                    <option value="Confirmed" {{ $filterStatus === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="request" {{ $filterStatus === 'request' ? 'selected' : '' }}>Request</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <label for="venue-filter" style="margin: 0; font-weight: 600;">Venue:</label>
                <select name="venue" id="venue-filter" class="form-control calendar-filter-select" style="width: 200px;">
                    <option value="">All Venues</option>
                    @foreach($venues as $venue)
                        <option value="{{ $venue->id }}" {{ $filterVenue == $venue->id ? 'selected' : '' }}>
                            {{ $venue->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($filterStatus || $filterVenue)
                <a href="{{ route('cnxevents.calendar', ['view' => $view, 'year' => $currentDate->year, 'month' => $currentDate->month, 'day' => $currentDate->day]) }}" 
                   class="btn btn-default btn-sm">
                    <i class="glyphicon glyphicon-remove"></i> Clear Filters
                </a>
            @endif
        </form>
    </div>

    @if($view === 'month')
        @include('cnxevents::calendar.month')
    @elseif($view === 'week')
        @include('cnxevents::calendar.week')
    @else
        @include('cnxevents::calendar.day')
    @endif

    <div class="legend">
        <strong>Status Legend:</strong>
        <span class="legend-item">
            <span class="legend-color event-status-confirmed"></span> Confirmed
        </span>
        <span class="legend-item">
            <span class="legend-color event-status-request"></span> Request
        </span>
    </div>
</div>

<script{!! \Helper::cspNonceAttr() !!}>
(function() {
    var filterForm = document.getElementById('calendar-filter-form');
    var filterSelects = document.querySelectorAll('.calendar-filter-select');
    
    if (filterForm && filterSelects.length > 0) {
        filterSelects.forEach(function(select) {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }
})();
</script>
@endsection