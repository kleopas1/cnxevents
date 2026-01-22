<div class="calendar-grid">
    @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
        <div class="calendar-day-header">{{ $day }}</div>
    @endforeach

    @foreach($calendarDays as $day)
        <div class="calendar-day {{ $day['isOtherMonth'] ? 'other-month' : '' }} {{ $day['isToday'] ? 'today' : '' }}">
            <div class="calendar-day-number">{{ $day['date']->format('j') }}</div>
            
            @foreach($day['events'] as $event)
                @php
                    $venueColor = $event->venue ? $event->venue->color : '#3c8dbc';
                    $statusClass = strtolower($event->status) === 'request' ? 'event-request' : '';
                @endphp
                <a href="{{ route('cnxevents.events.show', $event->id) }}" 
                   class="calendar-event event-venue-color {{ $statusClass }}"
                   style="background-color: {{ $venueColor }};"
                   title="{{ $event->title }} - {{ $event->start_datetime->format('g:i A') }} - {{ $event->venue ? $event->venue->name : 'No Venue' }}">
                    {{ $event->start_datetime->format('g:i A') }} - {{ $event->title }}
                </a>
            @endforeach
        </div>
    @endforeach
</div>
