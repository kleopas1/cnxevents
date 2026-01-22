@extends('layouts.app')

@section('title', $__env->yieldContent('title'))

@section('sidebar')
<div class="sidebar-title">
    {{ __('Events') }}
</div>
<ul class="sidebar-menu">
    <li class="{{ request()->routeIs('cnxevents.events.*') ? 'active' : '' }}">
        <a href="{{ route('cnxevents.events.index') }}">
            <i class="glyphicon glyphicon-calendar"></i> {{ __('Events') }}
        </a>
    </li>
    <li class="{{ request()->routeIs('cnxevents.calendar') ? 'active' : '' }}">
        <a href="{{ route('cnxevents.calendar') }}">
            <i class="glyphicon glyphicon-th"></i> {{ __('Calendar') }}
        </a>
    </li>
    <li class="{{ request()->routeIs('cnxevents.analytics') ? 'active' : '' }}">
        <a href="{{ route('cnxevents.analytics') }}">
            <i class="glyphicon glyphicon-stats"></i> {{ __('Analytics') }}
        </a>
    </li>
    @if(Auth::user()->isAdmin())
        <li class="{{ request()->routeIs('cnxevents.settings.*') ? 'active' : '' }}">
            <a href="{{ route('cnxevents.settings.index') }}">
                <i class="glyphicon glyphicon-cog"></i> {{ __('Settings') }}
            </a>
        </li>
    @endif
</ul>
@endsection

@section('content')
<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @yield('content')
</div>
@endsection

@section('stylesheets')
<style>
.sidebar-menu li.active a {
    background-color: #f5f5f5;
    font-weight: bold;
}

/* Modal fixes for Bootstrap 3 compatibility */
.modal-backdrop {
    z-index: 1040 !important;
}
.modal {
    z-index: 10000 !important;
}
/* Push modal down below the header */
.modal-dialog {
    margin-top: 70px;
}
</style>
@yield('stylesheets')
@endsection

@section('scripts')
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
@yield('scripts')
@stack('scripts')
@endsection