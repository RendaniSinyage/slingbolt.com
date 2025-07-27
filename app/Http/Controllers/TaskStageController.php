<?php

namespace App\Http\Controllers;

use App\Models\Utility;
use App\Models\TaskStage;
use App\Models\ProjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskStageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(\Auth::user()->can('manage project task stage'))
        {
            $task_stages = TaskStage::where('created_by', '=', \Auth::user()->creatorId())->orderBy('order','asc')->get();

            return view('task_stage.index',compact('task_stages'));
        }

        else
        {
            return redirect()->back()->with('errors', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if(\Auth::user()->can('create project task stage'))
        {
            $projectTypes = ProjectType::getTypes();
            return view('task_stage.create', compact('projectTypes'));
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Store a newly created resource via single form.
     */
    public function storingValue(Request $request)
    {
        if(\Auth::user()->can('create project task stage'))
        {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:20',
                    'type' => 'required|in:' . implode(',', array_keys(ProjectType::getTypes())),
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $order = TaskStage::where('created_by', \Auth::user()->creatorId())->count();
            
            $obj = new TaskStage();
            $obj->name       = $request->name;
            $obj->type       = $request->type;
            $obj->order      = $order + 1;
            $obj->color      = '#' . $request->color;
            $obj->created_by = \Auth::user()->creatorId();
            $obj->save();

            return redirect()->route('project-task-stages.index')->with('success', __('Project Task Stage Added Successfully'));
        }
        else
        {
            return redirect()->back()->with('errors', __('Permission Denied.'));
        }
    }

    /**
     * Store a newly created resource in storage (bulk creation).
     */
    public function store(Request $request)
    {
        if(\Auth::user()->can('create project task stage'))
        {
            $rules = [
                'stages' => 'required|present|array',
                'type' => 'required|in:' . implode(',', array_keys(ProjectType::getTypes())),
            ];

            $attributes = [];

            if($request->stages)
            {
                foreach($request->stages as $key => $val)
                {
                    $rules['stages.' . $key . '.name'] = 'required|max:255';
                    $attributes['stages.' . $key . '.name'] = __('Stage Name');
                }
            }

            $validator = Validator::make($request->all(), $rules, [], $attributes);
            if($validator->fails())
            {
                return redirect()->back()->with('errors', Utility::errorFormat($validator->getMessageBag()));
            }

            $arrStages = TaskStage::where('type', $request->type)
                                 ->where('created_by', \Auth::user()->creatorId())
                                 ->orderBy('order')
                                 ->pluck('name', 'id')
                                 ->all();
            $order = 0;

            foreach($request->stages as $key => $stage)
            {
                $obj = new TaskStage();
                if(isset($stage['id']) && !empty($stage['id']))
                {
                    $obj = TaskStage::find($stage['id']);
                    unset($arrStages[$obj->id]);
                }
                $obj->name       = $stage['name'];
                $obj->type       = $request->type;
                $obj->order      = $order++;
                $obj->color      = '#' . ($request->color ?? 'ffffff');
                $obj->created_by = \Auth::user()->creatorId();
                $obj->save();
            }

            // Delete removed stages of this type
            if($arrStages)
            {
                foreach($arrStages as $id => $name)
                {
                    TaskStage::find($id)->delete();
                }
            }

            return redirect()->route('project-task-stages.index')->with('success', __('Task Stages Added Successfully'));
        }
        else
        {
            return redirect()->back()->with('errors', __('Permission Denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskStage $taskStage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskStage $taskStage, $id)
    {
        $taskStage = TaskStage::findOrfail($id);
        if($taskStage->created_by == \Auth::user()->creatorId())
        {
            $projectTypes = ProjectType::getTypes();
            return view('task_stage.edit', compact('taskStage', 'projectTypes'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskStage $taskStage, $id)
    {
        $taskStage = TaskStage::findOrfail($id);
        if($taskStage->created_by == \Auth::user()->creatorId())
        {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:20',
                    'type' => 'required|in:' . implode(',', array_keys(ProjectType::getTypes())),
                ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->route('project-task-stages.index')->with('error', $messages->first());
            }

            $taskStage->name = $request->name;
            $taskStage->type = $request->type;
            $taskStage->color = '#' . $request->color;
            $taskStage->save();

            return redirect()->route('project-task-stages.index')->with('success', __('Task Stage successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskStage $taskStage, $id)
    {
        if(\Auth::user()->can('delete project task stage'))
        {
            $taskstage = TaskStage::find($id);
            
            // Check if any tasks are using this stage
            $tasksUsingStage = \App\Models\ProjectTask::where('stage_id', $id)->count();
            
            if($tasksUsingStage > 0)
            {
                return redirect()->back()->with('error', __('Cannot delete stage. Tasks are currently assigned to this stage.'));
            }
            
            $taskstage->delete();
            return redirect()->back()->with('success', __('Task Stage Successfully Deleted.'));
        }
        else
        {
            return redirect()->back()->with('errors', __('Permission Denied.'));
        }
    }

    /**
     * Update stage order.
     */
    public function order(Request $request)
    {
        $post = $request->all();
        foreach($post['order'] as $key => $item)
        {
            $status = TaskStage::where('id', '=', $item)->first();
            if($status) {
                $status->order = $key;
                $status->save();
            }
        }
    }

    /**
     * Get stages by project type (AJAX endpoint).
     */
    public function getStagesByType(Request $request)
    {
        if(\Auth::user()->can('manage project task stage'))
        {
            $type = $request->get('type', ProjectType::STANDARD);
            
            $stages = TaskStage::where('created_by', \Auth::user()->creatorId())
                              ->forProjectType($type)
                              ->orderBy('order')
                              ->get()
                              ->pluck('name', 'id');

            return response()->json($stages);
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }
}