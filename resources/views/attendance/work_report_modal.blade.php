<!-- Work Report Modal -->
<div class="modal fade" id="workReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title text-white">
                    <i class="ti ti-clipboard-list me-2"></i>
                    Work Report - {{ date('d M Y') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            Please fill in your work details for today before clocking out.
                        </div>
                    </div>
                </div>

                <form id="workReportForm" action="{{ route('workreport.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="employee_id" id="wr_employee_id">
                    <input type="hidden" name="attendance_id" id="wr_attendance_id">
                    <input type="hidden" name="date" id="wr_date">
                    <input type="hidden" name="clock_in" id="wr_clock_in">
                    <input type="hidden" name="clock_out" id="wr_clock_out">

                    <div class="row">
                        <!-- Attendance Summary -->
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="ti ti-clock me-2"></i>Attendance Summary</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td><strong>Clock In:</strong></td>
                                            <td id="wr_summary_clock_in">--:--</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Clock Out:</strong></td>
                                            <td id="wr_summary_clock_out">--:--</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Worked Hours:</strong></td>
                                            <td id="wr_summary_worked_hours">--:--</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td id="wr_summary_status">--</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Tasks -->
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="ti ti-list-check me-2"></i>Quick Tasks</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="task_meeting" value="Meeting" name="quick_tasks[]">
                                        <label class="form-check-label" for="task_meeting">Meeting</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="task_email" value="Email" name="quick_tasks[]">
                                        <label class="form-check-label" for="task_email">Email</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="task_coding" value="Coding" name="quick_tasks[]">
                                        <label class="form-check-label" for="task_coding">Coding</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="task_documentation" value="Documentation" name="quick_tasks[]">
                                        <label class="form-check-label" for="task_documentation">Documentation</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="task_design" value="Design" name="quick_tasks[]">
                                        <label class="form-check-label" for="task_design">Design</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="task_testing" value="Testing" name="quick_tasks[]">
                                        <label class="form-check-label" for="task_testing">Testing</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Work Description -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="ti ti-notes me-2"></i>Work Description</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="work_description" class="form-label">What did you work on today? <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="work_description" name="work_description" rows="4" placeholder="Describe your work today..." required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Achievements -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="ti ti-trophy me-2"></i>Achievements</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <textarea class="form-control" id="achievements" name="achievements" rows="3" placeholder="What did you achieve today?"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="ti ti-challenges me-2"></i>Challenges</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <textarea class="form-control" id="challenges" name="challenges" rows="3" placeholder="Any challenges faced today?"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tomorrow's Plan -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="ti ti-calendar-event me-2"></i>Tomorrow's Plan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <textarea class="form-control" id="tomorrow_plan" name="tomorrow_plan" rows="3" placeholder="What do you plan to do tomorrow?"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hourly Breakdown -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="ti ti-hourglass me-2"></i>Hourly Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Project Work</label>
                                                <input type="number" class="form-control" name="hours_project" id="hours_project" min="0" max="12" step="0.5" placeholder="Hours">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Meetings</label>
                                                <input type="number" class="form-control" name="hours_meeting" id="hours_meeting" min="0" max="12" step="0.5" placeholder="Hours">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="form-label">Admin/Other</label>
                                                <input type="number" class="form-control" name="hours_admin" id="hours_admin" min="0" max="12" step="0.5" placeholder="Hours">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-muted small mt-2">
                                        <i class="ti ti-info-circle me-1"></i>
                                        Total hours should match your worked hours ({{ number_format($workedHours ?? 0, 1) }} hrs)
                                        <span id="hours_total_warning" class="text-danger d-none">⚠️ Total doesn't match worked hours!</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Skip
                </button>
                <button type="button" class="btn btn-primary" id="submitWorkReportBtn">
                    <i class="ti ti-send me-1"></i> Submit Report & Clock Out
                </button>
            </div>
        </div>
    </div>
</div>