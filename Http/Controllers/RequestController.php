<?php

namespace Modules\CnxEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CnxEvents\Entities\Event;
use Modules\CnxEvents\Entities\Venue;

class RequestController extends Controller
{
    /**
     * Display a listing of the requests.
     * @return Response
     */
    public function index(Request $request)
    {
        $query = Event::requests()->with('venue', 'user');

        // Filters
        if ($request->filled('venue_id')) {
            $query->where('venue_id', $request->venue_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('start_date')) {
            $query->where('start_datetime', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('end_datetime', '<=', $request->end_date);
        }

        $requests = $query->paginate(10);
        $venues = Venue::all();
        $users = \App\User::all();

        return view('cnxevents::requests.index', compact('requests', 'venues', 'users'));
    }

    /**
     * Confirm a request to event.
     */
    public function confirm($id)
    {
        $event = Event::findOrFail($id);
        if ($event->status === 'request') {
            $event->update(['status' => 'confirmed']);
            return redirect()->back()->with('success', 'Request confirmed successfully.');
        }
        return redirect()->back()->with('error', 'Invalid request.');
    }
}