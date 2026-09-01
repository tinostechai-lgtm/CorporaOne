@extends('layouts.admin')

@section('page-title')
    {{__('Import / Export Leads')}}
@endsection

@push('css-page')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .card-hover {
            transition: all 0.3s;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }
        .file-drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        .file-drop-zone:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .file-drop-zone.dragover {
            border-color: #667eea;
            background: #f0f2ff;
        }
        .preview-table {
            max-height: 300px;
            overflow-y: auto;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .modal-header.bg-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .import-history-item {
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .import-history-item:hover {
            background: #f8f9fa;
            border-left-color: #667eea;
        }
    </style>
@endpush

@push('script-page')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('leads.index')}}">{{__('Lead')}}</a></li>
    <li class="breadcrumb-item active">{{__('Import / Export')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('leads.index') }}" data-bs-toggle="tooltip" title="{{__('Back to Leads')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-arrow-left"></i> {{__('Back')}}
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">{{__('Total Leads')}}</p>
                            <h2 class="fw-bold mb-0">{{ $totalLeads ?? 0 }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="ti ti-users fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">{{__('Last Import')}}</p>
                            <h2 class="fw-bold mb-0">{{ $lastImportDate ?? 'Never' }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="ti ti-file-import fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">{{__('Last Export')}}</p>
                            <h2 class="fw-bold mb-0">{{ $lastExportDate ?? 'Never' }}</h2>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="ti ti-file-export fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Import Section -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-semibold">
                        <i class="ti ti-file-import me-2 text-primary"></i>{{__('Import Leads')}}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Steps -->
                    <div class="d-flex justify-content-between mb-4">
                        <div class="text-center flex-grow-1">
                            <div class="step-circle bg-primary bg-opacity-10 text-primary mx-auto mb-2">1</div>
                            <small class="text-muted">{{__('Download Template')}}</small>
                        </div>
                        <div class="text-center flex-grow-1">
                            <div class="step-circle bg-primary bg-opacity-10 text-primary mx-auto mb-2">2</div>
                            <small class="text-muted">{{__('Fill Data')}}</small>
                        </div>
                        <div class="text-center flex-grow-1">
                            <div class="step-circle bg-primary bg-opacity-10 text-primary mx-auto mb-2">3</div>
                            <small class="text-muted">{{__('Upload File')}}</small>
                        </div>
                        <div class="text-center flex-grow-1">
                            <div class="step-circle bg-primary bg-opacity-10 text-primary mx-auto mb-2">4</div>
                            <small class="text-muted">{{__('Confirm Import')}}</small>
                        </div>
                    </div>

                    <!-- Download Template -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>{{__('Sample Template')}}</strong>
                                <p class="mb-0 small">{{__('Download the sample template to see the required format.')}}</p>
                            </div>
                            <button class="btn btn-sm btn-primary" onclick="downloadTemplate()">
                                <i class="ti ti-download"></i> {{__('Download Template')}}
                            </button>
                        </div>
                    </div>

                    <!-- File Upload Zone -->
                    <div class="file-drop-zone mb-4" id="fileDropZone" onclick="document.getElementById('importFile').click()">
                        <i class="ti ti-cloud-upload fs-1 text-muted mb-2"></i>
                        <p class="mb-1">{{__('Click or drag file here to upload')}}</p>
                        <small class="text-muted">{{__('Supported formats: CSV, XLSX, XLS. Max size: 10MB')}}</small>
                        <input type="file" id="importFile" class="d-none" accept=".csv,.xlsx,.xls" onchange="previewFile(this)">
                    </div>

                    <!-- Preview Section -->
                    <div id="previewSection" style="display: none;">
                        <h6 class="fw-semibold mb-2">{{__('Data Preview')}}</h6>
                        <div class="table-responsive preview-table mb-3">
                            <table class="table table-sm table-bordered" id="previewTable">
                                <thead id="previewHeader"></thead>
                                <tbody id="previewBody"></tbody>
                            </table>
                        </div>
                        
                        <!-- Column Mapping -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{__('Column Mapping')}}</label>
                            <div id="mappingFields"></div>
                        </div>

                        <!-- Import Options -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{__('Import Options')}}</label>
                            <div class="form-check mb-2">
                                <input type="checkbox" id="skipDuplicates" class="form-check-input" checked>
                                <label class="form-check-label" for="skipDuplicates">{{__('Skip duplicate emails')}}</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="sendNotification" class="form-check-input" checked>
                                <label class="form-check-label" for="sendNotification">{{__('Send notification to assigned users')}}</label>
                            </div>
                        </div>

                        <button class="btn btn-gradient w-100" onclick="processImport()">
                            <i class="ti ti-device-floppy"></i> {{__('Import Leads')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Section -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-semibold">
                        <i class="ti ti-file-export me-2 text-success"></i>{{__('Export Leads')}}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <!-- Export Options -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">{{__('Export Format')}}</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input type="radio" name="exportFormat" id="formatExcel" class="form-check-input" value="excel" checked>
                                <label class="form-check-label" for="formatExcel">
                                    <i class="ti ti-file-spreadsheet text-success"></i> {{__('Excel (XLSX)')}}
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="radio" name="exportFormat" id="formatCSV" class="form-check-input" value="csv">
                                <label class="form-check-label" for="formatCSV">
                                    <i class="ti ti-file-text text-info"></i> {{__('CSV')}}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Options -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">{{__('Filter Options (Optional)')}}</label>
                        <select class="form-select mb-2" id="exportPipeline">
                            <option value="">{{__('All Pipelines')}}</option>
                            @foreach($pipelines ?? [] as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <select class="form-select mb-2" id="exportSource">
                            <option value="">{{__('All Sources')}}</option>
                            <option value="manual">{{__('Manual')}}</option>
                            <option value="facebook">{{__('Facebook')}}</option>
                            <option value="instagram">{{__('Instagram')}}</option>
                            <option value="whatsapp">{{__('WhatsApp')}}</option>
                        </select>
                        <select class="form-select" id="exportStatus">
                            <option value="">{{__('All Status')}}</option>
                            <option value="active">{{__('Active')}}</option>
                            <option value="converted">{{__('Converted')}}</option>
                        </select>
                    </div>

                    <!-- Fields Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">{{__('Fields to Export')}}</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="fieldName" checked>
                                    <label class="form-check-label" for="fieldName">{{__('Name')}}</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="fieldEmail" checked>
                                    <label class="form-check-label" for="fieldEmail">{{__('Email')}}</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="fieldPhone" checked>
                                    <label class="form-check-label" for="fieldPhone">{{__('Phone')}}</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="fieldSubject" checked>
                                    <label class="form-check-label" for="fieldSubject">{{__('Subject')}}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="fieldSource" checked>
                                    <label class="form-check-label" for="fieldSource">{{__('Lead Source')}}</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="fieldStage" checked>
                                    <label class="form-check-label" for="fieldStage">{{__('Stage')}}</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="fieldScore" checked>
                                    <label class="form-check-label" for="fieldScore">{{__('Lead Score')}}</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="fieldCreated" checked>
                                    <label class="form-check-label" for="fieldCreated">{{__('Created Date')}}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Buttons -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-gradient" onclick="exportLeads()">
                            <i class="ti ti-download"></i> {{__('Export Leads')}}
                        </button>
                        <button class="btn btn-outline-secondary" onclick="exportSample()">
                            <i class="ti ti-file"></i> {{__('Export Sample Data')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import History Section -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-semibold">
                <i class="ti ti-clock-history me-2 text-warning"></i>{{__('Import History')}}
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{__('Date')}}</th>
                            <th>{{__('Filename')}}</th>
                            <th>{{__('Records Imported')}}</th>
                            <th>{{__('Status')}}</th>
                            <th>{{__('Imported By')}}</th>
                        </tr>
                    </thead>
                    <tbody id="importHistoryTable">
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="ti ti-database fs-1 d-block mb-2"></i>
                                {{__('No import history found')}}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
let importedData = null;
let fileHeaders = [];

// Download Template
function downloadTemplate() {
    const templateData = [
        ['Name', 'Email', 'Phone', 'Subject', 'Lead Source', 'Notes'],
        ['John Doe', 'john@example.com', '+1234567890', 'Interested in product', 'manual', 'Follow up next week'],
        ['Jane Smith', 'jane@example.com', '+1987654321', 'Need consultation', 'facebook', 'Called and interested']
    ];
    
    const ws = XLSX.utils.aoa_to_sheet(templateData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Lead Template');
    XLSX.writeFile(wb, 'lead_import_template.xlsx');
}

// File Preview
function previewFile(input) {
    const file = input.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
        const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1, defval: "" });
        
        if (jsonData.length > 0) {
            fileHeaders = jsonData[0];
            const previewData = jsonData.slice(1, 6); // First 5 rows for preview
            
            // Display preview
            displayPreview(fileHeaders, previewData);
            
            // Show column mapping
            displayColumnMapping(fileHeaders);
            
            // Show preview section
            document.getElementById('previewSection').style.display = 'block';
            
            // Store imported data
            importedData = jsonData.slice(1);
        }
    };
    reader.readAsArrayBuffer(file);
}

function displayPreview(headers, data) {
    const headerRow = document.getElementById('previewHeader');
    const bodyRow = document.getElementById('previewBody');
    
    headerRow.innerHTML = '<tr>' + headers.map(h => `<th>${h}</th>`).join('') + '</tr>';
    
    bodyRow.innerHTML = data.map(row => 
        '<tr>' + row.map(cell => `<td>${cell || '-'}</td>`).join('') + '</tr>'
    ).join('');
}

function displayColumnMapping(headers) {
    const mappingDiv = document.getElementById('mappingFields');
    const fields = ['name', 'email', 'phone', 'subject', 'lead_source', 'notes'];
    
    mappingDiv.innerHTML = '<div class="row g-2">';
    headers.forEach((header, index) => {
        mappingDiv.innerHTML += `
            <div class="col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">${header}</span>
                    <select class="form-select" data-column="${index}" id="map_${index}">
                        <option value="">-- Skip --</option>
                        ${fields.map(f => `<option value="${f}" ${header.toLowerCase().includes(f) ? 'selected' : ''}>${f.toUpperCase()}</option>`).join('')}
                    </select>
                </div>
            </div>
        `;
    });
    mappingDiv.innerHTML += '</div>';
}

function processImport() {
    if (!importedData || importedData.length === 0) {
        Swal.fire('Error', 'No data to import', 'error');
        return;
    }
    
    // Get column mapping
    const mapping = {};
    for (let i = 0; i < fileHeaders.length; i++) {
        const select = document.getElementById(`map_${i}`);
        if (select && select.value) {
            mapping[select.value] = i;
        }
    }
    
    // Transform data based on mapping
    const leads = [];
    for (const row of importedData) {
        const lead = {};
        for (const [field, colIndex] of Object.entries(mapping)) {
            lead[field] = row[colIndex] || '';
        }
        if (lead.name && lead.email) {
            leads.push(lead);
        }
    }
    
    if (leads.length === 0) {
        Swal.fire('Error', 'No valid leads found. Please check column mapping.', 'error');
        return;
    }
    
    Swal.fire({
        title: 'Confirm Import',
        text: `You are about to import ${leads.length} leads. Continue?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, import',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) {
            performImport(leads);
        }
    });
}

function performImport(leads) {
    Swal.fire({
        title: 'Importing Leads...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('{{ route("leads.import.process") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            leads: leads,
            skip_duplicates: document.getElementById('skipDuplicates').checked,
            send_notification: document.getElementById('sendNotification').checked
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Swal.fire('Success!', data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            Swal.fire('Error', data.error || 'Import failed', 'error');
        }
    })
    .catch(error => {
        Swal.close();
        Swal.fire('Error', error.message, 'error');
    });
}

// Export Functions
function exportLeads() {
    const format = document.querySelector('input[name="exportFormat"]:checked').value;
    const pipeline = document.getElementById('exportPipeline').value;
    const source = document.getElementById('exportSource').value;
    const status = document.getElementById('exportStatus').value;
    
    // Build fields array
    const fields = [];
    if (document.getElementById('fieldName').checked) fields.push('name');
    if (document.getElementById('fieldEmail').checked) fields.push('email');
    if (document.getElementById('fieldPhone').checked) fields.push('phone');
    if (document.getElementById('fieldSubject').checked) fields.push('subject');
    if (document.getElementById('fieldSource').checked) fields.push('lead_source');
    if (document.getElementById('fieldStage').checked) fields.push('stage');
    if (document.getElementById('fieldScore').checked) fields.push('lead_score');
    if (document.getElementById('fieldCreated').checked) fields.push('created_at');
    
    Swal.fire({
        title: 'Exporting Leads...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('{{ route("leads.export.process") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            format: format,
            pipeline: pipeline,
            source: source,
            status: status,
            fields: fields
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            // Download file
            window.location.href = data.download_url;
            Swal.fire('Success!', 'Export completed', 'success');
        } else {
            Swal.fire('Error', data.error || 'Export failed', 'error');
        }
    })
    .catch(error => {
        Swal.close();
        Swal.fire('Error', error.message, 'error');
    });
}

function exportSample() {
    // Export sample data (first 10 leads)
    Swal.fire({
        title: 'Exporting Sample...',
        didOpen: () => Swal.showLoading()
    });
    
    fetch('{{ route("leads.export.sample") }}')
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            window.location.href = data.download_url;
        } else {
            Swal.fire('Error', data.error || 'Export failed', 'error');
        }
    })
    .catch(error => {
        Swal.close();
        Swal.fire('Error', error.message, 'error');
    });
}

// Drag and drop functionality
const dropZone = document.getElementById('fileDropZone');
if (dropZone) {
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && (file.type.includes('spreadsheet') || file.name.endsWith('.csv'))) {
            document.getElementById('importFile').files = e.dataTransfer.files;
            previewFile({ files: [file] });
        } else {
            Swal.fire('Error', 'Please upload a valid Excel or CSV file', 'error');
        }
    });
}
</script>
@endsection