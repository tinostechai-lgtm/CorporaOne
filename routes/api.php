<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankReconciliationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CompanyPolicyController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\HrAdminController;
use App\Http\Controllers\Api\HrmSystemController;
use App\Http\Controllers\Api\IvrController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\MeetingController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\PerformanceController;
use App\Http\Controllers\Api\PipelineController;
use App\Http\Controllers\Api\RecruitmentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkReportController;

// ========== FACE ATTENDANCE CONTROLLER ==========
use App\Http\Controllers\Api\ApiFaceAttendanceController;

// ========== NEW HR MODULE CONTROLLERS ==========
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\TravelController;
use App\Http\Controllers\Api\ResignationController;
use App\Http\Controllers\Api\AwardController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\EmployeeTaskController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WarningController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\GoalTrackingController;
use App\Http\Controllers\Api\IndicatorController;
use App\Http\Controllers\Api\AttendanceApiController;

// ========== LOCATION CONTROLLER ==========
use App\Http\Controllers\Api\LocationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ============================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================
Route::post('login', [AuthController::class, 'login']);
Route::post('/meta-webhook', [LeadController::class, 'metaWebhook']);
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// Face Health Check - No Auth Required
Route::get('/face/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Face Attendance API is running',
        'routes' => [
            'POST /api/face-api/enroll',
            'POST /api/face-api/verify',
            'POST /api/face-api/mark-attendance',
            'GET /api/face-api/enrollment-status',
            'GET /api/face-api/attendance-status',
            'GET /api/face-api/attendance-stats',
            'POST /api/face-api/validate-location',
            'POST /api/face-api/work-report',
            'GET /api/face-api/status',
        ]
    ]);
});

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // ==================== ASSETS ====================
    Route::apiResource('assets', AssetController::class);
    
    // ==================== CLIENTS ====================
    Route::apiResource('clients', ClientController::class);
    Route::post('/clients/{id}/reset-password', [ClientController::class, 'resetPassword']);
    
    // ==================== COMPANY POLICIES ====================
    Route::apiResource('company-policies', CompanyPolicyController::class);
    
    // ==================== CONTRACTS ====================
    Route::apiResource('contracts', ContractController::class);
    
    // ==================== DEALS ====================
    Route::apiResource('deals', DealController::class);
    Route::post('/deals/{id}/discussions', [DealController::class, 'discussionStore']);
    Route::post('/deals/{id}/files', [DealController::class, 'fileUpload']);
    Route::post('/deals/{id}/tasks', [DealController::class, 'taskStore']);
    
    // ==================== DOCUMENTS ====================
    Route::apiResource('documents', DocumentController::class);
    
    // ==================== EMPLOYEES ====================
    Route::apiResource('employees', EmployeeController::class);
    Route::get('/employees/{id}/net-salary', [EmployeeController::class, 'getNetSalary']);
    
    // ==================== EVENTS ====================
    Route::apiResource('events', EventController::class);
    
    // ==================== HR ADMIN ====================
    Route::get('/announcements', [HrAdminController::class, 'indexAnnouncements']);
    Route::post('/announcements', [HrAdminController::class, 'storeAnnouncement']);
    Route::get('/announcements/{id}', [HrAdminController::class, 'showAnnouncement']);
    Route::put('/announcements/{id}', [HrAdminController::class, 'updateAnnouncement']);
    Route::delete('/announcements/{id}', [HrAdminController::class, 'destroyAnnouncement']);
    
    // Warnings
    Route::get('/warnings', [WarningController::class, 'index']);
    Route::post('/warnings', [WarningController::class, 'store']);
    Route::get('/warnings/{id}', [WarningController::class, 'show']);
    Route::put('/warnings/{id}', [WarningController::class, 'update']);
    Route::delete('/warnings/{id}', [WarningController::class, 'destroy']);

    // Work Reports
    Route::prefix('work-reports')->group(function () {
        Route::get('/my', [WorkReportController::class, 'myReports']);
        Route::get('/', [WorkReportController::class, 'index']);
        Route::post('/', [WorkReportController::class, 'store']);
        Route::get('/{id}', [WorkReportController::class, 'show']);
        Route::put('/{id}', [WorkReportController::class, 'update']);
        Route::delete('/{id}', [WorkReportController::class, 'destroy']);
        Route::patch('/{id}/review', [WorkReportController::class, 'review']);
    });

    // ==================== TRANSFERS ====================
    Route::prefix('transfers')->group(function () {
        Route::get('/', [TransferController::class, 'index']);
        Route::post('/', [TransferController::class, 'store']);
        Route::get('/{id}', [TransferController::class, 'show']);
        Route::put('/{id}', [TransferController::class, 'update']);
        Route::delete('/{id}', [TransferController::class, 'destroy']);
        Route::patch('/{id}/status', [TransferController::class, 'updateStatus']);
        Route::get('/employee/{employeeId}', [TransferController::class, 'getEmployeeTransfers']);
        Route::get('/stats', [TransferController::class, 'getStats']);
    });

    // ==================== ATTENDANCE API ====================
    Route::prefix('attendance')->group(function () {
        Route::get('dashboard', [AttendanceApiController::class, 'dashboard']);
        Route::get('live', [AttendanceApiController::class, 'live']);
        Route::get('daily', [AttendanceApiController::class, 'daily']);
        Route::get('roster', [AttendanceApiController::class, 'roster']);
        Route::get('employee/{employeeId}', [AttendanceApiController::class, 'employeeAttendance']);
        Route::post('clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('clock-out', [AttendanceController::class, 'clockOut']);
        Route::get('/', [AttendanceController::class, 'indexAttendance']);
        Route::get('today', [AttendanceController::class, 'todayAttendance']);
        Route::post('/', [AttendanceController::class, 'storeAttendance']);
        Route::get('{id}', [AttendanceController::class, 'showAttendance']);
        Route::put('{id}', [AttendanceController::class, 'updateAttendance']);
        Route::delete('{id}', [AttendanceController::class, 'deleteAttendance']);
    });

    // ==================== INDICATORS ====================
    Route::apiResource('indicators', IndicatorController::class);

    // ============================================================
    // ================ FACE ATTENDANCE - API ROUTES ================
    // ============================================================
    Route::prefix('face-api')->group(function () {
        
        // ✅ SIMPLE STATUS CHECK
        Route::get('/status', function (Request $request) {
            try {
                $user = $request->user();
                $employee = $user ? \App\Models\Employee::where('user_id', $user->id)->first() : null;
                
                return response()->json([
                    'success' => true,
                    'message' => 'Face Attendance API is operational',
                    'data' => [
                        'authenticated' => $user ? true : false,
                        'user_id' => $user ? $user->id : null,
                        'has_employee_record' => $employee ? true : false,
                        'employee_id' => $employee ? $employee->id : null,
                        'employee_name' => $employee ? $employee->name : null,
                        'is_face_enrolled' => $employee && $employee->face_descriptor ? true : false,
                        'timestamp' => now()->toDateTimeString(),
                        'api_version' => '1.0.0'
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error checking status',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
        
        // ============================================================
        // ENROLLMENT ROUTES
        // ============================================================
        Route::post('/enroll', [ApiFaceAttendanceController::class, 'enrollFace']);
        Route::get('/enrollment-status', [ApiFaceAttendanceController::class, 'getEnrollmentStatus']);
        Route::delete('/enrollment/{employeeId}', [ApiFaceAttendanceController::class, 'deleteEnrollment']);
        Route::get('/photo/{employeeId}', [ApiFaceAttendanceController::class, 'getFacePhoto']);
        Route::post('/bulk-enroll', [ApiFaceAttendanceController::class, 'bulkEnroll']);
        
        // ============================================================
        // VERIFICATION & ATTENDANCE ROUTES
        // ============================================================
        Route::post('/verify', [ApiFaceAttendanceController::class, 'verifyFace']);
        Route::post('/mark-attendance', [ApiFaceAttendanceController::class, 'markAttendance']);
        Route::get('/attendance-status', [ApiFaceAttendanceController::class, 'getAttendanceStatus']);
        Route::get('/attendance-stats', [ApiFaceAttendanceController::class, 'getAttendanceStats']);
        Route::get('/logs', [ApiFaceAttendanceController::class, 'getAttendanceLogs']);
        Route::get('/stats', [ApiFaceAttendanceController::class, 'getStats']);
        Route::post('/extract-descriptor', [ApiFaceAttendanceController::class, 'extractDescriptor']);
        
        // ============================================================
        // BREAK ROUTES
        // ============================================================
        Route::get('/break-status', [ApiFaceAttendanceController::class, 'getBreakStatus']);
        Route::post('/start-break', [ApiFaceAttendanceController::class, 'startBreak']);
        Route::post('/end-break', [ApiFaceAttendanceController::class, 'endBreak']);
        
        // ============================================================
        // LOCATION & WORK REPORT ROUTES
        // ============================================================
        Route::post('/validate-location', [ApiFaceAttendanceController::class, 'validateLocation']);
        Route::post('/work-report', [ApiFaceAttendanceController::class, 'saveWorkReport']);
        
        // ============================================================
        // ADMIN DASHBOARD
        // ============================================================
        Route::get('/dashboard', [ApiFaceAttendanceController::class, 'dashboard']);
        
        // ============================================================
        // DEBUG ROUTE
        // ============================================================
        Route::get('/debug', [ApiFaceAttendanceController::class, 'debug']);
    });

    // ============================================================
    // ================ LOCATION API ROUTES =========================
    // ============================================================
    // ✅ FOR FLUTTER APP - Location features
    Route::prefix('location')->group(function () {
        // Get office location settings
        Route::get('/office', [LocationController::class, 'getOfficeLocation']);
        
        // Validate user location against office
        Route::post('/validate', [LocationController::class, 'validateLocation']);
        
        // Get user's current location (GPS or IP)
        Route::get('/user', [LocationController::class, 'getUserLocation']);
        
        // Get location history for an employee
        Route::get('/history', [LocationController::class, 'getLocationHistory']);
        
        // Save location for attendance record
        Route::post('/save', [LocationController::class, 'saveLocation']);
        
        // Get nearby employees within radius
        Route::get('/nearby', [LocationController::class, 'getNearbyEmployees']);
    });

    // ==================== TERMINATIONS ====================
    Route::get('/terminations', [HrAdminController::class, 'indexTerminations']);
    Route::post('/terminations', [HrAdminController::class, 'storeTermination']);
    Route::get('/terminations/{id}', [HrAdminController::class, 'showTermination']);
    Route::put('/terminations/{id}', [HrAdminController::class, 'updateTermination']);
    Route::delete('/terminations/{id}', [HrAdminController::class, 'destroyTermination']);
    
    // ==================== HRM SYSTEM ====================
    Route::get('/holidays', [HrmSystemController::class, 'indexHolidays']);
    Route::post('/holidays', [HrmSystemController::class, 'storeHoliday']);
    Route::get('/holidays/{id}', [HrmSystemController::class, 'showHoliday']);
    Route::put('/holidays/{id}', [HrmSystemController::class, 'updateHoliday']);
    Route::delete('/holidays/{id}', [HrmSystemController::class, 'destroyHoliday']);
    
    Route::get('/taxes', [HrmSystemController::class, 'indexTaxes']);
    Route::post('/taxes', [HrmSystemController::class, 'storeTax']);
    Route::get('/taxes/{id}', [HrmSystemController::class, 'showTax']);
    Route::put('/taxes/{id}', [HrmSystemController::class, 'updateTax']);
    Route::delete('/taxes/{id}', [HrmSystemController::class, 'destroyTax']);
    
    // ==================== IVR / VOXBAY ====================
    Route::apiResource('ivr-settings', IvrController::class);
    Route::post('/ivr/import-voxbay', [IvrController::class, 'importVoxBaySetup']);
    Route::post('/ivr/test-connection', [IvrController::class, 'testVoxBayConnection']);
    Route::post('/ivr/make-call', [IvrController::class, 'makeCall']);
    Route::post('/ivr/hangup-call', [IvrController::class, 'hangupCall']);
    Route::get('/ivr/call-history', [IvrController::class, 'callHistory']);
    Route::get('/ivr/call-details/{callId}', [IvrController::class, 'callDetails']);
    
    // ==================== LEADS ====================
    Route::apiResource('leads', LeadController::class);
    Route::get('/lead-stages', [LeadController::class, 'stages']);
    Route::post('/leads/{id}/discussions', [LeadController::class, 'discussionStore']);
    Route::post('/leads/{id}/files', [LeadController::class, 'fileUpload']);
    
    // ==================== LEAVES ====================
    Route::apiResource('leaves', LeaveController::class);
    Route::get('/leave-types', [LeaveController::class, 'leaveTypes']);
    Route::post('/leave-types', [LeaveController::class, 'storeLeaveType']);
    Route::get('/leave-types/{id}', [LeaveController::class, 'showLeaveType']);
    Route::put('/leave-types/{id}', [LeaveController::class, 'updateLeaveType']);
    Route::delete('/leave-types/{id}', [LeaveController::class, 'destroyLeaveType']);
    
    // ==================== MEETINGS ====================
    Route::apiResource('meetings', MeetingController::class);
    
    // ==================== PAYROLL ====================
    Route::apiResource('payslips', PayrollController::class);
    Route::post('/employees/{employeeId}/generate-payslip', [PayrollController::class, 'generatePayslip']);
    
    // ==================== PERFORMANCE ====================
    Route::get('/appraisals', [PerformanceController::class, 'indexAppraisals']);
    Route::post('/appraisals', [PerformanceController::class, 'storeAppraisal']);
    Route::get('/appraisals/{id}', [PerformanceController::class, 'showAppraisal']);
    Route::put('/appraisals/{id}', [PerformanceController::class, 'updateAppraisal']);
    Route::delete('/appraisals/{id}', [PerformanceController::class, 'destroyAppraisal']);

    Route::apiResource('goal-trackings', GoalTrackingController::class);
    
    Route::get('/goals', [PerformanceController::class, 'indexGoals']);
    Route::post('/goals', [PerformanceController::class, 'storeGoal']);
    Route::get('/goals/{id}', [PerformanceController::class, 'showGoal']);
    Route::put('/goals/{id}', [PerformanceController::class, 'updateGoal']);
    Route::delete('/goals/{id}', [PerformanceController::class, 'destroyGoal']);
    
    Route::get('/competencies', [PerformanceController::class, 'indexCompetencies']);
    Route::post('/competencies', [PerformanceController::class, 'storeCompetency']);
    Route::put('/competencies/{id}', [PerformanceController::class, 'updateCompetency']);
    Route::delete('/competencies/{id}', [PerformanceController::class, 'destroyCompetency']);
    
    // ==================== PIPELINES ====================
    Route::apiResource('pipelines', PipelineController::class);
    
    // ==================== RECRUITMENT ====================
    Route::get('/jobs', [RecruitmentController::class, 'indexJobs']);
    Route::post('/jobs', [RecruitmentController::class, 'storeJob']);
    Route::get('/jobs/{id}', [RecruitmentController::class, 'showJob']);
    Route::put('/jobs/{id}', [RecruitmentController::class, 'updateJob']);
    Route::delete('/jobs/{id}', [RecruitmentController::class, 'destroyJob']);
    
    Route::get('/job-applications', [RecruitmentController::class, 'indexJobApplications']);
    Route::post('/job-applications', [RecruitmentController::class, 'storeJobApplication']);
    Route::get('/job-applications/{id}', [RecruitmentController::class, 'showJobApplication']);
    Route::put('/job-applications/{id}', [RecruitmentController::class, 'updateJobApplication']);
    Route::delete('/job-applications/{id}', [RecruitmentController::class, 'destroyJobApplication']);
    
    Route::get('/interview-schedules', [RecruitmentController::class, 'indexInterviewSchedules']);
    Route::post('/interview-schedules', [RecruitmentController::class, 'storeInterviewSchedule']);
    Route::get('/interview-schedules/{id}', [RecruitmentController::class, 'showInterviewSchedule']);
    Route::put('/interview-schedules/{id}', [RecruitmentController::class, 'updateInterviewSchedule']);
    Route::delete('/interview-schedules/{id}', [RecruitmentController::class, 'destroyInterviewSchedule']);
    
    // ==================== REPORTS ====================
    Route::get('/reports/income-summary', [ReportController::class, 'incomeSummary']);
    Route::get('/reports/expense-summary', [ReportController::class, 'expenseSummary']);
    Route::get('/reports/income-vs-expense', [ReportController::class, 'incomeVsExpenseSummary']);
    Route::get('/reports/tax-summary', [ReportController::class, 'taxSummary']);
    Route::get('/reports/employee-leaves', [ReportController::class, 'leave']);
    Route::get('/reports/employee/{id}/leave', [ReportController::class, 'employeeLeave']);
    
    // ==================== ROLES & PERMISSIONS ====================
    Route::apiResource('roles', RoleController::class);
    Route::get('/permissions', [RoleController::class, 'permissions']);
    
    // ==================== TRAINING ====================
    Route::get('/trainings', [TrainingController::class, 'indexTrainings']);
    Route::post('/trainings', [TrainingController::class, 'storeTraining']);
    Route::get('/trainings/{id}', [TrainingController::class, 'showTraining']);
    Route::put('/trainings/{id}', [TrainingController::class, 'updateTraining']);
    Route::delete('/trainings/{id}', [TrainingController::class, 'destroyTraining']);
    
    Route::get('/trainers', [TrainingController::class, 'indexTrainers']);
    Route::post('/trainers', [TrainingController::class, 'storeTrainer']);
    Route::get('/trainers/{id}', [TrainingController::class, 'showTrainer']);
    Route::put('/trainers/{id}', [TrainingController::class, 'updateTrainer']);
    Route::delete('/trainers/{id}', [TrainingController::class, 'destroyTrainer']);
    
    // ==================== USERS ====================
    Route::apiResource('users', UserController::class);
    
    // ==================== BANK RECONCILIATION ====================
    Route::get('/bank-reconciliation/ledger-transactions', [BankReconciliationController::class, 'ledgerTransactionsApi']);
    Route::get('/bank-reconciliation/transactions', [BankReconciliationController::class, 'ledgerTransactionsApi']);

    // ================================================================
    // ================ NEW HR MODULE ROUTES ==========================
    // ================================================================

    Route::apiResource('promotions', PromotionController::class);
    Route::apiResource('complaints', ComplaintController::class);
    Route::apiResource('travels', TravelController::class);
    Route::apiResource('resignations', ResignationController::class);
    Route::apiResource('awards', AwardController::class);
    Route::apiResource('transfers', TransferController::class);

    // ==================== TASK TRACKING ====================
    Route::prefix('employee-tasks')->group(function () {
        Route::get('/', [EmployeeTaskController::class, 'index']);
        Route::post('/', [EmployeeTaskController::class, 'store']);
        Route::get('/{id}', [EmployeeTaskController::class, 'show']);
        Route::put('/{id}', [EmployeeTaskController::class, 'update']);
        Route::delete('/{id}', [EmployeeTaskController::class, 'destroy']);
        Route::patch('/{id}/status', [EmployeeTaskController::class, 'updateStatus']);
        Route::get('/employee/{employeeId}/tasks', [EmployeeTaskController::class, 'getEmployeeTasks']);
        Route::post('/bulk-assign', [EmployeeTaskController::class, 'bulkAssign']);
        Route::get('/calendar', [EmployeeTaskController::class, 'calendarTasks']);
        Route::get('/stats', [EmployeeTaskController::class, 'stats']);
    });

    // ==================== TRANSACTIONS ====================
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::put('/{id}', [TransactionController::class, 'update']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
        Route::get('/export', [TransactionController::class, 'export']);
        Route::get('/statement', [TransactionController::class, 'statement']);
    });
});