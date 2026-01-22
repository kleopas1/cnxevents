<div class="day-view">
    {{-- Header row --}}
    <div class="day-row">
        <div class="time-slot-label" style="background: #3c8dbc; border-color: #2e6da4;"></div>
        <div class="day-header {{ $isToday ? 'today' : '' }}">
            {{ $currentDate->format('l, F j, Y') }}
        </div>
    </div>

    {{-- Time slots --}}
    @foreach($timeSlots as $slotIndex => $time)
        <div class="day-row">
            <div class="time-slot-label">{{ \Carbon\Carbon::createFromFormat('H:i', $time)->format('g A') }}</div>
            
            <div class="time-slot-cell {{ $isToday ? 'today' : '' }}">
                @if($slotIndex === 0)
                    {{-- Calculate overlaps for all events --}}
                    @php
                        $eventsArray = $dayEvents->all();
                        $eventPositions = [];
                        
                        foreach ($eventsArray as $index => $event) {
                            $startMinutes = $event->start_datetime->hour * 60 + $event->start_datetime->minute;
                            $endMinutes = $event->end_datetime->hour * 60 + $event->end_datetime->minute;
                            
                            // Find overlapping events
                            $overlaps = [];
                            foreach ($eventsArray as $otherIndex => $otherEvent) {
                                if ($index !== $otherIndex) {
                                    $otherStart = $otherEvent->start_datetime->hour * 60 + $otherEvent->start_datetime->minute;
                                    $otherEnd = $otherEvent->end_datetime->hour * 60 + $otherEvent->end_datetime->minute;
                                    
                                    // Check if they overlap
                                    if ($startMinutes < $otherEnd && $endMinutes > $otherStart) {
                                        $overlaps[] = $otherIndex;
                                    }
                                }
                            }
                            
                            // Calculate position in overlap group
                            $overlapsWithSelf = array_merge([$index], $overlaps);
                            sort($overlapsWithSelf);
                            $positionInGroup = array_search($index, $overlapsWithSelf);
                            $totalInGroup = count($overlapsWithSelf);
                            
                            $eventPositions[$index] = [
                                'column' => $positionInGroup,
                                'totalColumns' => $totalInGroup
                            ];
                        }
                    @endphp
                    
                    {{-- Render all events --}}
                    @foreach($dayEvents as $eventIndex => $event)
                        @php
                            // Calculate setup time (if exists)
                            $hasSetup = $event->setup_datetime && $event->setup_datetime < $event->start_datetime;
                            $hasRelease = $event->venue_release_datetime && $event->venue_release_datetime > $event->end_datetime;
                            
                            // Determine the actual start and end for the entire block
                            $blockStart = $hasSetup ? $event->setup_datetime : $event->start_datetime;
                            $blockEnd = $hasRelease ? $event->venue_release_datetime : $event->end_datetime;
                            
                            // Calculate positions
                            $blockStartHour = $blockStart->hour;
                            $blockStartMinute = $blockStart->minute;
                            $blockEndHour = $blockEnd->hour;
                            $blockEndMinute = $blockEnd->minute;
                            
                            // Top position (from setup or start)
                            $topPosition = (($blockStartHour - 6) * 60) + ($blockStartMinute);
                            
                            // Total height of entire block
                            $totalMinutes = ($blockEndHour - $blockStartHour) * 60 + ($blockEndMinute - $blockStartMinute);
                            $totalHeight = max($totalMinutes, 25);
                            
                            // Calculate section heights
                            $setupMinutes = $hasSetup ? 
                                (($event->start_datetime->hour - $blockStartHour) * 60 + ($event->start_datetime->minute - $blockStartMinute)) : 0;
                            $eventMinutes = (($event->end_datetime->hour - $event->start_datetime->hour) * 60 + 
                                ($event->end_datetime->minute - $event->start_datetime->minute));
                            $releaseMinutes = $hasRelease ? 
                                (($blockEndHour - $event->end_datetime->hour) * 60 + ($blockEndMinute - $event->end_datetime->minute)) : 0;
                            
                            // Get overlap positioning
                            $position = $eventPositions[$eventIndex];
                            $widthPercent = (100 / $position['totalColumns']) - 1;
                            $leftPercent = ($position['column'] * (100 / $position['totalColumns']));
                            
                            // Venue color and status
                            $venueColor = $event->venue ? $event->venue->color : '#3c8dbc';
                            $statusClass = strtolower($event->status) === 'request' ? 'event-request' : '';
                            
                            // Create gradient for setup/event/release sections
                            $setupPercent = $totalHeight > 0 ? ($setupMinutes / $totalMinutes * 100) : 0;
                            $eventPercent = $totalHeight > 0 ? ($eventMinutes / $totalMinutes * 100) : 100;
                            $releasePercent = $totalHeight > 0 ? ($releaseMinutes / $totalMinutes * 100) : 0;
                            
                            // Lighter color for setup/release (add opacity)
                            $lightColor = $venueColor . '40'; // Add alpha for lighter shade
                            
                            // Build background gradient
                            $backgroundStyle = '';
                            if ($hasSetup || $hasRelease) {
                                $gradientStops = [];
                                $currentPercent = 0;
                                
                                if ($hasSetup) {
                                    $gradientStops[] = "{$lightColor} 0%";
                                    $gradientStops[] = "{$lightColor} {$setupPercent}%";
                                    $currentPercent = $setupPercent;
                                }
                                
                                $gradientStops[] = "{$venueColor} {$currentPercent}%";
                                $currentPercent += $eventPercent;
                                $gradientStops[] = "{$venueColor} {$currentPercent}%";
                                
                                if ($hasRelease) {
                                    $gradientStops[] = "{$lightColor} {$currentPercent}%";
                                    $gradientStops[] = "{$lightColor} 100%";
                                }
                                
                                $backgroundStyle = 'background: linear-gradient(to bottom, ' . implode(', ', $gradientStops) . ');';
                            } else {
                                $backgroundStyle = "background-color: {$venueColor};";
                            }
                        @endphp
                        
                        <a href="{{ route('cnxevents.events.show', $event->id) }}" 
                           class="day-event event-venue-color {{ $statusClass }}"
                           style="{{ $backgroundStyle }} top: {{ $topPosition }}px; height: {{ $totalHeight }}px; left: {{ $leftPercent }}%; width: {{ $widthPercent }}%; right: auto;"
                           title="{{ $event->title }} ({{ $event->start_datetime->format('g:i A') }} - {{ $event->end_datetime->format('g:i A') }}) - {{ $event->venue ? $event->venue->name : 'No Venue' }}">
                            <div style="padding-top: {{ $setupMinutes }}px; height: 100%; box-sizing: border-box;">
                                <span class="event-time">
                                    {{ $event->start_datetime->format('g:i A') }} - {{ $event->end_datetime->format('g:i A') }}
                                </span>
                                <span class="event-title">{{ $event->title }}</span>
                                @if($event->venue && $totalHeight > 40 && $position['totalColumns'] <= 2)
                                    <span class="event-venue">
                                        <i class="glyphicon glyphicon-map-marker"></i> {{ $event->venue->name }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach
</div>
