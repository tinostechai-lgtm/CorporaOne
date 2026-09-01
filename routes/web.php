        <?php

        use App\Http\Controllers\AamarpayController;
        use Illuminate\Http\Request;
        use App\Models\TransactionLines;
        use App\Models\BankStatementSubmission;
        use App\Http\Controllers\AiTemplateController;
        use App\Http\Controllers\AllowanceController;
        use App\Http\Controllers\AllowanceOptionController;
        use App\Http\Controllers\AnnouncementController;
        use App\Http\Controllers\AppraisalController;
        use App\Http\Controllers\AssetController;
        use App\Http\Controllers\AttendanceEmployeeController;
        use App\Http\Controllers\Auth\AuthenticatedSessionController;
        use App\Http\Controllers\Auth\EmailVerificationNotificationController;
        use App\Http\Controllers\Auth\EmailVerificationPromptController;
        use App\Http\Controllers\Auth\RegisteredUserController;
        use App\Http\Controllers\Auth\VerifyEmailController;
        use App\Http\Controllers\AuthorizeNetController;
        use App\Http\Controllers\AwardController;
        use App\Http\Controllers\AwardTypeController;
        use App\Http\Controllers\BankAccountController;
        use App\Http\Controllers\BankTransferController;
        use App\Http\Controllers\BankTransferPaymentController;
        use App\Http\Controllers\BenefitPaymentController;
        use App\Http\Controllers\BillController;
        use App\Http\Controllers\BiometricAttendanceController;
        use App\Http\Controllers\BranchController;
        use App\Http\Controllers\BudgetController;
        use App\Http\Controllers\BugStatusController;
        use App\Http\Controllers\CashfreeController;
        use App\Http\Controllers\ChartOfAccountController;
        use App\Http\Controllers\CinetPayController;
        use App\Http\Controllers\ClientController;
        use App\Http\Controllers\CoingatePaymentController;
        use App\Http\Controllers\CommissionController;
        use App\Http\Controllers\CompanyPolicyController;
        use App\Http\Controllers\CompetenciesController;
        use App\Http\Controllers\ComplaintController;
        use App\Http\Controllers\ContractController;
        use App\Http\Controllers\ContractTypeController;
        use App\Http\Controllers\CouponController;
        use App\Http\Controllers\CreditNoteController;
        use App\Http\Controllers\CustomerController;
        use App\Http\Controllers\CustomFieldController;
        use App\Http\Controllers\CustomQuestionController;
        use App\Http\Controllers\DashboardController;
        use App\Http\Controllers\DealController;
        use App\Http\Controllers\DebitNoteController;
        use App\Http\Controllers\DeductionOptionController;
        use App\Http\Controllers\DepartmentController;
        use App\Http\Controllers\DesignationController;
        use App\Http\Controllers\DocumentController;
        use App\Http\Controllers\DucumentUploadController;
        use App\Http\Controllers\EasebuzzController;
        use App\Http\Controllers\EmailTemplateController;
        use App\Http\Controllers\EmployeeController;
        use App\Http\Controllers\EventController;
        use App\Http\Controllers\ExpenseController;
        use App\Http\Controllers\FedapayController;
        use App\Http\Controllers\FlutterwavePaymentController;
        use App\Http\Controllers\FormBuilderController;
        use App\Http\Controllers\GoalController;
        use App\Http\Controllers\GoalTrackingController;
        use App\Http\Controllers\GoalTypeController;
        use App\Http\Controllers\HolidayController;
        use App\Http\Controllers\ImportController;
        use App\Http\Controllers\IndicatorController;
        use App\Http\Controllers\InterviewScheduleController;
        use App\Http\Controllers\InvoiceController;
        use App\Http\Controllers\IyziPayController;
        use App\Http\Controllers\JobApplicationController;
        use App\Http\Controllers\JobCategoryController;
        use App\Http\Controllers\JobController;
        use App\Http\Controllers\JobStageController;
        use App\Http\Controllers\JournalEntryController;
        use App\Http\Controllers\KhaltiController;
        use App\Http\Controllers\LabelController;
        use App\Http\Controllers\LanguageController;
        use App\Http\Controllers\LeadController;
        use App\Http\Controllers\LeadStageController;
        use App\Http\Controllers\LeaveController;
        use App\Http\Controllers\LeaveTypeController;
        use App\Http\Controllers\LoanController;
        use App\Http\Controllers\LoanOptionController;
        use App\Http\Controllers\MeetingController;
        use App\Http\Controllers\MercadoPaymentController;
        use App\Http\Controllers\MolliePaymentController;
        use App\Http\Controllers\NotificationTemplatesController;
        use App\Http\Controllers\OtherPaymentController;
        use App\Http\Controllers\OvertimeController;
        use App\Http\Controllers\PayFastController;
        use App\Http\Controllers\PaymentController;
        use App\Http\Controllers\PaymentWallPaymentController;
        use App\Http\Controllers\PaypalController;
        use App\Http\Controllers\PaySlipController;
        use App\Http\Controllers\PayslipTypeController;
        use App\Http\Controllers\PaystackPaymentController;
        use App\Http\Controllers\PaytabController;
        use App\Http\Controllers\PaytmPaymentController;
        use App\Http\Controllers\PaytrController;
        use App\Http\Controllers\YooKassaController;
        use App\Http\Controllers\PerformanceTypeController;
        use App\Http\Controllers\PermissionController;
        use App\Http\Controllers\PipelineController;
        use App\Http\Controllers\PlanController;
        use App\Http\Controllers\PlanRequestController;
        use App\Http\Controllers\PosController;
        use App\Http\Controllers\ProductServiceCategoryController;
        use App\Http\Controllers\ProductServiceController;
        use App\Http\Controllers\ProductServiceUnitController;
        use App\Http\Controllers\ProductStockController;
        use App\Http\Controllers\ProjectController;
        use App\Http\Controllers\ProjectReportController;
        use App\Http\Controllers\ProjectstagesController;
        use App\Http\Controllers\ProjectTaskController;
        use App\Http\Controllers\PromotionController;
        use App\Http\Controllers\ProposalController;
        use App\Http\Controllers\PurchaseController;
        use App\Http\Controllers\RazorpayPaymentController;
        use App\Http\Controllers\ReportController;
        use App\Http\Controllers\ResignationController;
        use App\Http\Controllers\RevenueController;
        use App\Http\Controllers\RoleController;
        use App\Http\Controllers\SaturationDeductionController;
        use App\Http\Controllers\SetSalaryController;
        use App\Http\Controllers\SkrillPaymentController;
        use App\Http\Controllers\SourceController;
        use App\Http\Controllers\SspayController;
        use App\Http\Controllers\StageController;
        use App\Http\Controllers\StripePaymentController;
        use App\Http\Controllers\SupportController;
        use App\Http\Controllers\SystemController;
        use App\Http\Controllers\TaskStageController;
        use App\Http\Controllers\TaxController;
        use App\Http\Controllers\TerminationController;
        use App\Http\Controllers\TerminationTypeController;
        use App\Http\Controllers\TimesheetController;
        use App\Http\Controllers\TimeTrackerController;
        use App\Http\Controllers\ToyyibpayController;
        use App\Http\Controllers\TrainerController;
        use App\Http\Controllers\TrainingController;
        use App\Http\Controllers\TrainingTypeController;
        use App\Http\Controllers\TransactionController;
        use App\Http\Controllers\TransferController;
        use App\Http\Controllers\TravelController;
        use App\Http\Controllers\UserController;
        use App\Http\Controllers\VenderController;
        use App\Http\Controllers\WarehouseController;
        use App\Http\Controllers\WarehouseTransferController;
        use App\Http\Controllers\WarningController;
        use App\Http\Controllers\ZoomMeetingController;
        use App\Http\Controllers\XenditPaymentController;
        use App\Http\Controllers\MidtransPaymentController;
        use App\Http\Controllers\ProjectExpenseController;
        use App\Http\Controllers\NepalstePaymnetController;
        use App\Http\Controllers\OzowPaymentController;
        use App\Http\Controllers\PaiementProController;
        use App\Http\Controllers\PayHereController;
        use App\Http\Controllers\QuotationController;
        use App\Http\Controllers\ReferralProgramController;
        use App\Http\Controllers\TapController;
        use Illuminate\Support\Facades\Route;
        use Illuminate\Support\Facades\Artisan;
        use App\Http\Controllers\PosDashboardController;
        use App\Http\Controllers\IvrController;
        use App\Http\Controllers\MetaController;
        use App\Http\Controllers\WorkReportController;
        use App\Http\Controllers\InvoiceExtractController;
        use App\Http\Controllers\JoiningLetterController;
        use App\Http\Controllers\JoinUsController;
        use App\Http\Controllers\FaceAttendanceController;
        use App\Http\Controllers\BonvoiceController;
        use App\Http\Controllers\StaffWorkTaskController;
        use App\Http\Controllers\FrontendController;
        use App\Http\Controllers\HeroSectionController;
        use App\Http\Controllers\Chatify\MessagesController;
        use App\Http\Controllers\PurchaseReturnController;
        use App\Http\Controllers\BankStatementController;
        use App\Http\Controllers\BankReconciliationController;
        use App\Http\Controllers\ExtractBankStatementController;
        use App\Http\Controllers\SettingsController;
        use Spatie\Permission\Models\Permission;  // ← ADD THIS
        use Spatie\Permission\Models\Role;                                                                        

        /*
        |--------------------------------------------------------------------------
        | Web Routes
        |--------------------------------------------------------------------------
        */


        // ==============================================
        // DEBUG ROUTES
        // ==============================================
        Route::get('/debug-bank-submissions', function() {
            $submissions = App\Models\BankStatementSubmission::where('created_by', auth()->user()->creatorId())
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            $result = [];
            foreach ($submissions as $sub) {
                $transactions = is_string($sub->transactions) ? json_decode($sub->transactions, true) : $sub->transactions;
                $result[] = [
                    'id' => $sub->id,
                    'file_name' => $sub->original_file_name,
                    'created_at' => $sub->created_at->format('Y-m-d H:i:s'),
                    'transactions_count' => is_array($transactions) ? count($transactions) : 0,
                    'transactions' => $transactions
                ];
            }
            
            return response()->json($result);
        })->middleware('auth');

        Route::get('/test-controller', function() {
            if (class_exists('App\Http\Controllers\ExtractBankStatementController')) {
                return 'Controller exists!';
            } else {
                return 'Controller does NOT exist!';
            }
        });

        Route::get('/bonvoice-test-simple', function() {
            return response()->json([
                'success' => true,
                'message' => 'Bonvoice test route is working!',
                'time' => now()->toDateTimeString()
            ]);
        });

        Route::get('/bonvoice-direct-api-test', function() {
            try {
                return response()->json(['success' => true, 'message' => 'API test works']);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });

        // ==============================================
        // BANK RECONCILIATION ROUTES (Outside auth group)
        // ==============================================
        Route::get('/bank-reconciliation/compare-with-ledger', [BankReconciliationController::class, 'compareWithLedger'])
            ->name('bank-reconciliation.compare-with-ledger');

        Route::post('/bank-statement-upload-direct', [ExtractBankStatementController::class, 'upload'])
            ->middleware('auth')
            ->name('bank-statement.upload.direct');

        Route::post('/bank-reconciliation/add-to-ledger', [ExtractBankStatementController::class, 'addToLedger'])
            ->name('bank-reconciliation.add-to-ledger');

        Route::put('/bank-reconciliation/update-ledger/{id}', [BankReconciliationController::class, 'updateLedger'])
            ->name('bank-reconciliation.update-ledger');

        Route::delete('/bank-reconciliation/delete-ledger/{id}', [BankReconciliationController::class, 'deleteLedger'])
            ->name('bank-reconciliation.delete-ledger');

        Route::post('/bank-reconciliation/match-transaction', [BankReconciliationController::class, 'matchTransaction'])
            ->name('bank-reconciliation.match-transaction');

        Route::post('/bank-reconciliation/ignore-transaction', [BankReconciliationController::class, 'ignoreTransaction'])
            ->name('bank-reconciliation.ignore-transaction');

            Route::post('/debug-extract-pdf', [ExtractBankStatementController::class, 'debugExtract'])
            ->middleware('auth')
            ->name('debug.extract.pdf');

            Route::get('/bank-statement/{id}/export-comparison', [BankStatementController::class, 'exportComparison'])->name('bank-statement.export.comparison');

        Route::post('/bank-reconciliation/update-comparison-entry', [BankReconciliationController::class, 'updateComparisonEntry'])
            ->name('bank-reconciliation.update-comparison-entry');

        Route::post('/bank-reconciliation/manual-match', [BankReconciliationController::class, 'manualMatch'])
            ->name('bank-reconciliation.manual-match');

        Route::delete('/bank-reconciliation/delete-comparison-entry', [BankReconciliationController::class, 'deleteComparisonEntry'])
            ->name('bank-reconciliation.delete-comparison-entry');

        Route::post('/bank-reconciliation/reconcile-all', [BankReconciliationController::class, 'reconcileAll'])
            ->name('bank-reconciliation.reconcile-all');

            // Add this route for recent submissions
        Route::get('bank-reconciliation/recent-submissions', function() {
            $submissions = App\Models\BankStatementSubmission::where('created_by', auth()->user()->creatorId())
                ->latest()
                ->limit(10)
                ->get()
                ->map(function($s) {
                    $transactions = is_array($s->transactions) ? $s->transactions : json_decode($s->transactions, true);
                    return [
                        'id' => $s->id,
                        'name' => ($s->bank_name ?? 'Statement') . ' - ' . ($s->original_file_name ?? 'File') . ' (' . $s->created_at->format('M d') . ')',
                        'transactions_count' => is_array($transactions) ? count($transactions) : 0
                    ];
                });
            return response()->json($submissions);
        })->name('bank-reconciliation.recent-submissions')->middleware('auth');

        Route::get('/bank-statement-comparison/export/{submissionId}', [ReportController::class, 'exportComparison'])
            ->name('bank-statement-comparison.export');

        // ==============================================
        // TRANSACTION ROUTES
        // ==============================================
        Route::prefix('transaction')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('transaction.index');
            Route::get('/{id}', [TransactionController::class, 'show'])->name('transaction.show');
            Route::get('/{id}/edit', [TransactionController::class, 'edit'])->name('transaction.edit');
            Route::put('/{id}', [TransactionController::class, 'update'])->name('transaction.update');
            Route::delete('/{id}', [TransactionController::class, 'destroy'])->name('transaction.destroy');
        });

        // ==============================================
        // ACCOUNT TRANSACTIONS ROUTE - IMPORTANT: Place this BEFORE the auth group
        // ==============================================
        Route::get('/account/{accountId}/transactions', [ReportController::class, 'accountTransactions'])
            ->middleware('auth')
            ->name('report.account.transactions');

        // Account statement report route
        Route::get('/report/account-statement', [TransactionController::class, 'accountStatement'])
            ->middleware('auth')
            ->name('report.account.statement');

        // Export statement route
        Route::get('/export/statement', [TransactionController::class, 'exportStatement'])
            ->middleware('auth')
            ->name('export.statement');

        // ==============================================
        // LIST SUBMISSIONS ROUTE
        // ==============================================
        Route::get('/list-submissions', function() {
            try {
                $submissions = App\Models\BankStatementSubmission::where('created_by', auth()->user()->creatorId())
                    ->orderBy('created_at', 'desc')
                    ->get(['id', 'original_file_name', 'created_at', 'status']);
                
                return response()->json([
                    'success' => true,
                    'count' => $submissions->count(),
                    'submissions' => $submissions
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        })->middleware('auth');

        // Debug single PDF - shows extracted text
        Route::get('/debug-pdf/{id}', function($id) {
            try {
                $submission = App\Models\BankStatementSubmission::where('id', $id)
                    ->where('created_by', auth()->user()->creatorId())
                    ->first();
                    
                if (!$submission) {
                    return response()->json(['error' => 'Submission not found'], 404);
                }
                
                $fullPath = storage_path('app/public/' . $submission->file_path);
                
                $result = [
                    'submission_id' => $submission->id,
                    'original_file_name' => $submission->original_file_name,
                    'stored_file_name' => $submission->stored_file_name,
                    'file_exists' => file_exists($fullPath),
                    'file_path' => $fullPath,
                    'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                    'status' => $submission->status,
                    'stored_transactions' => $submission->transactions,
                    'transactions_count' => is_array($submission->transactions) ? count($submission->transactions) : 0,
                ];
                
                if (file_exists($fullPath)) {
                    try {
                        $parser = new \Smalot\PdfParser\Parser();
                        $pdf = $parser->parseFile($fullPath);
                        $text = $pdf->getText();
                        $result['extracted_text_length'] = strlen($text);
                        $result['extracted_text_preview'] = substr($text, 0, 1500);
                        $result['has_dates'] = preg_match('/\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/', $text) ? 'Yes' : 'No';
                        $result['has_amounts'] = preg_match('/[\d,]+\.\d{2}/', $text) ? 'Yes' : 'No';
                    } catch (\Exception $e) {
                        $result['parser_error'] = $e->getMessage();
                    }
                    
                    $rawContent = file_get_contents($fullPath);
                    $cleanText = preg_replace('/[^\x20-\x7E\n\r]/', '', $rawContent);
                    $result['raw_text_length'] = strlen($cleanText);
                    $result['raw_text_preview'] = substr($cleanText, 0, 1500);
                }
                
                return response()->json($result);
                
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        })->middleware('auth');

        // Test PDF extraction with a new upload
        Route::post('/test-pdf-extract', function(\Illuminate\Http\Request $request) {
            try {
                if (!$request->hasFile('file')) {
                    return response()->json(['error' => 'No file uploaded'], 400);
                }
                
                $file = $request->file('file');
                $filePath = $file->storeAs('temp', time() . '_' . $file->getClientOriginalName(), 'public');
                $fullPath = storage_path('app/public/' . $filePath);
                
                $result = [
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
                
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($fullPath);
                    $text = $pdf->getText();
                    $result['extracted_text'] = $text;
                    $result['text_length'] = strlen($text);
                    $result['text_preview'] = substr($text, 0, 2000);
                    
                    $lines = explode("\n", $text);
                    $result['total_lines'] = count($lines);
                    
                    $transactionLines = [];
                    foreach ($lines as $line) {
                        if (preg_match('/\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}/', $line) && 
                            preg_match('/[\d,]+\.\d{2}/', $line)) {
                            $transactionLines[] = trim($line);
                        }
                    }
                    $result['potential_transaction_lines'] = $transactionLines;
                    $result['potential_transactions_count'] = count($transactionLines);
                    
                } catch (\Exception $e) {
                    $result['parser_error'] = $e->getMessage();
                }
                
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
                
                return response()->json($result);
                
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        })->middleware('auth');

        // ==============================================
        // DIRECT UPLOAD ROUTES
        // ==============================================
        Route::post('/bank-statement-upload-direct', [ExtractBankStatementController::class, 'upload'])
            ->middleware('auth')
            ->name('bank-statement.upload.direct');

        Route::post('/bank-statement-upload-csv', [ExtractBankStatementController::class, 'uploadCsv'])
            ->middleware('auth')
            ->name('bank-statement.upload.csv');

            Route::get('report/bank-reconciliation/export', [ReportController::class, 'bankReconciliationExport'])
            ->name('report.bank-reconciliation.export');

            Route::get('bank-reconciliation/compare-with-ledger', [BankReconciliationController::class, 'compareWithLedger'])
            ->name('bank-reconciliation.compare-with-ledger');

        // ==============================================
        // MAIN AUTHENTICATED ROUTES GROUP
        // ==============================================
        Route::middleware(['auth', 'XSS'])->group(function () {
            
            // ==============================================
            // BANK RECONCILIATION ROUTES
            // ==============================================
            Route::prefix('bank-reconciliation')->name('bank-reconciliation.')->group(function () {
                Route::get('/', [BankReconciliationController::class, 'index'])->name('index');
                Route::get('ledger-transactions', [BankReconciliationController::class, 'getLedgerTransactions'])->name('ledger-transactions');
                Route::get('ledger-report/{accountId?}', [BankReconciliationController::class, 'ledgerReport'])->name('ledger-report');
                Route::get('compare/{ledger_id}/{submission_id}', [BankReconciliationController::class, 'compare'])->name('compare');
                Route::get('get-statements', [BankReconciliationController::class, 'getStatements'])->name('get-statements');
                Route::post('upload-statement', [ExtractBankStatementController::class, 'upload'])->name('upload-statement');
            });
            
            // ==============================================
            // BANK STATEMENT ROUTES
            // ==============================================
            Route::prefix('bank-statement')->name('bank-statement.')->group(function () {
                Route::get('/', [BankStatementController::class, 'index'])->name('index');
                Route::get('/create', [BankStatementController::class, 'create'])->name('create');
                Route::post('/', [BankStatementController::class, 'store'])->name('store');
                Route::get('/{id}', [BankStatementController::class, 'show'])->name('show');
                Route::get('/{id}/edit', [BankStatementController::class, 'edit'])->name('edit');
                Route::put('/{id}', [BankStatementController::class, 'update'])->name('update');
                Route::delete('/{id}', [BankStatementController::class, 'destroy'])->name('destroy');
                Route::get('/{id}/download', [BankStatementController::class, 'download'])->name('download');
                Route::get('/{id}/compare', [BankStatementController::class, 'compare'])->name('compare');
            });
            
            // Lead Management
        Route::resource('leads', LeadController::class);
        Route::get('lead-list', [LeadController::class, 'leadList'])->name('leads.list');
        Route::get('unassigned-leads', [LeadController::class, 'unassignedLeads'])->name('leads.unassigned');

        // Lead Assignment
        Route::post('leads/{lead}/assign-users', [LeadController::class, 'assignUsers'])->name('leads.assign-users');
        Route::delete('leads/{lead}/remove-user/{userId}', [LeadController::class, 'removeUser'])->name('leads.remove-user');
        Route::post('leads/bulk-assign', [LeadController::class, 'bulkAssign'])->name('leads.bulk-assign');
        Route::post('leads/{lead}/transfer', [LeadController::class, 'transferLead'])->name('leads.transfer');
        Route::post('leads/bulk-delete', [LeadController::class, 'bulkDelete'])->name('leads.bulk-delete');
        Route::get('/leads/import-export', [LeadController::class, 'importExport'])->name('leads.import-export');

        // Lead Order
        Route::post('leads/update-order', [LeadController::class, 'updateOrder'])->name('leads.update-order');

        // Social Media Integrations
        Route::post('leads/fetch-facebook', [LeadController::class, 'fetchFacebookLeads'])->name('leads.fetch-facebook');
        Route::post('leads/fetch-instagram', [LeadController::class, 'fetchInstagramLeads'])->name('leads.fetch-instagram');
        Route::post('leads/fetch-whatsapp', [LeadController::class, 'fetchWhatsAppLeads'])->name('leads.fetch-whatsapp');
        Route::post('leads/setup-facebook-webhook', [LeadController::class, 'setupFacebookWebhook'])->name('leads.setup-facebook-webhook');
        Route::post('leads/setup-instagram-webhook', [LeadController::class, 'setupInstagramWebhook'])->name('leads.setup-instagram-webhook');
        Route::post('webhook/leads', [LeadController::class, 'webhookLead'])->name('webhook.leads');

        // Call Management
        Route::post('leads/{lead}/calls', [LeadController::class, 'storeCall'])->name('leads.calls.store');
        Route::put('leads/{lead}/calls/{callId}', [LeadController::class, 'updateCall'])->name('leads.calls.update');
        Route::delete('leads/{lead}/calls/{callId}', [LeadController::class, 'deleteCall'])->name('leads.calls.destroy');
        Route::get('leads/{lead}/call-history', [LeadController::class, 'callHistory'])->name('leads.call-history');

        // Email Management
        Route::post('leads/{lead}/emails', [LeadController::class, 'storeEmail'])->name('leads.emails.store');

        // Discussion Management
        Route::post('leads/{lead}/discussions', [LeadController::class, 'storeDiscussion'])->name('leads.discussions.store');

        // File Management
        Route::post('leads/{lead}/files', [LeadController::class, 'uploadFile'])->name('leads.files.upload');
        Route::get('leads/{lead}/files/{fileId}/download', [LeadController::class, 'downloadFile'])->name('leads.file.download');
        Route::delete('leads/{lead}/files/{fileId}', [LeadController::class, 'deleteFile'])->name('leads.file.delete');

        // Label Management
        Route::get('leads/{lead}/labels', [LeadController::class, 'showLabels'])->name('leads.labels');
        Route::put('leads/{lead}/labels', [LeadController::class, 'updateLabels'])->name('leads.labels.update');

        // Source Management
        Route::get('leads/{lead}/sources', [LeadController::class, 'showSources'])->name('leads.sources');
        Route::put('leads/{lead}/sources', [LeadController::class, 'updateSources'])->name('leads.sources.update');

        //ajax routes for meta leads-

        // Social Media Routes (should be POST for JSON responses)
        Route::post('/leads/fetch-facebook', [LeadController::class, 'fetchFacebookLeads'])->name('leads.fetch-facebook');
        Route::post('/leads/fetch-instagram', [LeadController::class, 'fetchInstagramLeads'])->name('leads.fetch-instagram');
        Route::post('/leads/fetch-whatsapp', [LeadController::class, 'fetchWhatsAppLeads'])->name('leads.fetch-whatsapp');        


        Route::get('/leads-standalone', [LeadController::class, 'index'])->name('leads.standalone');
        //test

        Route::get('/test-social', function() {
            return view('test-social');
        });
        Route::get('/test-only', function() {
            return view('leads.test-only');
        });

        //tet
        Route::get('/social-connect', [LeadController::class, 'socialConnect'])->name('leads.social.connect');

        Route::get('/leads/filter', [LeadController::class, 'filterLeads'])->name('leads.filter');

        // Import/Export Routes

        // ==============================================
        // LEAD IMPORT/EXPORT ROUTES - CLEAN VERSION
        // ==============================================

        // Import Modal (returns the HTML modal)
        Route::get('/leads/import-modal', [LeadController::class, 'showImport'])->name('leads.import.modal');

        // Import File Upload (handles the file)
        Route::post('/leads/import', [LeadController::class, 'import'])->name('leads.import');

        // Export Leads
        Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');

        // Advanced Import/Export Page
        Route::get('/leads/import-export', [LeadController::class, 'importExport'])->name('leads.import.export');

        // Process Import (AJAX for advanced import)
        Route::post('/leads/import/process', [LeadController::class, 'processImport'])->name('leads.import.process');

        // Process Export (AJAX for advanced export)
        Route::post('/leads/export/process', [LeadController::class, 'processExport'])->name('leads.export.process');

        // Export Sample
        Route::get('/leads/export/sample', [LeadController::class, 'exportSample'])->name('leads.export.sample');
        // Add this route for the import modal


        // Product Management
        Route::get('leads/{lead}/products', [LeadController::class, 'showProducts'])->name('leads.products');
        Route::post('leads/{lead}/products', [LeadController::class, 'addProduct'])->name('leads.products.add');
        Route::delete('leads/{lead}/products/{productId}', [LeadController::class, 'removeProduct'])->name('leads.products.remove');

        // Note Management
        Route::post('leads/{lead}/notes', [LeadController::class, 'saveNote'])->name('leads.notes.save');

        // Convert to Deal
        Route::get('leads/{lead}/convert', [LeadController::class, 'showConvertToDeal'])->name('leads.convert');
        Route::post('leads/{lead}/convert', [LeadController::class, 'convertToDeal'])->name('leads.convert.store');

        // Import/Export

        // Statistics
        Route::get('leads/statistics', [LeadController::class, 'statistics'])->name('leads.statistics');
        Route::get('leads/calendar', [LeadController::class, 'calendarLeads'])->name('leads.calendar');

        // AJAX
        Route::get('leads/stages/json', [LeadController::class, 'getStages'])->name('leads.stages.json');
            // ==============================================
            // REPORT ROUTES FOR BANK RECONCILIATION
            // ==============================================
            Route::prefix('report')->name('report.')->group(function () {
                Route::get('bank-reconciliation', [BankReconciliationController::class, 'index'])->name('bank-reconciliation');
                Route::get('bank-statement-reconciliation', [BankReconciliationController::class, 'index'])->name('bank.statement.reconciliation');
                Route::get('bank-statement-comparison', [ReportController::class, 'bankStatementComparison'])->name('bank-statement.comparison');
                Route::get('bank-statement-comparison/{submissionId}/export', [ReportController::class, 'exportComparison'])->name('bank-statement.comparison.export');
                Route::get('ledger/preview', [ReportController::class, 'ledgerPreview'])->name('ledger.preview');
            });

            
            
            // ==============================================
            // ACCOUNT STATEMENT REPORT ROUTES
            // ==============================================
            Route::prefix('account-statement-report')->name('account.statement.')->group(function () {
                Route::get('/', [ReportController::class, 'accountStatementReport'])->name('report');
                Route::post('/upload', [ReportController::class, 'uploadBankStatement'])->name('upload');
                Route::get('/compare/{id}', [ReportController::class, 'compareWithStatement'])->name('compare');
                Route::get('/view/{id}', [ReportController::class, 'viewStatement'])->name('view');
                Route::delete('/delete/{id}', [ReportController::class, 'deleteStatement'])->name('delete');
            });


            Route::get('/import/leads/file', [LeadController::class, 'importFile'])->name('leads.import.file');
            Route::post('/leads/import', [LeadController::class, 'fileImport'])->name('leads.file.import');
            Route::get('/import/leads/modal', [LeadController::class, 'fileImportModal'])->name('leads.import.modal');
            Route::post('/import/leads', [LeadController::class, 'leadImportdata'])->name('leads.import.data');
            
            // Export route
            Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
            
            // Create route
            Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
            Route::post('/leads/store', [LeadController::class, 'store'])->name('leads.store');
            
            // Lead Resource route (this will create all standard routes)
            Route::resource('leads', LeadController::class);
            
            // Other lead routes
            Route::get('/leads/list', [LeadController::class, 'leadList'])->name('leads.list');
            Route::post('/leads/order', [LeadController::class, 'order'])->name('leads.order');
            Route::get('/leads/json', [LeadController::class, 'json'])->name('leads.json');
            
            // Social Connect
            Route::get('/social-connect', [LeadController::class, 'socialConnect'])->name('leads.social.connect');
            Route::post('/leads/fetch-facebook', [LeadController::class, 'fetchFacebookLeads'])->name('leads.fetch-facebook');
            Route::post('/leads/fetch-instagram', [LeadController::class, 'fetchInstagramLeads'])->name('leads.fetch-instagram');
            Route::post('/leads/fetch-whatsapp', [LeadController::class, 'fetchWhatsAppLeads'])->name('leads.fetch-whatsapp');
            
            // Other lead management routes
            Route::post('/leads/{id}/file', [LeadController::class, 'fileUpload'])->name('leads.file.upload');
            Route::get('/leads/{id}/file/{fid}', [LeadController::class, 'fileDownload'])->name('leads.file.download');
            Route::delete('/leads/{id}/file/delete/{fid}', [LeadController::class, 'fileDelete'])->name('leads.file.delete');
            Route::post('/leads/{id}/note', [LeadController::class, 'noteStore'])->name('leads.note.store');
            Route::get('/leads/{id}/labels', [LeadController::class, 'labels'])->name('leads.labels');
            Route::post('/leads/{id}/labels', [LeadController::class, 'labelStore'])->name('leads.labels.store');
            Route::get('/leads/{id}/users', [LeadController::class, 'userEdit'])->name('leads.users.edit');
            Route::put('/leads/{id}/users', [LeadController::class, 'userUpdate'])->name('leads.users.update');
            Route::delete('/leads/{id}/users/{uid}', [LeadController::class, 'userDestroy'])->name('leads.users.destroy');
            Route::get('/leads/{id}/products', [LeadController::class, 'productEdit'])->name('leads.products.edit');
            Route::put('/leads/{id}/products', [LeadController::class, 'productUpdate'])->name('leads.products.update');
            Route::delete('/leads/{id}/products/{uid}', [LeadController::class, 'productDestroy'])->name('leads.products.destroy');
            Route::get('/leads/{id}/sources', [LeadController::class, 'sourceEdit'])->name('leads.sources.edit');
            Route::put('/leads/{id}/sources', [LeadController::class, 'sourceUpdate'])->name('leads.sources.update');
            Route::delete('/leads/{id}/sources/{uid}', [LeadController::class, 'sourceDestroy'])->name('leads.sources.destroy');
            Route::get('/leads/{id}/discussions', [LeadController::class, 'discussionCreate'])->name('leads.discussions.create');
            Route::post('/leads/{id}/discussions', [LeadController::class, 'discussionStore'])->name('leads.discussion.store');
            Route::get('/leads/{id}/call', [LeadController::class, 'callCreate'])->name('leads.calls.create');
            Route::post('/leads/{id}/call', [LeadController::class, 'callStore'])->name('leads.calls.store');
            Route::get('/leads/{id}/call/{cid}/edit', [LeadController::class, 'callEdit'])->name('leads.calls.edit');
            Route::put('/leads/{id}/call/{cid}', [LeadController::class, 'callUpdate'])->name('leads.calls.update');
            Route::delete('/leads/{id}/call/{cid}', [LeadController::class, 'callDestroy'])->name('leads.calls.destroy');
            Route::get('/leads/{id}/email', [LeadController::class, 'emailCreate'])->name('leads.emails.create');
            Route::post('/leads/{id}/email', [LeadController::class, 'emailStore'])->name('leads.emails.store');
            Route::get('/leads/{id}/show_convert', [LeadController::class, 'showConvertToDeal'])->name('leads.convert.deal');
            Route::post('/leads/{id}/convert', [LeadController::class, 'convertToDeal'])->name('leads.convert.to.deal');
        });
            
            // ==============================================
            // DIRECT ROUTES (without prefix)
            // ==============================================
            Route::get('bank-reconciliation', [BankReconciliationController::class, 'index'])->name('bank-reconciliation.index');
            Route::get('bank-reconciliation/ledger-transactions', [BankReconciliationController::class, 'getLedgerTransactions'])->name('bank-reconciliation.ledger-transactions');
            Route::get('bank-reconciliation/compare/{ledger_id}/{submission_id}', [BankReconciliationController::class, 'compare'])->name('bank-reconciliation.compare');
            Route::get('report/bank.statement.reconciliation', [BankReconciliationController::class, 'index'])->name('report.bank.statement.reconciliation');
            
            // Bank Statement Resource
            Route::resource('bank-statement', BankStatementController::class);
            Route::post('bank-statement/store', [BankStatementController::class, 'store'])->name('bank-statement.store.direct');
            Route::get('bank-statement/{id}/download', [BankStatementController::class, 'download'])->name('bank-statement.download');

            //
            // Add these routes inside your authenticated routes group
        Route::group(['middleware' => ['auth', 'verified']], function() {
            // ... your existing routes ...
            
            // Lead Import/Export Routes
            Route::post('/leads/import/process', [LeadController::class, 'processImport'])->name('leads.import.process');
            Route::post('/leads/export/process', [LeadController::class, 'processExport'])->name('leads.export.process');
            Route::get('/leads/export/sample', [LeadController::class, 'exportSample'])->name('leads.export.sample');
        });
            
            // Compare routes
            Route::get('compare/{submission_id}', [BankReconciliationController::class, 'compare'])->name('compare.single');
            Route::get('compare/{ledger_id}/{submission_id}', [BankReconciliationController::class, 'compare'])->name('compare.full');

        // ==============================================
        // AUTH ROUTES (from Laravel)
        // ==============================================
        require __DIR__ . '/auth.php';
        // ==============================================
        // TEST ROUTES (No Auth Required)
        // ==============================================
        Route::get('/bonvoice-test-simple', function() {
            return response()->json([
                'success' => true,
                'message' => 'Bonvoice test route is working!',
                'time' => now()->toDateTimeString()
            ]);
        });

        Route::get('/bonvoice-direct-api-test', function() {
            try {
                $client = new \GuzzleHttp\Client(['verify' => false, 'timeout' => 30]);
                $response = $client->post('https://backend.pbx.bonvoice.com/usermanagement/external-auth/', [
                    'json' => [
                        'username' => 'BAYTHOMES',
                        'password' => 'Bayt@home#21'
                    ],
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    ]
                ]);
                
                $result = json_decode($response->getBody(), true);
                
                return response()->json([
                    'success' => true,
                    'token_received' => true,
                    'token' => substr($result['data']['token'] ?? '', 0, 30) . '...',
                    'full_response' => $result
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        });

        // ==============================================
        // Bonvoice Routes - Complete & Organized
        // ==============================================
        // ... all your other routes ...

        // ==============================================
        // Bonvoice Routes
        // ==============================================
        // Public Bonvoice routes (no auth)
        // ==============================================
        // Bonvoice Routes
        // ==============================================
        // Public Bonvoice routes (no auth)
        Route::prefix('bonvoice')->name('bonvoice.')->group(function () {
            Route::post('webhook', [BonvoiceController::class, 'handleWebhook'])->name('webhook')->withoutMiddleware(['auth']);
            Route::get('call-record/{callId}', [BonvoiceController::class, 'getCallRecord'])->name('call.record')->withoutMiddleware(['auth']);
            Route::post('test-connection', [BonvoiceController::class, 'testConnection'])->name('test.connection')->withoutMiddleware(['auth']);
        });

        // Auth protected Bonvoice routes
        Route::prefix('bonvoice')->name('bonvoice.')->middleware(['auth', 'verified'])->group(function () {
            // Settings routes
            Route::get('settings', [BonvoiceController::class, 'settings'])->name('settings');
            Route::post('settings', [BonvoiceController::class, 'saveSettings'])->name('settings.save');
            
            // Test routes
            Route::get('test-simple', [BonvoiceController::class, 'testSimple'])->name('test.simple');
            Route::get('test-api', [BonvoiceController::class, 'testApiDirect'])->name('test.api');
            Route::post('test-post', [BonvoiceController::class, 'testPost'])->name('test.post');
            Route::post('debug', [BonvoiceController::class, 'debugApi'])->name('debug');
            
            // Call management routes
            Route::post('make-call', [BonvoiceController::class, 'makeCall'])->name('make.call');
            Route::post('make-tts-call', [BonvoiceController::class, 'makeTextToSpeechCall'])->name('make.tts');
            Route::post('make-voicebot-call', [BonvoiceController::class, 'makeVoicebotCall'])->name('make.voicebot');
            
            // Call logs routes
            Route::get('call-logs', [BonvoiceController::class, 'callLogs'])->name('call_logs');
            Route::get('call-details/{id}', [BonvoiceController::class, 'callDetails'])->name('call_details');
            Route::get('call-log/{eventId}', [BonvoiceController::class, 'getCallLogByEventId'])->name('call.log.by.event');
            
            // Admin Call Logs
            Route::get('admin-call-logs', [BonvoiceController::class, 'adminCallLogs'])->name('admin.call.logs');
            
            // ========== NEW ROUTES ==========
            // Fetch call logs by Event ID
            Route::get('fetch-call-log/{eventId}', [BonvoiceController::class, 'fetchCallLogsFromBonvoice'])->name('fetch.call.log');
            
            // Fetch logs page (to manually enter Event ID)
            Route::get('fetch-logs', [BonvoiceController::class, 'fetchLogsPage'])->name('fetch.logs.page');
            // ========== END NEW ROUTES ==========
            
            // Reports routes
            Route::get('reports', [BonvoiceController::class, 'reports'])->name('reports');
            
            // IVR Configuration
            Route::get('ivr-config', [BonvoiceController::class, 'ivrConfig'])->name('ivr_config');
            Route::post('ivr-config', [BonvoiceController::class, 'saveIvrConfig'])->name('ivr_config.save');
            Route::post('ivr-test', [BonvoiceController::class, 'testIvr'])->name('test.ivr');
            
            // Route list
            Route::get('route-list', [BonvoiceController::class, 'getRouteList'])->name('route_list');
        });
        // ==============================================
        // AUTHENTICATION ROUTES
        // ==============================================
        // Logout route - must be outside any groups
        Route::post('logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
        // ==============================================
        // Chatify Messenger Routes
        // ==============================================
        Route::group(['middleware' => ['web', 'auth']], function () {
            Route::get('/chats', function () {
                return redirect()->route('chatify.index');
            });
            
            Route::get('/chatify', [MessagesController::class, 'index'])->name('chatify.index');
            Route::get('/chatify/{id}', [MessagesController::class, 'index'])->name('chatify');
            
            Route::group(['prefix' => 'chatify/api'], function () {
                Route::post('/pusher/auth', [MessagesController::class, 'pusherAuth'])->name('chatify.pusher.auth');
                Route::get('/user/{id}', [MessagesController::class, 'idFetchData'])->name('chatify.user');
                Route::post('/message', [MessagesController::class, 'send'])->name('chatify.send');
                Route::get('/messages/{id}', [MessagesController::class, 'fetch'])->name('chatify.fetch');
                Route::post('/seen', [MessagesController::class, 'seen'])->name('chatify.seen');
                Route::get('/contacts', [MessagesController::class, 'getContact'])->name('chatify.contacts');
                Route::post('/contact/update', [MessagesController::class, 'updateContactItem'])->name('chatify.contact.update');
                Route::post('/favorite', [MessagesController::class, 'favorite'])->name('chatify.favorite');
                Route::get('/favorites', [MessagesController::class, 'getFavorites'])->name('chatify.favorites');
                Route::post('/search', [MessagesController::class, 'search'])->name('chatify.search');
                Route::get('/shared/photos/{id}', [MessagesController::class, 'sharedPhotos'])->name('chatify.shared.photos');
                Route::delete('/conversation', [MessagesController::class, 'deleteConversation'])->name('chatify.conversation.delete');
            });
            
            Route::get('/chatify/download/{filename}', [MessagesController::class, 'download'])->name('chatify.download');
            Route::post('/chatify/settings', [MessagesController::class, 'updateSettings'])->name('chatify.settings');
            Route::post('/chatify/active-status', [MessagesController::class, 'setActiveStatus'])->name('chatify.active.status');
        });

        Route::post('settings/update-profile', [MessagesController::class, 'updateSettings'])->name('settings.update');

        // ==============================================
        // Letter Routes
        // ==============================================
        Route::post('joining-letter/{lang}', [JoiningLetterController::class, 'update'])->name('joiningletter.update');
        Route::get('joining-letter-language/{noclangs}/{explangs}/{offerlangs}/{joininglangs}', [JoiningLetterController::class, 'changeLanguage'])->name('get.joiningletter.language');
        Route::get('joining-letter/download/pdf/{id}', [JoiningLetterController::class, 'downloadPdf'])->name('joiningletter.download.pdf')->middleware(['web', 'auth', 'verified']);
        Route::get('joining-letter/download/doc/{id}', [JoiningLetterController::class, 'downloadDoc'])->name('joiningletter.download.doc')->middleware(['web', 'auth', 'verified']);
        Route::get('employee-document/{employeeDocumentId}/download', [EmployeeController::class, 'downloadDocument'])->name('employee.document.download');
        Route::get('/job-application/{id}/download', [JobApplicationController::class, 'downloadResume'])->name('job_application.download');
        Route::get('/job-application/edit/{id}', [JobApplicationController::class, 'edit'])->name('job_application.edit');
        Route::post('/job-application/update/{id}', [JobApplicationController::class, 'update'])->name('job_application.update');

        // ==============================================
        // Frontend Routes
        // ==============================================
        Route::get('about', [FrontendController::class, 'about'])->name('frontend.about');
        Route::get('our_features', [FrontendController::class, 'our_features'])->name('frontend.features');
        Route::get('showplans', [FrontendController::class, 'showPlans'])->name('frontend.showplans');
        Route::get('post-register-plans', [FrontendController::class, 'postRegisterPlans'])->name('frontend.post_register_plans');
        Route::get('new_faq', [FrontendController::class, 'new_faq'])->name('frontend.faq');
        Route::get('/contact', [FrontendController::class, 'contact'])->name('frontend.contact');
        Route::post('/contact', [FrontendController::class, 'storeContact'])->name('contact.store');
        Route::get('/contacts-list', [FrontendController::class, 'showContacts'])->name('frontend.contacts.list');
        Route::put('/contact/{id}', [FrontendController::class, 'updateContact'])->name('contact.update');
        Route::delete('/contact/{id}', [FrontendController::class, 'destroyContact'])->name('contact.destroy');
        Route::get('privacy_policy', [FrontendController::class, 'privacy_policy'])->name('frontend.privacy_policy');
        Route::get('terms_and_conditions', [FrontendController::class, 'terms_and_conditions'])->name('frontend.terms_and_conditions');
        Route::post('/subscribe-to-trial', [DashboardController::class, 'subscribeToTrial'])->name('subscribe.trial');

        // ==============================================
        // Invoice and Bill Links
        // ==============================================
        Route::get('/customer/invoice/{id}/', [InvoiceController::class, 'invoiceLink'])->name('invoice.link.copy');
        Route::get('/vender/bill/{id}/', [BillController::class, 'invoiceLink'])->name('bill.link.copy');
        Route::get('/vendor/purchase/{id}/', [PurchaseController::class, 'purchaseLink'])->name('purchase.link.copy');
        Route::get('/customer/proposal/{id}/', [ProposalController::class, 'invoiceLink'])->name('proposal.link.copy');
        Route::get('proposal/pdf/{id}', [ProposalController::class, 'proposal'])->name('proposal.pdf')->middleware(['XSS', 'revalidate']);

        // ==============================================
        // Invoice Payment Gateways
        // ==============================================
        Route::post('/customer-pay-with-bank', [BankTransferPaymentController::class, 'customerPayWithBank'])->name('customer.pay.with.bank')->middleware(['XSS']);
        Route::get('invoice/{id}/action', [BankTransferPaymentController::class, 'invoiceAction'])->name('invoice.action');
        Route::post('invoice/{id}/changeaction', [BankTransferPaymentController::class, 'invoiceChangeStatus'])->name('invoice.changestatus');
        Route::post('{id}/pay-with-paypal', [PaypalController::class, 'customerPayWithPaypal'])->name('customer.pay.with.paypal');
        Route::get('{id}/get-payment-status/{amount}', [PaypalController::class, 'customerGetPaymentStatus'])->name('customer.get.payment.status')->middleware(['XSS']);
        Route::post('/customer-pay-with-paystack', [PaystackPaymentController::class, 'customerPayWithPaystack'])->name('customer.pay.with.paystack')->middleware(['XSS']);
        Route::get('/customer/paystack/{pay_id}/{invoice_id}', [PaystackPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.paystack');
        Route::post('/customer-pay-with-paytm', [PaytmPaymentController::class, 'customerPayWithPaytm'])->name('customer.pay.with.paytm')->middleware(['XSS']);
        Route::post('/customer/paytm/{invoice}/{amount}', [PaytmPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.paytm');
        Route::post('/customer-pay-with-flaterwave', [FlutterwavePaymentController::class, 'customerPayWithFlutterwave'])->name('customer.pay.with.flaterwave')->middleware(['XSS']);
        Route::get('/customer/flaterwave/{txref}/{invoice_id}', [FlutterwavePaymentController::class, 'getInvoicePaymentStatus'])->name('customer.flaterwave');
        Route::post('/customer-pay-with-razorpay', [RazorpayPaymentController::class, 'customerPayWithRazorpay'])->name('customer.pay.with.razorpay')->middleware(['XSS']);
        Route::get('/customer/razorpay/{txref}/{invoice_id}', [RazorpayPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.razorpay');
        Route::post('/customer-pay-with-mercado', [MercadoPaymentController::class, 'customerPayWithMercado'])->name('customer.pay.with.mercado')->middleware(['XSS']);
        Route::get('/customer/mercado/{invoice}', [MercadoPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.mercado');
        Route::post('/customer-pay-with-mollie', [MolliePaymentController::class, 'customerPayWithMollie'])->name('customer.pay.with.mollie')->middleware(['XSS']);
        Route::get('/customer/mollie/{invoice}/{amount}', [MolliePaymentController::class, 'getInvoicePaymentStatus'])->name('customer.mollie');
        Route::post('/customer-pay-with-skrill', [SkrillPaymentController::class, 'customerPayWithSkrill'])->name('customer.pay.with.skrill')->middleware(['XSS']);
        Route::get('/customer/skrill/{invoice}/{amount}', [SkrillPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.skrill');
        Route::post('/customer-pay-with-coingate', [CoingatePaymentController::class, 'customerPayWithCoingate'])->name('customer.pay.with.coingate')->middleware(['XSS']);
        Route::get('/customer/coingate/{invoice}/{amount}', [CoingatePaymentController::class, 'getInvoicePaymentStatus'])->name('customer.coingate');
        Route::post('/paymentwall', [PaymentWallPaymentController::class, 'invoicepaymentwall'])->name('invoice.paymentwallpayment')->middleware(['XSS']);
        Route::post('/invoice-pay-with-paymentwall/{invoice}', [PaymentWallPaymentController::class, 'invoicePayWithPaymentwall'])->name('invoice.pay.with.paymentwall')->middleware(['XSS']);
        Route::get('/invoices/{flag}/{invoice}', [PaymentWallPaymentController::class, 'invoiceerror'])->name('error.invoice.show');
        Route::post('/customer-pay-with-toyyibpay', [ToyyibpayController::class, 'invoicepaywithtoyyibpay'])->name('customer.pay.with.toyyibpay');
        Route::get('/customer/toyyibpay/{invoice}/{amount}', [ToyyibpayController::class, 'getInvoicePaymentStatus'])->name('customer.toyyibpay');
        Route::post('invoice-with-payfast', [PayFastController::class, 'invoicePayWithPayFast'])->name('invoice.with.payfast');
        Route::get('invoice-payfast-status/{success}', [PayFastController::class, 'invoicepayfaststatus'])->name('invoice.payfast.status');
        Route::post('/customer-pay-with-iyzipay', [IyziPayController::class, 'invoicepaywithiyzipay'])->name('customer.pay.with.iyzipay');
        Route::post('iyzipay/callback/{invoice}/{amount}', [IyziPayController::class, 'getInvoiceiyzipayCallback'])->name('iyzipay.invoicepayment.callback');
        Route::post('/customer-pay-with-sspay', [SspayController::class, 'invoicepaywithsspaypay'])->name('customer.pay.with.sspay');
        Route::get('/customer/sspay/{invoice}/{amount}', [SspayController::class, 'getInvoicePaymentStatus'])->name('customer.sspay');
        Route::post('/invoice-pay-with-paytab', [PaytabController::class, 'invoicePayWithpaytab'])->name('customer.pay.with.paytab');
        Route::any('/invoice-paytab-success/{invoice}', [PaytabController::class, 'getInvoicePaymentStatus'])->name('invoice.paytab.success');
        Route::any('invoice-with-benefit', [BenefitPaymentController::class, 'invoicepaywithbenefit'])->name('invoice.benefit.initiate');
        Route::any('/invoice/benefit/{invoice_id}/{amount}', [BenefitPaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.benefit.call_back');
        Route::post('invoice-with-cashfree', [CashfreeController::class, 'invoicepaywithcashfree'])->name('customer.pay.with.cashfree');
        Route::any('invoice-with-cashfree/cashfree', [CashfreeController::class, 'getInvoicePaymentStatus'])->name('invoice.cashfreePayment.success');
        Route::post('aamarpay/invoice/', [AamarpayController::class, 'invoicepaywithaamarpay'])->name('customer.pay.with.aamarpay');
        Route::any('aamarpay/invoice/success/{data}', [AamarpayController::class, 'getInvoicePaymentStatus'])->name('invoice.pay.aamarpay.success');
        Route::post('/invoice-with-paytr', [PaytrController::class, 'invoicepaywithpaytr'])->name('customer.pay.with.paytr');
        Route::get('/invoice/paytr/status', [PaytrController::class, 'getInvoicePaymentStatus'])->name('invoice.paytr');
        Route::post('invoice-with-yookassa/', [YooKassaController::class, 'invoicePayWithYookassa'])->name('customer.with.yookassa');
        Route::any('invoice-yookassa-status/', [YooKassaController::class, 'getInvociePaymentStatus'])->name('invoice.yookassa.status');
        Route::any('invoice-with-midtrans/', [MidtransPaymentController::class, 'invoicePayWithMidtrans'])->name('customer.with.midtrans');
        Route::any('invoice-midtrans-status/', [MidtransPaymentController::class, 'getInvociePaymentStatus'])->name('invoice.midtrans.status');
        Route::any('/invoice-with-xendit', [XenditPaymentController::class, 'invoicePayWithXendit'])->name('customer.with.xendit');
        Route::any('/invoice-xendit-status', [XenditPaymentController::class, 'getInvociePaymentStatus'])->name('invoice.xendit.status');
        Route::post('invoice-with-paiementpro/', [PaiementProController::class, 'invoicePayWithPaiementPro'])->name('customer.with.paiementpro');
        Route::get('invoice-paiementpro-status/{invoice_id}', [PaiementProController::class, 'getInvociePaymentStatus'])->name('invoice.paiementpro.status');
        Route::post('/invoice-nepalste/payment', [NepalstePaymnetController::class, 'invoicePayWithnepalste'])->name('customer.with.nepalste');
        Route::get('invoice-nepalste/status/', [NepalstePaymnetController::class, 'invoiceGetNepalsteStatus'])->name('invoice.nepalste.status');
        Route::get('invoice-nepalste/cancel/', [NepalstePaymnetController::class, 'invoiceGetNepalsteCancel'])->name('invoice.nepalste.cancel');
        Route::post('/invoice/company/payment', [CinetPayController::class, 'invoicePayWithCinetPay'])->name('customer.with.cinetpay');
        Route::post('/invoice/company/payment/return', [CinetPayController::class, 'invoiceCinetPayReturn'])->name('invoice.cinetpay.return');
        Route::post('/invoice/company/payment/notify/', [CinetPayController::class, 'invoiceCinetPayNotify'])->name('invoice.cinetpay.notify');
        Route::post('invoice-with-fedapay/', [FedapayController::class, 'invoicePayWithFedapay'])->name('customer.with.fedapay');
        Route::get('invoice-fedapay-status/', [FedapayController::class, 'getInvociePaymentStatus'])->name('invoice.fedapay.status');
        Route::post('invoice-with-payhere/', [PayHereController::class, 'invoicePayWithPayHere'])->name('customer.with.payhere');
        Route::get('invoice-payhere-status/', [PayHereController::class, 'getInvociePaymentStatus'])->name('invoice.payhere.status');
        Route::post('/invoice-pay-with/tap', [TapController::class, 'invoicePayWithtap'])->name('invoice.pay.with.tap');
        Route::get('/invoice/tap/{invoice_id}/{amount}', [TapController::class, 'getInvoicePaymentStatus'])->name('invoice.tap');
        Route::any('/invoice-pay-with-authorize-net', [AuthorizeNetController::class, 'invoicePayWithAuthorizeNet'])->name('invoice.pay.with.authorizenet');
        Route::any('/invoice-get-authorizenet-status', [AuthorizeNetController::class, 'getInvoicePaymentStatus'])->name('invoice.get.authorizenet.status');
        Route::post('/invoice-pay-khalti', [KhaltiController::class, 'getInvoicePaymentStatus'])->name('invoice.pay.with.khalti');
        Route::any('/invoice-pay-easebuzz', [EasebuzzController::class, 'invoicePayWitheasebuzz'])->name('invoice.pay.with.easebuzz');
        Route::match(['get', 'post'], '/invoice-easebuzz-payment-return', [EasebuzzController::class, 'invoiceNotifyUrl'])->name('invoice.easebuzz.return');
        Route::match(['get', 'post'], 'invoice-easebuzz-payment-notify', [EasebuzzController::class, 'invoiceReturnUrl'])->name('invoice.get.easebuzz.notify');
        Route::post('/invoice-pay-ozow', [OzowPaymentController::class, 'invoicePayWithOzow'])->name('invoice.pay.with.ozow');
        Route::get('/invoice-pay-ozow/{invoice_id}/', [OzowPaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.ozow.status');

        // ==============================================
        // Career Routes
        // ==============================================
        Route::get('career/{id}/{lang}', [JobController::class, 'career'])->name('career')->middleware(['XSS']);
        Route::get('job/requirement/{code}/{lang}', [JobController::class, 'jobRequirement'])->name('job.requirement')->middleware(['XSS']);
        Route::get('job/apply/{code}/{lang}', [JobController::class, 'jobApply'])->name('job.apply')->middleware(['XSS']);
        Route::post('job/apply/data/{code}', [JobController::class, 'jobApplyData'])->name('job.apply.data')->middleware(['XSS']);

        // ==============================================
        // Project Routes
        // ==============================================
        Route::get('/projects/copylink/{id}', [ProjectController::class, 'projectCopyLink'])->name('projects.copylink');
        Route::any('/projects/link/{id}/{lang?}', [ProjectController::class, 'projectlink'])->name('projects.link')->middleware(['XSS']);
        Route::get('timesheet-table-view', [TimesheetController::class, 'filterTimesheetTableView'])->name('filter.timesheet.table.view')->middleware(['auth', 'XSS']);

        // ==============================================
        // Invoice PDF
        // ==============================================
        Route::post('customer/{id}/payment', [StripePaymentController::class, 'addpayment'])->name('customer.payment');
        Route::get('invoice/pdf/{id}', [InvoiceController::class, 'invoice'])->name('invoice.pdf')->middleware(['XSS', 'revalidate']);

        // ==============================================
        // User Login Routes
        // ==============================================
        Route::get('users/{id}/login-with-company', [UserController::class, 'LoginWithCompany'])->name('login.with.company')->middleware(['auth']);
        Route::get('login-with-company/exit', [UserController::class, 'ExitCompany'])->name('exit.company')->middleware(['auth']);
        Route::get('user-login/{id}', [UserController::class, 'LoginManage'])->name('users.login');

        // ==============================================
        // Form Builder Routes
        // ==============================================
        Route::get('/form/{code}', [FormBuilderController::class, 'formView'])->name('form.view')->middleware(['XSS']);
        Route::post('/form_view_store', [FormBuilderController::class, 'formViewStore'])->name('form.view.store')->middleware(['XSS']);

        // ==============================================
        // Landing Page
        // ==============================================
        Route::get('/', [DashboardController::class, 'landingpage'])->middleware(['XSS', 'revalidate'])->name('dashboard.landingpage');

        // ==============================================
        // Cache Clear Route
        // ==============================================
        Route::get('/config-cache', function () {
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('optimize:clear');
            return redirect()->back()->with('success', 'Cache Clear Successfully');
        })->name('config.cache');

        // ==============================================
        // Helper Check Route
        // ==============================================
        Route::get('/check-helpers', function() {
            return response()->json(get_defined_functions()['user']);
        });

        // ==============================================
        // Invoice Extract Routes
        // ==============================================
        Route::get('/invoice/extract', [InvoiceExtractController::class, 'index'])->name('invoice.extract');
        Route::post('/invoice/process', [InvoiceExtractController::class, 'process'])->name('invoice.process');
        Route::post('/invoice/save', [InvoiceExtractController::class, 'saveAndDownload'])->name('invoice.save');
        Route::post('/invoice/save-download', [InvoiceExtractController::class, 'saveAndDownload'])->name('invoice.saveAndDownload');

        // ==============================================
        // Work Report Routes
        // ==============================================
        // ============================================================
        // WORK REPORT ROUTES - SINGLE CLEAN VERSION
        // ============================================================
        Route::prefix('work-report')->as('workreport.')->middleware(['auth', 'verified'])->group(function () {
            Route::get('/', [WorkReportController::class, 'index'])->name('index');
            Route::get('/create', [WorkReportController::class, 'create'])->name('create');
            Route::post('/submit', [WorkReportController::class, 'store'])->name('submit');
            Route::get('/my', [WorkReportController::class, 'myReports'])->name('my');
            Route::get('/{id}', [WorkReportController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [WorkReportController::class, 'edit'])->name('edit');
            Route::put('/{id}', [WorkReportController::class, 'update'])->name('update');
            Route::delete('/{id}', [WorkReportController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/review', [WorkReportController::class, 'review'])->name('review');
            Route::post('/{id}/review', [WorkReportController::class, 'updateReview'])->name('updateReview');
            Route::get('/export', [WorkReportController::class, 'export'])->name('export');
            
            // AJAX Routes
            Route::get('/status', [WorkReportController::class, 'getStatus'])->name('status');
            Route::get('/popup-data', [WorkReportController::class, 'getPopupData'])->name('popup-data');
        });

        // ==============================================
        // Face Attendance Routes
        // ==============================================
        Route::middleware(['auth'])->group(function () {
            Route::get('/face/attendance', [FaceAttendanceController::class, 'showAttendance'])->name('face.attendance');
            Route::get('/face/enroll', function () {
                return view('face.enroll');
            })->name('face.enroll.view');
            Route::post('/face/enroll', [FaceAttendanceController::class, 'enroll'])->name('face.enroll');
            Route::post('/face/recognize', [FaceAttendanceController::class, 'recognize'])->name('face.recognize');
            Route::post('/face/mark/location', [FaceAttendanceController::class, 'markLocation'])->name('face.mark.location');
            Route::post('/face/mark/action', [FaceAttendanceController::class, 'mark.action']);
            Route::post('/face/mark', [FaceAttendanceController::class, 'markAction'])->name('face.mark');
            Route::get('/attendance/status', [AttendanceEmployeeController::class, 'getStatus'])->name('attendance.status');

            // Attendance – Break management
        Route::post('/attendance/break/start', [AttendanceEmployeeController::class, 'startBreak'])->name('attendance.break.start');
        Route::post('/attendance/break/end', [AttendanceEmployeeController::class, 'endBreak'])->name('attendance.break.end');
        Route::post('/attendance/live/refresh', [AttendanceEmployeeController::class, 'refreshLive'])->name('attendance.live.refresh');
        Route::post('/attendance/live/refresh', [AttendanceEmployeeController::class, 'refreshLive'])->name('attendance.live.refresh');
        Route::get('/attendance/details', [AttendanceEmployeeController::class, 'getAttendanceDetails'])->name('attendance.details');

        });


        // ============================================================
        // FACE RECOGNITION ROUTES
        // ============================================================
        Route::middleware(['auth'])->group(function () {
            
            // ===== EMPLOYEE SELF-SERVICE FACE ATTENDANCE =====
            Route::get('/face/clockin', function() {
                if(!\Auth::user()->can('view face id attendance') || !\Auth::user()->can('mark face id attendance')) {
                    abort(403, 'You do not have permission to access this page.');
                }
                return view('face.clockin');
            })->name('face.clockin');
            
            // ===== FACE ENROLLMENT =====
            Route::get('/face/enroll', [AttendanceEmployeeController::class, 'faceEnrollmentPage'])
                ->name('face.enroll.page')
                ->middleware('can:create face id attendance');
            
            Route::post('/face/enroll', [AttendanceEmployeeController::class, 'enrollFace'])
                ->name('face.enroll')
                ->middleware('can:create face id attendance');
            
            Route::get('/face/enrollment-status', [AttendanceEmployeeController::class, 'getFaceEnrollmentStatus'])
                ->name('face.enrollment.status');
            
            // ===== ADMIN FACE ATTENDANCE MARKING =====
            Route::get('/attendance/face-mark', [AttendanceEmployeeController::class, 'faceMarkAttendance'])
                ->name('attendance.face.mark')
                ->middleware('can:manage face id attendance');
            
            // ===== FACE VERIFICATION & MARKING (AJAX) =====
            Route::post('/attendance/verify-face', [AttendanceEmployeeController::class, 'verifyFace'])
                ->name('attendance.verify.face');
            
            Route::post('/attendance/mark-face', [AttendanceEmployeeController::class, 'markFaceAttendance'])
                ->name('attendance.mark.face');
            
            // ===== FACE ATTENDANCE STATS =====
            Route::get('/attendance/face-stats', [AttendanceEmployeeController::class, 'getFaceAttendanceStats'])
                ->name('attendance.face.stats');
            
            // ===== ATTENDANCE STATUS =====
            Route::get('/attendance/face-status', [AttendanceEmployeeController::class, 'getFaceAttendanceStatus'])
                ->name('attendance.face.status');
            
            Route::get('/attendance/status', [AttendanceEmployeeController::class, 'getUserAttendanceStatus'])
                ->name('attendance.status');
            
            // ===== TEA BREAK & PUNCH OUT =====
            Route::post('/attendance/attendance', [AttendanceEmployeeController::class, 'attendance'])
                ->name('attendance.attendance');
            
            // ===== LOCATION VALIDATION =====
            Route::post('/attendance/validate-location', [AttendanceEmployeeController::class, 'validateLocation'])
                ->name('attendance.validate.location');
            
            // ===== ATTENDANCE DASHBOARD & LIVE =====
            Route::get('/attendance/dashboard', [AttendanceEmployeeController::class, 'dashboard'])
                ->name('attendance.dashboard')
                ->middleware('can:manage attendance');
            
            Route::get('/attendance/live', [AttendanceEmployeeController::class, 'live'])
                ->name('attendance.live')
                ->middleware('can:view attendance');
            
            Route::get('/attendance/daily', [AttendanceEmployeeController::class, 'daily'])
                ->name('attendance.daily')
                ->middleware('can:view attendance');
            
            Route::get('/attendance/roster', [AttendanceEmployeeController::class, 'roster'])
                ->name('attendance.roster')
                ->middleware('can:view attendance');
            
            Route::get('/attendanceemployee/index', [AttendanceEmployeeController::class, 'index'])
                ->name('attendanceemployee.index')
                ->middleware('can:manage attendance');
            
            Route::post('/attendanceemployee/store', [AttendanceEmployeeController::class, 'store'])
                ->name('attendanceemployee.store')
                ->middleware('can:create attendance');
            
            Route::get('/attendanceemployee/{id}/edit', [AttendanceEmployeeController::class, 'edit'])
                ->name('attendanceemployee.edit')
                ->middleware('can:edit attendance');
            
            Route::put('/attendanceemployee/{id}', [AttendanceEmployeeController::class, 'update'])
                ->name('attendanceemployee.update')
                ->middleware('can:edit attendance');
            
            Route::delete('/attendanceemployee/{id}', [AttendanceEmployeeController::class, 'destroy'])
                ->name('attendanceemployee.destroy')
                ->middleware('can:delete attendance');
        });

        // ============================================================
        // LOCATION SETTINGS
        // ============================================================
        Route::middleware(['auth'])->group(function () {
            Route::get('/settings/location', [SettingsController::class, 'location'])
                ->name('settings.location')
                ->middleware('can:manage company settings');
            
            Route::post('/settings/location', [SettingsController::class, 'updateLocation'])
                ->name('settings.location.update')
                ->middleware('can:manage company settings');
        });

        // ============================================================
        // FACE ID ROLE MANAGEMENT
        // ============================================================
        Route::middleware(['auth'])->group(function () {
            Route::get('/face-role/create', function() {
                if (\Auth::user()->can('create role')) {
                    $user = \Auth::user();
                    if($user->type == 'super admin' || $user->type == 'company') {
                        $permissions = Permission::all()->pluck('name', 'id')->toArray();
                    } else {
                        $permissions = new \Illuminate\Support\Collection();
                        foreach($user->roles as $role) {
                            $permissions = $permissions->merge($role->permissions);
                        }
                        $permissions = $permissions->pluck('name', 'id')->toArray();
                    }
                    return view('role.face_role_create', ['permissions' => $permissions]);
                } else {
                    return redirect()->back()->with('error', 'Permission denied.');
                }
            })->name('face.role.create');
            
            Route::post('/face-role/store', [\App\Http\Controllers\RoleController::class, 'store'])
                ->name('face.role.store');
        });

        Route::get('/attendance/recalculate/{id}', [AttendanceEmployeeController::class, 'recalculateAttendance'])->name('attendance.recalculate');
        // Attendance Routes
        Route::group(['middleware' => ['auth', 'verified', 'XSS', 'revalidate']], function () {
            // ... your existing attendance routes ...
            
            // ✅ Add this line for the refresh endpoint
            Route::get('/attendance/refresh', [AttendanceEmployeeController::class, 'refreshLive'])->name('attendance.refresh');
            
            // ✅ Also make sure these routes exist
            Route::get('/attendance/live', [AttendanceEmployeeController::class, 'live'])->name('attendance.live');
            Route::get('/attendance/details', [AttendanceEmployeeController::class, 'getAttendanceDetails'])->name('attendance.details');
        });

        // System Controller - Week Off & Holiday routes
        Route::get('/settings/week-off', [SystemController::class, 'getWeekOffSettings'])->name('settings.week-off');
        Route::get('/settings/holidays', [SystemController::class, 'getHolidaySettings'])->name('settings.holidays');
        Route::get('/settings/is-holiday/{date}', [SystemController::class, 'isHoliday'])->name('settings.is-holiday');
        Route::get('/settings/is-week-off/{employeeId}/{date}', [SystemController::class, 'isWeekOff'])->name('settings.is-week-off');

        // ==============================================
        // Public Webhook Routes
        // ==============================================
        Route::post('/webhook/leads', [LeadController::class, 'webhook'])->name('webhook.leads');

        // ==============================================
        // Authenticated Routes (All routes below require auth)
        // ==============================================
        Route::group(['middleware' => ['verified', 'auth']], function () {
            
            // Dashboard routes
            Route::get('/account-dashboard', [DashboardController::class, 'account_dashboard_index'])->name('dashboard')->middleware(['XSS', 'revalidate']);
            Route::get('/project-dashboard', [DashboardController::class, 'project_dashboard_index'])->name('project.dashboard')->middleware(['XSS', 'revalidate']);
            Route::get('/hrm-dashboard', [DashboardController::class, 'hrm_dashboard_index'])->name('hrm.dashboard')->middleware(['XSS', 'revalidate']);
            Route::get('/crm-dashboard', [DashboardController::class, 'crm_dashboard_index'])->name('crm.dashboard')->middleware(['XSS', 'revalidate']);
            Route::get('/pos-dashboard', [DashboardController::class, 'pos_dashboard_index'])->name('pos.dashboard')->middleware(['XSS', 'revalidate']);

            // Profile routes
            Route::get('profile', [UserController::class, 'profile'])->name('profile')->middleware(['XSS', 'revalidate']);
            Route::any('edit-profile', [UserController::class, 'editprofile'])->name('update.account')->middleware(['XSS', 'revalidate']);

            // User routes
            Route::resource('users', UserController::class)->middleware(['XSS', 'revalidate']);
            Route::post('change-password', [UserController::class, 'updatePassword'])->name('update.password');
            Route::any('user-reset-password/{id}', [UserController::class, 'userPassword'])->name('users.reset');
            Route::post('user-reset-password/{id}', [UserController::class, 'userPasswordReset'])->name('user.password.update');
            Route::get('company-info/{id}', [UserController::class, 'companyInfo'])->name('company.info');
            Route::post('user-unable', [UserController::class, 'userUnable'])->name('user.unable');
            Route::get('/change/mode', [UserController::class, 'changeMode'])->name('change.mode');

            // Role and Permission routes
            Route::resource('roles', RoleController::class)->middleware(['XSS', 'revalidate']);
            Route::resource('permissions', PermissionController::class)->middleware(['XSS', 'revalidate']);

            // Language routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('change-language/{lang}', [LanguageController::class, 'changeLanquage'])->name('change.language');
                Route::get('manage-language/{lang}', [LanguageController::class, 'manageLanguage'])->name('manage.language');
                Route::post('store-language-data/{lang}', [LanguageController::class, 'storeLanguageData'])->name('store.language.data');
                Route::get('create-language', [LanguageController::class, 'createLanguage'])->name('create.language');
                Route::any('store-language', [LanguageController::class, 'storeLanguage'])->name('store.language');
                Route::delete('/lang/{lang}', [LanguageController::class, 'destroyLang'])->name('lang.destroy');
                Route::post('disable-language', [LanguageController::class, 'disableLang'])->name('disablelanguage');
            });

            // System settings routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::resource('systems', SystemController::class);
                Route::post('email-settings', [SystemController::class, 'saveEmailSettings'])->name('email.settings');
                Route::post('company-email-settings', [SystemController::class, 'saveCompanyEmailSettings'])->name('company.email.settings');
                Route::post('company-settings', [SystemController::class, 'saveCompanySettings'])->name('company.settings');
                Route::post('system-settings', [SystemController::class, 'saveSystemSettings'])->name('system.settings');
                Route::post('zoom-settings', [SystemController::class, 'saveZoomSettings'])->name('zoom.settings');
                Route::post('tracker-settings', [SystemController::class, 'saveTrackerSettings'])->name('tracker.settings');
                Route::post('slack-settings', [SystemController::class, 'saveSlackSettings'])->name('slack.settings');
                Route::post('telegram-settings', [SystemController::class, 'saveTelegramSettings'])->name('telegram.settings');
                Route::post('twilio-settings', [SystemController::class, 'saveTwilioSettings'])->name('twilio.setting');
                Route::get('print-setting', [SystemController::class, 'printIndex'])->name('print.setting');
                Route::get('settings', [SystemController::class, 'companyIndex'])->name('settings');
                Route::post('business-setting', [SystemController::class, 'saveBusinessSettings'])->name('business.setting');
                Route::post('company-payment-setting', [SystemController::class, 'saveCompanyPaymentSettings'])->name('company.payment.settings');
                Route::post('currency-settings', [SystemController::class, 'saveCurrencySettings'])->name('currency.settings');
                Route::post('company-preview', [SystemController::class, 'currencyPreview'])->name('currency.preview');
                Route::any('test-mail', [SystemController::class, 'testMail'])->name('test.mail');
                Route::post('test-mail/send', [SystemController::class, 'testSendMail'])->name('test.send.mail');
                Route::post('stripe-settings', [SystemController::class, 'savePaymentSettings'])->name('payment.settings');
                Route::post('pusher-setting', [SystemController::class, 'savePusherSettings'])->name('pusher.setting');
                Route::post('recaptcha-settings', [SystemController::class, 'recaptchaSettingStore'])->name('recaptcha.settings.store');
                Route::post('seo-settings', [SystemController::class, 'seoSettings'])->name('seo.settings.store');
                Route::any('webhook-settings', [SystemController::class, 'webhook'])->name('webhook.settings');
                Route::get('webhook-settings/create', [SystemController::class, 'webhookCreate'])->name('webhook.create');
                Route::post('webhook-settings/store', [SystemController::class, 'webhookStore'])->name('webhook.store');
                Route::get('webhook-settings/{wid}/edit', [SystemController::class, 'webhookEdit'])->name('webhook.edit');
                Route::post('webhook-settings/{wid}/edit', [SystemController::class, 'webhookUpdate'])->name('webhook.update');
                Route::delete('webhook-settings/{wid}', [SystemController::class, 'webhookDestroy'])->name('webhook.destroy');
                Route::post('cookie-setting', [SystemController::class, 'saveCookieSettings'])->name('cookie.setting');
                Route::post('cache-settings', [SystemController::class, 'cacheSettingStore'])->name('cache.settings.store');
                Route::post('storage-settings', [SystemController::class, 'storageSettingStore'])->name('storage.setting.store');
                Route::post('chatgpt-settings', [SystemController::class, 'chatgptSetting'])->name('chatgpt.settings');
                Route::post('system-settings/note', [SystemController::class, 'footerNoteStore'])->name('system.settings.footernote');
                Route::post('setting/google-calender', [SystemController::class, 'saveGoogleCalenderSettings'])->name('google.calender.settings');
                Route::get('create/ip', [SystemController::class, 'createIp'])->name('create.ip');
                Route::post('create/ip', [SystemController::class, 'storeIp'])->name('store.ip');
                Route::get('edit/ip/{id}', [SystemController::class, 'editIp'])->name('edit.ip');
                Route::post('edit/ip/{id}', [SystemController::class, 'updateIp'])->name('update.ip');
                Route::delete('destroy/ip/{id}', [SystemController::class, 'destroyIp'])->name('destroy.ip');
            });

            // Product Service routes
            Route::get('productservice/{id}/detail', [ProductServiceController::class, 'warehouseDetail'])->name('productservice.detail');
            Route::post('empty-cart', [ProductServiceController::class, 'emptyCart'])->middleware(['XSS']);
            Route::post('warehouse-empty-cart', [ProductServiceController::class, 'warehouseemptyCart'])->name('warehouse-empty-cart')->middleware(['XSS']);
            Route::resource('productservice', ProductServiceController::class)->middleware(['XSS', 'revalidate']);
            Route::resource('productstock', ProductStockController::class)->middleware(['XSS']);

            // Customer and Vender routes
            Route::resource('customer', CustomerController::class)->middleware(['XSS', 'revalidate']);
            Route::resource('vender', VenderController::class)->middleware(['XSS', 'revalidate']);
            Route::resource('bank-account', BankAccountController::class)->middleware(['XSS', 'revalidate']);
            Route::resource('bank-transfer', BankTransferController::class)->middleware(['XSS', 'revalidate']);

            // Tax and Category routes
            Route::resource('taxes', TaxController::class)->middleware(['XSS', 'revalidate']);
            Route::resource('product-category', ProductServiceCategoryController::class)->middleware(['XSS', 'revalidate']);
            Route::post('product-category/getaccount', [ProductServiceCategoryController::class, 'getAccount'])->name('productServiceCategory.getaccount')->middleware(['XSS', 'revalidate']);
            Route::resource('product-unit', ProductServiceUnitController::class)->middleware(['XSS', 'revalidate']);

            // Invoice routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('invoice/{id}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoice.duplicate');
                Route::get('invoice/{id}/shipping/print', [InvoiceController::class, 'shippingDisplay'])->name('invoice.shipping.print');
                Route::get('invoice/{id}/payment/reminder', [InvoiceController::class, 'paymentReminder'])->name('invoice.payment.reminder');
                Route::post('invoice/product/destroy', [InvoiceController::class, 'productDestroy'])->name('invoice.product.destroy');
                Route::post('invoice/product', [InvoiceController::class, 'product'])->name('invoice.product');
                Route::post('invoice/customer', [InvoiceController::class, 'customer'])->name('invoice.customer');
                Route::get('invoice/{id}/sent', [InvoiceController::class, 'sent'])->name('invoice.sent');
                Route::get('invoice/{id}/resent', [InvoiceController::class, 'resent'])->name('invoice.resent');
                Route::get('invoice/{id}/payment', [InvoiceController::class, 'payment'])->name('invoice.payments');
                Route::post('invoice/{id}/payment', [InvoiceController::class, 'createPayment'])->name('invoice.payment');
                Route::post('invoice/{id}/payment/{pid}/destroy', [InvoiceController::class, 'paymentDestroy'])->name('invoice.payment.destroy');
                Route::get('invoice/items', [InvoiceController::class, 'items'])->name('invoice.items');
                Route::resource('invoice', InvoiceController::class);
                Route::get('invoice/create/{cid}', [InvoiceController::class, 'create'])->name('customer.invoice.create');
            });

            Route::get('/invoices/preview/{template}/{color}', [InvoiceController::class, 'previewInvoice'])->name('invoice.preview');
            Route::post('/invoices/template/setting', [InvoiceController::class, 'saveTemplateSettings'])->name('template.setting');

            // Credit Note routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('credit-note', [CreditNoteController::class, 'index'])->name('credit.note');
                Route::get('custom-credit-note', [CreditNoteController::class, 'customCreate'])->name('invoice.custom.credit.notes');
                Route::post('custom-credit-note', [CreditNoteController::class, 'customStore'])->name('invoice.custom.credit.note');
                Route::get('credit-note/invoice', [CreditNoteController::class, 'getinvoice'])->name('invoice.get');
                Route::get('invoice/{id}/credit-note', [CreditNoteController::class, 'create'])->name('saas-');
                Route::post('invoice/{id}/credit-note', [CreditNoteController::class, 'store'])->name('invoice.credit.note');
                Route::get('invoice/{id}/credit-notes/edit/{cn_id}', [CreditNoteController::class, 'edit'])->name('invoice.edit.credit.notes');
                Route::post('invoice/{id}/credit-note/edit/{cn_id}', [CreditNoteController::class, 'update'])->name('invoice.edit.credit.note');
                Route::delete('invoice/{id}/credit-note/delete/{cn_id}', [CreditNoteController::class, 'destroy'])->name('invoice.delete.credit.note');
            });

            // Debit Note routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('debit-note', [DebitNoteController::class, 'index'])->name('debit.note');
                Route::get('custom-debit-notes', [DebitNoteController::class, 'customCreate'])->name('bill.custom.debit.notes');
                Route::post('custom-debit-note', [DebitNoteController::class, 'customStore'])->name('bill.custom.debit.note');
                Route::get('debit-note/bill', [DebitNoteController::class, 'getbill'])->name('bill.get');
                Route::get('bill/{id}/debit-notes', [DebitNoteController::class, 'create'])->name('bill.debit.notes');
                Route::post('bill/{id}/debit-note', [DebitNoteController::class, 'store'])->name('bill.debit.note');
                Route::get('bill/{id}/debit-notes/edit/{cn_id}', [DebitNoteController::class, 'edit'])->name('bill.edit.debit.notes');
                Route::post('bill/{id}/debit-note/edit/{cn_id}', [DebitNoteController::class, 'update'])->name('bill.edit.debit.note');
                Route::delete('bill/{id}/debit-note/delete/{cn_id}', [DebitNoteController::class, 'destroy'])->name('bill.delete.debit.note');
            });

            // Bill routes
            Route::get('/bill/preview/{template}/{color}', [BillController::class, 'previewBill'])->name('bill.preview')->middleware(['XSS']);
            Route::post('/bill/template/setting', [BillController::class, 'saveBillTemplateSettings'])->name('bill.template.setting');
            Route::get('bill/pdf/{id}', [BillController::class, 'bill'])->name('bill.pdf')->middleware(['XSS', 'revalidate']);

            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('bill/{id}/duplicate', [BillController::class, 'duplicate'])->name('bill.duplicate');
                Route::get('bill/{id}/shipping/print', [BillController::class, 'shippingDisplay'])->name('bill.shipping.print');
                Route::post('bill/product/destroy', [BillController::class, 'productDestroy'])->name('bill.product.destroy');
                Route::post('bill/product', [BillController::class, 'product'])->name('bill.product');
                Route::post('bill/vender', [BillController::class, 'vender'])->name('bill.vender');
                Route::get('bill/{id}/sent', [BillController::class, 'sent'])->name('bill.sent');
                Route::get('bill/{id}/resent', [BillController::class, 'resent'])->name('bill.resent');
                Route::get('bill/{id}/payments', [BillController::class, 'payment'])->name('bill.payments');
                Route::post('bill/{id}/payment', [BillController::class, 'createPayment'])->name('bill.payment');
                Route::post('bill/{id}/payment/{pid}/destroy', [BillController::class, 'paymentDestroy'])->name('bill.payment.destroy');
                Route::get('bill/items', [BillController::class, 'items'])->name('bill.items');
                Route::resource('bill', BillController::class);
            });

            // Revenue and Payment routes
            Route::resource('revenue', RevenueController::class)->middleware(['XSS', 'revalidate']);
            Route::get('payment/pdf/{id}', [PaymentController::class, 'receiptPdf'])->name('payment.pdf')->middleware(['XSS', 'revalidate']);
            Route::resource('payment', PaymentController::class)->middleware(['XSS', 'revalidate']);

            // Transaction routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('report/transaction', [TransactionController::class, 'index'])->name('transaction.index');
            });

            //

            // Report routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('report/income-summary', [ReportController::class, 'incomeSummary'])->name('report.income.summary');
                Route::get('report/expense-summary', [ReportController::class, 'expenseSummary'])->name('report.expense.summary');
                Route::get('report/income-vs-expense-summary', [ReportController::class, 'incomeVsExpenseSummary'])->name('report.income.vs.expense.summary');
                Route::get('report/tax-summary', [ReportController::class, 'taxSummary'])->name('report.tax.summary');
                Route::get('report/invoice-summary', [ReportController::class, 'invoiceSummary'])->name('report.invoice.summary');
                Route::get('report/bill-summary', [ReportController::class, 'billSummary'])->name('report.bill.summary');
                Route::get('report/product-stock-report', [ReportController::class, 'productStock'])->name('report.product.stock.report');
                Route::get('report/invoice-report', [ReportController::class, 'invoiceReport'])->name('report.invoice');
                Route::get('report/account-statement-report', [ReportController::class, 'accountStatement'])->name('report.account.statement');
                Route::get('balance-sheet-report/{view?}/{collapseview?}', [ReportController::class, 'balanceSheet'])->name('report.balance.sheet');
                Route::get('profit-loss-report/{view?}/{collapseView?}', [ReportController::class, 'profitLoss'])->name('report.profit.loss');
                Route::get('ledger-report/{account?}', [ReportController::class, 'ledgerSummary'])->name('report.ledger');
                Route::get('trial-balance-report/{view?}', [ReportController::class, 'trialBalanceSummary'])->name('trial.balance');
                Route::get('reports-monthly-cashflow', [ReportController::class, 'monthlyCashflow'])->name('report.monthly.cashflow');
                Route::get('reports-quarterly-cashflow', [ReportController::class, 'quarterlyCashflow'])->name('report.quarterly.cashflow');
                Route::post('export/trial-balance', [ReportController::class, 'trialBalanceExport'])->name('trial.balance.export');
                Route::post('export/balance-sheet', [ReportController::class, 'balanceSheetExport'])->name('balance.sheet.export');
                Route::post('export/profit-loss', [ReportController::class, 'profitLossExport'])->name('profit.loss.export');
                Route::get('report/sales', [ReportController::class, 'salesReport'])->name('report.sales');
                Route::post('export/sales', [ReportController::class, 'salesReportExport'])->name('sales.export');
                Route::get('report/receivables', [ReportController::class, 'ReceivablesReport'])->name('report.receivables');
                Route::post('export/receivables', [ReportController::class, 'ReceivablesExport'])->name('receivables.export');
                Route::get('report/payables', [ReportController::class, 'PayablesReport'])->name('report.payables');
                Route::get('report/leave', [ReportController::class, 'leave'])->name('report.leave');
                Route::get('employee/{id}/leave/{status}/{type}/{month}/{year}', [ReportController::class, 'employeeLeave'])->name('report.employee.leave');
                Route::get('reports-payroll', [ReportController::class, 'payroll'])->name('report.payroll');
                Route::post('reports-payroll/getdepartment', [ReportController::class, 'getPayrollDepartment'])->name('report.payroll.getdepartment');
                Route::post('reports-payroll/getemployee', [ReportController::class, 'getPayrollEmployee'])->name('report.payroll.getemployee');
                Route::get('reports-monthly-attendance', [ReportController::class, 'monthlyAttendance'])->name('report.monthly.attendance');
                Route::post('reports-monthly-attendance/getdepartment', [ReportController::class, 'getdepartment'])->name('report.attendance.getdepartment');
                Route::post('reports-monthly-attendance/getemployee', [ReportController::class, 'getemployee'])->name('report.attendance.getemployee');
                Route::get('report/attendance/{month}/{branch}/{department}', [ReportController::class, 'exportCsv'])->name('report.attendance');
                Route::get('reports-lead', [ReportController::class, 'leadReport'])->name('report.lead');
                Route::get('reports-deal', [ReportController::class, 'dealReport'])->name('report.deal');
                Route::get('reports-warehouse', [ReportController::class, 'warehouseReport'])->name('report.warehouse');
                Route::get('reports-daily-purchase', [ReportController::class, 'purchaseDailyReport'])->name('report.daily.purchase');
                Route::get('reports-monthly-purchase', [ReportController::class, 'purchaseMonthlyReport'])->name('report.monthly.purchase');
                Route::get('reports-daily-pos', [ReportController::class, 'posDailyReport'])->name('report.daily.pos');
                Route::get('reports-monthly-pos', [ReportController::class, 'posMonthlyReport'])->name('report.monthly.pos');
                Route::get('reports-pos-vs-purchase', [ReportController::class, 'posVsPurchaseReport'])->name('report.pos.vs.purchase');
            });

            // Proposal routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('proposal/{id}/status/change', [ProposalController::class, 'statusChange'])->name('proposal.status.change');
                Route::get('proposal/{id}/convert', [ProposalController::class, 'convert'])->name('proposal.convert');
                Route::get('proposal/{id}/duplicate', [ProposalController::class, 'duplicate'])->name('proposal.duplicate');
                Route::post('proposal/product/destroy', [ProposalController::class, 'productDestroy'])->name('proposal.product.destroy');
                Route::post('proposal/customer', [ProposalController::class, 'customer'])->name('proposal.customer');
                Route::post('proposal/product', [ProposalController::class, 'product'])->name('proposal.product');
                Route::get('proposal/items', [ProposalController::class, 'items'])->name('proposal.items');
                Route::get('proposal/{id}/sent', [ProposalController::class, 'sent'])->name('proposal.sent');
                Route::get('proposal/{id}/resent', [ProposalController::class, 'resent'])->name('proposal.resent');
                Route::resource('proposal', ProposalController::class);
            });

            Route::get('/proposal/preview/{template}/{color}', [ProposalController::class, 'previewProposal'])->name('proposal.preview');
            Route::post('/proposal/template/setting', [ProposalController::class, 'saveProposalTemplateSettings'])->name('proposal.template.setting');

            // Goal, Budget, Asset routes
            Route::resource('goal', GoalController::class)->middleware(['XSS', 'revalidate']);
            Route::resource('budget', BudgetController::class)->middleware(['XSS', 'revalidate']);
            Route::resource('account-assets', AssetController::class)->middleware(['XSS', 'revalidate']);
            Route::resource('custom-field', CustomFieldController::class)->middleware(['XSS', 'revalidate']);

            // Chart of Account routes
            Route::post('chart-of-account/subtype', [ChartOfAccountController::class, 'getSubType'])->name('charofAccount.subType')->middleware(['XSS', 'revalidate']);
            Route::resource('chart-of-account', ChartOfAccountController::class)->middleware(['XSS', 'revalidate']);

            // Journal Entry routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::post('journal-entry/account/destroy', [JournalEntryController::class, 'accountDestroy'])->name('journal.account.destroy');
                Route::delete('journal-entry/journal/destroy/{item_id}', [JournalEntryController::class, 'journalDestroy'])->name('journal.destroy');
                Route::resource('journal-entry', JournalEntryController::class);
            });

            

            // Client routes
            Route::resource('clients', ClientController::class)->middleware(['XSS']);
            Route::any('client-reset-password/{id}', [ClientController::class, 'clientPassword'])->name('clients.reset');
            Route::post('client-reset-password/{id}', [ClientController::class, 'clientPasswordReset'])->name('client.password.update');

            // Deal Module routes
            Route::post('/deals/user', [DealController::class, 'jsonUser'])->name('deal.user.json');
            Route::post('/deals/order', [DealController::class, 'order'])->name('deals.order')->middleware(['XSS']);
            Route::post('/deals/change-pipeline', [DealController::class, 'changePipeline'])->name('deals.change.pipeline')->middleware(['XSS']);
            Route::post('/deals/change-deal-status/{id}', [DealController::class, 'changeStatus'])->name('deals.change.status')->middleware(['XSS']);
            Route::get('/deals/{id}/labels', [DealController::class, 'labels'])->name('deals.labels')->middleware(['XSS']);
            Route::post('/deals/{id}/labels', [DealController::class, 'labelStore'])->name('deals.labels.store')->middleware(['XSS']);
            Route::get('/deals/{id}/users', [DealController::class, 'userEdit'])->name('deals.users.edit')->middleware(['XSS']);
            Route::put('/deals/{id}/users', [DealController::class, 'userUpdate'])->name('deals.users.update')->middleware(['XSS']);
            Route::delete('/deals/{id}/users/{uid}', [DealController::class, 'userDestroy'])->name('deals.users.destroy')->middleware(['XSS']);
            Route::get('/deals/{id}/clients', [DealController::class, 'clientEdit'])->name('deals.clients.edit')->middleware(['XSS']);
            Route::put('/deals/{id}/clients', [DealController::class, 'clientUpdate'])->name('deals.clients.update')->middleware(['XSS']);
            Route::delete('/deals/{id}/clients/{uid}', [DealController::class, 'clientDestroy'])->name('deals.clients.destroy')->middleware(['XSS']);
            Route::get('/deals/{id}/products', [DealController::class, 'productEdit'])->name('deals.products.edit')->middleware(['XSS']);
            Route::put('/deals/{id}/products', [DealController::class, 'productUpdate'])->name('deals.products.update')->middleware(['XSS']);
            Route::delete('/deals/{id}/products/{uid}', [DealController::class, 'productDestroy'])->name('deals.products.destroy')->middleware(['XSS']);
            Route::get('/deals/{id}/sources', [DealController::class, 'sourceEdit'])->name('deals.sources.edit')->middleware(['XSS']);
            Route::put('/deals/{id}/sources', [DealController::class, 'sourceUpdate'])->name('deals.sources.update')->middleware(['XSS']);
            Route::delete('/deals/{id}/sources/{uid}', [DealController::class, 'sourceDestroy'])->name('deals.sources.destroy')->middleware(['XSS']);
            Route::post('/deals/{id}/file', [DealController::class, 'fileUpload'])->name('deals.file.upload')->middleware(['XSS']);
            Route::get('/deals/{id}/file/{fid}', [DealController::class, 'fileDownload'])->name('deals.file.download')->middleware(['XSS']);
            Route::delete('/deals/{id}/file/delete/{fid}', [DealController::class, 'fileDelete'])->name('deals.file.delete')->middleware(['XSS']);
            Route::post('/deals/{id}/note', [DealController::class, 'noteStore'])->name('deals.note.store');
            Route::get('/deals/{id}/task', [DealController::class, 'taskCreate'])->name('deals.tasks.create')->middleware(['XSS']);
            Route::post('/deals/{id}/task', [DealController::class, 'taskStore'])->name('deals.tasks.store')->middleware(['XSS']);
            Route::get('/deals/{id}/task/{tid}/show', [DealController::class, 'taskShow'])->name('deals.tasks.show')->middleware(['XSS']);
            Route::get('/deals/{id}/task/{tid}/edit', [DealController::class, 'taskEdit'])->name('deals.tasks.edit')->middleware(['XSS']);
            Route::put('/deals/{id}/task/{tid}', [DealController::class, 'taskUpdate'])->name('deals.tasks.update')->middleware(['XSS']);
            Route::put('/deals/{id}/task_status/{tid}', [DealController::class, 'taskUpdateStatus'])->name('deals.tasks.update_status')->middleware(['XSS']);
            Route::delete('/deals/{id}/task/{tid}', [DealController::class, 'taskDestroy'])->name('deals.tasks.destroy')->middleware(['XSS']);
            Route::get('/deals/{id}/discussions', [DealController::class, 'discussionCreate'])->name('deals.discussions.create')->middleware(['XSS']);
            Route::post('/deals/{id}/discussions', [DealController::class, 'discussionStore'])->name('deals.discussion.store')->middleware(['XSS']);
            Route::get('/deals/{id}/permission/{cid}', [DealController::class, 'permission'])->name('deals.client.permission')->middleware(['XSS']);
            Route::put('/deals/{id}/permission/{cid}', [DealController::class, 'permissionStore'])->name('deals.client.permissions.store')->middleware(['XSS']);
            Route::get('/deals/list', [DealController::class, 'deal_list'])->name('deals.list')->middleware(['XSS']);
            Route::get('/deals/export', [DealController::class, 'export'])->name('deals.export')->middleware(['XSS']);
            Route::get('import/deals/file', [DealController::class, 'importFile'])->name('deals.import');
            Route::post('deals/import', [DealController::class, 'fileImport'])->name('deals.file.import');
            Route::get('import/deals/modal', [DealController::class, 'fileImportModal'])->name('deals.import.modal');
            Route::post('import/deals', [DealController::class, 'dealImportdata'])->name('deals.import.data');
            Route::get('/deals/{id}/call', [DealController::class, 'callCreate'])->name('deals.calls.create')->middleware(['XSS']);
            Route::post('/deals/{id}/call', [DealController::class, 'callStore'])->name('deals.calls.store');
            Route::get('/deals/{id}/call/{cid}/edit', [DealController::class, 'callEdit'])->name('deals.calls.edit');
            Route::put('/deals/{id}/call/{cid}', [DealController::class, 'callUpdate'])->name('deals.calls.update');
            Route::delete('/deals/{id}/call/{cid}', [DealController::class, 'callDestroy'])->name('deals.calls.destroy')->middleware(['XSS']);
            Route::get('/deals/{id}/email', [DealController::class, 'emailCreate'])->name('deals.emails.create')->middleware(['XSS']);
            Route::post('/deals/{id}/email', [DealController::class, 'emailStore'])->name('deals.emails.store')->middleware(['XSS']);
            Route::resource('deals', DealController::class)->middleware(['XSS']);

            // Search
            Route::get('/search', [UserController::class, 'search'])->name('search.json');

            // Stage and Pipeline routes
            Route::post('/stages/order', [StageController::class, 'order'])->name('stages.order');
            Route::post('/stages/json', [StageController::class, 'json'])->name('stages.json');
            Route::resource('stages', StageController::class);
            Route::resource('pipelines', PipelineController::class);
            Route::resource('labels', LabelController::class);
            Route::resource('sources', SourceController::class);
            Route::resource('custom_fields', CustomFieldController::class);

            // Leads Module
            Route::post('/lead_stages/order', [LeadStageController::class, 'order'])->name('lead_stages.order');
            Route::resource('lead_stages', LeadStageController::class);
            Route::post('/leads/json', [LeadController::class, 'json'])->name('leads.json');
            Route::post('/leads/order', [LeadController::class, 'order'])->name('leads.order')->middleware(['XSS']);
            Route::get('/leads/list', [LeadController::class, 'lead_list'])->name('leads.list')->middleware(['XSS']);
            Route::post('/leads/{id}/file', [LeadController::class, 'fileUpload'])->name('leads.file.upload')->middleware(['XSS']);
            Route::get('/leads/{id}/file/{fid}', [LeadController::class, 'fileDownload'])->name('leads.file.download')->middleware(['XSS']);
            Route::delete('/leads/{id}/file/delete/{fid}', [LeadController::class, 'fileDelete'])->name('leads.file.delete')->middleware(['XSS']);
            Route::post('/leads/{id}/note', [LeadController::class, 'noteStore'])->name('leads.note.store');
            Route::get('/leads/{id}/labels', [LeadController::class, 'labels'])->name('leads.labels')->middleware(['XSS']);
            Route::post('/leads/{id}/labels', [LeadController::class, 'labelStore'])->name('leads.labels.store')->middleware(['XSS']);
            Route::get('/leads/{id}/users', [LeadController::class, 'userEdit'])->name('leads.users.edit')->middleware(['XSS']);
            Route::put('/leads/{id}/users', [LeadController::class, 'userUpdate'])->name('leads.users.update')->middleware(['XSS']);
            Route::delete('/leads/{id}/users/{uid}', [LeadController::class, 'userDestroy'])->name('leads.users.destroy')->middleware(['XSS']);
            Route::get('/leads/{id}/products', [LeadController::class, 'productEdit'])->name('leads.products.edit')->middleware(['XSS']);
            Route::put('/leads/{id}/products', [LeadController::class, 'productUpdate'])->name('leads.products.update')->middleware(['XSS']);
            Route::delete('/leads/{id}/products/{uid}', [LeadController::class, 'productDestroy'])->name('leads.products.destroy')->middleware(['XSS']);
            Route::get('/leads/{id}/sources', [LeadController::class, 'sourceEdit'])->name('leads.sources.edit')->middleware(['XSS']);
            Route::put('/leads/{id}/sources', [LeadController::class, 'sourceUpdate'])->name('leads.sources.update')->middleware(['XSS']);
            Route::delete('/leads/{id}/sources/{uid}', [LeadController::class, 'sourceDestroy'])->name('leads.sources.destroy')->middleware(['XSS']);
            Route::get('/leads/{id}/discussions', [LeadController::class, 'discussionCreate'])->name('leads.discussions.create')->middleware(['XSS']);
            Route::post('/leads/{id}/discussions', [LeadController::class, 'discussionStore'])->name('leads.discussion.store')->middleware(['XSS']);
            Route::get('/leads/{id}/show_convert', [LeadController::class, 'showConvertToDeal'])->name('leads.convert.deal')->middleware(['XSS']);
            Route::post('/leads/{id}/convert', [LeadController::class, 'convertToDeal'])->name('leads.convert.to.deal')->middleware(['XSS']);
            Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export')->middleware(['XSS']);
            Route::get('import/leads/file', [LeadController::class, 'importFile'])->name('leads.import');
            Route::post('leads/import', [LeadController::class, 'fileImport'])->name('leads.file.import');
            Route::get('import/leads/modal', [LeadController::class, 'fileImportModal'])->name('leads.import.modal');
            Route::post('import/leads', [LeadController::class, 'leadImportdata'])->name('leads.import.data');
            Route::get('/leads/{id}/call', [LeadController::class, 'callCreate'])->name('leads.calls.create')->middleware(['XSS']);
            Route::post('/leads/{id}/call', [LeadController::class, 'callStore'])->name('leads.calls.store');
            Route::get('/leads/{id}/call/{cid}/edit', [LeadController::class, 'callEdit'])->name('leads.calls.edit')->middleware(['XSS']);
            Route::put('/leads/{id}/call/{cid}', [LeadController::class, 'callUpdate'])->name('leads.calls.update');
            Route::delete('/leads/{id}/call/{cid}', [LeadController::class, 'callDestroy'])->name('leads.calls.destroy')->middleware(['XSS']);
            Route::get('/leads/{id}/email', [LeadController::class, 'emailCreate'])->name('leads.emails.create')->middleware(['XSS']);
            Route::post('/leads/{id}/email', [LeadController::class, 'emailStore'])->name('leads.emails.store');
            Route::resource('leads', LeadController::class)->middleware(['XSS']);

            // Plan and User routes
            Route::get('user/{id}/plan', [UserController::class, 'upgradePlan'])->name('plan.upgrade')->middleware(['XSS']);
            Route::get('user/{id}/plan/{pid}', [UserController::class, 'activePlan'])->name('plan.active')->middleware(['XSS']);
            Route::get('/{uid}/notification/seen', [UserController::class, 'notificationSeen'])->name('notification.seen');

            // Email Templates
            Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, 'manageEmailLang'])->name('manage.email.language')->middleware(['XSS']);
            Route::any('email_template_store', [EmailTemplateController::class, 'updateStatus'])->name('status.email.language');
            Route::any('email_template_store/{pid}', [EmailTemplateController::class, 'storeEmailLang'])->name('store.email.language');
            Route::resource('email_template', EmailTemplateController::class)->middleware(['XSS']);

            // HRM Routes
            Route::resource('user', UserController::class)->middleware(['XSS']);
            Route::post('employee/json', [EmployeeController::class, 'json'])->name('employee.json')->middleware(['XSS']);
            Route::post('branch/employee/json', [EmployeeController::class, 'employeeJson'])->name('branch.employee.json')->middleware(['XSS']);
            Route::get('employee-profile', [EmployeeController::class, 'profile'])->name('employee.profile')->middleware(['XSS']);
            Route::get('show-employee-profile/{id}', [EmployeeController::class, 'profileShow'])->name('show.employee.profile')->middleware(['XSS']);
            Route::get('lastlogin', [EmployeeController::class, 'lastLogin'])->name('lastlogin')->middleware(['XSS']);
            Route::resource('employee', EmployeeController::class)->middleware(['XSS']);
            Route::post('employee/getdepartment', [EmployeeController::class, 'getDepartment'])->name('employee.getdepartment')->middleware(['XSS']);
            Route::resource('department', DepartmentController::class)->middleware(['XSS']);
            Route::resource('designation', DesignationController::class)->middleware(['XSS']);
            Route::resource('document', DocumentController::class)->middleware(['XSS']);
            Route::resource('branch', BranchController::class)->middleware(['XSS']);
            Route::get('employee/salary/{eid}', [SetSalaryController::class, 'employeeBasicSalary'])->name('employee.basic.salary')->middleware(['XSS']);

            // Payslip routes
            Route::resource('paysliptype', PayslipTypeController::class)->middleware(['XSS']);
            Route::resource('allowance', AllowanceController::class)->middleware(['XSS']);
            Route::resource('commission', CommissionController::class)->middleware(['XSS']);
            Route::resource('allowanceoption', AllowanceOptionController::class)->middleware(['XSS']);
            Route::resource('loanoption', LoanOptionController::class)->middleware(['XSS']);
            Route::resource('deductionoption', DeductionOptionController::class)->middleware(['XSS']);
            Route::resource('loan', LoanController::class)->middleware(['XSS']);
            Route::resource('saturationdeduction', SaturationDeductionController::class)->middleware(['XSS']);
            Route::resource('otherpayment', OtherPaymentController::class)->middleware(['XSS']);
            Route::resource('overtime', OvertimeController::class)->middleware(['XSS']);
            Route::post('employee/update/sallary/{id}', [SetSalaryController::class, 'employeeUpdateSalary'])->name('employee.salary.update')->middleware(['XSS']);
            Route::get('salary/employeeSalary', [SetSalaryController::class, 'employeeSalary'])->name('employeesalary')->middleware(['XSS']);
            Route::resource('setsalary', SetSalaryController::class)->middleware(['XSS']);
            Route::get('allowances/create/{eid}', [AllowanceController::class, 'allowanceCreate'])->name('allowances.create')->middleware(['XSS']);
            Route::get('commissions/create/{eid}', [CommissionController::class, 'commissionCreate'])->name('commissions.create')->middleware(['XSS']);
            Route::get('loans/create/{eid}', [LoanController::class, 'loanCreate'])->name('loans.create')->middleware(['XSS']);
            Route::get('saturationdeductions/create/{eid}', [SaturationDeductionController::class, 'saturationdeductionCreate'])->name('saturationdeductions.create')->middleware(['XSS']);
            Route::get('otherpayments/create/{eid}', [OtherPaymentController::class, 'otherpaymentCreate'])->name('otherpayments.create')->middleware(['XSS']);
            Route::get('overtimes/create/{eid}', [OvertimeController::class, 'overtimeCreate'])->name('overtimes.create')->middleware(['XSS']);
            Route::get('payslip/paysalary/{id}/{date}', [PaySlipController::class, 'paysalary'])->name('payslip.paysalary')->middleware(['XSS']);
            Route::get('payslip/bulk_pay_create/{date}', [PaySlipController::class, 'bulk_pay_create'])->name('payslip.bulk_pay_create')->middleware(['XSS']);
            Route::post('payslip/bulkpayment/{date}', [PaySlipController::class, 'bulkpayment'])->name('payslip.bulkpayment')->middleware(['XSS']);
            Route::post('payslip/search_json', [PaySlipController::class, 'search_json'])->name('payslip.search_json')->middleware(['XSS']);
            Route::get('payslip/employeepayslip', [PaySlipController::class, 'employeepayslip'])->name('payslip.employeepayslip')->middleware(['XSS']);
            Route::get('payslip/showemployee/{id}', [PaySlipController::class, 'showemployee'])->name('payslip.showemployee')->middleware(['XSS']);
            Route::get('payslip/editemployee/{id}', [PaySlipController::class, 'editemployee'])->name('payslip.editemployee')->middleware(['XSS']);
            Route::post('payslip/editemployee/{id}', [PaySlipController::class, 'updateEmployee'])->name('payslip.updateemployee')->middleware(['XSS']);
            Route::get('payslip/pdf/{id}/{m}', [PaySlipController::class, 'pdf'])->name('payslip.pdf')->middleware(['XSS']);
            Route::get('payslip/payslipPdf/{id}', [PaySlipController::class, 'payslipPdf'])->name('payslip.payslipPdf')->middleware(['XSS']);
            Route::get('payslip/send/{id}/{m}', [PaySlipController::class, 'send'])->name('payslip.send')->middleware(['XSS']);
            Route::get('payslip/delete/{id}', [PaySlipController::class, 'destroy'])->name('payslip.delete')->middleware(['XSS']);
            Route::resource('payslip', PaySlipController::class)->middleware(['XSS']);
            Route::post('export/payslip', [PaySlipController::class, 'export'])->name('payslip.export');

            // Company Policy and Appraisal routes
            Route::resource('company-policy', CompanyPolicyController::class)->middleware(['XSS']);
            Route::resource('indicator', IndicatorController::class)->middleware(['XSS']);
            Route::resource('appraisal', AppraisalController::class)->middleware(['XSS']);
            Route::resource('goaltype', GoalTypeController::class)->middleware(['XSS']);
            Route::resource('goaltracking', GoalTrackingController::class)->middleware(['XSS']);

            // Event routes
            Route::post('event/getdepartment', [EventController::class, 'getdepartment'])->name('event.getdepartment')->middleware(['XSS']);
            Route::post('event/getemployee', [EventController::class, 'getemployee'])->name('event.getemployee')->middleware(['XSS']);
            Route::resource('event', EventController::class)->middleware(['XSS']);
            Route::any('event/get_event_data', [EventController::class, 'get_event_data'])->name('event.get_event_data')->middleware(['XSS']);
            Route::any('event/get_dashboard_event_data', [EventController::class, 'get_dashboard_event_data'])->name('event.get_dashboard_event_data')->middleware(['XSS']);

            // Meeting routes
            Route::post('meeting/getdepartment', [MeetingController::class, 'getdepartment'])->name('meeting.getdepartment')->middleware(['XSS']);
            Route::post('meeting/getemployee', [MeetingController::class, 'getemployee'])->name('meeting.getemployee')->middleware(['XSS']);
            Route::resource('meeting', MeetingController::class)->middleware(['XSS']);
            Route::any('meeting/get_meeting_data', [MeetingController::class, 'get_meeting_data'])->name('meeting.get_meeting_data')->middleware(['XSS']);
            Route::get('meeting-calender', [MeetingController::class, 'calender'])->name('meeting.calender')->middleware(['XSS']);

            // Training routes
            Route::resource('trainingtype', TrainingTypeController::class)->middleware(['XSS']);
            Route::resource('trainer', TrainerController::class)->middleware(['XSS']);
            Route::post('training/status', [TrainingController::class, 'updateStatus'])->name('training.status')->middleware(['XSS']);
            Route::resource('training', TrainingController::class)->middleware(['XSS']);

            // HRM - HR Module routes
            Route::resource('awardtype', AwardTypeController::class)->middleware(['XSS']);
            Route::resource('award', AwardController::class)->middleware(['XSS']);
            Route::resource('resignation', ResignationController::class)->middleware(['XSS']);
            Route::resource('travel', TravelController::class)->middleware(['XSS']);
            Route::resource('promotion', PromotionController::class)->middleware(['XSS']);
            Route::resource('complaint', ComplaintController::class)->middleware(['XSS']);
            Route::resource('warning', WarningController::class)->middleware(['XSS']);
            Route::resource('termination', TerminationController::class)->middleware(['XSS']);
            Route::get('termination/{id}/description', [TerminationController::class, 'description'])->name('termination.description');
            Route::resource('terminationtype', TerminationTypeController::class)->middleware(['XSS']);

            // Announcement routes
            Route::post('announcement/getdepartment', [AnnouncementController::class, 'getdepartment'])->name('announcement.getdepartment');
            Route::post('announcement/getemployee', [AnnouncementController::class, 'getemployee'])->name('announcement.getemployee');
            Route::resource('announcement', AnnouncementController::class)->middleware(['XSS']);

            // Holiday routes
            Route::resource('holiday', HolidayController::class)->middleware(['XSS']);
            Route::get('holiday-calender', [HolidayController::class, 'calender'])->name('holiday.calender');
            Route::any('holiday/get_holiday_data', [HolidayController::class, 'get_holiday_data'])->name('holiday.get_holiday_data')->middleware(['XSS']);

            // Recruitment routes
            Route::resource('job-category', JobCategoryController::class)->middleware(['XSS']);
            Route::resource('job-stage', JobStageController::class)->middleware(['XSS']);
            Route::post('job-stage/order', [JobStageController::class, 'order'])->name('job.stage.order');
            Route::resource('job', JobController::class)->middleware(['XSS']);
            Route::get('candidates-job-applications', [JobApplicationController::class, 'candidate'])->name('job.application.candidate')->middleware(['XSS']);
            Route::resource('job-application', JobApplicationController::class)->middleware(['XSS']);
            Route::post('job-application/order', [JobApplicationController::class, 'order'])->name('job.application.order')->middleware(['XSS']);
            Route::post('job-application/{id}/rating', [JobApplicationController::class, 'rating'])->name('job.application.rating')->middleware(['XSS']);
            Route::delete('job-application/{id}/archive', [JobApplicationController::class, 'archive'])->name('job.application.archive')->middleware(['XSS']);
            Route::post('job-application/{id}/skill/store', [JobApplicationController::class, 'addSkill'])->name('job.application.skill.store')->middleware(['XSS']);
            Route::post('job-application/{id}/note/store', [JobApplicationController::class, 'addNote'])->name('job.application.note.store')->middleware(['XSS']);
            Route::delete('job-application/{id}/note/destroy', [JobApplicationController::class, 'destroyNote'])->name('job.application.note.destroy')->middleware(['XSS']);
            Route::post('job-application/getByJob', [JobApplicationController::class, 'getByJob'])->name('get.job.application')->middleware(['XSS']);
            Route::get('job-onboard', [JobApplicationController::class, 'jobOnBoard'])->name('job.on.board')->middleware(['XSS']);
            Route::get('job-onboard/create/{id}', [JobApplicationController::class, 'jobBoardCreate'])->name('job.on.board.create')->middleware(['XSS']);
            Route::post('job-onboard/store/{id}', [JobApplicationController::class, 'jobBoardStore'])->name('job.on.board.store')->middleware(['XSS']);
            Route::get('job-onboard/edit/{id}', [JobApplicationController::class, 'jobBoardEdit'])->name('job.on.board.edit')->middleware(['XSS']);
            Route::post('job-onboard/update/{id}', [JobApplicationController::class, 'jobBoardUpdate'])->name('job.on.board.update')->middleware(['XSS']);
            Route::delete('job-onboard/delete/{id}', [JobApplicationController::class, 'jobBoardDelete'])->name('job.on.board.delete')->middleware(['XSS']);
            Route::get('job-onboard/convert/{id}', [JobApplicationController::class, 'jobBoardConvert'])->name('job.on.board.converts')->middleware(['XSS']);
            Route::post('job-onboard/convert/{id}', [JobApplicationController::class, 'jobBoardConvertData'])->name('job.on.board.convert')->middleware(['XSS']);
            Route::post('job-application/stage/change', [JobApplicationController::class, 'stageChange'])->name('job.application.stage.change')->middleware(['XSS']);
            Route::resource('custom-question', CustomQuestionController::class)->middleware(['XSS']);
            Route::resource('interview-schedule', InterviewScheduleController::class)->middleware(['XSS']);
            Route::any('interview-schedule/get_interview_data', [InterviewScheduleController::class, 'get_interview_data'])->name('holiday.get_interview_data')->middleware(['XSS']);

            // Task and Document routes
            Route::get('taskboard/{view?}', [ProjectTaskController::class, 'taskBoard'])->name('taskBoard.view')->middleware(['XSS']);
            Route::get('taskboard-view', [ProjectTaskController::class, 'taskboardView'])->name('project.taskboard.view')->middleware(['XSS']);
            Route::resource('document-upload', DucumentUploadController::class)->middleware(['XSS']);
            Route::resource('transfer', TransferController::class)->middleware(['XSS']);

            // Employee Task Tracker Routes
            Route::group(['middleware' => ['auth', 'verified']], function () {
                Route::get('employee-task-tracker', [StaffWorkTaskController::class, 'index'])->name('employee.task.tracker');
                Route::get('employee-task-tracker/create', [StaffWorkTaskController::class, 'create'])->name('employee.task.tracker.create');
                Route::post('employee-task-tracker', [StaffWorkTaskController::class, 'store'])->name('employee.task.tracker.store');
                Route::get('employee-task-tracker/{id}', [StaffWorkTaskController::class, 'show'])->name('employee.task.tracker.show');
                Route::get('employee-task-tracker/{id}/edit', [StaffWorkTaskController::class, 'edit'])->name('employee.task.tracker.edit');
                Route::put('employee-task-tracker/{id}', [StaffWorkTaskController::class, 'update'])->name('employee.task.tracker.update');
                Route::delete('employee-task-tracker/{id}', [StaffWorkTaskController::class, 'destroy'])->name('employee.task.tracker.destroy');
                Route::get('employee-task-tracker/tasks/{employeeId}', [StaffWorkTaskController::class, 'getEmployeeTasks'])->name('employee.task.tracker.tasks');
                Route::get('employee-task-tracker/stats', [StaffWorkTaskController::class, 'getTaskStats'])->name('employee.task.tracker.stats');
                Route::post('employee-task-tracker/update-status/{id}', [StaffWorkTaskController::class, 'updateStatus'])->name('employee.task.tracker.update-status');
                Route::post('employee-task-tracker/bulk-assign', [StaffWorkTaskController::class, 'bulkAssign'])->name('employee.task.tracker.bulk-assign');
                Route::get('employee-task-tracker/calendar/tasks', [StaffWorkTaskController::class, 'calendarTasks'])->name('employee.task.tracker.calendar-tasks');
            });

            // Attendance routes
            Route::get('attendanceemployee/bulkattendance', [AttendanceEmployeeController::class, 'bulkAttendance'])->name('attendanceemployee.bulkattendance')->middleware(['XSS']);
            Route::post('attendanceemployee/bulkattendances', [AttendanceEmployeeController::class, 'bulkAttendanceData'])->name('attendanceemployee.bulkattendances')->middleware(['XSS']);
            Route::post('attendanceemployee/attendance', [AttendanceEmployeeController::class, 'attendance'])->name('attendanceemployee.attendance')->middleware(['XSS']);
            Route::resource('attendanceemployee', AttendanceEmployeeController::class)->middleware(['XSS']);

            // ==============================================
        // SalaryBox‑Style Attendance Routes
        // ==============================================
        Route::prefix('attendance')->name('attendance.')->middleware(['XSS', 'revalidate'])->group(function () {
            Route::get('/dashboard', [AttendanceEmployeeController::class, 'dashboard'])->name('dashboard');
            Route::get('/live', [AttendanceEmployeeController::class, 'live'])->name('live');
            Route::post('/live/refresh', [AttendanceEmployeeController::class, 'refreshLive'])->name('live.refresh');
            Route::get('/daily', [AttendanceEmployeeController::class, 'daily'])->name('daily');
            Route::post('/daily/filter', [AttendanceEmployeeController::class, 'daily'])->name('daily.filter'); // optional
            Route::get('/roster', [AttendanceEmployeeController::class, 'roster'])->name('roster');
            Route::post('/clock-in', [AttendanceEmployeeController::class, 'quickClockIn'])->name('clock.in');
            Route::post('/clock-out', [AttendanceEmployeeController::class, 'quickClockOut'])->name('clock.out');
        });
        Route::get('/attendance-daily-direct', [App\Http\Controllers\AttendanceEmployeeController::class, 'daily'])->name('attendance.daily.direct')->middleware(['auth']);

            // Leave routes
            Route::resource('leavetype', LeaveTypeController::class)->middleware(['XSS']);
            Route::get('leave/{id}/action', [LeaveController::class, 'action'])->name('leave.action')->middleware(['XSS']);
            Route::post('leave/changeaction', [LeaveController::class, 'changeaction'])->name('leave.changeaction')->middleware(['XSS']);
            Route::post('leave/jsoncount', [LeaveController::class, 'jsoncount'])->name('leave.jsoncount')->middleware(['XSS']);
            Route::resource('leave', LeaveController::class)->middleware(['XSS']);

            // User Management
            Route::get('users/{view?}', [UserController::class, 'index'])->name('users')->middleware(['XSS']);
            Route::get('users-view', [UserController::class, 'filterUserView'])->name('filter.user.view')->middleware(['XSS']);
            Route::get('checkuserexists', [UserController::class, 'checkUserExists'])->name('user.exists')->middleware(['XSS']);
            Route::post('/profile', [UserController::class, 'updateProfile'])->name('update.profile')->middleware(['XSS']);
            Route::get('user/info/{id}', [UserController::class, 'userInfo'])->name('users.info')->middleware(['XSS']);
            Route::get('user/{id}/info/{type}', [UserController::class, 'getProjectTask'])->name('user.info.popup')->middleware(['XSS']);
            Route::post('/todo/create', [UserController::class, 'todo_store'])->name('todo.store')->middleware(['XSS']);
            Route::post('/todo/{id}/update', [UserController::class, 'todo_update'])->name('todo.update')->middleware(['XSS']);
            Route::delete('/todo/{id}', [UserController::class, 'todo_destroy'])->name('todo.destroy')->middleware(['XSS']);
            Route::get('dashboard-view', [DashboardController::class, 'filterView'])->name('dashboard.view')->middleware(['XSS']);
            Route::get('dashboard', [DashboardController::class, 'clientView'])->name('client.dashboard.view')->middleware(['XSS']);


            // Milestone routes
            Route::get('projects/{id}/milestone', [ProjectController::class, 'milestone'])->name('project.milestone')->middleware(['XSS']);
            Route::post('projects/{id}/milestone', [ProjectController::class, 'milestoneStore'])->name('project.milestone.store')->middleware(['XSS']);
            Route::get('projects/milestone/{id}/edit', [ProjectController::class, 'milestoneEdit'])->name('project.milestone.edit')->middleware(['XSS']);
            Route::post('projects/milestone/{id}', [ProjectController::class, 'milestoneUpdate'])->name('project.milestone.update')->middleware(['XSS']);
            Route::delete('projects/milestone/{id}', [ProjectController::class, 'milestoneDestroy'])->name('project.milestone.destroy')->middleware(['XSS']);
            Route::get('projects/milestone/{id}/show', [ProjectController::class, 'milestoneShow'])->name('project.milestone.show')->middleware(['XSS']);

            // Project Module routes
            Route::get('invite-project-member/{id}', [ProjectController::class, 'inviteMemberView'])->name('invite.project.member.view')->middleware(['XSS']);
            Route::post('invite-project-user-member', [ProjectController::class, 'inviteProjectUserMember'])->name('invite.project.user.member')->middleware(['XSS']);
            Route::delete('projects/{id}/users/{uid}', [ProjectController::class, 'destroyProjectUser'])->name('projects.user.destroy')->middleware(['XSS']);
            Route::get('project/{view?}', [ProjectController::class, 'index'])->name('projects.list')->middleware(['XSS']);
            Route::get('projects-view', [ProjectController::class, 'filterProjectView'])->name('filter.project.view')->middleware(['XSS']);
            Route::post('projects/{id}/store-stages/{slug}', [ProjectController::class, 'storeProjectTaskStages'])->name('project.stages.store')->middleware(['XSS']);
            Route::patch('remove-user-from-project/{project_id}/{user_id}', [ProjectController::class, 'removeUserFromProject'])->name('remove.user.from.project')->middleware(['XSS']);
            Route::get('projects-users', [ProjectController::class, 'loadUser'])->name('project.user')->middleware(['XSS']);
            Route::get('projects/{id}/gantt/{duration?}', [ProjectController::class, 'gantt'])->name('projects.gantt')->middleware(['XSS']);
            Route::post('projects/{id}/gantt', [ProjectController::class, 'ganttPost'])->name('projects.gantt.post')->middleware(['XSS']);
            Route::resource('projects', ProjectController::class)->middleware(['XSS']);
            Route::get('/project/copy/{id}', [ProjectController::class, 'copyproject'])->name('project.copy')->middleware(['XSS']);
            Route::post('/project/copy/store/{id}', [ProjectController::class, 'copyprojectstore'])->name('project.copy.store')->middleware(['XSS']);
            Route::any('/projects/copy/link/{id}', [ProjectController::class, 'copylinksetting'])->name('projects.copy.link');
            Route::any('/projects{id}/settingcreate', [ProjectController::class, 'copylink_setting_create'])->name('projects.copylink.setting.create');
            Route::get('/shareproject/{lang?}', [ProjectController::class, 'shareproject'])->name('shareproject');
            Route::get('projects/{id}/user/{uid}/permission', [ProjectController::class, 'userPermission'])->name('projects.user.permission')->middleware(['XSS']);
            Route::post('projects/{id}/user/{uid}/permission', [ProjectController::class, 'userPermissionStore'])->name('projects.user.permission.store')->middleware(['XSS']);
            Route::get('stage/{id}/tasks', [ProjectTaskController::class, 'getStageTasks'])->name('stage.tasks')->middleware(['XSS']);
            Route::get('/projects/{id}/task', [ProjectTaskController::class, 'index'])->name('projects.tasks.index')->middleware(['XSS']);
            Route::get('/projects/{pid}/task/{sid}', [ProjectTaskController::class, 'create'])->name('projects.tasks.create')->middleware(['XSS']);
            Route::post('/projects/{pid}/task/{sid}', [ProjectTaskController::class, 'store'])->name('projects.tasks.store')->middleware(['XSS']);
            Route::get('/projects/{id}/task/{tid}/show', [ProjectTaskController::class, 'show'])->name('projects.tasks.show')->middleware(['XSS']);
            Route::get('/projects/{id}/task/{tid}/edit', [ProjectTaskController::class, 'edit'])->name('projects.tasks.edit')->middleware(['XSS']);
            Route::post('/projects/{id}/task/update/{tid}', [ProjectTaskController::class, 'update'])->name('projects.tasks.update')->middleware(['XSS']);
            Route::delete('/projects/{id}/task/{tid}', [ProjectTaskController::class, 'destroy'])->name('projects.tasks.destroy')->middleware(['XSS']);
            Route::patch('/projects/{id}/task/order', [ProjectTaskController::class, 'taskOrderUpdate'])->name('tasks.update.order')->middleware(['XSS']);
            Route::patch('update-task-priority-color', [ProjectTaskController::class, 'updateTaskPriorityColor'])->name('update.task.priority.color')->middleware(['XSS']);
            Route::post('/projects/{id}/comment/{tid}/file', [ProjectTaskController::class, 'commentStoreFile'])->name('comment.store.file')->middleware(['XSS']);
            Route::delete('/projects/{id}/comment/{tid}/file/{fid}', [ProjectTaskController::class, 'commentDestroyFile'])->name('comment.destroy.file');
            Route::post('/projects/{id}/comment/{tid}', [ProjectTaskController::class, 'commentStore'])->name('task.comment.store');
            Route::delete('/projects/{id}/comment/{tid}/{cid}', [ProjectTaskController::class, 'commentDestroy'])->name('comment.destroy');
            Route::post('/projects/{id}/checklist/{tid}', [ProjectTaskController::class, 'checklistStore'])->name('checklist.store');
            Route::post('/projects/{id}/checklist/update/{cid}', [ProjectTaskController::class, 'checklistUpdate'])->name('checklist.update');
            Route::delete('/projects/{id}/checklist/{cid}', [ProjectTaskController::class, 'checklistDestroy'])->name('checklist.destroy');
            Route::post('/projects/{id}/change/{tid}/fav', [ProjectTaskController::class, 'changeFav'])->name('change.fav');
            Route::post('/projects/{id}/change/{tid}/complete', [ProjectTaskController::class, 'changeCom'])->name('change.complete');
            Route::post('/projects/{id}/change/{tid}/progress', [ProjectTaskController::class, 'changeProg'])->name('change.progress');
            Route::get('/projects/task/{id}/get', [ProjectTaskController::class, 'taskGet'])->name('projects.tasks.get')->middleware(['XSS']);
            Route::get('/calendar/{id}/show', [ProjectTaskController::class, 'calendarShow'])->name('task.calendar.show')->middleware(['XSS']);
            Route::post('/calendar/{id}/drag', [ProjectTaskController::class, 'calendarDrag'])->name('task.calendar.drag');
            Route::get('calendar/{task}/{pid?}', [ProjectTaskController::class, 'calendarView'])->name('task.calendar')->middleware(['XSS']);
            Route::post('calendar/get_task_data', [ProjectTaskController::class, 'get_task_data'])->name('task.calendar.get_task_data')->middleware(['XSS']);
            Route::resource('project-task-stages', TaskStageController::class)->middleware(['XSS']);
            Route::post('/project-task-stages/order', [TaskStageController::class, 'order'])->name('project-task-stages.order');
            Route::post('project-task-new-stage', [TaskStageController::class, 'storingValue'])->name('new-task-stage')->middleware(['XSS']);

            // Project Expense Module
            Route::get('/projects/{id}/expense', [ProjectExpenseController::class, 'index'])->name('projects.expenses.index')->middleware(['XSS']);
            Route::get('/projects/{pid}/expense/create', [ProjectExpenseController::class, 'create'])->name('projects.expenses.create')->middleware(['XSS']);
            Route::post('/projects/{pid}/expense/store', [ProjectExpenseController::class, 'store'])->name('projects.expenses.store')->middleware(['XSS']);
            Route::get('/projects/{id}/expense/{eid}/edit', [ProjectExpenseController::class, 'edit'])->name('projects.expenses.edit')->middleware(['XSS']);
            Route::post('/projects/{id}/expense/{eid}', [ProjectExpenseController::class, 'update'])->name('projects.expenses.update')->middleware(['XSS']);
            Route::delete('/projects/{eid}/expense/', [ProjectExpenseController::class, 'destroy'])->name('projects.expenses.destroy')->middleware(['XSS']);
            Route::get('/expense-list', [ExpenseController::class, 'expenseList'])->name('expense.list')->middleware(['XSS']);

            // Contract type routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::resource('contractType', ContractTypeController::class);
            });

            // Timesheet routes
            Route::get('append-timesheet-task-html', [TimesheetController::class, 'appendTimesheetTaskHTML'])->name('append.timesheet.task.html')->middleware(['XSS']);
            Route::get('timesheet-view', [TimesheetController::class, 'filterTimesheetView'])->name('filter.timesheet.view')->middleware(['XSS']);
            Route::get('timesheet-list', [TimesheetController::class, 'timesheetList'])->name('timesheet.list')->middleware(['XSS']);
            Route::get('timesheet-list-get', [TimesheetController::class, 'timesheetListGet'])->name('timesheet.list.get')->middleware(['XSS']);
            Route::get('/project/{id}/timesheet', [TimesheetController::class, 'timesheetView'])->name('timesheet.index')->middleware(['XSS']);
            Route::get('/project/{id}/timesheet/create', [TimesheetController::class, 'timesheetCreate'])->name('timesheet.create')->middleware(['XSS']);
            Route::post('/project/timesheet', [TimesheetController::class, 'timesheetStore'])->name('timesheet.store')->middleware(['XSS']);
            Route::get('/project/timesheet/{project_id}/edit/{timesheet_id}', [TimesheetController::class, 'timesheetEdit'])->name('timesheet.edit')->middleware(['XSS']);
            Route::any('/project/timesheet/update/{timesheet_id}', [TimesheetController::class, 'timesheetUpdate'])->name('timesheet.update')->middleware(['XSS']);
            Route::delete('/project/timesheet/{timesheet_id}', [TimesheetController::class, 'timesheetDestroy'])->name('timesheet.destroy')->middleware(['XSS']);

            // Project stages and bug routes
            Route::group(['middleware' => ['XSS']], function () {
                Route::resource('projectstages', ProjectstagesController::class);
                Route::post('/projectstages/order', [ProjectstagesController::class, 'order'])->name('projectstages.order');
                Route::post('projects/bug/kanban/order', [ProjectController::class, 'bugKanbanOrder'])->name('bug.kanban.order');
                Route::get('projects/{id}/bug/kanban', [ProjectController::class, 'bugKanban'])->name('task.bug.kanban');
                Route::get('projects/{id}/bug', [ProjectController::class, 'bug'])->name('task.bug');
                Route::get('projects/{id}/bug/create', [ProjectController::class, 'bugCreate'])->name('task.bug.create');
                Route::post('projects/{id}/bug/store', [ProjectController::class, 'bugStore'])->name('task.bug.store');
                Route::get('projects/{id}/bug/{bid}/edit', [ProjectController::class, 'bugEdit'])->name('task.bug.edit');
                Route::post('projects/{id}/bug/{bid}/update', [ProjectController::class, 'bugUpdate'])->name('task.bug.update');
                Route::delete('projects/{id}/bug/{bid}/destroy', [ProjectController::class, 'bugDestroy'])->name('task.bug.destroy');
                Route::get('projects/{id}/bug/{bid}/show', [ProjectController::class, 'bugShow'])->name('task.bug.show');
                Route::post('projects/{id}/bug/{bid}/comment', [ProjectController::class, 'bugCommentStore'])->name('bug.comment.store');
                Route::post('projects/bug/{bid}/file', [ProjectController::class, 'bugCommentStoreFile'])->name('bug.comment.file.store');
                Route::delete('projects/bug/comment/{id}', [ProjectController::class, 'bugCommentDestroy'])->name('bug.comment.destroy');
                Route::delete('projects/bug/file/{id}', [ProjectController::class, 'bugCommentDestroyFile'])->name('bug.comment.file.destroy');
                Route::resource('bugstatus', BugStatusController::class);
                Route::post('/bugstatus/order', [BugStatusController::class, 'order'])->name('bugstatus.order');
                Route::get('bugs-report/{view?}', [ProjectTaskController::class, 'allBugList'])->name('bugs.view')->middleware(['XSS']);
            });

            // Saas routes
            Route::resource('plans', PlanController::class)->middleware(['XSS', 'revalidate']);
            Route::get('plan-trial/{id}', [PlanController::class, 'planTrial'])->name('plan.trial')->middleware(['XSS', 'revalidate']);
            Route::post('plan-disable', [PlanController::class, 'planDisable'])->name('plan.disable')->middleware(['XSS', 'revalidate']);
            Route::resource('coupons', CouponController::class)->middleware(['XSS', 'revalidate']);
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('/orders', [StripePaymentController::class, 'index'])->name('order.index');
                Route::get('/refund/{id}/{user_id}', [StripePaymentController::class, 'refund'])->name('order.refund');
                Route::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');
                Route::post('/stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
            });
            Route::get('/apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon')->middleware(['XSS', 'revalidate']);

            // Form Builder routes
            Route::resource('form_builder', FormBuilderController::class)->middleware(['XSS']);
            Route::get('/form_builder/{id}/field', [FormBuilderController::class, 'fieldCreate'])->name('form.field.create')->middleware(['XSS']);
            Route::post('/form_builder/{id}/field', [FormBuilderController::class, 'fieldStore'])->name('form.field.store')->middleware(['XSS']);
            Route::get('/form_builder/{id}/field/{fid}/show', [FormBuilderController::class, 'fieldShow'])->name('form.field.show')->middleware(['XSS']);
            Route::get('/form_builder/{id}/field/{fid}/edit', [FormBuilderController::class, 'fieldEdit'])->name('form.field.edit')->middleware(['XSS']);
            Route::post('/form_builder/{id}/field/{fid}', [FormBuilderController::class, 'fieldUpdate'])->name('form.field.update')->middleware(['XSS']);
            Route::delete('/form_builder/{id}/field/{fid}', [FormBuilderController::class, 'fieldDestroy'])->name('form.field.destroy')->middleware(['XSS']);
            Route::get('/form_response/{id}', [FormBuilderController::class, 'viewResponse'])->name('form.response')->middleware(['XSS']);
            Route::get('/response/{id}', [FormBuilderController::class, 'responseDetail'])->name('response.detail')->middleware(['XSS']);
            Route::get('/form_field/{id}', [FormBuilderController::class, 'formFieldBind'])->name('form.field.bind')->middleware(['XSS']);
            Route::post('/form_field_store/{id}}', [FormBuilderController::class, 'bindStore'])->name('form.bind.store')->middleware(['XSS']);

            // Contract routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('contract/{id}/description', [ContractController::class, 'description'])->name('contract.description');
                Route::get('contract/grid', [ContractController::class, 'grid'])->name('contract.grid');
                Route::resource('contract', ContractController::class);
            });
            Route::post('/contract/{id}/file', [ContractController::class, 'fileUpload'])->name('contract.file.upload')->middleware(['XSS']);
            Route::get('contract/pdf/{id}', [ContractController::class, 'pdffromcontract'])->name('contract.download.pdf');
            Route::get('contract/{id}/get_contract', [ContractController::class, 'printContract'])->name('get.contract');
            Route::post('/contract_status_edit/{id}', [ContractController::class, 'contract_status_edit'])->name('contract.status')->middleware(['XSS']);
            Route::post('contract/{id}/contract_description', [ContractController::class, 'contract_descriptionStore'])->name('contract.contract_description.store');
            Route::get('/contract/{id}/file/{fid}', [ContractController::class, 'fileDownload'])->name('contracts.file.download')->middleware(['XSS']);
            Route::delete('/contract/{id}/file/delete/{fid}', [ContractController::class, 'fileDelete'])->name('contracts.file.delete')->middleware(['XSS']);
            Route::get('/contract/copy/{id}', [ContractController::class, 'copycontract'])->name('contract.copy')->middleware(['XSS']);
            Route::post('/contract/copy/store', [ContractController::class, 'copycontractstore'])->name('contract.copy.store')->middleware(['XSS']);
            Route::get('/contract/{id}/mail', [ContractController::class, 'sendmailContract'])->name('send.mail.contract');
            Route::get('/signature/{id}', [ContractController::class, 'signature'])->name('signature');
            Route::post('/signaturestore', [ContractController::class, 'signatureStore'])->name('signaturestore')->middleware(['XSS']);
            Route::post('/contract/{id}/comment', [ContractController::class, 'commentStore'])->name('comment.store');
            Route::post('/contract/{id}/notes', [ContractController::class, 'noteStore'])->name('note_store.store');
            Route::delete('/contract/{id}/notes', [ContractController::class, 'noteDestroy'])->name('note_store.destroy');
            Route::delete('/contract/{id}/comment', [ContractController::class, 'commentDestroy'])->name('comment_store.destroy');
            Route::get('get-projects/{client_id}', [ContractController::class, 'clientByProject'])->name('project.by.user.id')->middleware(['XSS']);
            Route::any('/contract/clients/select/{bid}', [ContractController::class, 'clientwiseproject'])->name('contract.clients.select');

            // Plan Payment Gateways
            Route::post('plan-pay-with-bank', [BankTransferPaymentController::class, 'planPayWithBank'])->name('plan.pay.with.bank')->middleware(['XSS', 'revalidate']);
            Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name('plan.pay.with.paypal')->middleware(['XSS', 'revalidate']);
            Route::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name('plan.get.payment.status')->middleware(['XSS', 'revalidate']);
            Route::post('/plan-pay-with-paystack', [PaystackPaymentController::class, 'planPayWithPaystack'])->name('plan.pay.with.paystack')->middleware(['XSS']);
            Route::get('/plan/paystack/{pay_id}/{plan_id}', [PaystackPaymentController::class, 'getPaymentStatus'])->name('plan.paystack');
            Route::post('/plan-pay-with-flaterwave', [FlutterwavePaymentController::class, 'planPayWithFlutterwave'])->name('plan.pay.with.flaterwave')->middleware(['XSS']);
            Route::get('/plan/flaterwave/{txref}/{plan_id}', [FlutterwavePaymentController::class, 'getPaymentStatus'])->name('plan.flaterwave');
            Route::post('/plan-pay-with-razorpay', [RazorpayPaymentController::class, 'planPayWithRazorpay'])->name('plan.pay.with.razorpay')->middleware(['XSS']);
            Route::get('/plan/razorpay/{txref}/{plan_id}', [RazorpayPaymentController::class, 'getPaymentStatus'])->name('plan.razorpay');
            Route::post('/plan-pay-with-paytm', [PaytmPaymentController::class, 'planPayWithPaytm'])->name('plan.pay.with.paytm')->middleware(['XSS']);
            Route::post('/plan/paytm/{plan}', [PaytmPaymentController::class, 'getPaymentStatus'])->name('plan.paytm');
            Route::post('/plan-pay-with-mercado', [MercadoPaymentController::class, 'planPayWithMercado'])->name('plan.pay.with.mercado')->middleware(['XSS']);
            Route::get('/plan/mercado/{plan}/{amount}', [MercadoPaymentController::class, 'getPaymentStatus'])->name('plan.mercado');
            Route::post('/plan-pay-with-mollie', [MolliePaymentController::class, 'planPayWithMollie'])->name('plan.pay.with.mollie')->middleware(['XSS']);
            Route::get('/plan/mollie/{plan}', [MolliePaymentController::class, 'getPaymentStatus'])->name('plan.mollie');
            Route::post('/plan-pay-with-skrill', [SkrillPaymentController::class, 'planPayWithSkrill'])->name('plan.pay.with.skrill')->middleware(['XSS']);
            Route::get('/plan/skrill/{plan}', [SkrillPaymentController::class, 'getPaymentStatus'])->name('plan.skrill');
            Route::post('/plan-pay-with-coingate', [CoingatePaymentController::class, 'planPayWithCoingate'])->name('plan.pay.with.coingate')->middleware(['XSS']);
            Route::get('/plan/coingate/{plan}', [CoingatePaymentController::class, 'getPaymentStatus'])->name('plan.coingate');
            Route::post('/toyyibpay', [ToyyibpayController::class, 'planPayWithToyyibpay'])->name('plan.toyyibpaypayment');
            Route::get('/plan-pay-with-toyyibpay/{id}/{status}/{coupon}', [ToyyibpayController::class, 'getPaymentStatus'])->name('plan.status');
            Route::post('payfast-plan', [PayFastController::class, 'planPayWithPayfast'])->name('payfast.payment');
            Route::get('payfast-plan/{success}', [PayFastController::class, 'getPaymentStatus'])->name('payfast.payment.success');
            Route::post('iyzipay/prepare', [IyziPayController::class, 'initiatePayment'])->name('iyzipay.payment.init');
            Route::post('iyzipay/callback/plan/{id}/{amount}/{coupan_code?}', [IyziPayController::class, 'iyzipayCallback'])->name('iyzipay.payment.callback');
            Route::post('/sspay', [SspayController::class, 'SspayPaymentPrepare'])->name('plan.sspaypayment');
            Route::get('sspay-payment-plan/{plan_id}/{amount}/{couponCode}', [SspayController::class, 'SspayPlanGetPayment'])->name('plan.sspay.callback');
            Route::post('plan-pay-with-paytab', [PaytabController::class, 'planPayWithpaytab'])->name('plan.pay.with.paytab');
            Route::any('paytab-success/plan', [PaytabController::class, 'PaytabGetPayment'])->name('plan.paytab.success');
            Route::any('/payment/initiate', [BenefitPaymentController::class, 'initiatePayment'])->name('plan.pay.with.benefit');
            Route::any('call_back', [BenefitPaymentController::class, 'call_back'])->name('benefit.call_back');
            Route::post('cashfree/payments/store', [CashfreeController::class, 'cashfreePaymentStore'])->name('plan.pay.with.cashfree');
            Route::any('cashfree/payments/success', [CashfreeController::class, 'cashfreePaymentSuccess'])->name('cashfreePayment.success');
            Route::post('/aamarpay/payment', [AamarpayController::class, 'pay'])->name('plan.pay.with.aamarpay');
            Route::any('/aamarpay/payment/{data}', [AamarpayController::class, 'aamarpaysuccess'])->name('pay.aamarpay.success');
            Route::post('/paytr/payment/{plan_id}', [PaytrController::class, 'PlanpayWithPaytr'])->name('plan.pay.with.paytr');
            Route::get('/paytr/sussess/', [PaytrController::class, 'paytrsuccess'])->name('pay.paytr.success');
            Route::post('/plan/yookassa/payment', [YooKassaController::class, 'planPayWithYooKassa'])->name('plan.pay.with.yookassa');
            Route::get('/plan/yookassa/{plan}', [YooKassaController::class, 'planGetYooKassaStatus'])->name('plan.yookassa.status');
            Route::any('/midtrans', [MidtransPaymentController::class, 'planPayWithMidtrans'])->name('plan.pay.with.midtrans');
            Route::any('/midtrans/callback', [MidtransPaymentController::class, 'planGetMidtransStatus'])->name('plan.get.midtrans.status');
            Route::any('/xendit/payment', [XenditPaymentController::class, 'planPayWithXendit'])->name('plan.pay.with.xendit');
            Route::any('/xendit/payment/status', [XenditPaymentController::class, 'planGetXenditStatus'])->name('plan.xendit.status');
            Route::post('/nepalste/payment', [NepalstePaymnetController::class, 'planPayWithnepalste'])->name('plan.pay.with.nepalste');
            Route::get('nepalste/status/', [NepalstePaymnetController::class, 'planGetNepalsteStatus'])->name('nepalste.status');
            Route::get('nepalste/cancel/', [NepalstePaymnetController::class, 'planGetNepalsteCancel'])->name('nepalste.cancel');
            Route::post('/paiementpro/payment', [PaiementProController::class, 'planPayWithPaiementpro'])->name('plan.pay.with.paiementpro');
            Route::any('/paiementpro/status', [PaiementProController::class, 'planGetPaiementProStatus'])->name('plan.get.paiementpro.status');
            Route::post('/plan/company/payment', [CinetPayController::class, 'planPayWithCinetPay'])->name('plan.pay.with.cinetpay');
            Route::post('/plan/company/payment/return', [CinetPayController::class, 'planCinetPayReturn'])->name('plan.cinetpay.return');
            Route::post('/plan/company/payment/notify/', [CinetPayController::class, 'planCinetPayNotify'])->name('plan.cinetpay.notify');
            Route::post('/fedapay', [FedapayController::class, 'planPayWithFedapay'])->name('plan.pay.with.fedapay');
            Route::get('/fedapay/status', [FedapayController::class, 'planGetFedapayStatus'])->name('plan.get.fedapay.status');
            Route::post('/payhere', [PayHereController::class, 'planPayWithPayHere'])->name('plan.pay.with.payhere');
            Route::get('/payhere/status', [PayHereController::class, 'planGetPayHereStatus'])->name('plan.get.payhere.status');
            Route::post('plan-pay-with/tap', [TapController::class, 'planPayWithTap'])->name('plan.pay.with.tap');
            Route::get('plan-get-tap-status/{plan_id}', [TapController::class, 'planGetTapStatus'])->name('plan.get.tap.status');
            Route::any('/plan-pay-with-authorize-net', [AuthorizeNetController::class, 'planPayWithAuthorizeNet'])->name('plan.pay.with.authorizenet');
            Route::post('/plan-get-authorizenet-status', [AuthorizeNetController::class, 'planPayWithAuthorizeNetData'])->name('plan.get.authorizenet.status');
            Route::post('plan-pay-with-khalti', [KhaltiController::class, 'planPayWithKhalti'])->name('plan.pay.with.khalti');
            Route::post('plan-get-khalti-status', [KhaltiController::class, 'planGetKhaltiStatus'])->name('plan.get.khalti.status');
            Route::post('/plan-pay-with-easebuzz', [EasebuzzController::class, 'planPayWithEasebuzz'])->name('plan.pay.with.easebuzz');
            Route::match(['get', 'post'], '/plan-easebuzz-payment-return', [EasebuzzController::class, 'return_url'])->name('plan.easebuzz.return');
            Route::match(['get', 'post'], 'plan-easebuzz-payment-notify', [EasebuzzController::class, 'notify_url'])->name('plan.get.easebuzz.notify');
            Route::post('plan-pay-with/ozow', [OzowPaymentController::class, 'planPayWithOzow'])->name('plan.pay.with.ozow');
            Route::get('plan-get-ozow-status/{plan_id}', [OzowPaymentController::class, 'planGetOzowStatus'])->name('plan.get.ozow.status');
            Route::post('order/{id}/changeaction', [BankTransferPaymentController::class, 'changeStatus'])->name('order.changestatus');
            Route::delete('order/{id}', [BankTransferPaymentController::class, 'orderDestroy'])->name('order.destroy');
            Route::get('order/{id}/action', [BankTransferPaymentController::class, 'action'])->name('order.action');

            // Support routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('support/{id}/reply', [SupportController::class, 'reply'])->name('support.reply');
                Route::post('support/{id}/reply', [SupportController::class, 'replyAnswer'])->name('support.reply.answer');
                Route::get('support/grid', [SupportController::class, 'grid'])->name('support.grid');
                Route::resource('support', SupportController::class);
            });

            // Competencies routes
            Route::resource('competencies', CompetenciesController::class)->middleware(['XSS']);

            // Performance Type routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::resource('performanceType', PerformanceTypeController::class);
            });

            // Plan Request Module
            Route::get('plan_request', [PlanRequestController::class, 'index'])->name('plan_request.index')->middleware(['XSS']);
            Route::get('request_frequency/{id}', [PlanRequestController::class, 'requestView'])->name('request.view')->middleware(['XSS']);
            Route::get('request_send/{id}', [PlanRequestController::class, 'userRequest'])->name('send.request')->middleware(['XSS']);
            Route::get('request_response/{id}/{response}', [PlanRequestController::class, 'acceptRequest'])->name('response.request')->middleware(['XSS']);
            Route::get('request_cancel/{id}', [PlanRequestController::class, 'cancelRequest'])->name('request.cancel')->middleware(['XSS']);

            // Import/Export Routes
            Route::get('export/productservice', [ProductServiceController::class, 'export'])->name('productservice.export');
            Route::get('import/productservice/file', [ProductServiceController::class, 'importFile'])->name('productservice.file.import');
            Route::post('import/productservice', [ProductServiceController::class, 'productserviceImportdata'])->name('productservice.import.data');
            Route::get('export/customer', [CustomerController::class, 'export'])->name('customer.export');
            Route::get('import/customer/file', [CustomerController::class, 'importFile'])->name('customer.file.import');
            Route::post('import/customer', [CustomerController::class, 'customerImportdata'])->name('customer.import.data');
            Route::get('export/vender', [VenderController::class, 'export'])->name('vender.export');
            Route::get('import/vender/file', [VenderController::class, 'importFile'])->name('vender.file.import');
            Route::post('import/vender', [VenderController::class, 'venderImportdata'])->name('vender.import.data');
            Route::get('export/invoice', [InvoiceController::class, 'export'])->name('invoice.export');
            Route::get('export/proposal', [ProposalController::class, 'export'])->name('proposal.export');
            Route::get('export/bill', [BillController::class, 'export'])->name('bill.export');
            Route::get('export/employee', [EmployeeController::class, 'export'])->name('employee.export');
            Route::get('import/employee/file', [EmployeeController::class, 'importFile'])->name('employee.file.import');
            Route::post('employee/import', [EmployeeController::class, 'fileImport'])->name('employee.import');
            Route::get('import/employee/modal', [EmployeeController::class, 'fileImportModal'])->name('employee.import.modal');
            Route::post('import/employee', [EmployeeController::class, 'employeeImportdata'])->name('employee.import.data');
            Route::get('import/attendance/file', [AttendanceEmployeeController::class, 'importFile'])->name('attendance.file.import');
            Route::post('csv/import', [ImportController::class, 'fileImport'])->name('csv.import');
            Route::get('import/csv/modal/', [ImportController::class, 'fileImportModal'])->name('csv.import.modal');
            Route::post('import/attendance', [AttendanceEmployeeController::class, 'attendanceImportdata'])->name('attendance.import.data');
            Route::get('export/transaction', [TransactionController::class, 'export'])->name('transaction.export');
            Route::get('export/accountstatement', [ReportController::class, 'export'])->name('accountstatement.export');
            Route::get('export/productstock', [ReportController::class, 'stock_export'])->name('productstock.export');
            Route::get('export/payroll', [ReportController::class, 'PayrollReportExport'])->name('payroll.export');
            Route::get('export/leave', [ReportController::class, 'LeaveReportExport'])->name('leave.export');

            // Time-Tracker routes
            Route::post('stop-tracker', [DashboardController::class, 'stopTracker'])->name('stop.tracker')->middleware(['XSS']);
            Route::get('time-tracker', [TimeTrackerController::class, 'index'])->name('time.tracker')->middleware(['XSS']);
            Route::delete('tracker/{tid}/destroy', [TimeTrackerController::class, 'Destroy'])->name('tracker.destroy');
            Route::post('tracker/image-view', [TimeTrackerController::class, 'getTrackerImages'])->name('tracker.image.view');
            Route::delete('tracker/image-remove', [TimeTrackerController::class, 'removeTrackerImages'])->name('tracker.image.remove');
            Route::get('projects/time-tracker/{id}', [ProjectController::class, 'tracker'])->name('projecttime.tracker')->middleware(['XSS']);

            // Zoom Meeting routes
            Route::resource('zoom-meeting', ZoomMeetingController::class)->middleware(['XSS']);
            Route::any('/zoom-meeting/projects/select/{bid}', [ZoomMeetingController::class, 'projectwiseuser'])->name('zoom-meeting.projects.select');
            Route::get('zoom-meeting-calender', [ZoomMeetingController::class, 'calender'])->name('zoom-meeting.calender')->middleware(['XSS']);
            Route::any('zoom-meeting/get_zoom_meeting_data', [ZoomMeetingController::class, 'get_zoom_meeting_data'])->name('zoom-meeting.get_zoom_meeting_data')->middleware(['XSS']);

            // Purchase Return routes
            Route::resource('purchase-return', PurchaseReturnController::class);
            Route::post('/purchase-return/update-status/{id}', [PurchaseReturnController::class, 'updateStatus'])->name('purchase-return.update.status');
            Route::get('/purchase-return/get-products', [PurchaseReturnController::class, 'getProducts'])->name('purchase-return.get-products');
            Route::get('/purchase-return/get-product/{id}', [PurchaseReturnController::class, 'getProduct'])->name('purchase-return.get-product');
            Route::get('/purchase-return/print/{id}', [PurchaseReturnController::class, 'printReturn'])->name('purchase-return.print');
            Route::get('/purchase-return/pdf/{id}', [PurchaseReturnController::class, 'pdf'])->name('purchase-return.pdf');

            // PaymentWall routes
            Route::post('/paymentwalls', [PaymentWallPaymentController::class, 'paymentwall'])->name('plan.paymentwallpayment')->middleware(['XSS']);
            Route::post('/plan-pay-with-paymentwall/{plan}', [PaymentWallPaymentController::class, 'planPayWithPaymentWall'])->name('plan.pay.with.paymentwall')->middleware(['XSS']);
            Route::get('/plan/{flag}', [PaymentWallPaymentController::class, 'planeerror'])->name('error.plan.show');

            // POS System routes
            Route::get('quotation/items', [QuotationController::class, 'items'])->name('quotation.items');
            Route::resource('quotation', QuotationController::class)->middleware(['XSS', 'revalidate']);
            Route::any('quotation/create/{cid}', [QuotationController::class, 'quotationCreate'])->name('quotations.create')->middleware(['XSS', 'revalidate']);
            Route::post('quotation/product', [QuotationController::class, 'product'])->name('quotation.product');
            Route::post('quotation/product/destroy', [QuotationController::class, 'productDestroy'])->name('quotation.product.destroy');
            Route::get('quotation/convert/{id}', [QuotationController::class, 'convert'])->name('quotation.convert');
            Route::post('quantity/product', [QuotationController::class, 'productQuantity'])->name('product.quantity');
            Route::post('/quotation/template/setting', [QuotationController::class, 'saveQuotationTemplateSettings'])->name('quotation.template.setting');
            Route::get('quotation/preview/{template}/{color}', [QuotationController::class, 'previewQuotation'])->name('quotation.preview')->middleware(['XSS']);
            Route::get('printview/quotation', [QuotationController::class, 'printView'])->name('quotation.printview');
            Route::get('quotation/pdf/{id}', [QuotationController::class, 'quotation'])->name('quotation.pdf')->middleware(['XSS', 'revalidate']);

            // Warehouse routes
            Route::resource('warehouse', WarehouseController::class)->middleware(['XSS', 'revalidate']);

            // Purchase routes
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::get('purchase/items', [PurchaseController::class, 'items'])->name('purchase.items');
                Route::resource('purchase', PurchaseController::class);
                Route::get('purchase/{id}/payments', [PurchaseController::class, 'payment'])->name('purchase.payments');
                Route::post('purchase/{id}/payment', [PurchaseController::class, 'createPayment'])->name('purchase.payment');
                Route::post('purchase/{id}/payment/{pid}/destroy', [PurchaseController::class, 'paymentDestroy'])->name('purchase.payment.destroy');
                Route::post('purchase/product/destroy', [PurchaseController::class, 'productDestroy'])->name('purchase.product.destroy');
                Route::post('purchase/vender', [PurchaseController::class, 'vender'])->name('purchase.vender');
                Route::post('purchase/product', [PurchaseController::class, 'product'])->name('purchase.product');
                Route::get('purchase/{id}/sent', [PurchaseController::class, 'sent'])->name('purchase.sent');
                Route::get('purchase/{id}/resent', [PurchaseController::class, 'resent'])->name('purchase.resent');
            });

            // POS Template Routes
            Route::get('pos/preview/{template}/{color}', [PosController::class, 'previewPos'])->name('pos.preview')->middleware(['XSS']);
            Route::post('/pos/template/setting', [PosController::class, 'savePosTemplateSettings'])->name('pos.template.setting')->middleware(['XSS']);
            Route::get('pos/pdf/{id}', [PosController::class, 'pos'])->name('pos.pdf')->middleware(['XSS', 'revalidate']);

            // POS Routes
            Route::prefix('pos')->name('pos.')->middleware(['XSS', 'revalidate', 'auth'])->group(function () {
                Route::get('/create', [PosController::class, 'create'])->name('create');
                Route::post('/store', [PosController::class, 'store'])->name('store');
                Route::get('/report', [PosController::class, 'report'])->name('report');
                Route::get('/barcode', [PosController::class, 'barcode'])->name('barcode');
                Route::get('/setting', [PosController::class, 'setting'])->name('setting');
                Route::post('/barcode-settings', [PosController::class, 'BarcodesettingStore'])->name('barcode.setting');
                Route::get('/print', [PosController::class, 'printBarcode'])->name('print');
                Route::post('/get-product', [PosController::class, 'getproduct'])->name('getproduct');
                Route::any('/receipt', [PosController::class, 'receipt'])->name('receipt');
                Route::get('/print-view', [PosController::class, 'printView'])->name('printview');
                Route::get('/show/{ids}', [PosController::class, 'show'])->name('show');
                Route::post('/save-template-settings', [PosController::class, 'savePosTemplateSettings'])->name('save.template');
                Route::post('/cartdiscount', [PosController::class, 'cartdiscount'])->name('discount');
                Route::get('/{id?}', [PosController::class, 'index'])->name('index');
            });

            // Cart and product routes
            Route::middleware(['XSS', 'auth'])->group(function () {
                Route::get('product-categories', [ProductServiceCategoryController::class, 'getProductCategories'])->name('product.categories');
                Route::get('add-to-cart/{id}/{session}/{warehouse_id?}', [ProductServiceController::class, 'addToCart'])->name('add.to.cart');
                Route::patch('update-cart', [ProductServiceController::class, 'updateCart'])->name('update.cart');
                Route::delete('remove-from-cart', [ProductServiceController::class, 'removeFromCart'])->name('remove.from.cart');
                Route::post('warehouse-empty-cart', [ProductServiceController::class, 'warehouseemptyCart'])->name('warehouse-empty-cart');
                Route::post('empty-cart', [ProductServiceController::class, 'emptyCart'])->name('empty.cart');
                Route::get('search-products', [ProductServiceController::class, 'searchProducts'])->name('search.products');
                Route::get('name-search-products', [ProductServiceCategoryController::class, 'searchProductsByName'])->name('name.search.products');
                Route::get('search-products-sku', [ProductServiceController::class, 'searchProductsSku'])->name('search.products.sku');
            });

            Route::get('pos-print-setting', [SystemController::class, 'posPrintIndex'])->name('pos.print.setting')->middleware(['XSS']);
            Route::get('purchase/preview/{template}/{color}', [PurchaseController::class, 'previewPurchase'])->name('purchase.preview')->middleware(['XSS']);
            Route::post('/purchase/template/setting', [PurchaseController::class, 'savePurchaseTemplateSettings'])->name('purchase.template.setting');
            Route::get('purchase/pdf/{id}', [PurchaseController::class, 'purchase'])->name('purchase.pdf')->middleware(['XSS', 'revalidate']);
            Route::resource('warehouse-transfer', WarehouseTransferController::class)->middleware(['XSS', 'revalidate']);
            Route::post('warehouse-transfer/getproduct', [WarehouseTransferController::class, 'getproduct'])->name('warehouse-transfer.getproduct')->middleware(['XSS']);
            Route::post('warehouse-transfer/getquantity', [WarehouseTransferController::class, 'getquantity'])->name('warehouse-transfer.getquantity')->middleware(['XSS']);

            // Appraisal routes
            Route::post('/appraisals', [AppraisalController::class, 'empByStar'])->name('empByStar')->middleware(['XSS']);
            Route::post('/appraisals1', [AppraisalController::class, 'empByStar1'])->name('empByStar1')->middleware(['XSS']);
            Route::post('/getemployee', [AppraisalController::class, 'getemployee'])->name('getemployee');

            // Offer Letter routes
            Route::post('setting/offerlatter/{lang?}', [SystemController::class, 'offerletterupdate'])->name('offerlatter.update');
            Route::get('setting/offerlatter', [SystemController::class, 'companyIndex'])->name('get.offerlatter.language')->middleware(['XSS']);
            Route::get('job-onboard/pdf/{id}', [JobApplicationController::class, 'offerletterPdf'])->name('offerlatter.download.pdf');
            Route::get('job-onboard/doc/{id}', [JobApplicationController::class, 'offerletterDoc'])->name('offerlatter.download.doc');

            // Joining Letter routes
            Route::post('/join-us/settings/store', [JoinUsController::class, 'store'])->name('join_us.store');
            Route::get('/join-us/{id}/edit', [JoinUsController::class, 'edit'])->name('join_us.edit');
            Route::put('/join-us/{id}', [JoinUsController::class, 'update'])->name('join_us.update');
            Route::delete('/join-us/{id}', [JoinUsController::class, 'destroy'])->name('join_us.destroy');
            Route::post('/join-us/user/store', [JoinUsController::class, 'joinUsUserStore'])->name('join_us.user.store');

            // Experience Certificate routes
            Route::post('setting/exp/{lang?}', [SystemController::class, 'experienceCertificateupdate'])->name('experiencecertificate.update');
            Route::get('setting/exp', [SystemController::class, 'companyIndex'])->name('get.experiencecertificate.language')->middleware(['XSS']);
            Route::get('employee/exppdf/{id}', [EmployeeController::class, 'ExpCertificatePdf'])->name('exp.download.pdf');
            Route::get('employee/expdoc/{id}', [EmployeeController::class, 'ExpCertificateDoc'])->name('exp.download.doc');

            // NOC routes
            Route::post('setting/noc/{lang?}', [SystemController::class, 'NOCupdate'])->name('noc.update');
            Route::get('setting/noc', [SystemController::class, 'companyIndex'])->name('get.noc.language')->middleware(['XSS']);
            Route::get('employee/nocpdf/{id}', [EmployeeController::class, 'NocPdf'])->name('noc.download.pdf');
            Route::get('employee/nocdoc/{id}', [EmployeeController::class, 'NocDoc'])->name('noc.download.doc');

            // Project Reports
            Route::resource('/project_report', ProjectReportController::class)->middleware(['XSS']);
            Route::post('/project_report_data', [ProjectReportController::class, 'ajax_data'])->name('projects.ajax')->middleware(['XSS']);
            Route::post('/project_report/tasks/{id}', [ProjectReportController::class, 'ajax_tasks_report'])->name('tasks.report.ajaxdata')->middleware(['XSS']);
            Route::get('export/task_report/{id}', [ProjectReportController::class, 'export'])->name('project_report.export');

            // User Log routes
            Route::get('/userlogs', [UserController::class, 'userLog'])->name('user.userlog')->middleware(['XSS']);
            Route::get('userlogs/{id}', [UserController::class, 'userLogView'])->name('user.userlogview')->middleware(['XSS']);
            Route::delete('userlogs/{id}', [UserController::class, 'userLogDestroy'])->name('user.userlogdestroy')->middleware(['XSS']);

            // Notification Template routes
            Route::get('notification_templates/{id?}/{lang?}', [NotificationTemplatesController::class, 'index'])->name('notification_templates.index')->middleware(['XSS']);
            Route::get('notification-templates-lang/{id}/{lang?}', [NotificationTemplatesController::class, 'manageNotificationLang'])->name('manage.notification.language')->middleware(['XSS']);
            Route::resource('notification-templates', NotificationTemplatesController::class)->middleware(['XSS']);

            // AI module routes
            Route::get('generate/{template_name}', [AiTemplateController::class, 'create'])->name('generate');
            Route::post('generate/keywords/{id}', [AiTemplateController::class, 'getKeywords'])->name('generate.keywords');
            Route::post('generate/response', [AiTemplateController::class, 'AiGenerate'])->name('generate.response');
            Route::get('grammar/{template}', [AiTemplateController::class, 'grammar'])->name('grammar')->middleware(['XSS']);
            Route::post('grammar/response', [AiTemplateController::class, 'grammarProcess'])->name('grammar.response')->middleware(['XSS']);

            // Expense Module routes
            Route::get('expense/pdf/{id}', [ExpenseController::class, 'expense'])->name('expense.pdf')->middleware(['XSS', 'revalidate']);
            Route::group(['middleware' => ['XSS', 'revalidate']], function () {
                Route::any('expense/customer', [ExpenseController::class, 'customer'])->name('expense.customer');
                Route::post('expense/vender', [ExpenseController::class, 'vender'])->name('expense.vender');
                Route::post('expense/employee', [ExpenseController::class, 'employee'])->name('expense.employee');
                Route::post('expense/product/destroy', [ExpenseController::class, 'productDestroy'])->name('expense.product.destroy');
                Route::post('expense/product', [ExpenseController::class, 'product'])->name('expense.product');
                Route::get('expense/{id}/payment', [ExpenseController::class, 'payment'])->name('expense.payment');
                Route::get('expense/items', [ExpenseController::class, 'items'])->name('expense.items');
                Route::resource('expense', ExpenseController::class);
            });

            // Referral Program routes
            Route::get('referral-program/company', [ReferralProgramController::class, 'companyIndex'])->name('referral-program.company');
            Route::resource('referral-program', ReferralProgramController::class);
            Route::get('request-amount-sent/{id}', [ReferralProgramController::class, 'requestedAmountSent'])->name('request.amount.sent');
            Route::get('request-amount-cancel/{id}', [ReferralProgramController::class, 'requestCancel'])->name('request.amount.cancel');
            Route::post('request-amount-store/{id}', [ReferralProgramController::class, 'requestedAmountStore'])->name('request.amount.store');
            Route::get('request-amount/{id}/{status}', [ReferralProgramController::class, 'requestedAmount'])->name('amount.request');

            // IVR Settings
            Route::get('ivr/settings', [IvrController::class, 'settings'])->name('ivr.settings');
            Route::post('ivr/settings', [IvrController::class, 'saveSettings'])->name('ivr.settings.save');
            Route::post('ivr/test-connection', [IvrController::class, 'testConnection'])->name('ivr.test.connection');

            // Meta Routes
            Route::prefix('meta')->name('meta.')->middleware(['auth', 'XSS', 'revalidate'])->group(function () {
                Route::get('/settings', [MetaController::class, 'settings'])->name('settings');
                Route::post('/settings', [MetaController::class, 'saveSettings'])->name('settings.save');
                Route::post('/test-connection', [MetaController::class, 'testConnection'])->name('test.connection');
                Route::get('/leads', [MetaController::class, 'leads'])->name('leads');
                Route::post('/leads/details', [MetaController::class, 'getLeadDetails'])->name('leads.details');
                Route::post('/leads/sync', [MetaController::class, 'syncLeads'])->name('leads.sync');
            });

            Route::middleware('auth:api')->group(function () {
    Route::prefix('work-reports')->group(function () {
        // List all reports (HRM/Admin)
        Route::get('/', [WorkReportController::class, 'index']);
        
        // Get current employee's reports
        Route::get('/my', [WorkReportController::class, 'myReports']);
        
        // Get report status
        Route::get('/status', [WorkReportController::class, 'getStatus']);
        
        // Get popup data
        Route::get('/popup-data', [WorkReportController::class, 'getPopupData']);
        
        // Get statistics (HRM/Admin)
        Route::get('/stats', [WorkReportController::class, 'getStats']);
        
        // Export reports (HRM/Admin)
        Route::get('/export', [WorkReportController::class, 'export']);
        
        // Create new report
        Route::post('/', [WorkReportController::class, 'store']);
        
        // Get, update, delete specific report
        Route::get('/{id}', [WorkReportController::class, 'show']);
        Route::put('/{id}', [WorkReportController::class, 'update']);
        Route::delete('/{id}', [WorkReportController::class, 'destroy']);
        
        // Review report (HRM/Admin)
        Route::post('/{id}/review', [WorkReportController::class, 'review']);
    });
});

            // Meta Webhook - No auth required
            Route::post('/api/leads/meta-webhook', [MetaController::class, 'handleWebhook'])->name('meta.webhook')->withoutMiddleware(['auth']);

            // WhatsApp Settings
            Route::get('whatsapp/settings', [\App\Http\Controllers\WhatsAppController::class, 'settings'])->name('whatsapp.settings');
            Route::post('whatsapp/settings', [\App\Http\Controllers\WhatsAppController::class, 'saveSettings'])->name('whatsapp.settings.save');
            Route::post('whatsapp/test-connection', [\App\Http\Controllers\WhatsAppController::class, 'testConnection'])->name('whatsapp.test.connection');
            Route::get('whatsapp/dashboard', [\App\Http\Controllers\WhatsAppController::class, 'dashboard'])->name('whatsapp.dashboard');
            Route::get('whatsapp/conversations', [\App\Http\Controllers\WhatsAppController::class, 'conversations'])->name('whatsapp.conversations');
            Route::get('whatsapp/conversation/{id}', [\App\Http\Controllers\WhatsAppController::class, 'showConversation'])->name('whatsapp.conversation.show');
            Route::post('whatsapp/conversation/{id}/update', [\App\Http\Controllers\WhatsAppController::class, 'updateCustomer'])->name('whatsapp.conversation.update');
            Route::post('whatsapp/conversation/{id}/close', [\App\Http\Controllers\WhatsAppController::class, 'closeConversation'])->name('whatsapp.conversation.close');
            Route::post('whatsapp/send-message', [\App\Http\Controllers\WhatsAppController::class, 'sendMessage'])->name('whatsapp.send.message');

            // VoxBay IVR Settings
            Route::any('/cookie-consent', [SystemController::class, 'CookieConsent'])->name('cookie-consent');
        });