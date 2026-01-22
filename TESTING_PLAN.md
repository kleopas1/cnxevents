# CnxEvents Module Testing Plan

## Pre-Testing Setup
1. **Activate Module**
   ```bash
   cd /path/to/freescout
   php artisan module:enable "Hotel Events and Banquets Management"
   ```

2. **Run Migrations**
   ```bash
   php artisan migrate
   ```

3. **Clear Cache**
   ```bash
   php artisan route:clear && php artisan config:clear && php artisan view:clear
   ```

4. **Create Test Admin User**
   - Ensure you have an admin user account in FreeScout
   - Log in as admin to access settings

## Testing Checklist

### ✅ **1. Module Activation & Navigation**
- [✅] "Events" button appears in FreeScout's main navigation
- [✅] Clicking "Events" navigates to `/cnxevents/events`
- [✅] Sidebar shows: Events, Calendar, Analytics (Settings only for admins)
- [✅] Active menu item highlights correctly when navigating
- [✅] FreeScout navbar and layout remain intact

### ✅ **2. Settings Management (Admin Only)**
#### Departments Tab
- [✅] Can view existing departments in table
- [✅] "Add Department" button opens modal
- [✅] Can create new department (name field and description)
- [✅] Can edit existing department
- [✅] Can delete department (with confirmation)
- [✅] Table updates after CRUD operations

#### Custom Fields Tab
- [✅] Can view existing custom fields with type, required status, departments
- [✅] "Add Custom Field" button opens modal
- [✅] Can create custom field with: name, type (text/select/date), required checkbox, department selection
- [✅] Select type shows options textarea when selected
- [✅] Can edit existing custom field
- [✅] Can delete custom field
- [✅] Department assignment works (many-to-many)

#### Venues Tab
- [✅] Can view existing venues with name and capacity
- [✅] "Add Venue" button opens modal
- [✅] Can create venue with name and capacity
- [✅] Can edit existing venue
- [✅] Can delete venue
- [✅] Table updates after CRUD operations

### ✅ **3. Events Management**
#### Events Index Page
- [✅] Page loads with table showing: Title, Venue, Start, End, Status, Actions
- [✅] "Add Event" button opens modal
- [ ] Filters work: Status (All/Request/Confirmed), Venue, Date Range
- [ ] Pagination works if many events
- [ ] Table shows correct data

#### Create/Edit Event Modal
- [ ] All fields present: Title, Description, Venue dropdown, All Day checkbox
- [ ] Datetime fields: Start, End, Setup, Venue Release
- [ ] Client fields: Name, Email, Phone, Company
- [ ] Dynamic custom fields appear based on venue/department assignments
- [ ] Required fields marked with asterisk
- [ ] Form validation works (required fields, datetime logic)
- [ ] Can save new event
- [ ] Can edit existing event
- [ ] Modal closes and table updates after save

#### Event Actions
- [ ] Edit button opens modal with populated data
- [ ] Delete button shows confirmation and removes event
- [ ] Confirm button (for requests) changes status to confirmed
- [ ] Status changes reflected in table

### ✅ **4. Calendar View**
- [ ] FullCalendar loads on page
- [ ] Events display on calendar with correct dates
- [ ] Different colors for request vs confirmed events
- [ ] Clicking event opens details modal
- [ ] Modal shows: title, description, venue, times, status, client info, custom fields
- [ ] "Edit Event" button in modal works
- [ ] Calendar navigation (month/week/day) works
- [ ] Today button works

### ✅ **5. Analytics Dashboard**
#### KPI Cards
- [ ] Total Events count displays
- [ ] Confirmed Events count displays
- [ ] Request Events count displays
- [ ] Venue Utilization shows

#### Charts
- [ ] Events by Status pie chart loads and shows correct data
- [ ] Events by Venue bar chart loads and shows correct data
- [ ] Monthly Events line chart loads and shows last 12 months
- [ ] Charts are interactive (tooltips, legends)
- [ ] Charts update when data changes

