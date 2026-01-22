# CnxEvents Module Implementation Plan

## Overview
CnxEvents is a hotel event and banquet management module for FreeScout. It provides built-in scheduling, venue management, and event handling with request/confirmation workflow. Key features include calendar views, paginated tables with filters, modal-based CRUD, settings for venues and custom fields, and analytics with KPIs.

## Models
- **Venue**: Represents physical venues (e.g., rooms, halls). Fields: name, description, capacity, features (JSON), custom_fields (JSON).
- **Event**: Represents events/banquets. Fields: title, description, start_datetime, end_datetime, setup_datetime, venue_release_datetime, all_day (boolean), venue_id, status (request/confirmed), user_id (requester), client_name, client_email, client_phone, client_company, custom_fields (JSON), created_at, updated_at. (Note: Events are not assigned to departments; custom fields are.)
- **Department**: Represents departments (e.g., Catering, AV). Fields: name, description, timestamps. Used for assigning custom fields and BEO generation.
- **CustomField**: Defines custom fields for events. Fields: name, type (text, select, date), options (JSON for select), is_required (boolean), timestamps. Assigned to multiple departments via pivot table. Values stored in Event's custom_fields JSON.

## Database Migrations
- Create `departments` table: id, name, description, timestamps.
- Create `custom_fields` table: id, name, type (enum: text, select, date), options (json), is_required (boolean, default false), timestamps.
- Create `custom_field_department` pivot table: custom_field_id, department_id.
- Create `venues` table: id, name, description, capacity, features (json), custom_fields (json), timestamps.
- Create `events` table: id, title, description, start_datetime, end_datetime, setup_datetime, venue_release_datetime, all_day (boolean), venue_id (foreign), status (enum: request, confirmed), user_id (foreign), client_name, client_email, client_phone, client_company, custom_fields (json), timestamps.
- Add indexes on status, venue_id, user_id, department_id for performance.

## Controllers
- **VenueController**: CRUD for venues (index, create, store, edit, update, destroy).
- **EventController**: CRUD for events (index, create, store, edit, update, destroy). Include confirm action to convert request to event. Load CustomField definitions for forms, enforce required fields validation, and store values in custom_fields JSON.
- **RequestController**: Similar to EventController but filtered for status='request'. Include confirm action.
- **DepartmentController**: CRUD for departments (index, create, store, edit, update, destroy).
- **CustomFieldController**: CRUD for custom fields (index, create, store, edit, update, destroy). Assign to multiple departments and set required status.
- **SettingsController**: Manage venues, venue features/fields, departments, and custom fields. Restrict access to admins using FreeScout's permission system.
- **AnalyticsController**: Generate reports and KPIs (e.g., total events, revenue, occupancy).
- **BeoController**: Generate and view Banquet Event Orders (BEO) for events, grouping custom fields by department.

## Views
- **Navigation**: Add dropdown in header with options: Calendar (confirmed, requests, both), Events, Requests, Settings, Analytics. Integrate into FreeScout's main navigation.
- **Calendar View**: Use a calendar library (e.g., FullCalendar) to display events/requests. Filter by status. Extend FreeScout's layout.
- **Events Index**: Table view with pagination, filters (date range, venue, user, department). Modal for create/edit. Extend FreeScout's layout.
- **Requests Index**: Similar to Events but only status='request'. Add "Confirm" button to convert to event. Extend FreeScout's layout.
- **Settings**: Forms for adding/editing venues, defining features/fields, custom fields per department. Extend FreeScout's layout.
- **Analytics**: Charts and tables for KPIs (e.g., events per month, venue utilization). Extend FreeScout's layout.

## Routes
- Define in `Http/routes.php`:
  - GET /cnxevents/venues -> VenueController@index
  - Resource routes for venues, events, requests
  - GET /cnxevents/settings -> SettingsController@index
  - GET /cnxevents/analytics -> AnalyticsController@index
  - POST /cnxevents/requests/{id}/confirm -> RequestController@confirm

## Settings
- **Departments Setup**: Add/edit departments (e.g., Catering, AV, Decor).
- **Venues Setup**: Add/edit venues with features (e.g., AV equipment) and custom fields (e.g., setup time).
- **Custom Fields Setup**: Define event custom fields (text, select, date) assigned to multiple departments. Specify options for select fields and mark as required. All fields must have at least one department attached for BEO generation.
- **BEO Templates**: Configure templates for Banquet Event Orders, pulling data from event custom fields grouped by department.

## Analytics
- **KPIs**: Total events, confirmed vs requests, revenue (if pricing added), venue occupancy rate, BEO generation count.
- **Reports**: Events by date, venue, department; BEO summaries; department-wise custom field usage.
- Use charting library (e.g., Chart.js) for visualizations.

## Dependencies
Add to `Modules/CnxEvents/composer.json`:
- "fullcalendar/fullcalendar": "^3.10" for calendar view
- "chartjs/chart.js": "^2.9" for analytics charts
- Any other packages as needed (e.g., for PDF BEO generation: "dompdf/dompdf")

## Implementation Steps
1. **Setup Module Structure** ✅ COMPLETED:
   - All required folders (Entities, Http/Controllers, Database/Migrations, Resources/views, etc.) are created.
   - Updated composer.json with required packages (FullCalendar, Chart.js, Dompdf for BEO PDFs).

