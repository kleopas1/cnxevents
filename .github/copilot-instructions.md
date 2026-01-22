# GitHub Copilot Instructions for CnxEvents Module

## Project Overview

**CnxEvents** is a custom Laravel 5 module for FreeScout that provides hotel event and banquet management functionality. This module enables scheduling, venue management, and event handling with a request/confirmation workflow, calendar views, analytics, and BEO (Banquet Event Order) generation.

### Key Information
- **Framework**: Laravel 5.5+
- **PHP Version**: 8.0+
- **Frontend**: Bootstrap 3, jQuery
- **Parent Application**: FreeScout (helpdesk system)
- **Module Namespace**: `Modules\CnxEvents`
- **Route Prefix**: `/cnxevents`
- **Development Scope**: All development MUST be within `Modules/CnxEvents/` directory only

## Technology Stack

### Backend
- **PHP**: 8.0+ with strict types
- **Laravel**: 5.5+ (using FreeScout's Laravel instance)
- **Database**: MySQL with Eloquent ORM
- **Validation**: Laravel's built-in validation with custom rules
- **PDF Generation**: Dompdf for BEO documents

### Frontend
- **CSS Framework**: Bootstrap 3.x (NOT Bootstrap 4 or 5)
- **JavaScript**: Vanilla JS and jQuery (FreeScout uses jQuery)
- **Calendar Library**: FullCalendar 3.x
- **Charts**: Chart.js 2.9
- **AJAX**: jQuery Ajax for modal interactions

### Architecture
- **Design Pattern**: MVC (Model-View-Controller)
- **Module System**: Laravel Modules (nwidart/laravel-modules)
- **Integration**: Eventy hooks for FreeScout integration
- **Authentication**: FreeScout's built-in auth system

## Core Entities & Relationships

### Models (Located in `Entities/`)

#### Event (`Event.php`)
Primary entity representing events/banquets.
```php
// Fields:
- title (string, required)
- description (text, nullable)
- start_datetime (datetime, required)
- end_datetime (datetime, required)
- setup_datetime (datetime, nullable)
- venue_release_datetime (datetime, nullable)
- all_day (boolean, default false)
- venue_id (foreign key, required)
- status (enum: 'request'|'confirmed', default 'request')
- user_id (foreign key, creator)
- client_name, client_email, client_phone, client_company (strings, nullable)
- custom_fields (JSON, stores custom field values)

// Relationships:
- belongsTo: Venue, User
- hasMany: EventCustomFieldValue

// Scopes:
- scopeRequests() - filters status='request'
- scopeConfirmed() - filters status='confirmed'

// Validation:
- Datetime sequence: setup < start < end < venue_release (when not all_day)
- Validation occurs in model's boot() method
```

#### Venue (`Venue.php`)
Represents physical venues (rooms, halls).
```php
// Fields:
- name (string, required)
- description (text, nullable)
- capacity (integer, nullable)
- features (JSON, array of venue features)
- custom_fields (JSON, venue-specific fields)

// Relationships:
- hasMany: Event
```

#### CustomField (`CustomField.php`)
Defines dynamic custom fields for events.
```php
// Fields:
- name (string, required)
- type (enum: 'text'|'select'|'multiselect'|'date'|'integer'|'decimal')
- options (JSON, for select/multiselect types)
- is_required (boolean, default false)
- position (integer, for ordering)

// Relationships:
- belongsToMany: Department (pivot table)
- hasMany: EventCustomFieldValue

// Methods:
- isNumeric() - checks if type is integer/decimal
- requiresOptions() - checks if type needs options
- getTypeLabel() - returns user-friendly type name
```

#### Department (`Department.php`)
Organizational units for grouping custom fields (e.g., Catering, AV, Decor).
```php
// Fields:
- name (string, required)
- description (text, nullable)

// Relationships:
- belongsToMany: CustomField (pivot table)
```

#### EventCustomFieldValue (`EventCustomFieldValue.php`)
Stores actual values of custom fields for events (relational storage instead of pure JSON).
```php
// Fields:
- event_id (foreign key)
- custom_field_id (foreign key)
- value (text, stores the actual value)

// Relationships:
- belongsTo: Event, CustomField
```

## Controllers (Located in `Http/Controllers/`)

### EventController
Handles main CRUD operations for events.
```php
Methods:
- index() - List events with filters (status, venue, date range) and pagination
- create() - Show create form (not used, using modals)
- store() - Validate and create new event (status defaults to 'request')
- show() - Return event data as JSON for AJAX (used by edit modal)
- edit() - Show edit form (not used, using modals)
- update() - Validate and update existing event
- destroy() - Delete event
- confirm() - Change status from 'request' to 'confirmed'

Key Points:
- Load venues and custom fields for forms
- Validate custom fields (required fields must have values)
- Store custom field values in event's custom_fields JSON
- Handle AJAX requests for modal operations
```

### VenueController
CRUD operations for venues (admin only).

### DepartmentController
CRUD operations for departments (admin only).

### CustomFieldController
CRUD operations for custom fields with department assignments (admin only).
```php
Key Points:
- Handle department many-to-many sync
- Validate 'options' JSON for select/multiselect types
- Ensure at least one department is assigned
```

### CalendarController
Displays events in calendar format.
```php
Methods:
- index() - Shows FullCalendar view
- events() - Returns events as JSON for FullCalendar
```

### AnalyticsController
Generates reports and KPI data.
```php
Methods:
- index() - Display analytics dashboard
- statusData() - Event counts by status (JSON)
- venueData() - Event counts by venue (JSON)
- monthlyData() - Events per month for last 12 months (JSON)
```

### BeoController
Generates Banquet Event Orders.
```php
Methods:
- show() - Display BEO for an event (grouped by department)
- pdf() - Generate and download BEO as PDF
- updateDepartments() - Update selected departments for BEO
```

### SettingsController
Admin settings page with tabs for departments, venues, and custom fields.

## Routing Conventions

All routes defined in `Http/routes.php` with:
- **Prefix**: `/cnxevents`
- **Namespace**: `Modules\CnxEvents\Http\Controllers`
- **Route Name Prefix**: `cnxevents.`
- **Middleware**: `['web', 'auth']` (all routes require authentication)
- **Admin Routes**: Additional `'roles' => ['admin']` middleware for settings

Example Routes:
```php
// Events
Route::resource('events', 'EventController');
Route::post('events/{event}/confirm', 'EventController@confirm')->name('events.confirm');

// Calendar
Route::get('calendar', 'CalendarController@index')->name('calendar');
Route::get('calendar/events', 'CalendarController@events')->name('calendar.events');

// Analytics
Route::get('analytics', 'AnalyticsController@index')->name('analytics');

// Settings (admin only)
Route::get('settings', 'SettingsController@index')->middleware(['auth', 'roles'])->name('settings.index');
```

## View Architecture

### Layout Structure
All views extend `cnxevents::layouts.app` which itself extends FreeScout's `layouts.app`.

```blade
@extends('cnxevents::layouts.app')

@section('title', 'Page Title')

@section('content')
    <!-- Page content -->
@endsection

@section('scripts')
    <!-- Page-specific scripts -->
@endsection
```

### View Locations (`Resources/views/`)
```
layouts/
  - app.blade.php (module layout with sidebar)
events/
  - index.blade.php (events table with modal)
calendar.blade.php
analytics.blade.php
beo.blade.php
settings/
  - index.blade.php (tabbed settings page)
departments/
  - (partials if needed)
```

### Bootstrap 3 Components to Use

**Forms:**
```html
<div class="form-group">
    <label>Label</label>
    <input type="text" name="field" class="form-control" required>
</div>
```

**Buttons:**
```html
<button class="btn btn-primary">Primary</button>
<button class="btn btn-default">Default</button>
<button class="btn btn-success">Success</button>
<button class="btn btn-danger">Danger</button>
<button class="btn btn-sm">Small</button>
```

**Grid:**
```html
<div class="row">
    <div class="col-md-6">Column 1</div>
    <div class="col-md-6">Column 2</div>
</div>
```

**Tables:**
```html
<table class="table table-striped table-bordered">
    <thead>
        <tr><th>Header</th></tr>
    </thead>
    <tbody>
        <tr><td>Data</td></tr>
    </tbody>
</table>
```

**Modals:**
```html
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Title</h4>
            </div>
            <div class="modal-body">
                <!-- Content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
```

**Alerts:**
```html
<div class="alert alert-success">Success message</div>
<div class="alert alert-danger">Error message</div>
<div class="alert alert-warning">Warning message</div>
<div class="alert alert-info">Info message</div>
```

**Glyphicons (Bootstrap 3):**
```html
<i class="glyphicon glyphicon-calendar"></i>
<i class="glyphicon glyphicon-cog"></i>
<i class="glyphicon glyphicon-stats"></i>
<i class="glyphicon glyphicon-th"></i>
```

### NEVER Use (Bootstrap 4/5 Syntax)
❌ `btn-outline-*` (use `btn-default` instead)
❌ `mr-2`, `ml-2` (use `margin-right`, `margin-left` in custom CSS)
❌ `form-control-sm` (use `input-sm`)
❌ `text-muted` classes extensively (limited support in BS3)
❌ `card` components (use `panel` instead)
❌ Font Awesome icons (use Glyphicons)

## JavaScript Patterns

### Modal Operations (AJAX-based)
```javascript
// Reset form for "Add" operation
function resetForm() {
    const form = document.getElementById('eventForm');
    form.action = '/cnxevents/events';
    form.reset();
    
    // Remove _method input if exists
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();
    
    document.getElementById('modalTitle').textContent = 'Add Event';
}

// Populate form for "Edit" operation
function editEvent(id) {
    fetch(`/cnxevents/events/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const form = document.getElementById('eventForm');
        form.action = `/cnxevents/events/${id}`;
        
        // Add PUT method
        let methodInput = form.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            form.appendChild(methodInput);
        }
        methodInput.value = 'PUT';
        
        // Populate fields
        Object.keys(data).forEach(key => {
            const element = form.querySelector(`[name="${key}"]`);
            if (element) {
                element.value = data[key];
            }
        });
        
        document.getElementById('modalTitle').textContent = 'Edit Event';
    });
}
```

### Delete Confirmation
```javascript
document.querySelectorAll('.delete-event-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to delete this event?')) {
            e.preventDefault();
        }
    });
});
```

### Chart.js Integration (Version 2.9)
```javascript
// Pie chart example
const ctx = document.getElementById('statusChart').getContext('2d');
fetch('/cnxevents/analytics/status')
    .then(response => response.json())
    .then(data => {
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: ['#5cb85c', '#f0ad4e']
                }]
            }
        });
    });
