<?php

\Log::info('CnxEvents routes file loaded');

Route::group(['middleware' => ['web', 'auth'], 'prefix' => \Helper::getSubdirectory() . 'cnxevents', 'namespace' => 'Modules\CnxEvents\Http\Controllers', 'as' => 'cnxevents.'], function()
{
    // Events
    Route::resource('events', 'EventController');
    Route::post('events/{event}/confirm', 'EventController@confirm')->name('events.confirm');
    Route::post('events/{event}/cancel', 'EventController@cancel')->name('events.cancel');
    Route::post('events/{event}/activate', 'EventController@activate')->name('events.activate');

    // Calendar
    Route::get('calendar', 'CalendarController@index')->name('calendar');
    Route::get('calendar/events', 'CalendarController@events')->name('calendar.events');

    // Analytics
    Route::get('analytics', 'AnalyticsController@index')->name('analytics');
    Route::get('analytics/status', 'AnalyticsController@statusData')->name('analytics.status');
    Route::get('analytics/venue', 'AnalyticsController@venueData')->name('analytics.venue');
    Route::get('analytics/monthly', 'AnalyticsController@monthlyData')->name('analytics.monthly');

    // Settings (Admin only)
    Route::get('settings', ['uses' => 'SettingsController@index', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']])->name('settings.index');
    Route::resource('departments', 'DepartmentController', ['middleware' => ['auth', 'roles'], 'roles' => ['admin']]);
    Route::resource('custom-fields', 'CustomFieldController', ['middleware' => ['auth', 'roles'], 'roles' => ['admin']]);
    Route::resource('venues', 'VenueController', ['middleware' => ['auth', 'roles'], 'roles' => ['admin']]);

    // BEO
    Route::get('beo/{event}', 'BeoController@show')->name('beo.show');
    Route::get('beo/{event}/pdf', 'BeoController@pdf')->name('beo.pdf');
    Route::post('beo/{event}/update-departments', 'BeoController@updateDepartments')->name('beo.update-departments');
});