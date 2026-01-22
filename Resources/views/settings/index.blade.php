@extends('cnxevents::layouts.app')

@section('title', 'Settings')

@section('stylesheets')
    <style>
        .modal {
            z-index: 100000 !important;
        }

        .modal-content {
            z-index: 100001 !important;
            position: relative !important;
        }

        .modal.show .modal-dialog {
            transform: translate(0, 0) !important;
        }

        .modal-dialog {
            margin-top: 10vh !important;
        }

        /* Bootstrap 3 tab behavior - ensure proper display */
        .tab-content > .tab-pane {
            display: none;
        }

        .tab-content > .tab-pane.active {
            display: block;
        }
    </style>
@endsection

@section('content')
    <div class="container" data-active-tab="{{ $activeTab }}">
        <h1>Settings</h1>
        
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('success') }}
            </div>
        @endif
        
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ session('error') }}
            </div>
        @endif
        
        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="departments-tab" data-toggle="tab" href="#departments"
                    role="tab">Departments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="custom-fields-tab" data-toggle="tab" href="#custom-fields" role="tab">Custom
                    Fields</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="venues-tab" data-toggle="tab" href="#venues" role="tab">Venues</a>
            </li>
        </ul>
        <div class="tab-content" id="settingsTabsContent">
            <!-- Departments Tab -->
            <div class="tab-pane fade in active" id="departments" role="tabpanel">
                @include('cnxevents::settings._departments')
            </div>
            <!-- Custom Fields Tab -->
            <div class="tab-pane fade" id="custom-fields" role="tabpanel">
                @include('cnxevents::settings._custom_fields')
            </div>
            <!-- Venues Tab -->
            <div class="tab-pane fade" id="venues" role="tabpanel">
                @include('cnxevents::settings._venues')
            </div>
        </div>
    </div>

    @stack('scripts')

    <script src="{{ \Module::asset('cnxevents:js/settings/tab-persistence.js') }}"></script>
    <script src="{{ \Module::asset('cnxevents:js/settings/departments.js') }}"></script>
    <script src="{{ \Module::asset('cnxevents:js/settings/custom-fields.js') }}"></script>
    <script src="{{ \Module::asset('cnxevents:js/settings/venues.js') }}"></script>
@endsection