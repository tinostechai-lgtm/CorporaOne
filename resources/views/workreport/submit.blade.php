@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h4>Submit Daily Work Report</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('workreport.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label>Date</label>
                <input type="date" name="report_date" class="form-control" value="{{ old('report_date', today()->format('Y-m-d')) }}" required>
            </div>
            <div class="mb-3">
                <label>Tasks Completed</label>
                <textarea name="tasks_completed" class="form-control" rows="4" required>{{ old('tasks_completed') }}</textarea>
            </div>
            <div class="mb-3">
                <label>Hours Worked</label>
                <input type="number" step="0.5" name="hours_worked" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Challenges Faced (optional)</label>
                <textarea name="challenges" class="form-control" rows="3">{{ old('challenges') }}</textarea>
            </div>
            <div class="mb-3">
                <label>Plan for Tomorrow (optional)</label>
                <textarea name="plan_for_tomorrow" class="form-control" rows="3">{{ old('plan_for_tomorrow') }}</textarea>
            </div>
            <div class="mb-3">
                <label>Attachment (optional)</label>
                <input type="file" name="attachment" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Submit Report</button>
        </form>
    </div>
</div>
@endsection