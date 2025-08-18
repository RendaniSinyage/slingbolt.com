<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomFieldResource;
use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomFieldController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage constant custom field')) {
            $custom_fields = CustomField::where('created_by', '=', Auth::user()->creatorId())->get();
            return CustomFieldResource::collection($custom_fields);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create constant custom field')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:40',
                    'type' => 'required',
                    'module' => 'required',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $custom_field = new CustomField();
            $custom_field->name = $request->name;
            $custom_field->type = $request->type;
            $custom_field->module = $request->module;
            $custom_field->created_by = Auth::user()->creatorId();
            $custom_field->save();

            return new CustomFieldResource($custom_field);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function show(CustomField $customField)
    {
        if (Auth::user()->can('manage constant custom field') && $customField->created_by == Auth::user()->creatorId()) {
            return new CustomFieldResource($customField);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function update(Request $request, CustomField $customField)
    {
        if (Auth::user()->can('edit constant custom field') && $customField->created_by == Auth::user()->creatorId()) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:40',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $customField->name = $request->name;
            $customField->save();

            return new CustomFieldResource($customField);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function destroy(CustomField $customField)
    {
        if (Auth::user()->can('delete constant custom field') && $customField->created_by == Auth::user()->creatorId()) {
            $customField->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }
}
