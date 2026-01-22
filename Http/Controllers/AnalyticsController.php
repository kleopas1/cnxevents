<?php

namespace Modules\CnxEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CnxEvents\Entities\Event;
use Modules\CnxEvents\Entities\Venue;

class AnalyticsController extends Controller
{
    /**
     * Display analytics.
     * @return Response
     */
    public function index()
    {
        // KPIs
        $totalEvents = Event::count();
        $confirmedEvents = Event::confirmed()->count();
        $requestEvents = Event::requests()->count();
        $venueUtilization = Venue::withCount('events')->get();

        // Charts data (for Chart.js)
        $eventsByMonth = Event::selectRaw('MONTH(start_datetime) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        return view('cnxevents::analytics.index', compact('totalEvents', 'confirmedEvents', 'requestEvents', 'venueUtilization', 'eventsByMonth'));
    }

    /**
     * Get status data for charts.
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusData()
    {
        $data = Event::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'labels' => array_keys($data->toArray()),
            'values' => array_values($data->toArray())
        ]);
    }

    /**
     * Get venue data for charts.
     * @return \Illuminate\Http\JsonResponse
     */
    public function venueData()
    {
        $data = Venue::withCount('events')
            ->orderBy('events_count', 'desc')
            ->pluck('events_count', 'name');

        return response()->json([
            'labels' => array_keys($data->toArray()),
            'values' => array_values($data->toArray())
        ]);
    }

    /**
     * Get monthly data for charts.
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthlyData()
    {
        $data = Event::selectRaw('MONTH(start_datetime) as month, YEAR(start_datetime) as year, COUNT(*) as count')
            ->whereRaw('start_datetime >= DATE_SUB(NOW(), INTERVAL 12 MONTH)')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                $monthName = date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                return [$monthName => $item->count];
            });

        return response()->json([
            'labels' => array_keys($data->toArray()),
            'values' => array_values($data->toArray())
        ]);
    }
}