```

## Database Schema Conventions

### Table Naming
- Prefix all tables with `cnx_` (e.g., `cnx_events`, `cnx_venues`)
- Pivot tables: `cnx_custom_field_department`

### Migration Structure
```php
Schema::create('cnx_events', function (Blueprint $table) {
    $table->increments('id');
    $table->string('title');
    $table->text('description')->nullable();
    $table->dateTime('start_datetime');
    $table->dateTime('end_datetime');
    $table->dateTime('setup_datetime')->nullable();
    $table->dateTime('venue_release_datetime')->nullable();
    $table->boolean('all_day')->default(false);
    $table->unsignedInteger('venue_id');
    $table->enum('status', ['request', 'confirmed'])->default('request');
    $table->unsignedInteger('user_id');
    $table->string('client_name')->nullable();
    $table->string('client_email')->nullable();
    $table->string('client_phone')->nullable();
    $table->string('client_company')->nullable();
    $table->json('custom_fields')->nullable();
    $table->timestamps();

    // Foreign keys
    $table->foreign('venue_id')->references('id')->on('cnx_venues')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

    // Indexes for performance
    $table->index('status');
    $table->index('venue_id');
    $table->index('start_datetime');
});
```

## FreeScout Integration

### Navigation Hooks (in `Providers/CnxEventsServiceProvider.php`)
```php
public function hooks()
{
    // Add menu item to FreeScout's main navigation
    \Eventy::addAction('menu.append', function() {
        echo '<li class="' . \App\Misc\Helper::menuSelectedHtml('cnxevents') . '">
            <a href="' . route('cnxevents.events.index') . '">
                <i class="glyphicon glyphicon-calendar"></i> ' . __('Events') . '
            </a>
        </li>';
    });

    // Register routes for menu selection
    \Eventy::addFilter('menu.selected', function($menu) {
        $menu['cnxevents'] = [
            'cnxevents.events.index',
            'cnxevents.calendar',
            'cnxevents.analytics',
            'cnxevents.settings.index',
        ];
        return $menu;
    });
}
```

### Using FreeScout's User Model
```php
// In controllers or models
$user = \App\User::find($id);
$currentUser = auth()->user();
$isAdmin = $currentUser->isAdmin();
```

### Permission Checks
```php
// In routes
Route::get('settings', 'SettingsController@index')
    ->middleware(['auth', 'roles'])
    ->roles(['admin']);

