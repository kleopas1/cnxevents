@extends('cnxevents::layouts.app')

@section('title', 'Banquet Event Order (BEO)')

@section('content')
<div class="container">
    <h1>Banquet Event Order (BEO)</h1>
    @if($event)
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>{{ $event->title }}</h3>
                        <a href="{{ route('cnxevents.beo.pdf', $event->id) }}" class="btn btn-primary" target="_blank">Generate PDF</a>
                    </div>
                    <div class="card-body">
                        <p><strong>Description:</strong> {{ $event->description }}</p>
                        <p><strong>Venue:</strong> {{ $event->venue->name }}</p>
                        <p><strong>Start:</strong> {{ $event->start_datetime->format('Y-m-d H:i') }}</p>
                        <p><strong>End:</strong> {{ $event->end_datetime->format('Y-m-d H:i') }}</p>
                        <p><strong>Setup:</strong> {{ $event->setup_datetime ? $event->setup_datetime->format('Y-m-d H:i') : 'N/A' }}</p>
                        <p><strong>Venue Release:</strong> {{ $event->venue_release_datetime ? $event->venue_release_datetime->format('Y-m-d H:i') : 'N/A' }}</p>
                        <p><strong>Status:</strong> {{ ucfirst($event->status) }}</p>
                        <p><strong>Client Name:</strong> {{ $event->client_name }}</p>
                        <p><strong>Client Email:</strong> {{ $event->client_email }}</p>
                        <p><strong>Client Phone:</strong> {{ $event->client_phone }}</p>
                        <p><strong>Client Company:</strong> {{ $event->client_company }}</p>
                        @if($event->custom_fields)
                            <h4>Department Requirements</h4>
                            @if(count($beoData) > 0)
                                @foreach($beoData as $department => $fields)
                                    <h5>{{ $department }}</h5>
                                    <ul>
                                        @foreach($fields as $fieldData)
                                            <li><strong>{{ $fieldData['field'] }}:</strong> {{ $fieldData['value'] }}</li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            @else
                                <p>No department-specific requirements found.</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3>Departments</h3>
                    </div>
                    <div class="card-body">
                        @foreach($departments as $department)
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input department-checkbox" id="dept_{{ $department->id }}" value="{{ $department->id }}">
                                <label class="form-check-label" for="dept_{{ $department->id }}">{{ $department->name }}</label>
                            </div>
                        @endforeach
                        <button class="btn btn-secondary mt-3" onclick="updateDepartments()">Update Departments</button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <p>Select an event to view BEO.</p>
    @endif
</div>

@endsection

@section('scripts')
<script>
function updateDepartments() {
    const selectedDepartments = Array.from(document.querySelectorAll('.department-checkbox:checked')).map(cb => cb.value);
    fetch('{{ route("cnxevents.beo.update-departments", $event->id ?? 0) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ departments: selectedDepartments })
    })
    .then(response => response.json())
    .then(data => {
        alert('Departments updated successfully!');
    })
    .catch(error => {
        alert('Error updating departments.');
    });
}
</script>
@endsection