<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobApplicationNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/jobs",
     *     summary="Get all jobs",
     *     tags={"Recruitment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Job")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     )
     * )
     */
    public function index()
    {
        if (!Auth::user()->can('manage job')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $jobs = Job::where('created_by', '=', Auth::user()->creatorId())->with(['branches', 'createdBy'])->get();
        return response()->json($jobs);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/jobs",
     *     summary="Create a new job",
     *     tags={"Recruitment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Job")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Job created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Job")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('create job')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'required',
            'branch' => 'required',
            'category' => 'required',
            'skill' => 'required',
            'position' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $job = new Job();
        $job->title = $request->title;
        $job->branch = $request->branch;
        $job->category = $request->category;
        $job->skill = $request->skill;
        $job->position = $request->position;
        $job->status = $request->status;
        $job->start_date = $request->start_date;
        $job->end_date = $request->end_date;
        $job->description = $request->description;
        $job->requirement = $request->requirement;
        $job->code = uniqid();
        $job->applicant = !empty($request->applicant) ? implode(',', $request->applicant) : '';
        $job->visibility = !empty($request->visibility) ? implode(',', $request->visibility) : '';
        $job->custom_question = !empty($request->custom_question) ? implode(',', $request->custom_question) : '';
        $job->created_by = Auth::user()->creatorId();
        $job->save();

        return response()->json($job, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jobs/{id}",
     *     summary="Get a specific job",
     *     tags={"Recruitment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Job")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function show(Job $job)
    {
        if (!Auth::user()->can('manage job')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        return response()->json($job->load(['branches', 'createdBy']));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/jobs/{id}",
     *     summary="Update a job",
     *     tags={"Recruitment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Job")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Job")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Job $job)
    {
        if (!Auth::user()->can('edit job')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'required',
            'branch' => 'required',
            'category' => 'required',
            'skill' => 'required',
            'position' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $job->title = $request->title;
        $job->branch = $request->branch;
        $job->category = $request->category;
        $job->skill = $request->skill;
        $job->position = $request->position;
        $job->status = $request->status;
        $job->start_date = $request->start_date;
        $job->end_date = $request->end_date;
        $job->description = $request->description;
        $job->requirement = $request->requirement;
        $job->applicant = !empty($request->applicant) ? implode(',', $request->applicant) : '';
        $job->visibility = !empty($request->visibility) ? implode(',', $request->visibility) : '';
        $job->custom_question = !empty($request->custom_question) ? implode(',', $request->custom_question) : '';
        $job->save();

        return response()->json($job);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/jobs/{id}",
     *     summary="Delete a job",
     *     tags={"Recruitment"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Job deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     )
     * )
     */
    public function destroy(Job $job)
    {
        if (!Auth::user()->can('delete job')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $application = JobApplication::where('job', $job->id)->get()->pluck('id');
        JobApplicationNote::whereIn('application_id', $application)->delete();
        JobApplication::where('job', $job->id)->delete();
        $job->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jobs/career/{id}/{lang}",
     *     summary="Get jobs for a specific company",
     *     tags={"Recruitment"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function career($id, $lang)
    {
        \Session::put('lang', $lang);
        App::setLocale($lang);

        $jobs = Job::where('created_by', $id)->where('status', 'active')->with(['branches'])->get();

        return response()->json($jobs);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/jobs/requirement/{code}/{lang}",
     *     summary="Get job requirements",
     *     tags={"Recruitment"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="lang",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function jobRequirement($code, $lang)
    {
        \Session::put('lang', $lang);
        App::setLocale($lang);

        $job = Job::where('code', $code)->where('status', 'active')->first();

        if (!$job) {
            return response()->json(['error' => __('Job not found')], 404);
        }

        return response()->json($job);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/jobs/apply/{code}/{lang}",
     *     summary="Apply for a job",
     *     tags={"Recruitment"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *      @OA\Parameter(
     *         name="lang",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="phone", type="string"),
     *                 @OA\Property(property="profile", type="string", format="binary"),
     *                 @OA\Property(property="resume", type="string", format="binary"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application submitted successfully"
     *     )
     * )
     */
    public function jobApplyData(Request $request, $code)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'profile' => 'mimes:jpeg,png,jpg,gif,svg|max:20480',
            'resume' => 'mimes:jpeg,png,jpg,gif,svg,pdf,doc,zip|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $job = Job::where('code', $code)->first();
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        $profile = null;
        if ($request->hasFile('profile')) {
            $profile = time() . '_' . $request->file('profile')->getClientOriginalName();
            $request->file('profile')->storeAs('public/job/profile', $profile);
        }

        $resume = null;
        if ($request->hasFile('resume')) {
            $resume = time() . '_' . $request->file('resume')->getClientOriginalName();
            $request->file('resume')->storeAs('public/job/resume', $resume);
        }

        $jobApplication = new JobApplication();
        $jobApplication->job = $job->id;
        $jobApplication->name = $request->name;
        $jobApplication->email = $request->email;
        $jobApplication->phone = $request->phone;
        $jobApplication->profile = $profile;
        $jobApplication->resume = $resume;
        $jobApplication->cover_letter = $request->cover_letter;
        $jobApplication->dob = $request->dob;
        $jobApplication->gender = $request->gender;
        $jobApplication->country = $request->country;
        $jobApplication->state = $request->state;
        $jobApplication->city = $request->city;
        $jobApplication->custom_question = json_encode($request->question);
        $jobApplication->created_by = $job->created_by;
        $jobApplication->stage = \App\Models\JobStage::where('created_by', $job->created_by)->first()->id;
        $jobApplication->save();

        return response()->json(['success' => __('Job application successfully sent')]);
    }
}
