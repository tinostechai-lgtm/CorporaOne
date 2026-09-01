<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\InterviewSchedule;
use App\Models\JobStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecruitmentController extends Controller
{
    /**
     * Display a listing of jobs
     */
    public function indexJobs(Request $request)
    {
        $jobs = Job::where('created_by', $request->user()->creatorId())
            ->with(['jobCategory', 'jobStage'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs
        ], 200);
    }

    /**
     * Store a newly created job
     */
    public function storeJob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'branch' => 'nullable|exists:branches,id',
            'category' => 'nullable|exists:job_categories,id',
            'position' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'skill' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $job = new Job();
        $job->fill($request->all());
        $job->created_by = $request->user()->creatorId();
        $job->save();

        return response()->json([
            'success' => true,
            'message' => 'Job created successfully',
            'data' => $job->load(['jobCategory', 'jobStage'])
        ], 201);
    }

    /**
     * Display the specified job
     */
    public function showJob(Request $request, $id)
    {
        $job = Job::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with(['jobCategory', 'jobStage', 'jobApplications'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $job
        ]);
    }

    /**
     * Update the specified job
     */
    public function updateJob(Request $request, $id)
    {
        $job = Job::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'branch' => 'nullable|exists:branches,id',
            'category' => 'nullable|exists:job_categories,id',
            'position' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'skill' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $job->fill($request->only(['title', 'branch', 'category', 'position', 'status', 'start_date', 'end_date', 'skill', 'description']));
        $job->save();

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'data' => $job->load(['jobCategory', 'jobStage'])
        ]);
    }

    /**
     * Remove the specified job
     */
    public function destroyJob(Request $request, $id)
    {
        $job = Job::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully'
        ]);
    }

    /**
     * Display a listing of job applications
     */
    public function indexJobApplications(Request $request)
    {
        $applications = JobApplication::where('created_by', $request->user()->creatorId())
            ->with(['job', 'jobApplicationNotes'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $applications
        ], 200);
    }

    /**
     * Store a newly created job application
     */
    public function storeJobApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job' => 'required|exists:jobs,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'cover_letter' => 'nullable|string',
            'custom_question' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $application = new JobApplication();
        $application->fill($request->all());
        $application->created_by = $request->user()->creatorId();
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Job application created successfully',
            'data' => $application->load('job')
        ], 201);
    }

    /**
     * Display the specified job application
     */
    public function showJobApplication(Request $request, $id)
    {
        $application = JobApplication::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with(['job', 'jobApplicationNotes', 'interviewSchedules'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $application
        ]);
    }

    /**
     * Update the specified job application
     */
    public function updateJobApplication(Request $request, $id)
    {
        $application = JobApplication::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'phone' => 'nullable|string',
            'cover_letter' => 'nullable|string',
            'custom_question' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $application->fill($request->only(['name', 'email', 'phone', 'cover_letter', 'custom_question']));
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Job application updated successfully',
            'data' => $application->load('job')
        ]);
    }

    /**
     * Remove the specified job application
     */
    public function destroyJobApplication(Request $request, $id)
    {
        $application = JobApplication::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $application->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job application deleted successfully'
        ]);
    }

    /**
     * Display a listing of interview schedules
     */
    public function indexInterviewSchedules(Request $request)
    {
        $schedules = InterviewSchedule::where('created_by', $request->user()->creatorId())
            ->with(['jobApplication', 'employee'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules
        ], 200);
    }

    /**
     * Store a newly created interview schedule
     */
    public function storeInterviewSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_application' => 'required|exists:job_applications,id',
            'employee' => 'nullable|array',
            'employee.*' => 'exists:employees,id',
            'date' => 'required|date',
            'time' => 'required',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $schedule = new InterviewSchedule();
        $schedule->fill($request->except('employee'));
        $schedule->created_by = $request->user()->creatorId();
        $schedule->save();

        if ($request->has('employee')) {
            $schedule->employee()->sync($request->employee);
        }

        return response()->json([
            'success' => true,
            'message' => 'Interview schedule created successfully',
            'data' => $schedule->load(['jobApplication', 'employee'])
        ], 201);
    }

    /**
     * Display the specified interview schedule
     */
    public function showInterviewSchedule(Request $request, $id)
    {
        $schedule = InterviewSchedule::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->with(['jobApplication', 'employee'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $schedule
        ]);
    }

    /**
     * Update the specified interview schedule
     */
    public function updateInterviewSchedule(Request $request, $id)
    {
        $schedule = InterviewSchedule::where('created_by', $request->user()->creatorId())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'employee' => 'nullable|array',
            'employee.*' => 'exists:employees,id',
            'date' => 'sometimes|date',
            'time' => 'sometimes',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $schedule->fill($request->except('employee'));
        $schedule->save();

        if ($request->has('employee')) {
            $schedule->employee()->sync($request->employee);
        }

        return response()->json([
            'success' => true,
            'message' => 'Interview schedule updated successfully',
            'data' => $schedule->load(['jobApplication', 'employee'])
        ]);
    }

    /**
     * Remove the specified interview schedule
     */
    public function destroyInterviewSchedule(Request $request, $id)
    {
        $schedule = InterviewSchedule::where('created_by', $request->user()->creatorId())
            ->where('id', $id)
            ->firstOrFail();

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Interview schedule deleted successfully'
        ]);
    }
}
