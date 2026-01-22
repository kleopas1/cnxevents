<?php

namespace Modules\CnxEvents\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CnxEvents\Entities\Event;
use Modules\CnxEvents\Entities\CustomField;
use Modules\CnxEvents\Entities\Department;
use PDF; // Assuming Dompdf is installed

class BeoController extends Controller
{
    /**
     * Generate BEO for an event.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $event = Event::with('venue')->findOrFail($id);
        $departments = Department::all();

        // Group custom fields by department for this event
        $beoData = [];
        $customFields = CustomField::with('departments')->get();

        foreach ($customFields as $field) {
            // Only include fields that have values for this event
            if (isset($event->custom_fields[$field->id])) {
                foreach ($field->departments as $department) {
                    $deptName = $department->name;
                    if (!isset($beoData[$deptName])) {
                        $beoData[$deptName] = [];
                    }
                    $beoData[$deptName][] = [
                        'field' => $field->name,
                        'value' => $event->custom_fields[$field->id],
                        'type' => $field->type,
                        'required' => $field->is_required
                    ];
                }
            }
        }

        return view('cnxevents::beo.show', compact('event', 'beoData', 'departments'));
    }

    /**
     * Download BEO as PDF.
     * @param int $id
     * @return Response
     */
    public function pdf($id)
    {
        $event = Event::with('venue')->findOrFail($id);

        // Group custom fields by department for this event
        $beoData = [];
        $customFields = CustomField::with('departments')->get();

        foreach ($customFields as $field) {
            // Only include fields that have values for this event
            if (isset($event->custom_fields[$field->id])) {
                foreach ($field->departments as $department) {
                    $deptName = $department->name;
                    if (!isset($beoData[$deptName])) {
                        $beoData[$deptName] = [];
                    }
                    $beoData[$deptName][] = [
                        'field' => $field->name,
                        'value' => $event->custom_fields[$field->id],
                        'type' => $field->type,
                        'required' => $field->is_required
                    ];
                }
            }
        }

        $pdf = PDF::loadView('cnxevents::beo.pdf', compact('event', 'beoData'));
        return $pdf->download('beo_' . $event->id . '.pdf');
    }
}