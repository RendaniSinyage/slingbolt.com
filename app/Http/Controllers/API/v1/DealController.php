<?php

namespace App\Http\Controllers\API\v1;

use App\Events\CreateDeal;
use App\Events\UpdateDeal;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\Stage;
use App\Models\User;
use App\Models\UserDeal;
use App\Models\ClientDeal;
use Illuminate\Http\Request;
use App\Http\Resources\DealResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class DealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('manage deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $user = Auth::user();
        $pipeline_id = $request->input('pipeline_id', $user->default_pipeline);

        if (!$pipeline_id) {
            $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->first();
        } else {
            $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->where('id', '=', $pipeline_id)->first();
        }

        if (!$pipeline) {
            return response()->json(['data' => [], 'message' => 'No pipeline found.']);
        }

        if ($user->type == 'client') {
            $id_deals = $user->clientDeals->pluck('id');
        } else {
            $id_deals = $user->deals->pluck('id');
        }

        $query = Deal::whereIn('id', $id_deals)->where('pipeline_id', '=', $pipeline->id);

        if ($request->has('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        $deals = $query->orderBy('order')->get();

        return DealResource::collection($deals);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'required',
                'clients' => 'required|array',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if ($user->default_pipeline) {
            $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->where('id', '=', $user->default_pipeline)->first();
        } else {
            $pipeline = Pipeline::where('created_by', '=', $user->ownerId())->first();
        }

        if (!$pipeline) {
            return response()->json(['error' => 'No pipeline found.'], 404);
        }

        $stage = Stage::where('pipeline_id', '=', $pipeline->id)->first();

        if (!$stage) {
            return response()->json(['error' => 'Please create a stage for this pipeline.'], 404);
        }

        $deal = new Deal();
        $deal->name = $request->name;
        $deal->phone = $request->phone;
        $deal->price = $request->price ?? 0;
        $deal->pipeline_id = $pipeline->id;
        $deal->stage_id = $stage->id;
        $deal->status = 'Active';
        $deal->created_by = $user->ownerId();
        $deal->save();
        event(new CreateDeal($deal, $request));

        foreach ($request->clients as $client_id) {
            ClientDeal::create([
                'deal_id' => $deal->id,
                'client_id' => $client_id,
            ]);
        }

        $assignees = [$user->id];
        if ($user->type != 'company') {
           $assignees[] = $user->ownerId();
        }

        foreach ($assignees as $assignee_id) {
            UserDeal::create([
                'user_id' => $assignee_id,
                'deal_id' => $deal->id,
            ]);
        }

        return (new DealResource($deal->load('pipeline', 'stage', 'users', 'clients')))->additional(['message' => 'Deal successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Deal  $deal
     * @return \Illuminate\Http\Response
     */
    public function show(Deal $deal)
    {
        if (Gate::denies('manage deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new DealResource($deal->load('pipeline', 'stage', 'users', 'clients', 'discussions', 'files', 'tasks'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Deal  $deal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'name' => 'sometimes|required|max:20',
                'pipeline_id' => 'sometimes|required|exists:pipelines,id',
                'stage_id' => 'sometimes|required|exists:stages,id',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deal->fill($request->only([
            'name', 'phone', 'price', 'pipeline_id', 'stage_id', 'notes'
        ]));

        if($request->has('sources')) {
            $deal->sources = implode(",", array_filter($request->sources));
        }
        if($request->has('products')) {
            $deal->products = implode(",", array_filter($request->products));
        }

        $deal->save();
        event(new UpdateDeal($deal, $request));

        return (new DealResource($deal->load('pipeline', 'stage', 'users', 'clients')))->additional(['message' => 'Deal successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Deal  $deal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Deal $deal)
    {
        if (Gate::denies('delete deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $deal->delete();

        return response()->json(['message' => 'Deal successfully deleted.']);
    }

    public function userUpdate(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'users' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $users = array_filter($request->users);
        foreach ($users as $user_id) {
            UserDeal::create([
                'deal_id' => $deal->id,
                'user_id' => $user_id,
            ]);
        }

        return (new DealResource($deal->fresh()->load('users')))->additional(['message' => 'Users successfully added.']);
    }

    public function userDestroy(Deal $deal, User $user)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        UserDeal::where('deal_id', '=', $deal->id)->where('user_id', '=', $user->id)->delete();

        return (new DealResource($deal->fresh()->load('users')))->additional(['message' => 'User successfully removed.']);
    }

    public function clientUpdate(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'clients' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $clients = array_filter($request->clients);
        foreach ($clients as $client_id) {
            ClientDeal::create([
                'deal_id' => $deal->id,
                'client_id' => $client_id,
            ]);
        }

        return (new DealResource($deal->fresh()->load('clients')))->additional(['message' => 'Clients successfully added.']);
    }

    public function clientDestroy(Deal $deal, \App\Models\Client $client)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        ClientDeal::where('deal_id', '=', $deal->id)->where('client_id', '=', $client->id)->delete();

        return (new DealResource($deal->fresh()->load('clients')))->additional(['message' => 'Client successfully removed.']);
    }

    public function productUpdate(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'products' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $products = array_filter($request->products);
        $old_products = explode(',', $deal->products);
        $deal->products = implode(',', array_unique(array_merge($old_products, $products)));
        $deal->save();

        return (new DealResource($deal->fresh()->load('products')))->additional(['message' => 'Products successfully updated.']);
    }

    public function productDestroy(Deal $deal, \App\Models\ProductService $product)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $products = explode(',', $deal->products);
        $products = array_diff($products, [$product->id]);
        $deal->products = implode(',', $products);
        $deal->save();

        return (new DealResource($deal->fresh()->load('products')))->additional(['message' => 'Product successfully removed.']);
    }

    public function sourceUpdate(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'sources' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $sources = array_filter($request->sources);
        $deal->sources = implode(',', $sources);
        $deal->save();

        return (new DealResource($deal->fresh()->load('sources')))->additional(['message' => 'Sources successfully updated.']);
    }

    public function sourceDestroy(Deal $deal, \App\Models\Source $source)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $sources = explode(',', $deal->sources);
        $sources = array_diff($sources, [$source->id]);
        $deal->sources = implode(',', $sources);
        $deal->save();

        return (new DealResource($deal->fresh()->load('sources')))->additional(['message' => 'Source successfully removed.']);
    }

    public function fileUpload(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), ['file' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx|max:20480']);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $file_name = $request->file->getClientOriginalName();
        $file_path = $deal->id . "_" . md5(time()) . "_" . $request->file->getClientOriginalName();

        $request->file->storeAs('deal_files', $file_path);

        $file = \App\Models\DealFile::create([
            'deal_id' => $deal->id,
            'file_name' => $file_name,
            'file_path' => $file_path,
        ]);

        return (new \App\Http\Resources\DealFileResource($file))->additional(['message' => 'File successfully uploaded.']);
    }

    public function fileDownload(Deal $deal, $file_id)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $file = \App\Models\DealFile::find($file_id);
        if (!$file) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        $file_path = storage_path('app/deal_files/' . $file->file_path);

        return \Response::download($file_path, $file->file_name);
    }

    public function fileDelete(Deal $deal, $file_id)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $file = \App\Models\DealFile::find($file_id);
        if (!$file) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        $path = storage_path('app/deal_files/' . $file->file_path);
        if (file_exists($path)) {
            \File::delete($path);
        }
        $file->delete();

        return response()->json(['message' => 'File successfully deleted.']);
    }

    public function noteStore(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'note' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $deal->notes = $request->note;
        $deal->save();

        return (new DealResource($deal->fresh()))->additional(['message' => 'Note successfully saved.']);
    }

    public function taskStore(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'priority' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $task = \App\Models\DealTask::create([
            'deal_id' => $deal->id,
            'name' => $request->name,
            'date' => $request->date,
            'time' => $request->time,
            'priority' => $request->priority,
            'status' => $request->status,
        ]);

        return (new \App\Http\Resources\DealTaskResource($task))->additional(['message' => 'Task successfully created.']);
    }

    public function taskUpdate(Request $request, Deal $deal, \App\Models\DealTask $task)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'sometimes|required',
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required',
            'priority' => 'sometimes|required',
            'status' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $task->fill($request->all())->save();

        return (new \App\Http\Resources\DealTaskResource($task))->additional(['message' => 'Task successfully updated.']);
    }

    public function taskDestroy(Deal $deal, \App\Models\DealTask $task)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task successfully deleted.']);
    }

    public function discussionStore(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), ['comment' => 'required']);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $discussion = \App\Models\DealDiscussion::create([
            'deal_id' => $deal->id,
            'comment' => $request->comment,
            'created_by' => Auth::user()->id,
        ]);

        return (new \App\Http\Resources\DealDiscussionResource($discussion))->additional(['message' => 'Discussion successfully added.']);
    }

    public function callStore(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'subject' => 'required',
            'call_type' => 'required',
            'user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $call = \App\Models\DealCall::create([
            'deal_id' => $deal->id,
            'subject' => $request->subject,
            'call_type' => $request->call_type,
            'duration' => $request->duration,
            'user_id' => $request->user_id,
            'description' => $request->description,
            'call_result' => $request->call_result,
        ]);

        return (new \App\Http\Resources\DealCallResource($call))->additional(['message' => 'Call successfully created.']);
    }

    public function callUpdate(Request $request, Deal $deal, \App\Models\DealCall $call)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'subject' => 'sometimes|required',
            'call_type' => 'sometimes|required',
            'user_id' => 'sometimes|required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $call->fill($request->all())->save();

        return (new \App\Http\Resources\DealCallResource($call))->additional(['message' => 'Call successfully updated.']);
    }

    public function callDestroy(Deal $deal, \App\Models\DealCall $call)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $call->delete();

        return response()->json(['message' => 'Call successfully deleted.']);
    }

    public function emailStore(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'to' => 'required|email',
            'subject' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $email = \App\Models\DealEmail::create([
            'deal_id' => $deal->id,
            'to' => $request->to,
            'subject' => $request->subject,
            'description' => $request->description,
        ]);

        return (new \App\Http\Resources\DealEmailResource($email))->additional(['message' => 'Email successfully created.']);
    }

    public function labelStore(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'labels' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $labels = array_filter($request->labels);
        $deal->labels = implode(',', $labels);
        $deal->save();

        return (new DealResource($deal->fresh()->load('labels')))->additional(['message' => 'Labels successfully updated.']);
    }

    public function permissionStore(Request $request, Deal $deal)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($deal->created_by != Auth::user()->ownerId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make($request->all(), [
            'permissions' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        foreach($request->permissions as $client_id => $permissions) {
            $client = \App\Models\Client::find($client_id);
            if($client && $client->created_by == Auth::user()->ownerId()) {
                $client->updatePermissions($permissions);
            }
        }

        return (new DealResource($deal->fresh()->load('clients')))->additional(['message' => 'Permissions successfully updated.']);
    }

    public function order(Request $request)
    {
        if (Gate::denies('edit deal')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $user = Auth::user();
        $ownerId = $user->ownerId();

        $deals = Deal::where('created_by', '=', $ownerId)->get();

        foreach ($deals as $deal) {
            $deal->order = 0;
            $deal->save();
        }

        foreach ($request->all() as $key => $items) {
            if ($key == 'owner' || $key == 'usr') {
                continue;
            }

            foreach ($items as $item) {
                $deal = Deal::find($item);
                if ($deal && $deal->created_by == $ownerId) {
                    $deal->stage_id = $key;
                    $deal->save();
                }
            }
        }

        return response()->json(['message' => 'Deals successfully ordered.']);
    }
}
