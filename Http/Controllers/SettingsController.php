<?php

namespace Modules\CnxEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CnxEvents\Entities\Department;
use Modules\CnxEvents\Entities\CustomField;
use Modules\CnxEvents\Entities\Venue;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Check if user is admin using FreeScout's isAdmin() method
            if (!auth()->user() || !auth()->user()->isAdmin()) {
                abort(403, 'Access denied.');
            }
            return $next($request);
        });
    }

    /**
     * Display the settings page.
     * @return Response
     */
    public function index()
    {
        $departments = Department::all();
        $customFields = CustomField::with('departments')->get();
        $venues = Venue::all();

        // Get active tab from session (set by redirectWithTab) or default to departments
        $activeTab = session('active_tab', 'departments');

        return view('cnxevents::settings.index', compact('departments', 'customFields', 'venues', 'activeTab'));
    }
}