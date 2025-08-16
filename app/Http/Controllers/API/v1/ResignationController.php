<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Resignation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResignationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage resignation')) {
            if (Auth::user()->type == 'Employee') {
                $employee = Auth::user()->employee;
                $resignations = Resignation::where('created_by', Auth::user()->creatorId())->where('employee_id', $employee->id)->with('employee')->get();
            } else {
                $resignations = Resignation::where('created_by', Auth::user()->creatorId())->with('employee')->get();
            }
            return response()->json($resignations);
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
        if (Auth::user()->can('create resignation')) {
            $validator = \Validator::make($request->all(), [
                'notice_date' => 'required|date',
                'resignation_date' => 'required|date',
                'description' => 'nullable|string',
                'employee_id' => 'required_if:auth_user_type,company,HR|exists:employees,id',
            ]);

            $validator->addImplicitExtension('auth_user_type', function($attribute, $value, $parameters) {
                return Auth::user()->type;
            });

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $resignation = new Resignation();
            if (Auth::user()->type == 'Employee') {
                $resignation->employee_id = Auth::user()->employee->id;
            } else {
                $resignation->employee_id = $request->employee_id;
            }
            $resignation->notice_date = $request->notice_date;
            $resignation->resignation_date = $request->resignation_date;
            $resignation->description = $request->description;
            $resignation->created_by = Auth::user()->creatorId();
            $resignation->save();

            return response()->json($resignation, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Resignation  $resignation
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Resignation $resignation)
    {
        if (Auth::user()->can('manage resignation') && $resignation->created_by == Auth::user()->creatorId()) {
            return response()->json($resignation->load('employee'));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Resignation  $resignation
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Resignation $resignation)
    {
        if (Auth::user()->can('edit resignation') && $resignation->created_by == Auth::user()->creatorId()) {
            $validator = \Validator::make($request->all(), [
                'notice_date' => 'required|date',
                'resignation_date' => 'required|date',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $resignation->notice_date = $request->notice_date;
            $resignation->resignation_date = $request->resignation_date;
            $resignation->description = $request->description;
            $resignation->save();

            return response()->json($resignation);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Resignation  $resignation
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Resignation $resignation)
    {
        if (Auth::user()->can('delete resignation') && $resignation->created_by == Auth::user()->creatorId()) {
            $resignation->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
