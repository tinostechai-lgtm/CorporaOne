<?php

namespace App\Http\Controllers;

use App\Mail\SendLeadEmail;
use App\Models\ClientDeal;
use App\Models\Deal;
use App\Models\DealCall;
use App\Models\DealDiscussion;
use App\Models\DealEmail;
use App\Models\DealFile;
use App\Models\Label;
use App\Models\Lead;
use App\Models\LeadActivityLog;
use App\Models\LeadCall;
use App\Models\LeadDiscussion;
use App\Models\LeadEmail;
use App\Models\LeadFile;
use App\Models\LeadStage;
use App\Models\Pipeline;
use App\Models\ProductService;
use App\Models\Source;
use App\Models\Stage;
use App\Models\User;
use App\Models\UserDeal;
use App\Models\UserLead;
use App\Models\Utility;
use App\Models\WebhookSetting;
use App\Services\SocialLeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeadExport;
use App\Imports\LeadImport;

class LeadController extends Controller
{
    protected $socialLeadService;

    public function __construct(SocialLeadService $socialLeadService)
    {
        $this->socialLeadService = $socialLeadService;
        $this->middleware('auth');
    }

    /**
     * Display leads dashboard with Kanban view
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('manage lead')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $user = auth()->user();
        $creatorId = $user->creatorId();

        // Get pipeline
        if ($user->default_pipeline) {
            $pipeline = Pipeline::where('created_by', $creatorId)
                ->where('id', $user->default_pipeline)
                ->first();
            if (!$pipeline) {
                $pipeline = Pipeline::where('created_by', $creatorId)->first();
            }
        } else {
            $pipeline = Pipeline::where('created_by', $creatorId)->first();
        }

        if (!$pipeline) {
            return redirect()->route('pipelines.index')->with('error', __('Please create a pipeline first.'));
        }

        $pipelines = Pipeline::where('created_by', $creatorId)->pluck('name', 'id');
        
        $query = Lead::where('created_by', $creatorId)
            ->where('pipeline_id', $pipeline->id)
            ->with(['stage', 'users']);

        // Apply filters
        if ($request->filled('source')) {
            $query->where('lead_source', $request->source);
        }

        if ($request->filled('assignment')) {
            if ($request->assignment == 'assigned') {
                $query->has('users');
            } elseif ($request->assignment == 'unassigned') {
                $query->doesntHave('users');
            }
        }

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('order')->get();
        $totalLeads = Lead::where('created_by', $creatorId)->count();
        
        $assignedLeads = Lead::where('created_by', $creatorId)->has('users')->count();
        $unassignedLeads = Lead::where('created_by', $creatorId)->doesntHave('users')->count();
        $socialLeads = Lead::where('created_by', $creatorId)
            ->whereIn('lead_source', ['facebook', 'instagram', 'whatsapp'])
            ->count();

        $stages = LeadStage::where('pipeline_id', $pipeline->id)
            ->orderBy('order')
            ->get();

        $users = User::where('created_by', $creatorId)
            ->whereNotIn('type', ['client', 'company'])
            ->pluck('name', 'id');

        return view('leads.index', compact(
            'pipelines', 'pipeline', 'leads', 'totalLeads', 
            'stages', 'users', 'assignedLeads', 'unassignedLeads', 'socialLeads'
        ));
    }


    

    /**
     * Display leads in list view
     */
    public function leadList(Request $request)
    {
        if (!auth()->user()->can('manage lead')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $user = auth()->user();
        $creatorId = $user->creatorId();

        $query = Lead::where('created_by', $creatorId)
            ->with(['stage', 'users']);

        if ($request->filled('status')) {
            if ($request->status == 'converted') {
                $query->whereNotNull('is_converted');
            } else {
                $query->whereNull('is_converted');
            }
        }

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        if ($request->filled('assigned_to')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('user_id', $request->assigned_to);
            });
        }

        if ($request->filled('source')) {
            $query->where('lead_source', $request->source);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate(20);
        $totalLeads = Lead::where('created_by', $creatorId)->count();
        
        $assignedLeads = Lead::where('created_by', $creatorId)->has('users')->count();
        $unassignedLeads = Lead::where('created_by', $creatorId)->doesntHave('users')->count();
        $convertedLeads = Lead::where('created_by', $creatorId)->whereNotNull('is_converted')->count();

        return view('leads.list', compact('leads', 'totalLeads', 'assignedLeads', 'unassignedLeads', 'convertedLeads'));
    }

    /**
     * Show form to create new lead
     */
    public function create()
    {
        if (!auth()->user()->can('create lead')) {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }

        $users = User::where('created_by', auth()->user()->creatorId())
            ->whereNotIn('type', ['client', 'company'])
            ->where('id', '!=', auth()->id())
            ->pluck('name', 'id');
        $users->prepend(__('Select User'), '');
        
        $sources = Source::where('created_by', auth()->user()->creatorId())->pluck('name', 'id');
        $products = ProductService::where('created_by', auth()->user()->creatorId())->pluck('name', 'id');
        $pipelines = Pipeline::where('created_by', auth()->user()->creatorId())->pluck('name', 'id');

        return view('leads.create', compact('users', 'sources', 'products', 'pipelines'));
    }

