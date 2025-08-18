<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Projectstages;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectstagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage project stage')) {
            $projectstages = Projectstages::where('created_by', Auth::user()->creatorId())->orderBy('order')->get();
            return response()->json($projectstages);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('create project stage')) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:20',
                'color' => 'required|string|max:7',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $lastStage = Projectstages::where('created_by', Auth::user()->creatorId())->orderBy('id', 'DESC')->first();

            $stage = new Projectstages();
            $stage->name = $request->name;
            $stage->color = $request->color;
            $stage->created_by = Auth::user()->creatorId();
            $stage->order = ($lastStage ? $lastStage->order + 1 : 0);
            $stage->save();

            return response()->json($stage, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (Auth::user()->can('manage project stage')) {
            $stage = Projectstages::where('created_by', Auth::user()->creatorId())->find($id);
            if($stage) {
                return response()->json($stage);
            }
            return response()->json(['error' => __('Project Stage not found.')], 404);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit project stage')) {
            $stage = Projectstages::where('created_by', Auth::user()->creatorId())->find($id);
            if(!$stage){
                return response()->json(['error' => __('Project Stage not found.')], 404);
            }

            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:20',
                'color' => 'required|string|max:7',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $stage->name = $request->name;
            $stage->color = $request->color;
            $stage->save();

            return response()->json($stage);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (Auth::user()->can('delete project stage')) {
            $stage = Projectstages::where('created_by', Auth::user()->creatorId())->find($id);
            if(!$stage){
                return response()->json(['error' => __('Project Stage not found.')], 404);
            }

            if (Task::where('stage', $stage->id)->exists()) {
                 return response()->json(['error' => __('Project task already assign this stage , so please remove or move task to other project stage.')], 422);
            }

            $stage->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
