@extends('layouts.admin')

@section('page-title')
    {{ __('Test Bank Statement Upload') }}
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Upload Bank Statement for Testing</h5>
            </div>
            <div class="card-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Bank Statement File (PDF)</label>
                        <input type="file" name="file" class="form-control" accept=".pdf" required>
                        <small class="text-muted">Upload a PDF bank statement to test extraction</small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Bank Name (Optional)</label>
                        <input type="text" name="bank_name" class="form-control" placeholder="e.g., Chase Bank">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Upload & Extract</button>
                </form>
                
                <div id="result" class="mt-4" style="display: none;">
                    <h5>Extraction Result:</h5>
                    <pre id="resultContent" style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const resultDiv = document.getElementById('result');
    const resultContent = document.getElementById('resultContent');
    
    resultDiv.style.display = 'block';
    resultContent.textContent = 'Processing...';
    
    try {
        const response = await fetch('{{ route("bank.statement.upload.direct") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const data = await response.json();
        
        resultContent.textContent = JSON.stringify(data, null, 2);
        
        if (data.success) {
            alert('Success! ' + data.message);
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        resultContent.textContent = 'Error: ' + error.message;
        alert('Upload failed: ' + error.message);
    }
});
</script>
@endsection