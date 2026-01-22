<?php

namespace Modules\CnxEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CnxEvents\Entities\Department;

class DepartmentController extends Controller
{
    public function __construct()
    {    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $departments = Department::paginate(10);
        return view('cnxevents::departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('cnxevents::departments.create');
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
                'description' => 'nullable|string',
            ]);
            
            
            $department = Department::create($request->all());
                    
            return $this->redirectWithTab($request, 'Department created successfully.');
        } catch (\Exception $e) {
            \Log::error('DepartmentController@store - Error: ' . $e->getMessage());
            \Log::error('DepartmentController@store - Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        try {
            
            $department = Department::findOrFail($id);
            
            if (request()->ajax()) {
                return response()->json($department);
            }
            
            return view('cnxevents::departments.show', compact('department'));
        } catch (\Exception $e) {
            \Log::error('DepartmentController@show - Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $department = Department::findOrFail($id);
        return view('cnxevents::departments.edit', compact('department'));
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
                'description' => 'nullable|string',
            ]);
            
            
            $department = Department::findOrFail($id);
            
            $department->update($request->all());
                        
            return $this->redirectWithTab($request, 'Department updated successfully.');
        } catch (\Exception $e) {
            \Log::error('DepartmentController@update - Error: ' . $e->getMessage());
            \Log::error('DepartmentController@update - Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $department = Department::findOrFail($id);
        
        // Check if department is assigned to any custom fields
        $customFieldsCount = $department->customFields()->count();
        if ($customFieldsCount > 0) {
            return $this->redirectWithTab(
                request(), 
                'Cannot delete this department. It is assigned to ' . $customFieldsCount . ' custom field(s). Please reassign or delete those custom fields first.',
                true
            );
        }
        
        try {
            $department->delete();
            return $this->redirectWithTab(request(), 'Department deleted successfully.');
        } catch (\Exception $e) {
            return $this->redirectWithTab(
                request(), 
                'Cannot delete this department. It may be referenced by other records.',
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