// In views
@if(Auth::user()->isAdmin())
    <!-- Admin only content -->
@endif
```

## Validation Rules

### Event Validation
```php
$request->validate([
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'start_datetime' => 'required|date',
    'end_datetime' => 'required|date|after:start_datetime',
    'setup_datetime' => 'nullable|date|before:start_datetime',
    'venue_release_datetime' => 'nullable|date|after:end_datetime',
    'venue_id' => 'required|exists:cnx_venues,id',
    'client_email' => 'nullable|email',
]);
```

### Custom Field Validation (Dynamic)
```php
$customFields = CustomField::all();
foreach ($customFields as $field) {
    if ($field->is_required && !$request->has('custom_field_' . $field->id)) {
        return back()->withErrors([
            'custom_field_' . $field->id => 'This field is required.'
        ]);
    }
}
```

### Datetime Sequence Validation (in Model)
Handled automatically in `Event::boot()`:
- setup_datetime < start_datetime < end_datetime < venue_release_datetime
- Only enforced when `all_day = false`

## Custom Fields System

### Storage Strategy
Custom field values are stored in TWO places:
1. **event_custom_field_values table** (relational, for queries and reports)
2. **events.custom_fields JSON column** (denormalized, for quick access)

### Creating Custom Fields
```php
// CustomFieldController@store
$customField = CustomField::create([
    'name' => $request->name,
    'type' => $request->type, // text, select, date, integer, decimal
    'options' => $request->type === 'select' ? json_decode($request->options) : null,
    'is_required' => $request->has('is_required'),
    'position' => CustomField::max('position') + 1,
]);

