<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage contract')) {
            $query = Contract::where('created_by', Auth::user()->creatorId());

            if(Auth::user()->type == 'client'){
                $query->where('client_name', Auth::user()->id);
            }

            $contracts = $query->with(['clients', 'projects', 'types'])->get();
            return response()->json($contracts);
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
        if (Auth::user()->can('create contract')) {
            $validator = \Validator::make($request->all(), [
                'client_name' => 'required|exists:users,id',
                'subject' => 'required|string|max:255',
                'project_id' => 'nullable|exists:projects,id',
                'type' => 'required|exists:contract_types,id',
                'value' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $contract = new Contract();
            $contract->client_name = $request->client_name;
            $contract->subject = $request->subject;
            $contract->project_id = $request->project_id;
            $contract->type = $request->type;
            $contract->value = $request->value;
            $contract->start_date = $request->start_date;
            $contract->end_date = $request->end_date;
            $contract->description = $request->description;
            $contract->created_by = Auth::user()->creatorId();
            $contract->save();

            return response()->json($contract, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Contract $contract)
    {
        if (Auth::user()->can('show contract') && $contract->created_by == Auth::user()->creatorId()) {
            return response()->json($contract->load(['clients', 'projects', 'types']));
        }

        if (Auth::user()->type == 'client' && $contract->client_name == Auth::user()->id){
            return response()->json($contract->load(['clients', 'projects', 'types']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Contract $contract)
    {
        if (Auth::user()->can('edit contract') && $contract->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'client_name' => 'required|exists:users,id',
                'subject' => 'required|string|max:255',
                'project_id' => 'nullable|exists:projects,id',
                'type' => 'required|exists:contract_types,id',
                'value' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $contract->update($request->all());

            return response()->json($contract);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contract  $contract
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Contract $contract)
    {
        if (Auth::user()->can('delete contract') && $contract->created_by == Auth::user()->creatorId()) {
            $contract->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
