@extends('layouts.admin')

@section('page-title')
    Invoice Extraction
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Invoice Extraction</li>
@endsection

@section('content')
<div class="card shadow upload-card p-4" style="max-width: 550px; margin: 80px auto; border-radius: 12px;">
    <h3 class="text-center mb-3">📄 Invoice Extraction System</h3>
    <p class="text-center text-muted mb-4">Upload a PDF or image to extract details</p>

    <form method="POST" action="{{ route('invoice.process') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-bold">Select Invoice File:</label>
            <input type="file" name="file" accept=".pdf,.png,.jpg,.jpeg" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3">
            Upload & Extract
        </button>
    </form>
</div>

<style>
body { background: #f5f7fa; }
.upload-card {
    max-width: 550px;
    margin: 80px auto;
    border-radius: 12px;
}
</style>
@endsection
