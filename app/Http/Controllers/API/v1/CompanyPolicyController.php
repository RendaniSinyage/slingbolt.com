<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\CompanyPolicy;
use Illuminate\Http\Request;
use App\Http\Resources\CompanyPolicyResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class CompanyPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage company policy')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $companyPolicy = CompanyPolicy::where('created_by', '=', Auth::user()->creatorId())->with('branches')->get();

        return CompanyPolicyResource::collection($companyPolicy);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create company policy')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'branch' => 'required',
                'title' => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $policy = new CompanyPolicy();
        $policy->branch = $request->branch;
        $policy->title = $request->title;
        $policy->description = $request->description;
        $policy->created_by = Auth::user()->creatorId();

        if ($request->hasFile('attachment')) {
            $filenameWithExt = $request->file('attachment')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('attachment')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir = 'uploads/companyPolicy/';
            $path = $request->file('attachment')->storeAs($dir, $fileNameToStore);
            $policy->attachment = $fileNameToStore;
        }

        $policy->save();

        return (new CompanyPolicyResource($policy->load('branches')))->additional(['message' => 'Company policy successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CompanyPolicy  $companyPolicy
     * @return \Illuminate\Http\Response
     */
    public function show(CompanyPolicy $companyPolicy)
    {
        if (Gate::denies('manage company policy')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($companyPolicy->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new CompanyPolicyResource($companyPolicy->load('branches'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CompanyPolicy  $companyPolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CompanyPolicy $companyPolicy)
    {
        if (Gate::denies('edit company policy')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($companyPolicy->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'branch' => 'sometimes|required',
                'title' => 'sometimes|required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $companyPolicy->update($request->all());

        if ($request->hasFile('attachment')) {
            $filenameWithExt = $request->file('attachment')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('attachment')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;
            $dir = 'uploads/companyPolicy/';
            $path = $request->file('attachment')->storeAs($dir, $fileNameToStore);
            $companyPolicy->attachment = $fileNameToStore;
            $companyPolicy->save();
        }

        return (new CompanyPolicyResource($companyPolicy->load('branches')))->additional(['message' => 'Company policy successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompanyPolicy  $companyPolicy
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompanyPolicy $companyPolicy)
    {
        if (Gate::denies('delete company policy')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($companyPolicy->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $companyPolicy->delete();

        return response()->json(['message' => 'Company policy successfully deleted.']);
    }
}
