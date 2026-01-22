<?php

namespace Modules\CnxEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CnxEvents\Entities\Venue;

class VenueController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $venues = Venue::paginate(10);
        return view('cnxevents::venues.index', compact('venues'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('cnxevents::venues.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer',
            'features' => 'nullable|array',
            'custom_fields' => 'nullable|array',
        ]);

        Venue::create($request->all());

        return $this->redirectWithTab($request, 'Venue created successfully.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $venue = Venue::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json($venue);
        }
        
        return view('cnxevents::venues.show', compact('venue'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $venue = Venue::findOrFail($id);
        return view('cnxevents::venues.edit', compact('venue'));
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer',
            'features' => 'nullable|array',
            'custom_fields' => 'nullable|array',
        ]);

        $venue = Venue::findOrFail($id);
        $venue->update($request->all());

        return $this->redirectWithTab($request, 'Venue updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $venue = Venue::findOrFail($id);
        
        // Check if venue is being used by any events
        $eventsCount = $venue->events()->count();
        if ($eventsCount > 0) {
            return $this->redirectWithTab(
                request(), 
                'Cannot delete this venue. It is assigned to ' . $eventsCount . ' event(s). Please reassign or delete those events first.',
                true
            );
        }
        
        try {
            $venue->delete();
            return $this->redirectWithTab(request(), 'Venue deleted successfully.');
        } catch (\Exception $e) {
            return $this->redirectWithTab(
                request(), 
                'Cannot delete this venue. It may be referenced by other records.',
                true
            );
        }
    }

    /**
     * Redirect back to settings with active tab preserved
     */
    private function redirectWithTab(Request $request, $message, $isError = false)
    {
        $sessionKey = $isError ? 'error' : 'success';
        $redirect = redirect()->route('cnxevents.settings.index')->with($sessionKey, $message);
        
        if ($request->has('active_tab')) {
            $redirect = $redirect->with('active_tab', $request->active_tab);
        }
        
        return $redirect;
    }
}