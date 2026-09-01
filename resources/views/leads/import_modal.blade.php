<!-- Import Column Mapping Modal -->
<div class="modal fade" id="importModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Map Columns for Import') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="importDataContainer">
                    <!-- Data will be loaded here via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                        </div>
                        <p class="mt-2">{{ __('Processing file...') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="confirmImportBtn">{{ __('Import Data') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Column Mapping Template -->
<script id="importMappingTemplate" type="text/template">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>{{ __('Column Name') }}</th>
                    <th>{{ __('Map to Field') }}</th>
                    <th>{{ __('Sample Data') }}</th>
                </tr>
            </thead>
            <tbody id="mappingBody">
                <!-- Dynamic content -->
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        <div class="alert alert-info">
            <i class="ti ti-info-circle"></i>
            {{ __('Please map each column to the appropriate lead field. Required fields: Name, Email, Subject') }}
        </div>
    </div>
</script>

@push('js')
<script>
$(document).ready(function() {
    // Handle file selection and show mapping modal
    $('#importForm').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("leads.import.preview") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.html) {
                    $('#importDataContainer').html(response.html);
                    $('#importModal').modal('show');
                } else {
                    showToast('error', response.message || '{{ __("Error processing file") }}');
                }
            },
            error: function(xhr) {
                var error = xhr.responseJSON?.message || '{{ __("Error processing file") }}';
                showToast('error', error);
            }
        });
    });
    
    // Confirm import
    $('#confirmImportBtn').click(function() {
        var mappingData = {};
        $('.column-mapping').each(function() {
            var columnIndex = $(this).data('column-index');
            mappingData[columnIndex] = $(this).val();
        });
        
        $.ajax({
            url: '{{ route("leads.import.process") }}',
            type: 'POST',
            data: {
                mapping: mappingData,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    $('#importModal').modal('hide');
                    showToast('success', response.message);
                    setTimeout(function() {
                        window.location.href = '{{ route("leads.index") }}';
                    }, 2000);
                } else {
                    showToast('error', response.message);
                }
            },
            error: function(xhr) {
                showToast('error', '{{ __("Import failed") }}');
            }
        });
    });
    
    function showToast(type, message) {
        // Implement your toast notification
        alert(message);
    }
});
</script>
@endpush