// Attach to departments
$customField->departments()->sync($request->departments);
```

### Loading Custom Fields in Forms
```blade
@foreach($customFields ?? [] as $field)
    <div class="form-group">
        <label>{{ $field->name }} @if($field->is_required)*@endif</label>
        
        @if($field->type == 'text')
            <input type="text" name="custom_field_{{ $field->id }}" class="form-control" 
                   @if($field->is_required) required @endif>
        @elseif($field->type == 'select')
            <select name="custom_field_{{ $field->id }}" class="form-control" 
                    @if($field->is_required) required @endif>
                <option value="">Select</option>
                @foreach($field->options ?? [] as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        @elseif($field->type == 'date')
            <input type="date" name="custom_field_{{ $field->id }}" class="form-control" 
                   @if($field->is_required) required @endif>
        @elseif($field->type == 'integer')
            <input type="number" step="1" name="custom_field_{{ $field->id }}" class="form-control" 
                   @if($field->is_required) required @endif>
        @elseif($field->type == 'decimal')
            <input type="number" step="0.01" name="custom_field_{{ $field->id }}" class="form-control" 
                   @if($field->is_required) required @endif>
        @endif
    </div>
@endforeach
```

### Saving Custom Field Values
```php
// In EventController@store or update
$customFieldsData = [];
$customFields = CustomField::all();

foreach ($customFields as $field) {
    $key = 'custom_field_' . $field->id;
    if ($request->has($key)) {
        $value = $request->input($key);
        
        // Store in JSON
        $customFieldsData[$field->id] = $value;
        
        // Store in relational table
        EventCustomFieldValue::updateOrCreate(
            ['event_id' => $event->id, 'custom_field_id' => $field->id],
            ['value' => $value]
        );
    }
}

$event->custom_fields = $customFieldsData;
$event->save();
```

## BEO (Banquet Event Order) Generation

### Grouping Custom Fields by Department
```php
// In BeoController@show
$event = Event::with('venue', 'customFieldValues.customField.departments')->findOrFail($id);

$groupedFields = [];
foreach ($event->customFieldValues as $fieldValue) {
    foreach ($fieldValue->customField->departments as $department) {
        if (!isset($groupedFields[$department->name])) {
            $groupedFields[$department->name] = [];
        }
        $groupedFields[$department->name][] = [
            'label' => $fieldValue->customField->name,
            'value' => $fieldValue->value,
        ];
    }
}

return view('cnxevents::beo', compact('event', 'groupedFields'));
```

### BEO View Structure
```blade
<h2>Banquet Event Order</h2>
<h3>{{ $event->title }}</h3>

<div class="section">
    <h4>Event Details</h4>
    <p><strong>Venue:</strong> {{ $event->venue->name }}</p>
    <p><strong>Start:</strong> {{ $event->start_datetime->format('Y-m-d H:i') }}</p>
    <p><strong>End:</strong> {{ $event->end_datetime->format('Y-m-d H:i') }}</p>
</div>

@foreach($groupedFields as $department => $fields)
    <div class="section">
        <h4>{{ $department }}</h4>
        <table class="table">
            @foreach($fields as $field)
                <tr>
                    <th>{{ $field['label'] }}</th>
                    <td>{{ $field['value'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endforeach
```

### PDF Generation with Dompdf
```php
// In BeoController@pdf
use Dompdf\Dompdf;

$html = view('cnxevents::beo', compact('event', 'groupedFields'))->render();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

return $dompdf->stream("BEO_{$event->id}_{$event->title}.pdf");
```

## Analytics & Reporting

### KPI Calculations
```php
// In AnalyticsController@index
$totalEvents = Event::count();
$confirmedEvents = Event::confirmed()->count();
$requestEvents = Event::requests()->count();

// Venue utilization (example)
$venues = Venue::withCount('events')->get();
$totalCapacity = $venues->sum('capacity');
$bookedCapacity = Event::confirmed()
    ->join('cnx_venues', 'cnx_events.venue_id', '=', 'cnx_venues.id')
    ->sum('cnx_venues.capacity');
$utilization = $totalCapacity > 0 ? ($bookedCapacity / $totalCapacity) * 100 : 0;
```

### Chart Data Endpoints
```php
// Status data (pie chart)
public function statusData()
{
    $confirmed = Event::confirmed()->count();
    $requests = Event::requests()->count();
    
    return response()->json([
        'labels' => ['Confirmed', 'Requests'],
        'values' => [$confirmed, $requests],
    ]);
}

// Monthly data (line chart)
public function monthlyData()
{
    $months = [];
    $counts = [];
    
    for ($i = 11; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $months[] = $date->format('M Y');
        $counts[] = Event::whereYear('start_datetime', $date->year)
                         ->whereMonth('start_datetime', $date->month)
                         ->count();
    }
    
    return response()->json([
        'labels' => $months,
        'values' => $counts,
    ]);
}
```

## Testing Guidelines

### Feature Tests (Located in `Tests/Feature/`)
```php
use Tests\TestCase;
use Modules\CnxEvents\Entities\Event;
use Modules\CnxEvents\Entities\Venue;

class EventControllerTest extends TestCase
{
    public function test_can_create_event()
    {
        $venue = Venue::factory()->create();
        $user = \App\User::factory()->create();
        
        $response = $this->actingAs($user)->post(route('cnxevents.events.store'), [
            'title' => 'Test Event',
            'venue_id' => $venue->id,
            'start_datetime' => now()->addDays(1),
            'end_datetime' => now()->addDays(1)->addHours(2),
        ]);
        
        $response->assertRedirect(route('cnxevents.events.index'));
        $this->assertDatabaseHas('cnx_events', ['title' => 'Test Event']);
    }
}
```

### Unit Tests (Located in `Tests/Unit/`)
```php
class EventTest extends TestCase
{
    public function test_datetime_validation()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        
        Event::create([
            'title' => 'Invalid Event',
            'venue_id' => 1,
            'start_datetime' => now(),
            'end_datetime' => now()->subHour(), // Invalid: end before start
        ]);
    }
}
```

## Common Tasks & Patterns

### Adding a New Controller
1. Create in `Http/Controllers/`
2. Extend `Illuminate\Routing\Controller`
3. Use namespace `Modules\CnxEvents\Http\Controllers`
4. Add routes in `Http/routes.php`
5. Create corresponding views in `Resources/views/`

### Adding a New Migration
```bash
# Use date-based naming
2026_01_22_HHMMSS_create_table_name.php
```

### Adding a New Model
1. Create in `Entities/`
2. Use namespace `Modules\CnxEvents\Entities`
3. Specify `$table` property (with `cnx_` prefix)
4. Define `$fillable` and `$casts`
5. Add relationships

### Creating a New View
1. Save in `Resources/views/`
2. Extend `cnxevents::layouts.app`
3. Use Bootstrap 3 classes only
4. Follow blade naming: `resource.action.blade.php`

### Adding JavaScript Functionality
1. Save in `Public/js/`
2. Use vanilla JS or jQuery (no modern frameworks)
3. Include via `@section('scripts')` in view
4. Use asset helper: `{{ \Module::asset('cnxevents:js/filename.js') }}`

### Adding CSS Styles
1. Save in `Public/css/`
2. Use Bootstrap 3-compatible styles
3. Include via `@section('stylesheets')` in view
4. Use asset helper: `{{ \Module::asset('cnxevents:css/filename.css') }}`

## Code Style & Best Practices

### PHP Code Style
```php
// PSR-2 standard
// Use strict types
declare(strict_types=1);

// Type hints and return types
public function index(): View
{
    // Method body
}

// Use Eloquent relationships
$event->venue->name; // Good
Venue::find($event->venue_id)->name; // Avoid

// Use route names
route('cnxevents.events.index'); // Good
url('/cnxevents/events'); // Avoid
```

### Blade Templates
```blade
{{-- Use Blade comments --}}

{{-- Escape output by default --}}
{{ $variable }}

{{-- Unescaped only when necessary --}}
{!! $htmlContent !!}

{{-- Use @auth, @guest, @can directives --}}
@auth
    <p>Welcome, {{ Auth::user()->name }}</p>
@endauth
```

### JavaScript
```javascript
// Use strict mode
'use strict';

// Event delegation for dynamic elements
document.addEventListener('click', function(e) {
    if (e.target.matches('.edit-btn')) {
        // Handle edit
    }
});

// CSRF token in AJAX
$.ajax({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

## Error Handling

### Controller Error Handling
```php
try {
    $event = Event::findOrFail($id);
    // Process event
    return redirect()->route('cnxevents.events.index')
                     ->with('success', 'Event updated successfully.');
} catch (\Exception $e) {
    \Log::error('Event update failed: ' . $e->getMessage());
    return back()->with('error', 'An error occurred. Please try again.');
}
```

### JavaScript Error Handling
```javascript
fetch('/cnxevents/events/1')
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        // Process data
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load event. Please try again.');
    });
```

## Security Considerations

### CSRF Protection
Always include CSRF token in forms:
```blade
<form method="POST">
    @csrf
    <!-- Form fields -->
</form>
```

### Mass Assignment Protection
Use `$fillable` in models:
```php
protected $fillable = ['title', 'description', 'venue_id'];
// 'id', 'created_at', 'updated_at' automatically protected
```

### Authorization
Check permissions before sensitive operations:
```php
// In controller
if (!Auth::user()->isAdmin()) {
    abort(403, 'Unauthorized action.');
}

// In view
@can('manage-settings')
    <!-- Admin content -->
@endcan
```

### SQL Injection Prevention
Use Eloquent ORM and parameter binding:
```php
// Good
Event::where('status', $status)->get();

// Avoid raw queries
DB::select("SELECT * FROM cnx_events WHERE status = '$status'");
```

## Project State & Implementation Status

### ✅ Completed Features
- All database migrations (departments, custom_fields, venues, events)
- All core models with relationships and validation
- Full CRUD controllers for all entities
- Events management with modal-based UI
- Calendar view with FullCalendar integration
- Analytics dashboard with Chart.js
- BEO generation (view and PDF)
- Settings management (admin only)
- FreeScout navigation integration
- Custom fields system with department grouping

### 🔄 In Progress
- Comprehensive testing (unit and feature tests)
- UI/UX refinements and error handling
- Performance optimization

### 📋 Planned Features (Future)
- Email notifications for event confirmations
- Recurring events support
- Advanced reporting and exports
- External calendar integrations (Google Calendar)
- Event conflict detection and warnings
- Mobile responsiveness improvements

## Common Issues & Solutions

### Issue: Modal Not Showing
**Cause**: Bootstrap modal z-index conflicts or missing data-backdrop attribute
**Solution**: 
```blade
<!-- Ensure data-backdrop="false" and proper z-index -->
<div class="modal" data-backdrop="false" style="z-index: 100000;">
```

### Issue: Custom Fields Not Saving
**Cause**: Missing validation or incorrect field naming
**Solution**:
```php
// Controller
$customFieldsData = [];
foreach (CustomField::all() as $field) {
    if ($request->has('custom_field_' . $field->id)) {
        $customFieldsData[$field->id] = $request->input('custom_field_' . $field->id);
    }
}
```

### Issue: Datetime Validation Failing
**Cause**: Date comparison not accounting for null values
**Solution**:
```php
// In Event::boot()
if ($setup && $setup >= $start) { // Check if $setup exists before comparing
    $errors['setup_datetime'] = 'Setup must be before start.';
}
```

### Issue: Routes Not Found
**Cause**: Routes not registered or module not activated
**Solution**:
```bash
php artisan route:clear
php artisan config:clear
php artisan module:enable CnxEvents
```

## Helpful Laravel 5 / FreeScout Commands

```bash
# Clear caches
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# Run migrations
php artisan migrate
php artisan migrate:rollback

# Enable/disable module
php artisan module:enable CnxEvents
php artisan module:disable CnxEvents

# Generate IDE helper (if installed)
php artisan ide-helper:models

# Run tests
php artisan test
```

## File Organization

```
Modules/CnxEvents/
├── Config/
│   └── config.php
├── Console/
├── Database/
│   ├── Migrations/       (All table migrations)
│   ├── Seeders/          (Test data seeders)
│   └── factories/        (Model factories for testing)
├── Entities/             (Models)
│   ├── Event.php
│   ├── Venue.php
│   ├── Department.php
│   ├── CustomField.php
│   ├── EventCustomFieldValue.php
│   ├── DashboardCard.php
│   └── Report.php
├── Http/
│   ├── Controllers/      (All controllers)
│   ├── Middleware/
│   ├── Requests/         (Form request classes)
│   └── routes.php        (All route definitions)
├── Providers/
│   └── CnxEventsServiceProvider.php
├── Public/
│   ├── css/
│   └── js/
│       └── events.js
├── Resources/
│   ├── assets/
│   ├── lang/             (Translations)
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── events/
│       │   └── index.blade.php
│       ├── settings/
│       │   └── index.blade.php
│       ├── calendar.blade.php
│       ├── analytics.blade.php
│       └── beo.blade.php
├── Services/             (Business logic services)
├── Tests/
│   ├── Feature/
│   └── Unit/
├── composer.json
├── module.json
├── start.php
├── implementation_plan.md
└── TESTING_PLAN.md
```

## When to Use This Module vs Core FreeScout

### Use CnxEvents For:
- Event scheduling and management
- Venue bookings and capacity tracking
- Client information for events
- BEO generation and custom fields
- Event analytics and reporting

### Use Core FreeScout For:
- Customer support tickets
- Email conversations with clients
- User authentication and permissions
- General application settings

### Integration Points:
- CnxEvents uses FreeScout's User model for authentication
- Navigation appears in FreeScout's main menu
- Permission system follows FreeScout's role-based access
- Layout and styling match FreeScout's Bootstrap 3 theme

---

## Quick Reference

### Most Common Code Patterns

**Load all data for events index:**
```php
$events = Event::with('venue', 'user')->paginate(10);
$venues = Venue::all();
$customFields = CustomField::with('departments')->get();
```

**Create event with custom fields:**
```php
$event = Event::create($request->except('custom_field_*'));
foreach (CustomField::all() as $field) {
    if ($request->has('custom_field_' . $field->id)) {
        EventCustomFieldValue::create([
            'event_id' => $event->id,
            'custom_field_id' => $field->id,
            'value' => $request->input('custom_field_' . $field->id),
        ]);
    }
}
```

**Check if user is admin:**
```php
if (Auth::user()->isAdmin()) {
    // Admin logic
}
```

**Return JSON for AJAX:**
```php
return response()->json(['success' => true, 'data' => $data]);
```

**Redirect with flash message:**
```php
return redirect()->route('cnxevents.events.index')
                 ->with('success', 'Event created successfully.');
```

---

## Remember

1. **Always** develop within `Modules/CnxEvents/` directory
2. **Always** use Bootstrap 3 classes (NOT Bootstrap 4/5)
3. **Always** prefix database tables with `cnx_`
4. **Always** use route names, not hardcoded URLs
5. **Always** validate user input and sanitize output
6. **Always** check user permissions for admin operations
7. **Always** include CSRF tokens in forms
8. **Always** use Eloquent ORM, avoid raw SQL
9. **Always** follow Laravel 5 conventions
10. **Always** test changes in FreeScout environment

---

**Version**: 2.0.0  
**Last Updated**: 2026-01-21  
**Maintained By**: Cyntrix Ltd
