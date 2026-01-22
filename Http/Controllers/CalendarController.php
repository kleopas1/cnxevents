<?php

namespace Modules\CnxEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CnxEvents\Entities\Event;
use Carbon\Carbon;

class CalendarController extends Controller
{
    /**
     * Display the calendar.
     * @return Response
     */
    public function index(Request $request)
    {
        $view = $request->get('view', 'month'); // month, week, day
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));
        $day = $request->get('day', date('d'));
        
        // Filters
        $filterStatus = $request->get('status');
        $filterVenue = $request->get('venue');
        
        $currentDate = Carbon::createFromDate($year, $month, $day);
        
        // Navigation dates based on view
        switch ($view) {
            case 'day':
                $prevDate = $currentDate->copy()->subDay();
                $nextDate = $currentDate->copy()->addDay();
                $data = $this->getDayView($currentDate, $filterStatus, $filterVenue);
                break;
            case 'week':
                $prevDate = $currentDate->copy()->subWeek();
                $nextDate = $currentDate->copy()->addWeek();
                $data = $this->getWeekView($currentDate, $filterStatus, $filterVenue);
                break;
            case 'month':
            default:
                $prevDate = $currentDate->copy()->subMonth();
                $nextDate = $currentDate->copy()->addMonth();
                $data = $this->getMonthView($currentDate, $filterStatus, $filterVenue);
                break;
        }
        
        // Get venues for filter dropdown
        $venues = \Modules\CnxEvents\Entities\Venue::orderBy('name')->get();
        
        return view('cnxevents::calendar', array_merge([
            'view' => $view,
            'currentDate' => $currentDate,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'filterStatus' => $filterStatus,
            'filterVenue' => $filterVenue,
            'venues' => $venues,
        ], $data));
    }

    /**
     * Get month view data
     */
    private function getMonthView($date, $filterStatus = null, $filterVenue = null)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        // Get start of calendar (Sunday of the week containing the 1st)
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        
        // Get end of calendar (Saturday of the week containing the last day)
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);
        
        // Get all events for this period with filters
        $eventsQuery = Event::with('venue')
            ->where(function($query) use ($startOfCalendar, $endOfCalendar) {
                $query->whereBetween('start_datetime', [$startOfCalendar, $endOfCalendar])
                    ->orWhere(function($q) use ($startOfCalendar, $endOfCalendar) {
                        $q->where('start_datetime', '<=', $startOfCalendar)
                          ->where('end_datetime', '>=', $startOfCalendar);
                    });
            });
        
        if ($filterStatus) {
            $eventsQuery->where('status', $filterStatus);
        }
        
        if ($filterVenue) {
            $eventsQuery->where('venue_id', $filterVenue);
        }
        
        $events = $eventsQuery->orderBy('start_datetime')->get();
        
        // Group events by date
        $eventsByDate = [];
        foreach ($events as $event) {
            $dateKey = $event->start_datetime->format('Y-m-d');
            if (!isset($eventsByDate[$dateKey])) {
                $eventsByDate[$dateKey] = [];
            }
            $eventsByDate[$dateKey][] = $event;
        }
        
        // Build calendar days array
        $days = [];
        $currentDate = $startOfCalendar->copy();
        $today = Carbon::today();
        
        while ($currentDate <= $endOfCalendar) {
            $dateKey = $currentDate->format('Y-m-d');
            
            $days[] = [
                'date' => $currentDate->copy(),
                'isOtherMonth' => $currentDate->month !== $date->month,
                'isToday' => $currentDate->isSameDay($today),
                'events' => $eventsByDate[$dateKey] ?? [],
            ];
            
            $currentDate->addDay();
        }
        
        return ['calendarDays' => $days];
    }

    /**
     * Get week view data
     */
    private function getWeekView($date, $filterStatus = null, $filterVenue = null)
    {
        $startOfWeek = $date->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfWeek = $date->copy()->endOfWeek(Carbon::SATURDAY);
        
        // Get events for this week with filters
        $eventsQuery = Event::with('venue')
            ->where(function($query) use ($startOfWeek, $endOfWeek) {
                $query->whereBetween('start_datetime', [$startOfWeek, $endOfWeek])
                    ->orWhere(function($q) use ($startOfWeek, $endOfWeek) {
                        $q->where('start_datetime', '<=', $startOfWeek)
                          ->where('end_datetime', '>=', $startOfWeek);
                    });
            });
        
        if ($filterStatus) {
            $eventsQuery->where('status', $filterStatus);
        }
        
        if ($filterVenue) {
            $eventsQuery->where('venue_id', $filterVenue);
        }
        
        $events = $eventsQuery->orderBy('start_datetime')->get();
        
        // Build week days
        $weekDays = [];
        $currentDate = $startOfWeek->copy();
        $today = Carbon::today();
        
        for ($i = 0; $i < 7; $i++) {
            $dateKey = $currentDate->format('Y-m-d');
            
            // Get events for this day
            $dayEvents = $events->filter(function($event) use ($dateKey) {
                return $event->start_datetime->format('Y-m-d') === $dateKey;
            });
            
            $weekDays[] = [
                'date' => $currentDate->copy(),
                'isToday' => $currentDate->isSameDay($today),
                'events' => $dayEvents,
            ];
            
            $currentDate->addDay();
        }
        
        // Generate time slots (6 AM to 11 PM)
        $timeSlots = [];
        for ($hour = 6; $hour <= 23; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
        }
        
        return [
            'weekDays' => $weekDays,
            'timeSlots' => $timeSlots,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ];
    }

    /**
     * Get day view data
     */
    private function getDayView($date, $filterStatus = null, $filterVenue = null)
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();
        
        // Get events for this day with filters
        $eventsQuery = Event::with('venue')
            ->where(function($query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('start_datetime', [$startOfDay, $endOfDay])
                    ->orWhere(function($q) use ($startOfDay, $endOfDay) {
                        $q->where('start_datetime', '<=', $startOfDay)
                          ->where('end_datetime', '>=', $startOfDay);
                    });
            });
        
        if ($filterStatus) {
            $eventsQuery->where('status', $filterStatus);
        }
        
        if ($filterVenue) {
            $eventsQuery->where('venue_id', $filterVenue);
        }
        
        $events = $eventsQuery->orderBy('start_datetime')->get();
        
        // Generate time slots (6 AM to 11 PM)
        $timeSlots = [];
        for ($hour = 6; $hour <= 23; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
        }
        
        $today = Carbon::today();
        
        return [
            'dayEvents' => $events,
            'timeSlots' => $timeSlots,
            'isToday' => $date->isSameDay($today),
        ];
    }

    /**
     * Get events for calendar (AJAX endpoint - kept for future use).
     * @return Response
     */
    public function events(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $events = Event::whereBetween('start_datetime', [$start, $end])
            ->orWhereBetween('end_datetime', [$start, $end])
            ->get();

        $formattedEvents = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_datetime->toISOString(),
                'end' => $event->end_datetime->toISOString(),
                'url' => route('cnxevents.events.show', $event->id),
            ];
        });

        return response()->json($formattedEvents);
    }
}