### ✅ **6. BEO (Banquet Event Order)**
#### BEO Display
- [ ] BEO page loads for specific event
- [ ] Shows event details: title, venue, times, status, client info
- [ ] Custom fields grouped by department (not as flat list)
- [ ] Each department shows as heading with its fields
- [ ] Only fields with values for this event appear

#### PDF Generation
- [ ] "Generate PDF" button downloads PDF file
- [ ] PDF contains all event details
- [ ] PDF shows custom fields grouped by department
- [ ] PDF is properly formatted and readable

#### Department Selection
- [ ] Department checkboxes appear on BEO page
- [ ] Can select/deselect departments
- [ ] "Update Departments" button saves selection
- [ ] Selection affects BEO grouping

### ✅ **7. Business Logic Validation**
#### Time Constraints
- [ ] Cannot save event where setup_datetime >= start_datetime
- [ ] Cannot save event where start_datetime >= end_datetime
- [ ] Cannot save event where end_datetime >= venue_release_datetime
- [ ] All day events don't require datetime fields

#### Custom Field Logic
- [ ] Custom fields only appear for events in assigned departments
- [ ] Required custom fields must be filled
- [ ] Select fields show dropdown with defined options
- [ ] Date fields show date picker

#### Status Workflow
- [ ] New events default to "request" status
- [ ] Only admins can confirm requests
- [ ] Confirmed events cannot be changed back to request
- [ ] Status affects calendar colors

### ✅ **8. Permissions & Security**
- [ ] Non-admin users cannot access settings
- [ ] Admin users can access all settings tabs
- [ ] Route protection works (middleware)
- [ ] CSRF protection on forms
- [ ] SQL injection protection (Eloquent ORM)

### ✅ **9. UI/UX Testing**
#### Responsive Design
- [ ] Layout works on desktop (1200px+)
- [ ] Layout works on tablet (768px-1199px)
- [ ] Layout works on mobile (<768px)
- [ ] Modals work on all screen sizes
- [ ] Tables are scrollable on mobile

#### FreeScout Integration
- [ ] Module sidebar doesn't break FreeScout layout
- [ ] FreeScout navbar remains functional
- [ ] No CSS conflicts with FreeScout styles
- [ ] Consistent styling with FreeScout theme

#### User Experience
- [ ] Loading states for AJAX operations
- [ ] Success/error messages display correctly
- [ ] Form validation messages are clear
- [ ] Confirmation dialogs prevent accidental actions
- [ ] Intuitive navigation between sections

### ✅ **10. Edge Cases & Error Handling**
#### Empty States
- [ ] Events table shows message when no events
- [ ] Analytics show zeros when no data
- [ ] BEO shows message when no custom fields

#### Error Scenarios
- [ ] Invalid dates show validation errors
- [ ] Missing required fields show errors
- [ ] Non-existent event IDs show 404
- [ ] Database errors are handled gracefully

#### Data Integrity
- [ ] Deleting department removes field assignments
- [ ] Deleting venue affects related events
- [ ] Orphaned records don't break functionality

### ✅ **11. Performance Testing**
- [ ] Page loads within 2 seconds
- [ ] AJAX requests complete within 1 second
- [ ] Large datasets (100+ events) load without issues
- [ ] Calendar with many events renders smoothly
- [ ] PDF generation completes within 5 seconds

## Post-Testing Actions
1. **Fix any issues found during testing**
2. **Run tests again to verify fixes**
3. **Document any known limitations**
4. **Prepare for production deployment**

## Testing Tools Needed
- Web browser (Chrome/Firefox recommended)
- Admin user account in FreeScout
- Test data (sample departments, venues, custom fields, events)
- PDF viewer for BEO testing
- Different screen sizes for responsive testing

## Expected Test Duration
- Basic functionality: 30-45 minutes
- Comprehensive testing: 2-3 hours
- Full regression testing: 4-6 hours

---
**Note**: Mark each checkbox as you complete the test. If any test fails, document the issue and expected vs actual behavior.</content>
<parameter name="filePath">c:\laragon\www\freescout\Modules\CnxEvents\TESTING_PLAN.md