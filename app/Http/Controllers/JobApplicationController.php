<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CustomQuestion;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\GenerateOfferLetter;
use App\Models\InterviewSchedule;
use App\Models\Job;
use App\Models\PayslipType;
use Illuminate\Support\Facades\Auth;
use App\Models\JobApplication;
use App\Models\JobApplicationNote;
use App\Models\JobOnBoard;
use App\Models\JobStage;
use App\Models\Plan;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class JobApplicationController extends Controller
{
    public function downloadResume($id)
    {
        if (\Auth::user()->can('show job application')) {
            try {
                $jobApplication = JobApplication::findOrFail($id);
                $fileName = basename($jobApplication->resume);
                $filePath = public_path('uploads/job_application/resume/' . $fileName);

                \Log::info('Attempting to download resume', [
                    'job_application_id' => $id,
                    'resume' => $jobApplication->resume,
                    'file_path' => $filePath,
                    'user_id' => \Auth::user()->id,
                ]);

                if (empty($jobApplication->resume)) {
                    \Log::warning('No resume file specified for job application', [
                        'job_application_id' => $id,
                    ]);
                    return redirect()->back()->with('error', __('No resume file uploaded.'));
                }

                if (!file_exists($filePath)) {
                    \Log::error('Resume file not found', [
                        'job_application_id' => $id,
                        'file_path' => $filePath,
                        'directory_exists' => is_dir(public_path('uploads/job_application/resume')),
                        'directory_contents' => File::exists(public_path('uploads/job_application/resume')) ? File::files(public_path('uploads/job_application/resume')) : [],
                    ]);
                    return redirect()->back()->with('error', __('Resume file not found: ') . $jobApplication->resume . '. Please contact support.');
                }

                $mimeType = mime_content_type($filePath);
                \Log::info('File found, serving download', [
                    'job_application_id' => $id,
                    'file_path' => $filePath,
                    'mime_type' => $mimeType,
                ]);

                return response()->download($filePath, $jobApplication->resume, [
                    'Content-Type' => $mimeType,
                ]);
            } catch (\Exception $e) {
                \Log::error('Error downloading resume', [
                    'job_application_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return redirect()->back()->with('error', __('Error downloading file: ') . $e->getMessage());
            }
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage job application')) {
            $stages = JobStage::where('created_by', '=', \Auth::user()->creatorId())->orderBy('order', 'asc')->get();
            $jobs = Job::where('created_by', \Auth::user()->creatorId())->get()->pluck('title', 'id');
            $jobs->prepend('All', '');

            if (isset($request->start_date) && !empty($request->start_date)) {
                $filter['start_date'] = $request->start_date;
            } else {
                $filter['start_date'] = date("Y-m-d", strtotime("-1 month"));
            }

            if (isset($request->end_date) && !empty($request->end_date)) {
                $filter['end_date'] = $request->end_date;
            } else {
                $filter['end_date'] = date("Y-m-d H:i:s", strtotime("+1 hours"));
            }

            if (isset($request->job) && !empty($request->job)) {
                $filter['job'] = $request->job;
            } else {
                $filter['job'] = '';
            }

            return view('jobApplication.index', compact('stages', 'jobs', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $jobs = Job::where('created_by', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        $jobs->prepend('--', '');
        $questions = CustomQuestion::where('created_by', \Auth::user()->creatorId())->get();

        return view('jobApplication.create', compact('jobs', 'questions'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create job application')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'job' => 'required',
                    'name' => 'required',
                    'email' => 'required|email',
                    'phone' => 'required',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $profileFileName = '';
            if ($request->hasFile('profile')) {
                $filenameWithExt = $request->file('profile')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('profile')->getClientOriginalExtension();
                $profileFileName = $filename . '_' . time() . '.' . $extension;

                $dir = 'uploads/job_application/profile';
                $path = Utility::upload_file($request, 'profile', $profileFileName, $dir, []);

                if ($path['flag'] == 0) {
                    \Log::error('Profile upload failed', ['msg' => $path['msg']]);
                    return redirect()->back()->with('error', __($path['msg']));
                }

                \Log::info('Profile uploaded successfully', ['filename' => $profileFileName, 'path' => public_path($dir . '/' . $profileFileName)]);
            }

            $resumeFileName = '';
            if ($request->hasFile('resume')) {
                $filenameWithExt = $request->file('resume')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('resume')->getClientOriginalExtension();
                $resumeFileName = $filename . '_' . time() . '.' . $extension;

                $dir = 'uploads/job_application/resume';
                $path = Utility::upload_file($request, 'resume', $resumeFileName, $dir, []);

                if ($path['flag'] == 0) {
                    \Log::error('Resume upload failed', ['msg' => $path['msg']]);
                    return redirect()->back()->with('error', __($path['msg']));
                }

                \Log::info('Resume uploaded successfully', ['filename' => $resumeFileName, 'path' => public_path($dir . '/' . $resumeFileName)]);
            }

            $stages = JobStage::where('created_by', \Auth::user()->creatorId())->first();

            $job = new JobApplication();
            $job->job = $request->job;
            $job->name = $request->name;
            $job->email = $request->email;
            $job->phone = $request->phone;
            $job->profile = $profileFileName;
            $job->resume = $resumeFileName;
            $job->cover_letter = $request->cover_letter;
            $job->dob = $request->dob;
            $job->gender = $request->gender;
            $job->country = $request->country;
            $job->state = $request->state;
            $job->city = $request->city;
            $job->stage = !empty($stages) ? $stages->id : 1;
            $job->custom_question = json_encode($request->question);
            $job->created_by = \Auth::user()->creatorId();
            $job->save();

            return redirect()->route('job-application.index')->with('success', __('Job application successfully created.'));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function show($ids)
    {
        if (\Auth::user()->can('show job application')) {
            try {
                $id = Crypt::decrypt($ids);
                $jobApplication = JobApplication::find($id);
                
                if (!$jobApplication) {
                    \Log::error('Job application not found', ['id' => $id, 'encrypted_id' => $ids]);
                    return redirect()->route('job-application.index')->with('error', __('Job application not found.'));
                }

                $notes = JobApplicationNote::where('application_id', $id)->get();
                $stages = JobStage::where('created_by', \Auth::user()->creatorId())->get();

                \Log::info('Rendering jobApplication.show', [
                    'job_application_id' => $id,
                    'jobApplication_exists' => !empty($jobApplication),
                    'notes_count' => $notes->count(),
                    'stages_count' => $stages->count(),
                ]);

                return view('jobApplication.show', compact('jobApplication', 'notes', 'stages'));
            } catch (\Exception $e) {
                \Log::error('Error in show method', [
                    'encrypted_id' => $ids,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return redirect()->route('job-application.index')->with('error', __('Invalid job application ID.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(JobApplication $jobApplication)
    {
        if (\Auth::user()->can('delete job application')) {
            if (!empty($jobApplication->profile)) {
                $file_path = 'uploads/job_application/profile/' . $jobApplication->profile;
                Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
            }

            if (!empty($jobApplication->resume)) {
                $file_path = 'Uploads/job_application/resume/' . $jobApplication->resume;
                Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
            }

            $jobApplication->delete();

            return redirect()->route('job-application.index')->with('success', __('Job application successfully deleted.'));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function order(Request $request)
    {
        if (\Auth::user()->can('move job application')) {
            $post = $request->all();
            foreach ($post['order'] as $key => $item) {
                $application = JobApplication::where('id', '=', $item)->first();
                $application->order = $key;
                $application->stage = $post['stage_id'];
                $application->save();
            }
            return redirect()->route('job-application.index')->with('success', __('Job application successfully updated.'));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function addSkill(Request $request, $id)
    {
        if (\Auth::user()->can('add job application skill')) {
            $validator = Validator::make(
                $request->all(),
                ['skill' => 'required']
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $job = JobApplication::find($id);
            $job->skill = $request->skill;
            $job->save();

            return redirect()->back()->with('success', __('Job application skill successfully added.'));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function addNote(Request $request, $id)
    {
        if (\Auth::user()->can('add job application note')) {
            $validator = Validator::make(
                $request->all(),
                ['note' => 'required']
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            $note = new JobApplicationNote();
            $note->application_id = $id;
            $note->note = $request->note;
            $note->note_created = \Auth::user()->id;
            $note->created_by = \Auth::user()->creatorId();
            $note->save();

            return redirect()->back()->with('success', __('Job application notes successfully added.'));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function destroyNote($id)
    {
        if (\Auth::user()->can('delete job application note')) {
            $note = JobApplicationNote::find($id);
            $note->delete();

            return redirect()->back()->with('success', __('Job application notes successfully deleted.'));
        } else {
            return redirect()->route('job-application.index')->with('error', __('Permission denied.'));
        }
    }

    public function rating(Request $request, $id)
    {
        $jobApplication = JobApplication::find($id);
        $jobApplication->rating = $request->rating;
        $jobApplication->save();
        return true;
    }

    public function archive($id)
    {
        $jobApplication = JobApplication::find($id);
        if ($jobApplication->is_archive == 0) {
            $jobApplication->is_archive = 1;
            $jobApplication->save();
            return redirect()->route('job.application.candidate')->with('success', __('Job application successfully added to archive.'));
        } else {
            $jobApplication->is_archive = 0;
            $jobApplication->save();
            return redirect()->route('job-application.index')->with('success', __('Job application successfully remove to archive.'));
        }
    }

    public function candidate()
    {
        if (\Auth::user()->can('manage job onBoard')) {
            $archive_application = JobApplication::where('created_by', \Auth::user()->creatorId())->where('is_archive', 1)->get();
            return view('jobApplication.candidate', compact('archive_application'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function jobBoardCreate($id)
    {
        $status = JobOnBoard::$status;
        $job_type = JobOnBoard::$job_type;
        $salary_duration = JobOnBoard::$salary_duration;
        $salary_type = PayslipType::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $applications = InterviewSchedule::select('interview_schedules.*', 'job_applications.name')
            ->join('job_applications', 'interview_schedules.candidate', '=', 'job_applications.id')
            ->where('interview_schedules.created_by', \Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'candidate');
        $applications->prepend('-', '');

        return view('jobApplication.onboardCreate', compact('id', 'status', 'applications', 'job_type', 'salary_type', 'salary_duration'));
    }

    public function jobOnBoard()
    {
        if (\Auth::user()->can('manage job onBoard')) {
            $jobOnBoards = JobOnBoard::where('created_by', \Auth::user()->creatorId())->with('applications')->get();
            return view('jobApplication.onboard', compact('jobOnBoards'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function jobBoardStore(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'joining_date' => 'required',
                'job_type' => 'required',
                'days_of_week' => 'required|gt:0',
                'salary' => 'required|gt:0',
                'salary_type' => 'required',
                'salary_duration' => 'required',
                'status' => 'required',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $id = ($id == 0) ? $request->application : $id;

        $jobBoard = new JobOnBoard();
        $jobBoard->application = $id;
        $jobBoard->joining_date = $request->joining_date;
        $jobBoard->job_type = $request->job_type;
        $jobBoard->days_of_week = $request->days_of_week;
        $jobBoard->salary = $request->salary;
        $jobBoard->salary_type = $request->salary_type;
        $jobBoard->salary_duration = $request->salary_duration;
        $jobBoard->status = $request->status;
        $jobBoard->created_by = \Auth::user()->creatorId();
        $jobBoard->save();

        $interview = InterviewSchedule::where('candidate', $id)->first();
        if (!empty($interview)) {
            $interview->delete();
        }

        return redirect()->route('job.on.board')->with('success', __('Candidate successfully added in job board.'));
    }

    public function jobBoardUpdate(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'joining_date' => 'required',
                'job_type' => 'required',
                'days_of_week' => 'required',
                'salary' => 'required',
                'salary_type' => 'required',
                'salary_duration' => 'required',
                'status' => 'required',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $jobBoard = JobOnBoard::find($id);
        $jobBoard->joining_date = $request->joining_date;
        $jobBoard->job_type = $request->job_type;
        $jobBoard->days_of_week = $request->days_of_week;
        $jobBoard->salary = $request->salary;
        $jobBoard->salary_type = $request->salary_type;
        $jobBoard->salary_duration = $request->salary_duration;
        $jobBoard->status = $request->status;
        $jobBoard->save();

        return redirect()->route('job.on.board')->with('success', __('Job board Candidate successfully updated.'));
    }

    public function jobBoardEdit($id)
    {
        $jobOnBoard = JobOnBoard::find($id);
        $status = JobOnBoard::$status;
        $job_type = JobOnBoard::$job_type;
        $salary_duration = JobOnBoard::$salary_duration;
        $salary_type = PayslipType::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

        return view('jobApplication.onboardEdit', compact('jobOnBoard', 'status', 'job_type', 'salary_type', 'salary_duration'));
    }

    public function jobBoardDelete($id)
    {
        $jobBoard = JobOnBoard::find($id);
        $jobBoard->delete();

        return redirect()->route('job.on.board')->with('success', __('Job onBoard successfully deleted.'));
    }

    public function jobBoardConvert($id)
    {
        $jobOnBoard = JobOnBoard::find($id);
        $company_settings = Utility::settings();
        $documents = Document::where('created_by', \Auth::user()->creatorId())->get();
        $branches = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $employees = User::where('created_by', \Auth::user()->creatorId())->get();
        $employeesId = \Auth::user()->employeeIdFormat($this->employeeNumber());

        return view('jobApplication.convert', compact('jobOnBoard', 'employees', 'employeesId', 'departments', 'designations', 'documents', 'branches', 'company_settings'));
    }

    public function jobBoardConvertData(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'dob' => 'required',
                'gender' => 'required',
                'phone' => 'required',
                'address' => 'required',
                'email' => 'required|unique:users',
                'password' => 'required',
                'department_id' => 'required',
                'designation_id' => 'required',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withInput()->with('error', $validator->getMessageBag()->first());
        }

        $objUser = \Auth::user();
        $employees = User::where('type', '!=', 'client')->where('type', '!=', 'company')->where('created_by', \Auth::user()->creatorId())->get();
        $total_employee = $employees->count();
        $plan = Plan::find($objUser->plan);

        if ($total_employee < $plan->max_users || $plan->max_users == -1) {
            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
                'type' => 'employee',
                'lang' => 'en',
                'created_by' => \Auth::user()->creatorId(),
            ]);
            $user->save();
            $user->assignRole('Employee');
        } else {
            return redirect()->back()->with('error', __('Your employee limit is over, Please upgrade plan.'));
        }

        $document_implode = !empty($request->document) ? implode(',', array_keys($request->document)) : null;

        $employee = Employee::create([
            'user_id' => $user->id,
            'name' => $request['name'],
            'dob' => $request['dob'],
            'gender' => $request['gender'],
            'phone' => $request['phone'],
            'address' => $request['address'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'employee_id' => $this->employeeNumber(),
            'branch_id' => $request['branch_id'],
            'department_id' => $request['department_id'],
            'designation_id' => $request['designation_id'],
            'company_doj' => $request['company_doj'],
            'documents' => $document_implode,
            'account_holder_name' => $request['account_holder_name'],
            'account_number' => $request['account_number'],
            'bank_name' => $request['bank_name'],
            'bank_identifier_code' => $request['bank_identifier_code'],
            'branch_location' => $request['branch_location'],
            'tax_payer_id' => $request['tax_payer_id'],
            'created_by' => \Auth::user()->creatorId(),
        ]);

        if (!empty($employee)) {
            $JobOnBoard = JobOnBoard::find($id);
            $JobOnBoard->convert_to_employee = $employee->id;
            $JobOnBoard->save();
        }

        if ($request->hasFile('document')) {
            foreach ($request->document as $key => $document) {
                $filenameWithExt = $request->file('document')[$key]->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('document')[$key]->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $dir = public_path('uploads/document/');
                $image_path = $dir . $filenameWithExt;

                if (File::exists($image_path)) {
                    File::delete($image_path);
                }

                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }

                $path = $request->file('document')[$key]->storeAs('Uploads/document/', $fileNameToStore);
                $employee_document = EmployeeDocument::create([
                    'employee_id' => $employee['employee_id'],
                    'document_id' => $key,
                    'document_value' => $fileNameToStore,
                    'created_by' => \Auth::user()->creatorId(),
                ]);
                $employee_document->save();
            }
        }

        $settings = Utility::settings();
        if ($settings['new_user'] == 1) {
            $userArr = [
                'email' => $user->email,
                'password' => $user->password,
            ];
            $resp = Utility::sendEmailTemplate('new_user', [$user->id => $user->email], $userArr);
            return redirect()->back()->with('success', __('Application successfully converted to employee.') . (!empty($resp) && $resp['is_success'] == false && !empty($resp['error']) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }

        return redirect()->back()->with('success', __('Application successfully converted to employee.'));
    }

    function employeeNumber()
    {
        $latest = Employee::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        return $latest ? $latest->employee_id + 1 : 1;
    }

    public function getByJob(Request $request)
    {
        $job = Job::find($request->id);
        $job->applicant = !empty($job->applicant) ? explode(',', $job->applicant) : '';
        $job->visibility = !empty($job->visibility) ? explode(',', $job->visibility) : '';
        $job->custom_question = !empty($job->custom_question) ? explode(',', $job->custom_question) : '';

        return json_encode($job);
    }

    public function stageChange(Request $request)
    {
        $application = JobApplication::where('id', '=', $request->schedule_id)->first();
        $application->stage = $request->stage;
        $application->save();

        return response()->json([
            'success' => __('This candidate stage successfully changed.'),
        ], 200);
    }

    public function offerletterPdf($id)
    {
        $users = \Auth::user();
        $currantLang = $users->currentLanguage();
        $Offerletter = GenerateOfferLetter::where(['lang' => $currantLang, 'created_by' => \Auth::user()->creatorId()])->first();

        $job = JobApplication::find($id);
        $Onboard = JobOnBoard::find($id);
        $name = JobApplication::find($Onboard->application);
        $job_title = Job::find($name->job);
        $salary = PayslipType::find($Onboard->salary_type);

        $obj = [
            'applicant_name' => $name->name,
            'app_name' => env('APP_NAME'),
            'job_title' => $job_title->title,
            'job_type' => !empty($Onboard->job_type) ? $Onboard->job_type : '',
            'start_date' => $Onboard->joining_date,
            'workplace_location' => !empty($job->jobs->branches->name) ? $job->jobs->branches->name : '',
            'days_of_week' => !empty($Onboard->days_of_week) ? $Onboard->days_of_week : '',
            'salary' => !empty($Onboard->salary) ? $Onboard->salary : '',
            'salary_type' => !empty($salary->name) ? $salary->name : '',
            'salary_duration' => !empty($Onboard->salary_duration) ? $Onboard->salary_duration : '',
            'offer_expiration_date' => !empty($Onboard->joining_date) ? $Onboard->joining_date : '',
        ];
        $Offerletter->content = GenerateOfferLetter::replaceVariable($Offerletter->content, $obj);
        return view('jobApplication.template.offerletterpdf', compact('Offerletter', 'name'));
    }

    public function offerletterDoc($id)
    {
        $users = \Auth::user();
        $currantLang = $users->currentLanguage();
        $Offerletter = GenerateOfferLetter::where(['lang' => $currantLang, 'created_by' => \Auth::user()->creatorId()])->first();

        $job = JobApplication::find($id);
        $Onboard = JobOnBoard::find($id);
        $name = JobApplication::find($Onboard->application);
        $job_title = Job::find($name->job);
        $salary = PayslipType::find($Onboard->salary_type);

        $obj = [
            'applicant_name' => $name->name,
            'app_name' => env('APP_NAME'),
            'job_title' => $job_title->title,
            'job_type' => !empty($Onboard->job_type) ? $Onboard->job_type : '',
            'start_date' => $Onboard->joining_date,
            'workplace_location' => !empty($job->jobs->branches->name) ? $job->jobs->branches->name : '',
            'days_of_week' => !empty($Onboard->days_of_week) ? $Onboard->days_of_week : '',
            'salary' => !empty($Onboard->salary) ? $Onboard->salary : '',
            'salary_type' => !empty($salary->name) ? $salary->name : '',
            'salary_duration' => !empty($Onboard->salary_duration) ? $Onboard->salary_duration : '',
            'offer_expiration_date' => !empty($Onboard->joining_date) ? $Onboard->joining_date : '',
        ];
        $Offerletter->content = GenerateOfferLetter::replaceVariable($Offerletter->content, $obj);
        return view('jobApplication.template.offerletterdocx', compact('Offerletter', 'name'));
    }
}