2. **Create Migrations** ✅ COMPLETED:
   - Created migration files for departments, custom_fields, custom_field_department (pivot), venues, and events tables.
   - Defined table schemas with proper data types, foreign keys, and indexes. Included validation constraints in comments.

3. **Define Models** ✅ COMPLETED:
   - Created Department.php, CustomField.php, Venue.php, and Event.php in Entities/.
   - Added relationships: CustomField belongsToMany Department; Event belongsTo Venue, Event belongsTo User.
   - Added scopes for status filtering (scopeRequests, scopeConfirmed) on Event.
   - Added validation rules in Event model: setup_datetime < start_datetime < end_datetime < venue_release_datetime when not all_day (via saving event).

4. **Build Controllers** ✅ COMPLETED:
   - Implemented DepartmentController with full CRUD.
   - Implemented CustomFieldController with CRUD, department assignment via sync, and options handling.
   - Implemented VenueController with CRUD and JSON support.
   - Implemented EventController with CRUD, pagination, filters, confirm action, dynamic CustomField loading/validation, and custom_fields storage.
   - Implemented RequestController with filtered index and confirm action.
   - Implemented SettingsController with admin-only middleware.
   - Implemented AnalyticsController with KPI queries and chart data.
   - Implemented BeoController for viewing and PDF download of BEOs, grouping custom fields by department.

5. **Create Views** ✅ COMPLETED:
   - All views extend FreeScout's base layout for consistent integration.
   - Created index.blade.php for events with DataTables for pagination/filters and Bootstrap modals for forms. Include time fields and all_day toggle.
   - Created calendar.blade.php integrating FullCalendar with event sources, handling all_day events.
   - Created settings.blade.php with tabs for departments, custom fields, and venues.
   - Created analytics.blade.php with charts using Chart.js.
   - Created beo.blade.php for displaying/printing BEOs.
   - Updated layouts/app.blade.php to extend FreeScout's layout and use sidebar navigation.

6. **Add Routes** ✅ COMPLETED:
   - Registered comprehensive routes in routes.php: resources for departments, venues, events; custom routes for confirm, beo, analytics.
   - Grouped under 'cnxevents' prefix with proper middleware and admin restrictions.

7. **Implement Settings** ✅ COMPLETED:
   - ✅ Settings view with tabs for departments, custom fields, and venues
   - ✅ Tables display existing data with edit/delete actions
   - ✅ Bootstrap modals for CRUD operations
   - ✅ JavaScript for AJAX form handling
   - ✅ CRUD operations handled by separate controllers (DepartmentController, CustomFieldController, VenueController)

8. **Develop BEO Generation** ✅ COMPLETED:
   - ✅ BeoController with show() and pdf() methods
   - ✅ BEO view displays event details grouped by department
   - ✅ PDF generation using Dompdf with proper department grouping
   - ✅ Custom fields properly grouped by department for BEO display

9. **Develop Analytics** ✅ COMPLETED:
   - ✅ Analytics view with Chart.js integration
   - ✅ Basic KPI calculations in AnalyticsController index()
   - ✅ AJAX data endpoints (statusData, venueData, monthlyData) implemented for chart rendering

10. **Add Navigation** ✅ COMPLETED:
    - Updated FreeScout's header to include CnxEvents menu item using Eventy hooks, ensuring seamless integration with existing navigation.
    - Added menu selection logic for proper highlighting.
    - Ensure menu items link to module routes and respect permissions.

11. **Testing** 🔄 READY TO START:
    - Write unit tests for models and relationships
    - Write feature tests for controllers and CRUD operations
    - Test AJAX modals, filters, and confirm workflow
    - Validate form validation and error handling
    - Test UI responsiveness and FreeScout integration

12. **Refinement** 🔄 IN PROGRESS:
    - Add validation rules for forms, including time constraints and permissions.
    - Implement authorization/policies for access control using FreeScout's system.
    - Optimize queries for performance.
    - Add logging for actions like confirm and BEO generation.

## Additional Features to Consider
- **Time Slots**: Add start_time and end_time to events for precise scheduling.
- **Event Overlap Validation**: Prevent booking same venue at overlapping times.
- **Notifications**: Email alerts for new requests, confirmations, and reminders.
- **User Permissions**: Role-based access (e.g., managers can confirm, staff can view).
- **Advanced Search/Export**: Full-text search, CSV export for tables and reports.
- **Audit Logs**: Track changes to events, confirmations, and settings.
- **Recurring Events**: Support for repeating bookings.
- **Integrations**: Sync with external calendars (Google Calendar), payment gateways for deposits.

## Notes
- Use Laravel's pagination and query builders for filters.
- Ensure modals use AJAX for create/edit to avoid page reloads.
- For calendar, integrate FullCalendar with event sources for confirmed/requests, supporting all_day events.
- Custom fields: Defined in CustomField model with many-to-many department assignment and required flag. Values stored in Event's custom_fields JSON as field_id => value. Load fields dynamically in forms and views, with validation for required fields.
- Time slots: Enforce setup_datetime < start_datetime < end_datetime < venue_release_datetime. For all_day events, datetimes are optional.
- Permissions: Settings access restricted to admins via FreeScout's permission system.
- Confirm action updates status and logs activity.
- All views extend FreeScout's layout for integration.