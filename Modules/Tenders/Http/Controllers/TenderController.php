<?php

namespace Modules\Tenders\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Tenders\Entities\Tender;
use Modules\Tenders\Entities\TenderSetting;
use Modules\Tenders\Entities\DeniedTender;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\ProjectTask;
use App\Models\TaskFile;
use App\Models\Utility;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TenderController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $deniedTenderOcids = DeniedTender::where('created_by', $user->creatorId())->pluck('ocid');

        $tenders = $user->tenders()->whereNotIn('ocid', $deniedTenderOcids)->paginate(10);

        return view('tenders::index', compact('tenders'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('tenders::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('tenders::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('tenders::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function settings()
    {
        $settings = TenderSetting::where('created_by', Auth::user()->creatorId())->first();
        return view('tenders::settings', compact('settings'));
    }

    public function settingsStore(Request $request)
    {
        $request->validate([
            'categories' => 'nullable|array',
            'provinces' => 'nullable|array',
            'type' => 'nullable|string',
            'submission_type' => 'nullable|string',
        ]);

        TenderSetting::updateOrCreate(
            ['created_by' => Auth::user()->creatorId()],
            [
                'categories' => json_encode($request->categories),
                'provinces' => json_encode($request->provinces),
                'type' => $request->type,
                'submission_type' => $request->submission_type,
            ]
        );

        return redirect()->route('tenders.index')->with('success', 'Settings saved successfully.');
    }

    public function accept($id)
    {
        $tender = Tender::findOrFail($id);
        $user = Auth::user();

        $project = new Project();
        $project->project_name = $tender->title;
        $project->description = $tender->description;
        $project->start_date = date('Y-m-d');
        $project->end_date = $tender->tender_period_end_date;
        $project->created_by = $user->creatorId();
        $project->status = 'in_progress';
        $project->type = 'tender';
        $project->save();

        ProjectUser::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        Utility::project_task_stages($user->creatorId());

        // Create a default task for tender documents
        $task = new ProjectTask();
        $task->name = 'Tender Documents';
        $task->project_id = $project->id;
        $task->stage_id = \App\Models\TaskStage::where('created_by', $project->created_by)->first()->id;
        $task->save();

        // Fetch the full tender details to get the documents
        $response = Http::get('https://ocds-api.etenders.gov.za/api/OCDSReleases/release/' . $tender->ocid);
        if ($response->successful()) {
            $release = $response->json();
            $documents = $release['tender']['documents'] ?? [];

            foreach ($documents as $document) {
                try {
                    $fileContents = Http::get($document['url'])->body();
                    $fileName = basename($document['url']);
                    Storage::disk('public')->put('tasks/' . $fileName, $fileContents);

                    TaskFile::create([
                        'task_id' => $task->id,
                        'file' => 'tasks/' . $fileName,
                        'name' => $document['title'],
                        'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
                        'file_size' => strlen($fileContents),
                        'user_type' => 'User',
                        'created_by' => $user->id,
                    ]);
                } catch (\Exception $e) {
                    // Log the error and continue
                    \Log::error("Failed to download or save document for tender {$tender->ocid}: " . $e->getMessage());
                }
            }
        }

        return redirect()->route('projects.show', $project->id)->with('success', __('Tender accepted and project created successfully.'));
    }

    public function deny($id)
    {
        $tender = Tender::findOrFail($id);

        DeniedTender::create([
            'created_by' => Auth::user()->creatorId(),
            'ocid' => $tender->ocid,
        ]);


        return redirect()->route('tenders.index')->with('success', 'Tender denied.');
    }
}
