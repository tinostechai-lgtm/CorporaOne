@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Voxbay IVR Settings</h5>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('ivr.settings.save') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Voxbay API Key</label>
                    <input type="text" name="voxbay_api_key" class="form-control"
                           value="{{ old('voxbay_api_key', $settings['voxbay_api_key'] ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Voxbay API Secret</label>
                    <input type="text" name="voxbay_api_secret" class="form-control"
                           value="{{ old('voxbay_api_secret', $settings['voxbay_api_secret'] ?? '') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Voxbay Base URL</label>
                    <input type="url" name="voxbay_base_url" class="form-control"
                           value="{{ old('voxbay_base_url', $settings['voxbay_base_url'] ?? '') }}"
                           placeholder="https://api.voxbay.in/api" required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        Save Settings
                    </button>

                    <button type="button" id="testConnection" class="btn btn-secondary">
                        Test Connection
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('testConnection').addEventListener('click', function () {
    fetch("{{ route('ivr.test.connection') }}")
        .then(res => res.json())
        .then(data => {
            alert(data.message);
        })
        .catch(() => {
            alert('Connection failed!');
        });
});
</script>
@endsection
