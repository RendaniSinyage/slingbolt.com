<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;

use App\Models\Pipeline;
use App\Models\Stage;
use App\Models\UserDeal;
use Illuminate\Support\Facades\Validator;
use App\Models\DealDiscussion;
use App\Models\DealFile;
use App\Models\ClientDeal;
use App\Models\DealTask;
use App\Models\ActivityLog;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $deals = Deal::select('deals.*')
            ->join('user_deals', 'user_deals.deal_id', '=', 'deals.id')
            ->where('user_deals.user_id', '=', $user->id)
            ->orderBy('deals.order')
            ->get();

        return response()->json($deals);
    }

    public function show($id)
    {
        $user = request()->user();
        $deal = Deal::with(['users', 'products', 'sources'])
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->find($id);

        if (!$deal) {
            return response()->json(['error' => 'Deal not found or you do not have permission to view it'], 404);
        }

        return response()->json($deal);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make(
            $request->all(), [
                               'name' => 'required',
                               'price' => 'nullable|numeric',
                           ]
        );

        if($validator->fails())
        {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        if($user->default_pipeline)
        {
            $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->where('id', '=', $user->default_pipeline)->first();
            if(!$pipeline)
            {
                $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->first();
            }
        }
        else
        {
            $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->first();
        }

        if(empty($pipeline))
        {
            return response()->json(['error' => 'Please create a pipeline first.'], 400);
        }

        $stage = Stage::where('pipeline_id', '=', $pipeline->id)->first();
        if(empty($stage))
        {
            return response()->json(['error' => 'Please create a stage for this pipeline.'], 400);
        }

        $deal = new Deal();
        $deal->name  = $request->name;
        $deal->price = $request->price ?? 0;
        $deal->pipeline_id = $pipeline->id;
        $deal->stage_id    = $stage->id;
        $deal->status      = 'Active';
        $deal->created_by  = $user->ownerId();
        $deal->save();

        UserDeal::create([
            'user_id' => $user->id,
            'deal_id' => $deal->id,
        ]);

        return response()->json($deal, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $deal = Deal::where('created_by', '=', $user->ownerId())->find($id);

        if (!$deal) {
            return response()->json(['error' => 'Deal not found or you do not have permission to edit it'], 404);
        }

        $validator = Validator::make(
            $request->all(), [
                               'name' => 'required',
                               'price' => 'nullable|numeric',
                           ]
        );

        if($validator->fails())
        {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $deal->name  = $request->name;
        $deal->price = $request->price ?? 0;
        $deal->save();

        return response()->json($deal);
    }

    public function destroy($id)
    {
        $user = request()->user();
        $deal = Deal::where('created_by', '=', $user->ownerId())->find($id);

        if (!$deal) {
            return response()->json(['error' => 'Deal not found or you do not have permission to delete it'], 404);
        }

        DealDiscussion::where('deal_id', '=', $deal->id)->delete();
        DealFile::where('deal_id', '=', $deal->id)->delete();
        ClientDeal::where('deal_id', '=', $deal->id)->delete();
        UserDeal::where('deal_id', '=', $deal->id)->delete();
        DealTask::where('deal_id', '=', $deal->id)->delete();
        ActivityLog::where('deal_id', '=', $deal->id)->delete();

        $deal->delete();

        return response()->json(['success' => 'Deal successfully deleted.'], 200);
    }
}
