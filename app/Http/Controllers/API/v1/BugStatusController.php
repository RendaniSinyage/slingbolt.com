<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BugStatusResource;
use App\Models\BugStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BugStatusController extends Controller
{
    public function index()
    {
        $bugStatus = BugStatus::where('created_by', '=', Auth::user()->creatorId())->orderBy('order')->get();
        return BugStatusResource::collection($bugStatus);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required|max:20',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $all_status = BugStatus::where('created_by', Auth::user()->creatorId())->orderBy('id', 'DESC')->first();
        $status = new BugStatus();
        $status->title = $request->title;
        $status->created_by = Auth::user()->creatorId();
        $status->order = (!empty($all_status) ? ($all_status->order + 1) : 0);
        $status->save();

        event(new \App\Events\CreateBugStatus($request, $status));

        return new BugStatusResource($status);
    }

    public function show($id)
    {
        $bugStatus = BugStatus::where('created_by', Auth::user()->creatorId())->find($id);
        if ($bugStatus) {
            return new BugStatusResource($bugStatus);
        }
        return response()->json(['error' => __('Bug status not found.')], 404);
    }

    public function update(Request $request, $id)
    {
        $bugstatus = BugStatus::where('created_by', Auth::user()->creatorId())->find($id);
        if (!$bugstatus) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required|max:20',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $bugstatus->title = $request->title;
        $bugstatus->save();

        event(new \App\Events\UpdateBugStatus($request, $bugstatus));

        return new BugStatusResource($bugstatus);
    }

    public function destroy($id)
    {
        $bugstatus = BugStatus::where('created_by', Auth::user()->creatorId())->find($id);
        if (!$bugstatus) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        event(new \App\Events\DeleteBugStatus($bugstatus));
        $bugstatus->delete();

        return response()->json(null, 204);
    }

    public function order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $post = $request->all();
        foreach ($post['order'] as $key => $item) {
            $status = BugStatus::where('id', '=', $item)->first();
            if ($status) {
                $status->order = $key;
                $status->save();
            }
        }

        event(new \App\Events\OrderBugStatus($request));

        return response()->json(['message' => 'Order updated successfully.']);
    }
}