    /**
     * Store newly created lead
     */
    public function store(Request $request)
    {
        $usr = auth()->user();
        if ($usr->can('create lead')) {
            $validator = Validator::make($request->all(), [
                'subject' => 'required',
                'name' => 'required',
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            // Default Field Value
            if ($usr->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $usr->creatorId())->where('id', '=', $usr->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $usr->creatorId())->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $usr->creatorId())->first();
            }

            $stage = LeadStage::where('pipeline_id', '=', $pipeline->id)->first();

            if (empty($stage)) {
                return redirect()->back()->with('error', __('Please Create Stage for This Pipeline.'));
            } else {
                $lead = new Lead();
                $lead->name = $request->name;
                $lead->email = $request->email;
                $lead->phone = $request->phone;
                $lead->subject = $request->subject;
                $lead->user_id = $request->user_id;
                $lead->pipeline_id = $pipeline->id;
                $lead->stage_id = $stage->id;
                $lead->created_by = $usr->creatorId();
                $lead->date = date('Y-m-d');
                $lead->save();

                if ($request->user_id != auth()->user()->id) {
                    $usrLeads = [$usr->id, $request->user_id];
                } else {
                    $usrLeads = [$request->user_id];
                }

                foreach ($usrLeads as $usrLead) {
                    UserLead::create(['user_id' => $usrLead, 'lead_id' => $lead->id]);
                }

                // Send Email
                $setings = Utility::settings();
                if ($setings['lead_assigned'] == 1) {
                    $usrEmail = User::find($request->user_id);
                    $leadAssignArr = [
                        'lead_name' => $lead->name,
                        'lead_email' => $lead->email,
                        'lead_subject' => $lead->subject,
                        'lead_pipeline' => $pipeline->name,
                        'lead_stage' => $stage->name,
                    ];
                    $resp = Utility::sendEmailTemplate('lead_assigned', [$usrEmail->id => $usrEmail->email], $leadAssignArr);
                }

                // For Notification
                $setting = Utility::settings(auth()->user()->creatorId());
                $leadArr = [
                    'user_name' => auth()->user()->name,
                    'lead_name' => $lead->name,
                    'lead_email' => $lead->email,
                ];
                
                if (isset($setting['lead_notification']) && $setting['lead_notification'] == 1) {
                    Utility::send_slack_msg('new_lead', $leadArr);
                }

                if (isset($setting['telegram_lead_notification']) && $setting['telegram_lead_notification'] == 1) {
                    Utility::send_telegram_msg('new_lead', $leadArr);
                }

                return redirect()->back()->with('success', __('Lead successfully created!'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Display lead details
     */
    public function show(Lead $lead)
    {
        if ($lead->is_active) {
            $calenderTasks = [];
            $deal = Deal::where('id', '=', $lead->is_converted)->first();
            $stageCnt = LeadStage::where('pipeline_id', '=', $lead->pipeline_id)->where('created_by', '=', $lead->created_by)->get();
            $i = 0;
            foreach ($stageCnt as $stage) {
                $i++;
                if ($stage->id == $lead->stage_id) {
                    break;
                }
            }
            $precentage = number_format(($i * 100) / count($stageCnt));

            return view('leads.show', compact('lead', 'calenderTasks', 'deal', 'precentage'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show edit form
     */
    public function edit(Lead $lead)
    {
        if (auth()->user()->can('edit lead')) {
            if ($lead->created_by == auth()->user()->creatorId()) {
                $pipelines = Pipeline::where('created_by', '=', auth()->user()->creatorId())->get()->pluck('name', 'id');
                $pipelines->prepend(__('Select Pipeline'), '');
                $sources = Source::where('created_by', '=', auth()->user()->creatorId())->get()->pluck('name', 'id');
                $products = ProductService::where('created_by', '=', auth()->user()->creatorId())->get()->pluck('name', 'id');
                $users = User::where('created_by', '=', auth()->user()->creatorId())->where('type', '!=', 'client')->where('type', '!=', 'company')->where('id', '!=', auth()->user()->id)->get()->pluck('name', 'id');
                $lead->sources = explode(',', $lead->sources);
                $lead->products = explode(',', $lead->products);

                return view('leads.edit', compact('lead', 'pipelines', 'sources', 'products', 'users'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Update lead
     */
    public function update(Request $request, Lead $lead)
    {
        if (auth()->user()->can('edit lead')) {
            if ($lead->created_by == auth()->user()->creatorId()) {
                $validator = Validator::make($request->all(), [
                    'subject' => 'required',
                    'name' => 'required',
                    'email' => 'required|email',
                    'pipeline_id' => 'required',
                    'user_id' => 'required',
                    'stage_id' => 'required',
                    'sources' => 'required',
                    'products' => 'required',
                ]);

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $lead->name = $request->name;
                $lead->email = $request->email;
                $lead->phone = $request->phone;
                $lead->subject = $request->subject;
                $lead->user_id = $request->user_id;
                $lead->pipeline_id = $request->pipeline_id;
                $lead->stage_id = $request->stage_id;
                $lead->sources = implode(",", array_filter($request->sources));
                $lead->products = implode(",", array_filter($request->products));
                $lead->notes = $request->notes;
                $lead->save();

                return redirect()->back()->with('success', __('Lead successfully updated!'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Delete lead
     */
    public function destroy(Lead $lead)
    {
        if (auth()->user()->can('delete lead')) {
            if ($lead->created_by == auth()->user()->creatorId()) {
                LeadDiscussion::where('lead_id', '=', $lead->id)->delete();
                LeadFile::where('lead_id', '=', $lead->id)->delete();
                UserLead::where('lead_id', '=', $lead->id)->delete();
                LeadActivityLog::where('lead_id', '=', $lead->id)->delete();
                $lead->delete();

                return redirect()->back()->with('success', __('Lead successfully deleted!'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function json(Request $request)
    {
        $lead_stages = new LeadStage();
        if ($request->pipeline_id && !empty($request->pipeline_id)) {
            $lead_stages = $lead_stages->where('pipeline_id', '=', $request->pipeline_id);
            $lead_stages = $lead_stages->get()->pluck('name', 'id');
        } else {
            $lead_stages = [];
        }

        return response()->json($lead_stages);
    }

    // ============ IMPORT / EXPORT METHODS ============

    /**
     * Show import/export view
     */
    public function importExport()
    {
        if (!auth()->user()->can('manage lead')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = auth()->user()->creatorId();
        
        // Get statistics for the view
        $totalLeads = Lead::where('created_by', $creatorId)->count();
        
        $lastImport = Lead::where('created_by', $creatorId)
            ->orderBy('created_at', 'desc')
            ->first();
        $lastImportDate = $lastImport ? $lastImport->created_at->format('M d, Y') : null;
        
        $lastExport = session('last_export_date');
        $lastExportDate = $lastExport ? date('M d, Y', strtotime($lastExport)) : null;
        
        $pipelines = Pipeline::where('created_by', $creatorId)->pluck('name', 'id');
        
        return view('leads.import-export', compact('totalLeads', 'lastImportDate', 'lastExportDate', 'pipelines'));
    }

    /**
     * Show import file modal (from old working controller)
     */
    public function importFile()
    {
        return view('leads.import');
    }

    /**
     * Process uploaded file and show column mapping (from old working controller)
     */
    public function fileImport(Request $request)
    {
        session_start();

        $error = '';
        $html = '';

        if ($request->file->getClientOriginalName() != '') {
            $file_array = explode(".", $request->file->getClientOriginalName());
            $extension = end($file_array);
            
            if ($extension == 'csv') {
                $file_data = fopen($request->file->getRealPath(), 'r');
                $file_header = fgetcsv($file_data);
                $html .= '<table class="table table-bordered"><tr>';

                for ($count = 0; $count < count($file_header); $count++) {
                    $html .= '
                        <th>
                            <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                <option value="">Set Count Data</option>
                                <option value="subject">Subject</option>
                                <option value="name">Name</option>
                                <option value="email">Email</option>
                                <option value="phone">Phone No</option>
                            </select>
                        </th>
                    ';
                }
                $html .= '
                    <th>
                        <select name="set_column_data" class="form-control set_column_data user-name" data-column_number="' . $count + 1 . '">
                            <option value="user">User</option>
                        </select>
                    </th>
                ';
                $html .= '</tr>';
                $temp_data = [];
                
                while (($row = fgetcsv($file_data)) !== false) {
                    $html .= '<tr>';
                    for ($count = 0; $count < count($row); $count++) {
                        $html .= '<td>' . $row[$count] . '</td>';
                    }
                    $html .= '<td>
                        <select name="user" class="form-control user-name-value">';
                        if (auth()->user()->type == "company") {
                            $users = User::where('created_by', '=', auth()->user()->creatorId())->where('type', '!=', 'client')->get()->pluck('name', 'id');
                        } else {
                            $users = User::where('id', '=', auth()->user()->id)->where('type', '!=', 'client')->get()->pluck('name', 'id');
                        }
                        foreach ($users as $key => $user) {
                            $html .= ' <option value="' . $key . '">' . $user . '</option>';
                        }
                        $html .= '  </select>
                    </td>';
                    $html .= '</tr>';
                    $temp_data[] = $row;
                }
                $_SESSION['file_data'] = $temp_data;
                fclose($file_data);
            } else {
                $error = 'Only <b>.csv</b> file allowed';
            }
        } else {
            $error = 'Please Select CSV File';
        }
        
        $output = array(
            'error' => $error,
            'output' => $html,
        );

        return json_encode($output);
    }

    /**
     * Show column mapping modal (from old working controller)
     */
    public function fileImportModal()
    {
        return view('leads.import_modal');
    }

    /**
     * Process final import (from old working controller)
     */
    public function leadImportdata(Request $request)
    {
        $creatorId = auth()->user()->creatorId();
        session_start();
        $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
        $flag = 0;
        $html .= '<table class="table table-bordered">\n<thead>\n<tr>\n<th>Subject</th>\n<th>Name</th>\n<th>Email</th>\n<th>Phone</th>\n</tr>\n</thead>\n<tbody>';
        
        try {
            $file_data = $_SESSION['file_data'];
            unset($_SESSION['file_data']);
        } catch (\Throwable $th) {
            $html = '<h3 class="text-danger text-center">Oops, Session Time Out!</h3></br>';
            return response()->json([
                'html' => true,
                'response' => $html,
            ]);
        }

        $user = auth()->user();
        if ($user->default_pipeline) {
            $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('id', '=', $user->default_pipeline)->first();
            if (!$pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->first();
            }
        } else {
            $pipeline = Pipeline::where('created_by', '=', $creatorId)->first();
        }

        if (!empty($pipeline)) {
            $stage = LeadStage::where('pipeline_id', '=', $pipeline->id)->first();
            if (empty($stage)) {
                return response()->json([
                    'success' => false,
                    'message' => __('Please create stage for this pipeline.'),
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => __('Please create pipeline.'),
            ]);
        }

        foreach ($file_data as $key => $row) {
            try {
                $users = User::find($request->user[$key]);
                if (empty($users)) {
                    $users = User::where('created_by', auth()->user()->id)->first();
                }

                $lead = Lead::create([
                    'subject' => $row[$request->subject],
                    'name' => $row[$request->name],
                    'user_id' => $users->id,
                    'email' => $row[$request->email],
                    'phone' => $row[$request->phone],
                    'pipeline_id' => $pipeline->id,
                    'stage_id' => $stage->id,
                    'created_by' => $creatorId,
                    'date' => now()->format('Y-m-d'),
                    'lead_source' => 'import',
                ]);
                
                UserLead::create([
                    'user_id' => $creatorId,
                    'lead_id' => $lead->id,
                ]);
            } catch (\Exception $e) {
                $flag = 1;
                $html .= '<tr>';
                $html .= '<td>' . (isset($row[$request->subject]) ? $row[$request->subject] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request->name]) ? $row[$request->name] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request->email]) ? $row[$request->email] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request->phone]) ? $row[$request->phone] : '-') . '</td>';
                $html .= '</tr>';
            }
        }
        
        $html .= '</tbody></table><br />';
        
        if ($flag == 1) {
            return response()->json([
                'html' => true,
                'response' => $html,
            ]);
        } else {
            return response()->json([
                'html' => false,
                'response' => __('Data has been imported.'),
            ]);
        }
    }

    /**
     * Process AJAX import for the new import/export view
     */
    public function processImport(Request $request)
    {
        try {
            $request->validate([
                'leads' => 'required|array',
                'skip_duplicates' => 'boolean',
                'send_notification' => 'boolean'
            ]);

            $creatorId = auth()->user()->creatorId();
            $imported = 0;
            $skipped = 0;
            $errors = [];

            // Get pipeline and stage
            $user = auth()->user();
            if ($user->default_pipeline) {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->where('id', '=', $user->default_pipeline)->first();
                if (!$pipeline) {
                    $pipeline = Pipeline::where('created_by', '=', $creatorId)->first();
                }
            } else {
                $pipeline = Pipeline::where('created_by', '=', $creatorId)->first();
            }

            if (!$pipeline) {
                return response()->json([
                    'success' => false,
                    'error' => __('No pipeline found. Please create a pipeline first.')
                ], 400);
            }

            $stage = LeadStage::where('pipeline_id', '=', $pipeline->id)->first();
            if (!$stage) {
                return response()->json([
                    'success' => false,
                    'error' => __('No stage found. Please create a stage for this pipeline.')
                ], 400);
            }

            foreach ($request->leads as $leadData) {
                // Check for duplicate email if skip_duplicates is enabled
                if ($request->skip_duplicates && !empty($leadData['email'])) {
                    $existing = Lead::where('email', $leadData['email'])
                        ->where('created_by', $creatorId)
                        ->first();
                    if ($existing) {
                        $skipped++;
                        continue;
                }
                }

                try {
                    $lead = new Lead();
                    $lead->name = $leadData['name'] ?? 'Unknown';
                    $lead->email = $leadData['email'] ?? null;
                    $lead->phone = $leadData['phone'] ?? null;
                    $lead->subject = $leadData['subject'] ?? 'Imported Lead';
                    $lead->user_id = auth()->id();
                    $lead->pipeline_id = $pipeline->id;
                    $lead->stage_id = $stage->id;
                    $lead->created_by = $creatorId;
                    $lead->date = now()->format('Y-m-d');
                    $lead->lead_source = $leadData['lead_source'] ?? 'import';
                    $lead->notes = $leadData['notes'] ?? null;
                    $lead->save();

                    // Assign to creator
                    UserLead::create([
                        'user_id' => $creatorId,
                        'lead_id' => $lead->id,
                    ]);

                    $imported++;

                    // Send notification if enabled
                    if ($request->send_notification && $lead->user_id) {
                        $user = User::find($lead->user_id);
                        if ($user) {
                            // You can implement notification here
                            // $user->notify(new LeadAssignedNotification($lead));
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                    \Log::error('Import error: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} leads. Skipped: {$skipped} duplicates.",
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            \Log::error('Import process failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process AJAX export for the new import/export view
     */
    public function processExport(Request $request)
    {
        try {
            $request->validate([
                'format' => 'required|in:excel,csv',
                'fields' => 'required|array'
            ]);

            $creatorId = auth()->user()->creatorId();
            $query = Lead::where('created_by', $creatorId);

            // Apply filters
            if ($request->filled('pipeline')) {
                $query->where('pipeline_id', $request->pipeline);
            }

            if ($request->filled('source')) {
                $query->where('lead_source', $request->source);
            }

            if ($request->filled('status')) {
                if ($request->status == 'converted') {
                    $query->whereNotNull('is_converted');
                } else {
                    $query->whereNull('is_converted');
                }
            }

            $leads = $query->get();

            if ($leads->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => __('No leads found to export.')
                ], 400);
            }

            // Prepare export data
            $fields = $request->fields;
            $exportData = [];
            
            // Add headers
            $headers = array_map(function($field) {
                return ucfirst(str_replace('_', ' ', $field));
            }, $fields);
            $exportData[] = $headers;
            
            // Add data rows
            foreach ($leads as $lead) {
                $row = [];
                foreach ($fields as $field) {
                    $value = '';
                    switch ($field) {
                        case 'name':
                            $value = $lead->name;
                            break;
                        case 'email':
                            $value = $lead->email;
                            break;
                        case 'phone':
                            $value = $lead->phone;
                            break;
                        case 'subject':
                            $value = $lead->subject;
                            break;
                        case 'lead_source':
                            $value = $lead->lead_source;
                            break;
                        case 'stage':
                            $value = $lead->stage ? $lead->stage->name : '';
                            break;
                        case 'lead_score':
                            $value = $lead->lead_score ?? '';
                            break;
                        case 'created_at':
                            $value = $lead->created_at ? $lead->created_at->format('Y-m-d H:i:s') : '';
                            break;
                        default:
                            $value = $lead->$field ?? '';
                    }
                    $row[] = $value;
                }
                $exportData[] = $row;
            }

            // Generate filename
            $filename = 'leads_export_' . date('Y-m-d_His');
            
            // Create exports directory if it doesn't exist
            if (!Storage::disk('public')->exists('exports')) {
                Storage::disk('public')->makeDirectory('exports');
            }

            if ($request->format === 'csv') {
                $filename .= '.csv';
                $handle = fopen('php://temp', 'w');
                
                foreach ($exportData as $row) {
                    fputcsv($handle, $row);
                }
                
                rewind($handle);
                $csv = stream_get_contents($handle);
                fclose($handle);
                
                $path = 'exports/' . $filename;
                Storage::disk('public')->put($path, $csv);
                
            } else {
                // Excel format - create CSV first (since we don't have Excel package)
                $filename .= '.xlsx';
                $handle = fopen('php://temp', 'w');
                
                foreach ($exportData as $row) {
                    fputcsv($handle, $row);
                }
                
                rewind($handle);
                $csv = stream_get_contents($handle);
                fclose($handle);
                
                $path = 'exports/' . $filename;
                Storage::disk('public')->put($path, $csv);
            }

            // Store last export date in session
            session(['last_export_date' => now()]);

            $downloadUrl = Storage::disk('public')->url($path);

            return response()->json([
                'success' => true,
                'download_url' => $downloadUrl,
                'message' => 'Export completed successfully',
                'count' => $leads->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Export process failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export sample data
     */
    public function exportSample()
    {
        try {
            $creatorId = auth()->user()->creatorId();
            
            $sampleLeads = Lead::where('created_by', $creatorId)
                ->limit(10)
                ->get(['name', 'email', 'phone', 'subject', 'lead_source', 'notes', 'created_at']);
            
            $filename = 'sample_leads_' . date('Y-m-d') . '.xlsx';
            
            // Create exports directory if it doesn't exist
            if (!Storage::disk('public')->exists('exports')) {
                Storage::disk('public')->makeDirectory('exports');
            }
            
            // Prepare data
            $exportData = [];
            $exportData[] = ['Name', 'Email', 'Phone', 'Subject', 'Lead Source', 'Notes', 'Created At'];
            
            foreach ($sampleLeads as $lead) {
                $exportData[] = [
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->subject,
                    $lead->lead_source,
                    $lead->notes,
                    $lead->created_at ? $lead->created_at->format('Y-m-d H:i:s') : ''
                ];
            }
            
            // Create CSV
            $handle = fopen('php://temp', 'w');
            foreach ($exportData as $row) {
                fputcsv($handle, $row);
            }
            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);
            
            $path = 'exports/' . $filename;
            Storage::disk('public')->put($path, $csv);
            
            $downloadUrl = Storage::disk('public')->url($path);
            
            return response()->json([
                'success' => true,
                'download_url' => $downloadUrl
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Sample export failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export leads (existing method)
     */
    public function export()
    {
        if (!auth()->user()->can('manage lead')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $name = 'Lead_' . date('Y-m-d i:h:s');
        return Excel::download(new LeadExport(), $name . '.xlsx');
    }

    // ============ SOCIAL MEDIA INTEGRATIONS ============

    public function socialConnect()
    {
        if (!auth()->user()->can('manage lead')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $creatorId = auth()->user()->creatorId();
        
        $facebookLeads = Lead::where('created_by', $creatorId)->where('lead_source', 'facebook')->count();
        $instagramLeads = Lead::where('created_by', $creatorId)->where('lead_source', 'instagram')->count();
        $whatsappLeads = Lead::where('created_by', $creatorId)->where('lead_source', 'whatsapp')->count();
        
        $recentSocialLeads = Lead::where('created_by', $creatorId)
            ->whereIn('lead_source', ['facebook', 'instagram', 'whatsapp'])
            ->latest()
            ->limit(10)
            ->get();

        return view('leads.social-connect', compact('facebookLeads', 'instagramLeads', 'whatsappLeads', 'recentSocialLeads'));
    }

    public function fetchFacebookLeads(Request $request)
    {
        try {
            if (!auth()->user()->can('create lead')) {
                return response()->json(['error' => __('Permission Denied.')], 403);
            }

            $validator = Validator::make($request->all(), [
                'pageId' => 'required|string',
                'accessToken' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $leads = $this->socialLeadService->fetchFacebookLeads(
                $request->pageId,
                $request->accessToken
            );
            
            $created = $this->processSocialLeads($leads, 'facebook');
            
            return response()->json([
                'success' => true,
                'message' => $created . ' leads fetched and created from Facebook!',
                'count' => $created
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Facebook lead fetch failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fetchInstagramLeads(Request $request)
    {
        try {
            if (!auth()->user()->can('create lead')) {
                return response()->json(['error' => __('Permission Denied.')], 403);
            }

            $validator = Validator::make($request->all(), [
                'businessId' => 'required|string',
                'accessToken' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $leads = $this->socialLeadService->fetchInstagramLeads(
                $request->businessId,
                $request->accessToken
            );
            
            $created = $this->processSocialLeads($leads, 'instagram');
            
            return response()->json([
                'success' => true,
                'message' => $created . ' leads fetched and created from Instagram!',
                'count' => $created
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Instagram lead fetch failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fetchWhatsAppLeads(Request $request)
    {
        try {
            if (!auth()->user()->can('create lead')) {
                return response()->json(['error' => __('Permission Denied.')], 403);
            }

            $validator = Validator::make($request->all(), [
                'phoneNumberId' => 'required|string',
                'accessToken' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $leads = $this->socialLeadService->fetchWhatsAppLeads(
                $request->phoneNumberId,
                $request->accessToken
            );
            
            $created = $this->processSocialLeads($leads, 'whatsapp');
            
            return response()->json([
                'success' => true,
                'message' => $created . ' leads fetched and created from WhatsApp!',
                'count' => $created
            ]);
            
        } catch (\Exception $e) {
            \Log::error('WhatsApp lead fetch failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function webhookLead(Request $request)
    {
        try {
            \Log::info('Webhook hit', ['payload' => $request->all()]);

            $token = \DB::table('settings')
                ->where('name', 'webhook_token')
                ->value('value');

            if (!$token || ($request->header('X-Webhook-Token') !== $token && $request->query('token') !== $token)) {
                return response()->json(['error' => 'Invalid or missing token'], 401);
            }

            $data = $request->json()->all();
            $source = $this->detectLeadSource($data);
            $leadData = $this->extractLeadData($data);
            
            $validator = Validator::make($leadData, [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid lead data: ' . $validator->errors()->first()], 400);
            }

            if (empty($leadData['email']) && empty($leadData['phone'])) {
                return response()->json(['error' => 'Either email or phone is required'], 400);
            }

            $creator = $this->getSystemCreator();
            $creatorId = $creator->creatorId() ?? $creator->id;

            $pipeline = Pipeline::where('created_by', $creatorId)->first();
            if (!$pipeline) {
                return response()->json(['error' => 'No pipeline found'], 500);
            }

            $stage = LeadStage::where('pipeline_id', $pipeline->id)->orderBy('order')->first();
            if (!$stage) {
                return response()->json(['error' => 'No stage found in pipeline'], 500);
            }

            $lead = Lead::create([
                'name' => $leadData['name'],
                'email' => $leadData['email'] ?? null,
                'phone' => $leadData['phone'] ?? null,
                'subject' => $leadData['subject'] ?? 'Lead from ' . ucfirst($source),
                'pipeline_id' => $pipeline->id,
                'stage_id' => $stage->id,
                'created_by' => $creatorId,
                'user_id' => $creator->id,
                'date' => now()->format('Y-m-d'),
                'lead_source' => $source,
            ]);

            UserLead::create([
                'user_id' => $creatorId,
                'lead_id' => $lead->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully!',
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'source' => $source
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Webhook failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ============ CALL MANAGEMENT ============

    public function callCreate($id)
    {
        if (auth()->user()->can('create lead call')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $users = UserLead::where('lead_id', '=', $lead->id)->get();
                return view('leads.calls', compact('lead', 'users'));
            } else {
                return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
        }
    }

    public function callStore($id, Request $request)
    {
        if (auth()->user()->can('create lead call')) {
            $usr = auth()->user();
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $validator = Validator::make($request->all(), [
                    'subject' => 'required',
                    'call_type' => 'required',
                    'user_id' => 'required',
                ]);

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $leadCall = LeadCall::create([
                    'lead_id' => $lead->id,
                    'subject' => $request->subject,
                    'call_type' => $request->call_type,
                    'duration' => $request->duration,
                    'user_id' => $request->user_id,
                    'description' => $request->description,
                    'call_result' => $request->call_result,
                ]);

                LeadActivityLog::create([
                    'user_id' => $usr->id,
                    'lead_id' => $lead->id,
                    'log_type' => 'create lead call',
                    'remark' => json_encode(['title' => 'Create new Lead Call']),
                ]);

                return redirect()->back()->with('success', __('Call successfully created!'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }

    public function callEdit($id, $call_id)
    {
        if (auth()->user()->can('edit lead call')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $call = LeadCall::find($call_id);
                $users = UserLead::where('lead_id', '=', $lead->id)->get();
                return view('leads.calls', compact('call', 'lead', 'users'));
            } else {
                return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
        }
    }

    public function callUpdate($id, $call_id, Request $request)
    {
        if (auth()->user()->can('edit lead call')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $validator = Validator::make($request->all(), [
                    'subject' => 'required',
                    'call_type' => 'required',
                    'user_id' => 'required',
                ]);

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $call = LeadCall::find($call_id);
                $call->update([
                    'subject' => $request->subject,
                    'call_type' => $request->call_type,
                    'duration' => $request->duration,
                    'user_id' => $request->user_id,
                    'description' => $request->description,
                    'call_result' => $request->call_result,
                ]);

                return redirect()->back()->with('success', __('Call successfully updated!'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'tasks');
        }
    }

    public function callDestroy($id, $call_id)
    {
        if (auth()->user()->can('delete lead call')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $task = LeadCall::find($call_id);
                $task->delete();
                return redirect()->back()->with('success', __('Call successfully deleted!'))->with('status', 'calls');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'calls');
        }
    }

    // ============ EMAIL MANAGEMENT ============

    public function emailCreate($id)
    {
        if (auth()->user()->can('create lead email')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                return view('leads.emails', compact('lead'));
            } else {
                return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
        }
    }

    public function emailStore($id, Request $request)
    {
        if (auth()->user()->can('create lead email')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $settings = Utility::settings();
                $validator = Validator::make($request->all(), [
                    'to' => 'required|email',
                    'subject' => 'required',
                    'description' => 'required',
                ]);

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $leadEmail = LeadEmail::create([
                    'lead_id' => $lead->id,
                    'to' => $request->to,
                    'subject' => $request->subject,
                    'description' => $request->description,
                ]);

                $leadEmailData = [
                    'lead_name' => $lead->name,
                    'to' => $request->to,
                    'subject' => $request->subject,
                    'description' => $request->description,
                ];

                try {
                    Mail::to($request->to)->send(new SendLeadEmail($leadEmailData, $settings));
                } catch (\Exception $e) {
                    $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                }

                LeadActivityLog::create([
                    'user_id' => auth()->user()->id,
                    'lead_id' => $lead->id,
                    'log_type' => 'create lead email',
                    'remark' => json_encode(['title' => 'Create new Deal Email']),
                ]);

                return redirect()->back()->with('success', __('Email successfully created!') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''))->with('status', 'emails');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'emails');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'emails');
        }
    }

    // ============ DISCUSSION MANAGEMENT ============

    public function discussionCreate($id)
    {
        $lead = Lead::find($id);
        if ($lead->created_by == auth()->user()->creatorId()) {
            return view('leads.discussions', compact('lead'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function discussionStore($id, Request $request)
    {
        $usr = auth()->user();
        $lead = Lead::find($id);

        if ($lead->created_by == $usr->creatorId()) {
            $discussion = new LeadDiscussion();
            $discussion->comment = $request->comment;
            $discussion->lead_id = $lead->id;
            $discussion->created_by = $usr->id;
            $discussion->save();

            return redirect()->back()->with('success', __('Message successfully added!'))->with('status', 'discussion');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'discussion');
        }
    }

    // ============ FILE MANAGEMENT ============

    public function fileUpload($id, Request $request)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $image_size = $request->file('file')->getSize();
                $result = Utility::updateStorageLimit(auth()->user()->creatorId(), $image_size);
                $file_name = $request->file->getClientOriginalName();
                $file_path = $request->lead_id . "_" . md5(time()) . "_" . $request->file->getClientOriginalName();

                $file = LeadFile::create([
                    'lead_id' => $request->lead_id,
                    'file_name' => $file_name,
                    'file_path' => $file_path,
                ]);
                
                if ($result == 1) {
                    $request->file->storeAs('lead_files', $file_path);
                    $return = [];
                    $return['is_success'] = true;
                    $return['download'] = route('leads.file.download', [$lead->id, $file->id]);
                    $return['delete'] = route('leads.file.delete', [$lead->id, $file->id]);
                } else {
                    $return = [];
                    $return['is_success'] = true;
                    $return['status'] = 1;
                    $return['success_msg'] = ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : '');
                }

                LeadActivityLog::create([
                    'user_id' => auth()->user()->id,
                    'lead_id' => $lead->id,
                    'log_type' => 'Upload File',
                    'remark' => json_encode(['file_name' => $file_name]),
                ]);

                return response()->json($return);
            } else {
                return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
        }
    }

    public function fileDownload($id, $file_id)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $file = LeadFile::find($file_id);
                if ($file) {
                    $file_path = storage_path('app/public/lead_files/' . $file->file_path);
                    $filename = $file->file_name;
                    return response()->download($file_path, $filename);
                } else {
                    return redirect()->back()->with('error', __('File is not exist.'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function fileDelete($id, $file_id)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $file = LeadFile::find($file_id);
                if ($file) {
                    $file_path = 'lead_files/' . $file->file_path;
                    $result = Utility::changeStorageLimit(auth()->user()->creatorId(), $file_path);
                    $path = storage_path('app/public/lead_files/' . $file->file_path);
                    if (file_exists($path)) {
                        \File::delete($path);
                    }
                    $file->delete();
                    return response()->json(['is_success' => true], 200);
                } else {
                    return response()->json(['is_success' => false, 'error' => __('File is not exist.')], 200);
                }
            } else {
                return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
        }
    }

    // ============ NOTE MANAGEMENT ============

    public function noteStore($id, Request $request)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $lead->notes = $request->notes;
                $lead->save();
                return response()->json(['is_success' => true, 'success' => __('Note successfully saved!')], 200);
            } else {
                return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['is_success' => false, 'error' => __('Permission Denied.')], 401);
        }
    }

    // ============ LABEL MANAGEMENT ============

    public function labels($id)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $labels = Label::where('pipeline_id', '=', $lead->pipeline_id)->where('created_by', auth()->user()->creatorId())->get();
                $selected = $lead->labels();
                if ($selected) {
                    $selected = $selected->pluck('name', 'id')->toArray();
                } else {
                    $selected = [];
                }
                return view('leads.labels', compact('lead', 'labels', 'selected'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function labelStore($id, Request $request)
    {
        $validator = Validator::make($request->all(), ['labels' => 'required']);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        if (auth()->user()->can('edit lead')) {
            $leads = Lead::find($id);
            if ($leads->created_by == auth()->user()->creatorId()) {
                if ($request->labels) {
                    $leads->labels = implode(',', $request->labels);
                } else {
                    $leads->labels = $request->labels;
                }
                $leads->save();
                return redirect()->back()->with('success', __('Labels successfully updated!'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    // ============ USER MANAGEMENT ============

    public function userEdit($id)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $users = User::where('created_by', '=', auth()->user()->creatorId())
                    ->where('type', '!=', 'client')
                    ->where('type', '!=', 'company')
                    ->whereNotIn('id', function ($q) use ($lead) {
                        $q->select('user_id')->from('user_leads')->where('lead_id', '=', $lead->id);
                    })->get();
                $users = $users->pluck('name', 'id');
                return view('leads.users', compact('lead', 'users'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function userUpdate($id, Request $request)
    {
        if (auth()->user()->can('edit lead')) {
            $usr = auth()->user();
            $lead = Lead::find($id);
            if ($lead->created_by == $usr->creatorId()) {
                if (!empty($request->users)) {
                    $users = array_filter($request->users);
                    foreach ($users as $user) {
                        UserLead::create(['lead_id' => $lead->id, 'user_id' => $user]);
                    }
                }
                if (!empty($users) && !empty($request->users)) {
                    return redirect()->back()->with('success', __('Users successfully updated!'));
                } else {
                    return redirect()->back()->with('error', __('Please Select Valid User!'));
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function userDestroy($id, $user_id)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                UserLead::where('lead_id', '=', $lead->id)->where('user_id', '=', $user_id)->delete();
                return redirect()->back()->with('success', __('User successfully deleted!'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    // ============ PRODUCT MANAGEMENT ============

    public function productEdit($id)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $products = ProductService::where('created_by', '=', auth()->user()->creatorId())
                    ->whereNotIn('id', explode(',', $lead->products))
                    ->get()->pluck('name', 'id');
                return view('leads.products', compact('lead', 'products'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function productUpdate($id, Request $request)
    {
        if (auth()->user()->can('edit lead')) {
            $usr = auth()->user();
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                if (!empty($request->products)) {
                    $products = array_filter($request->products);
                    $old_products = explode(',', $lead->products);
                    $lead->products = implode(',', array_merge($old_products, $products));
                    $lead->save();

                    $objProduct = ProductService::whereIn('id', $products)->get()->pluck('name', 'id')->toArray();
                    LeadActivityLog::create([
                        'user_id' => $usr->id,
                        'lead_id' => $lead->id,
                        'log_type' => 'Add Product',
                        'remark' => json_encode(['title' => implode(",", $objProduct)]),
                    ]);
                }
                if (!empty($products) && !empty($request->products)) {
                    return redirect()->back()->with('success', __('Products successfully updated!'))->with('status', 'products');
                } else {
                    return redirect()->back()->with('error', __('Please Select Valid Product!'))->with('status', 'general');
                }
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
        }
    }

    public function productDestroy($id, $product_id)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $products = explode(',', $lead->products);
                foreach ($products as $key => $product) {
                    if ($product_id == $product) {
                        unset($products[$key]);
                    }
                }
                $lead->products = implode(',', $products);
                $lead->save();
                return redirect()->back()->with('success', __('Products successfully deleted!'))->with('status', 'products');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'products');
        }
    }

    // ============ SOURCE MANAGEMENT ============

    public function sourceEdit($id)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $sources = Source::where('created_by', '=', auth()->user()->creatorId())->get();
                $selected = $lead->sources();
                if ($selected) {
                    $selected = $selected->pluck('name', 'id')->toArray();
                }
                return view('leads.sources', compact('lead', 'sources', 'selected'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function sourceUpdate($id, Request $request)
    {
        if (auth()->user()->can('edit lead')) {
            $validator = Validator::make($request->all(), ['sources' => 'required']);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $usr = auth()->user();
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                if (!empty($request->sources) && count($request->sources) > 0) {
                    $lead->sources = implode(',', $request->sources);
                } else {
                    $lead->sources = "";
                }
                $lead->save();

                LeadActivityLog::create([
                    'user_id' => $usr->id,
                    'lead_id' => $lead->id,
                    'log_type' => 'Update Sources',
                    'remark' => json_encode(['title' => 'Update Sources']),
                ]);

                return redirect()->back()->with('success', __('Sources successfully updated!'))->with('status', 'sources');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
        }
    }

    public function sourceDestroy($id, $source_id)
    {
        if (auth()->user()->can('edit lead')) {
            $lead = Lead::find($id);
            if ($lead->created_by == auth()->user()->creatorId()) {
                $sources = explode(',', $lead->sources);
                foreach ($sources as $key => $source) {
                    if ($source_id == $source) {
                        unset($sources[$key]);
                    }
                }
                $lead->sources = implode(',', $sources);
                $lead->save();
                return redirect()->back()->with('success', __('Sources successfully deleted!'))->with('status', 'sources');
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'))->with('status', 'sources');
        }
    }

    // ============ ORDER MANAGEMENT ============

    public function order(Request $request)
    {
        if (auth()->user()->can('move lead')) {
            $usr = auth()->user();
            $post = $request->all();
            $lead = $this->lead($post['lead_id']);
            $lead_users = $lead->users->pluck('email', 'id')->toArray();

            if ($lead->stage_id != $post['stage_id']) {
                $newStage = LeadStage::find($post['stage_id']);
                LeadActivityLog::create([
                    'user_id' => auth()->user()->id,
                    'lead_id' => $lead->id,
                    'log_type' => 'Move',
                    'remark' => json_encode([
                        'title' => $lead->name,
                        'old_status' => $lead->stage->name,
                        'new_status' => $newStage->name,
                    ]),
                ]);

                $lArr = [
                    'lead_name' => $lead->name,
                    'lead_email' => $lead->email,
                    'lead_pipeline' => $lead->pipeline->name,
                    'lead_stage' => $lead->stage->name,
                    'lead_old_stage' => $lead->stage->name,
                    'lead_new_stage' => $newStage->name,
                ];

                Utility::sendEmailTemplate('Move Lead', $lead_users, $lArr);
            }

            foreach ($post['order'] as $key => $item) {
                $lead = $this->lead($item);
                $lead->order = $key;
                $lead->stage_id = $post['stage_id'];
                $lead->save();
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    private static $leadData = null;

    public function lead($item)
    {
        if (self::$leadData == null) {
            $lead = Lead::find($item);
            self::$leadData = $lead;
        }
        return self::$leadData;
    }

    // ============ CONVERSION TO DEAL ============

    public function showConvertToDeal($id)
    {
        $lead = Lead::findOrFail($id);
        $exist_client = User::where('type', '=', 'client')->where('email', '=', $lead->email)->where('created_by', '=', auth()->user()->creatorId())->first();
        $clients = User::where('type', '=', 'client')->where('created_by', '=', auth()->user()->creatorId())->get();
        return view('leads.convert', compact('lead', 'exist_client', 'clients'));
    }

    public function convertToDeal($id, Request $request)
    {
        $lead = Lead::findOrFail($id);
        $usr = auth()->user();

        if ($request->client_check == 'exist') {
            $validator = Validator::make($request->all(), ['clients' => 'required']);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $client = User::where('type', '=', 'client')->where('email', '=', $request->clients)->where('created_by', '=', $usr->creatorId())->first();
            if (empty($client)) {
                return redirect()->back()->with('error', 'Client is not available now.');
            }
        } else {
            $validator = Validator::make($request->all(), [
                'client_name' => 'required',
                'client_email' => 'required|email|unique:users,email',
                'client_password' => 'required',
            ]);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $role = Role::findByName('client');
            $client = User::create([
                'name' => $request->client_name,
                'email' => $request->client_email,
                'password' => \Hash::make($request->client_password),
                'type' => 'client',
                'lang' => 'en',
                'created_by' => $usr->creatorId(),
            ]);
            $client->assignRole($role);
            $cArr = ['email' => $request->client_email, 'password' => $request->client_password];
            Utility::sendEmailTemplate('New User', [$client->id => $client->email], $cArr);
        }

        // Create Deal
        $stage = Stage::where('pipeline_id', '=', $lead->pipeline_id)->first();
        if (empty($stage)) {
            return redirect()->back()->with('error', __('Please Create Stage for This Pipeline.'));
        }

        $deal = new Deal();
        $deal->name = $request->name;
        $deal->price = empty($request->price) ? 0 : $request->price;
        $deal->pipeline_id = $lead->pipeline_id;
        $deal->stage_id = $stage->id;
        if (!empty($request->is_transfer)) {
            $deal->sources = in_array('sources', $request->is_transfer) ? $lead->sources : '';
            $deal->products = in_array('products', $request->is_transfer) ? $lead->products : '';
            $deal->notes = in_array('notes', $request->is_transfer) ? $lead->notes : '';
        } else {
            $deal->sources = '';
            $deal->products = '';
            $deal->notes = '';
        }
        $deal->labels = $lead->labels;
        $deal->status = 'Active';
        $deal->created_by = $lead->created_by;
        $deal->save();

        // Make entry in ClientDeal Table
        ClientDeal::create(['deal_id' => $deal->id, 'client_id' => $client->id]);

        // Send Mail
        $pipeline = Pipeline::find($lead->pipeline_id);
        $dArr = [
            'deal_name' => $deal->name,
            'deal_pipeline' => $pipeline->name,
            'deal_stage' => $stage->name,
            'deal_status' => $deal->status,
            'deal_price' => $usr->priceFormat($deal->price),
        ];
        Utility::sendEmailTemplate('Assign Deal', [$client->id => $client->email], $dArr);

        // Make Entry in UserDeal Table
        $leadUsers = UserLead::where('lead_id', '=', $lead->id)->get();
        foreach ($leadUsers as $leadUser) {
            UserDeal::create(['user_id' => $leadUser->user_id, 'deal_id' => $deal->id]);
        }

        // Transfer Lead Discussion to Deal
        if (!empty($request->is_transfer)) {
            if (in_array('discussion', $request->is_transfer)) {
                $discussions = LeadDiscussion::where('lead_id', '=', $lead->id)->where('created_by', '=', $usr->creatorId())->get();
                if (!empty($discussions)) {
                    foreach ($discussions as $discussion) {
                        DealDiscussion::create([
                            'deal_id' => $deal->id,
                            'comment' => $discussion->comment,
                            'created_by' => $discussion->created_by,
                        ]);
                    }
                }
            }

            // Transfer Lead Files to Deal
            if (in_array('files', $request->is_transfer)) {
                $files = LeadFile::where('lead_id', '=', $lead->id)->get();
                if (!empty($files)) {
                    foreach ($files as $file) {
                        $location = storage_path('app/public/lead_files/' . $file->file_path);
                        $new_location = storage_path('app/public/deal_files/' . $file->file_path);
                        $dir = dirname($new_location);
                        if (!file_exists($dir)) {
                            mkdir($dir, 0755, true);
                        }
                        $copied = copy($location, $new_location);
                        if ($copied) {
                            DealFile::create([
                                'deal_id' => $deal->id,
                                'file_name' => $file->file_name,
                                'file_path' => $file->file_path,
                            ]);
                        }
                    }
                }
            }

            // Transfer Lead Calls to Deal
            if (in_array('calls', $request->is_transfer)) {
                $calls = LeadCall::where('lead_id', '=', $lead->id)->get();
                foreach ($calls as $call) {
                    DealCall::create($call->toArray());
                }
            }

            // Transfer Lead Emails to Deal
            if (in_array('emails', $request->is_transfer)) {
                $emails = LeadEmail::where('lead_id', '=', $lead->id)->get();
                foreach ($emails as $email) {
                    DealEmail::create($email->toArray());
                }
            }
        }

        // Update is_converted field as deal_id
        $lead->is_converted = $deal->id;
        $lead->save();

        // For Notification
        $setting = Utility::settings(auth()->user()->creatorId());
        $leadUsers = Lead::where('id', '=', $lead->id)->first();
        $leadUserArr = [
            'lead_user_name' => $leadUsers->name,
            'lead_name' => $lead->name,
            'lead_email' => $lead->email,
        ];
        if (isset($setting['leadtodeal_notification']) && $setting['leadtodeal_notification'] == 1) {
            Utility::send_slack_msg('lead_to_deal_conversion', $leadUserArr);
        }
        if (isset($setting['telegram_leadtodeal_notification']) && $setting['telegram_leadtodeal_notification'] == 1) {
            Utility::send_telegram_msg('lead_to_deal_conversion', $leadUserArr);
        }

        return redirect()->back()->with('success', __('Lead successfully converted'));
    }

    // ============ HELPER METHODS ============

    protected function processSocialLeads($leads, $source)
    {
        $creator = $this->getSystemCreator();
        $creatorId = $creator->creatorId() ?? $creator->id;
        
        $pipeline = Pipeline::where('created_by', $creatorId)->first();
        if (!$pipeline) return 0;
        
        $stage = LeadStage::where('pipeline_id', $pipeline->id)->orderBy('order')->first();
        if (!$stage) return 0;
        
        $created = 0;
        foreach ($leads as $leadData) {
            if (!empty($leadData['email']) && Lead::where('email', $leadData['email'])->exists()) {
                continue;
            }
            
            $lead = Lead::create([
                'name' => $leadData['name'] ?? 'Lead from ' . $source,
                'email' => $leadData['email'] ?? null,
                'phone' => $leadData['phone'] ?? null,
                'subject' => $leadData['subject'] ?? 'Lead from ' . ucfirst($source),
                'pipeline_id' => $pipeline->id,
                'stage_id' => $stage->id,
                'created_by' => $creatorId,
                'user_id' => $creator->id,
                'date' => now()->format('Y-m-d'),
                'lead_source' => $source,
            ]);
            
            UserLead::create([
                'user_id' => $creatorId,
                'lead_id' => $lead->id,
            ]);
            
            $created++;
        }
        
        return $created;
    }

    protected function getSystemCreator()
    {
        $creator = User::where('type', 'company')->where('created_by', 0)->first();
        if (!$creator) {
            $creator = User::first();
        }
        return $creator;
    }

    protected function detectLeadSource($data)
    {
        if (isset($data['facebook']) || isset($data['fbclid'])) {
            return 'facebook';
        }
        if (isset($data['instagram']) || isset($data['ig_lead'])) {
            return 'instagram';
        }
        if (isset($data['whatsapp']) || isset($data['wa_id'])) {
            return 'whatsapp';
        }
        if (isset($data['source']) && in_array($data['source'], ['facebook', 'instagram', 'whatsapp'])) {
            return $data['source'];
        }
        return 'webhook';
    }

    protected function extractLeadData($data)
    {
        $extracted = [
            'name' => $data['name'] ?? $data['full_name'] ?? $data['lead_name'] ?? 'Unknown',
            'email' => $data['email'] ?? $data['lead_email'] ?? null,
            'phone' => $data['phone'] ?? $data['phone_number'] ?? $data['mobile'] ?? null,
            'subject' => $data['subject'] ?? $data['message'] ?? null,
        ];

        if (isset($data['field_data'])) {
            foreach ($data['field_data'] as $field) {
                $name = strtolower($field['name'] ?? '');
                if (str_contains($name, 'name')) {
                    $extracted['name'] = $field['values'][0] ?? $extracted['name'];
                } elseif (str_contains($name, 'email')) {
                    $extracted['email'] = $field['values'][0] ?? null;
                } elseif (str_contains($name, 'phone')) {
                    $extracted['phone'] = $field['values'][0] ?? null;
                }
            }
        }

        return $extracted;
    }
}