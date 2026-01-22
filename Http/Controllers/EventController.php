<?php

namespace Modules\CnxEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CnxEvents\Entities\Event;
use Modules\CnxEvents\Entities\Venue;
use Modules\CnxEvents\Entities\CustomField;
use Modules\CnxEvents\Entities\EventCustomFieldValue;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $query = Event::with('venue', 'user');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('venue_id')) {
            $query->where('venue_id', $request->venue_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('start_date')) {
            $query->where('start_datetime', '>=', $request->start_date);
        } else {
            $query->where('start_datetime', '>=', now());
        }
        if ($request->filled('end_date')) {
            $query->where('end_datetime', '<=', $request->end_date);
        }

        $events = $query->paginate(10);
        $venues = Venue::all();
        $users = \App\User::all();
        $customFields = CustomField::all();

        return view('cnxevents::events.index', compact('events', 'venues', 'users', 'customFields'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $venues = Venue::all();
        $customFields = CustomField::with('departments')->get();
        return view('cnxevents::events.create', compact('venues', 'customFields'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'setup_datetime' => 'nullable|date',
            'venue_release_datetime' => 'nullable|date',
            'all_day' => 'boolean',
            'venue_id' => 'required|exists:cnx_venues,id',
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email',
            'client_phone' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
        ]);

        // Prepare event data (exclude custom fields)
        $data = $request->except(array_map(function($field) {
            return 'custom_field_' . $field->id;
        }, CustomField::all()->all()));
        
        // Convert datetime-local format (YYYY-MM-DDTHH:MM) to MySQL format (YYYY-MM-DD HH:MM:SS)
        if ($request->filled('start_datetime')) {
            $data['start_datetime'] = str_replace('T', ' ', $request->start_datetime) . ':00';
        }
        if ($request->filled('end_datetime')) {
            $data['end_datetime'] = str_replace('T', ' ', $request->end_datetime) . ':00';
        }
        if ($request->filled('setup_datetime')) {
            $data['setup_datetime'] = str_replace('T', ' ', $request->setup_datetime) . ':00';
        }
        if ($request->filled('venue_release_datetime')) {
            $data['venue_release_datetime'] = str_replace('T', ' ', $request->venue_release_datetime) . ':00';
        }
        
        $data['user_id'] = auth()->id();
        $data['status'] = 'request';

        $event = Event::create($data);
        
        // Handle custom fields - save to relational table
        $customFields = CustomField::all();
        foreach ($customFields as $field) {
            $key = 'custom_field_' . $field->id;
            if ($request->has($key)) {
                $value = $request->input($key);
                // Handle multiselect arrays
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                EventCustomFieldValue::create([
                    'event_id' => $event->id,
                    'custom_field_id' => $field->id,
                    'value' => $value
                ]);
            } elseif ($field->is_required) {
                $event->delete();
                return back()->withErrors([$key => 'This field is required.']);
            }
        }

        return redirect()->route('cnxevents.events.index')->with('success', 'Event created successfully.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $event = Event::with('venue', 'user', 'customFieldValues.customField')->findOrFail($id);

        // Return JSON for AJAX requests (used by edit modal and calendar)
        if (request()->ajax()) {
            $eventData = $event->toArray();
            
            // Convert datetime format from MySQL to datetime-local format (YYYY-MM-DDTHH:MM)
            if (isset($eventData['start_datetime'])) {
                $eventData['start_datetime'] = date('Y-m-d\TH:i', strtotime($eventData['start_datetime']));
            }
            if (isset($eventData['end_datetime'])) {
                $eventData['end_datetime'] = date('Y-m-d\TH:i', strtotime($eventData['end_datetime']));
            }
            if (isset($eventData['setup_datetime'])) {
                $eventData['setup_datetime'] = date('Y-m-d\TH:i', strtotime($eventData['setup_datetime']));
            }
            if (isset($eventData['venue_release_datetime'])) {
                $eventData['venue_release_datetime'] = date('Y-m-d\TH:i', strtotime($eventData['venue_release_datetime']));
            }
            
            // Load custom fields from relational table
            $customFieldsData = [];
            $customFields = CustomField::all();
            foreach ($event->customFieldValues as $fieldValue) {
                $field = $customFields->firstWhere('id', $fieldValue->custom_field_id);
                if ($field && $field->type === 'multiselect') {
                    // Convert comma-separated values back to arrays for multiselect
                    $customFieldsData[$fieldValue->custom_field_id] = array_map('trim', explode(',', $fieldValue->value));
                } else {
                    $customFieldsData[$fieldValue->custom_field_id] = $fieldValue->value;
                }
            }
            $eventData['custom_fields'] = $customFieldsData;

            return response()->json($eventData);
        }

        $customFields = CustomField::all();
        return view('cnxevents::events.show', compact('event', 'customFields'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $event = Event::findOrFail($id);
        $venues = Venue::all();
        $customFields = CustomField::with('departments')->get();
        return view('cnxevents::events.edit', compact('event', 'venues', 'customFields'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'setup_datetime' => 'nullable|date',
            'venue_release_datetime' => 'nullable|date',
            'all_day' => 'boolean',
            'venue_id' => 'required|exists:cnx_venues,id',
            'client_name' => 'nullable|string|max:255',
            'client_email' => 'nullable|email',
            'client_phone' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
        ]);

        $event = Event::findOrFail($id);

        // Prepare event data (exclude custom fields)
        $data = $request->except(array_map(function($field) {
            return 'custom_field_' . $field->id;
        }, CustomField::all()->all()));
        
        // Convert datetime-local format (YYYY-MM-DDTHH:MM) to MySQL format (YYYY-MM-DD HH:MM:SS)
        if ($request->filled('start_datetime')) {
            $data['start_datetime'] = str_replace('T', ' ', $request->start_datetime) . ':00';
        }
        if ($request->filled('end_datetime')) {
            $data['end_datetime'] = str_replace('T', ' ', $request->end_datetime) . ':00';
        }
        if ($request->filled('setup_datetime')) {
            $data['setup_datetime'] = str_replace('T', ' ', $request->setup_datetime) . ':00';
        }
        if ($request->filled('venue_release_datetime')) {
            $data['venue_release_datetime'] = str_replace('T', ' ', $request->venue_release_datetime) . ':00';
        }

        $event->update($data);
        
        // Handle custom fields - update in relational table
        $customFields = CustomField::all();
        foreach ($customFields as $field) {
            $key = 'custom_field_' . $field->id;
            if ($request->has($key)) {
                $value = $request->input($key);
                // Handle multiselect arrays
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                EventCustomFieldValue::updateOrCreate(
                    [
                        'event_id' => $event->id,
                        'custom_field_id' => $field->id
                    ],
                    ['value' => $value]
                );
            } elseif ($field->is_required) {
                // Check if value exists
                $existingValue = EventCustomFieldValue::where('event_id', $event->id)
                    ->where('custom_field_id', $field->id)
                    ->first();
                if (!$existingValue) {
                    return back()->withErrors([$key => 'This field is required.']);
                }
            }
        }

        return redirect()->route('cnxevents.events.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return redirect()->route('cnxevents.events.index')->with('success', 'Event deleted successfully.');
    }

    /**
     * Confirm a request to event.
     */
    public function confirm($id)
    {
        $event = Event::findOrFail($id);
        $event->update(['status' => 'confirmed']);

        return redirect()->back()->with('success', 'Event confirmed successfully.');
    }
}