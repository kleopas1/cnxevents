<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\CnxEvents\Http\Controllers'], function()
{
    Route::get('/', 'CnxEventsController@index');
});
