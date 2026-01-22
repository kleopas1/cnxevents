<?php

namespace Modules\CnxEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CnxEvents\Entities\CustomField;
use Modules\CnxEvents\Entities\Department;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $customFields = CustomField::with('departments')->paginate(10);
        return view('cnxevents::custom_fields.index', compact('customFields'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $departments = Department::all();
        return view('cnxevents::custom_fields.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:text,select,multiselect,date,integer,decimal',
                'options' => 'nullable|string',
                'is_required' => 'nullable|in:on', // Checkbox sends "on" when checked
                'position' => 'nullable|integer|min:0',
                'departments' => 'required|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            // Convert options string to array if it's a select or multiselect type
            $options = null;
            if (($request->type === 'select' || $request->type === 'multiselect') && $request->options) {
                $options = array_filter(array_map('trim', explode("\n", $request->options)));
            }

            $customField = CustomField::create([
                'name' => $request->name,
                'type' => $request->type,
                'options' => $options,
                'is_required' => $request->is_required === 'on',
                'position' => $request->position ?? 0,
            ]);
            
            $customField->departments()->attach($request->departments);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Custom field created successfully.',
                    'redirect' => route('cnxevents.settings.index', ['active_tab' => 'custom-fields'])
                ]);
            }
            
            return $this->redirectWithTab($request, 'Custom field created successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create custom field: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to create custom field: ' . $e->getMessage()]);
        }
    }
    public function show($id)
    {
        $customField = CustomField::with('departments')->findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json($customField);
        }
        
        return view('cnxevents::custom_fields.show', compact('customField'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $customField = CustomField::with('departments')->findOrFail($id);
        $departments = Department::all();
        return view('cnxevents::custom_fields.edit', compact('customField', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:text,select,multiselect,date,integer,decimal',
                'options' => 'nullable|string',
                'is_required' => 'nullable|in:on', // Checkbox sends "on" when checked
                'position' => 'nullable|integer|min:0',
                'departments' => 'required|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            // Convert options string to array if it's a select or multiselect type
            $options = null;
            if (($request->type === 'select' || $request->type === 'multiselect') && $request->options) {
                $options = array_filter(array_map('trim', explode("\n", $request->options)));
            }

            $customField = CustomField::findOrFail($id);
            $customField->update([
                'name' => $request->name,
                'type' => $request->type,
                'options' => $options,
                'is_required' => $request->is_required === 'on',
                'position' => $request->position ?? 0,
            ]);
            $customField->departments()->sync($request->departments);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Custom field updated successfully.',
                    'redirect' => route('cnxevents.settings.index', ['active_tab' => 'custom-fields'])
                ]);
            }

            return $this->redirectWithTab($request, 'Custom field updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update custom field: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to update custom field: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $customField = CustomField::findOrFail($id);
        
        // Check if custom field is used in any events
        $eventsCount = $customField->eventCustomFieldValues()->count();
        if ($eventsCount > 0) {
            return $this->redirectWithTab(
                request(), 
                'Cannot delete this custom field. It is used in ' . $eventsCount . ' event(s). Please remove those values first.',
                true
            );
        }
        
        try {
            $customField->departments()->detach();
            $customField->delete();
            return $this->redirectWithTab(request(), 'Custom field deleted successfully.');
        } catch (\Exception $e) {
            return $this->redirectWithTab(
                request(), 
                'Cannot delete this custom field. It may be referenced by other records.',
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