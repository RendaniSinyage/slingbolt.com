<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\FormBuilder;
use App\Models\FormField;
use App\Models\FormFieldResponse;
use App\Models\FormResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormBuilderController extends Controller
{
    // Form Management
    public function index()
    {
        if (Auth::user()->can('manage form builder')) {
            $forms = FormBuilder::where('created_by', Auth::user()->creatorId())->get();
            return response()->json($forms);
        }
        return response()->json(['error' => __('Permission denied.')], 403);
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create form builder')) {
            $validator = Validator::make($request->all(), ['name' => 'required']);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
            $form = FormBuilder::create([
                'name' => $request->name,
                'code' => uniqid() . time(),
                'is_active' => $request->input('is_active', 0),
                'created_by' => Auth::user()->creatorId(),
            ]);
            return response()->json($form, 201);
        }
        return response()->json(['error' => __('Permission denied.')], 403);
    }

    public function show(FormBuilder $formBuilder)
    {
        if (Auth::user()->can('manage form builder') && $formBuilder->created_by == Auth::user()->creatorId()) {
            $formBuilder->load('form_field');
            return response()->json($formBuilder);
        }
        return response()->json(['error' => __('Permission Denied.')], 403);
    }

    public function update(Request $request, FormBuilder $formBuilder)
    {
        if (Auth::user()->can('edit form builder') && $formBuilder->created_by == Auth::user()->creatorId()) {
            $validator = Validator::make($request->all(), ['name' => 'required']);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
            $formBuilder->update($request->only('name', 'is_active'));
            return response()->json($formBuilder);
        }
        return response()->json(['error' => __('Permission Denied.')], 403);
    }

    public function destroy(FormBuilder $formBuilder)
    {
        if (Auth::user()->can('delete form builder') && $formBuilder->created_by == Auth::user()->creatorId()) {
            FormField::where('form_id', $formBuilder->id)->delete();
            FormResponse::where('form_id', $formBuilder->id)->delete();
            $formBuilder->delete();
            return response()->json(null, 204);
        }
        return response()->json(['error' => __('Permission Denied.')], 403);
    }

    // Form Field Management
    public function getFields(FormBuilder $form)
    {
        return response()->json($form->form_field);
    }

    public function addField(Request $request, FormBuilder $form)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required|in:' . implode(',', FormBuilder::$fieldTypes),
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $field = $form->form_field()->create([
            'name' => $request->name,
            'type' => $request->type,
            'created_by' => Auth::user()->creatorId(),
        ]);
        return response()->json($field, 201);
    }

    // Form Submission & Responses
    public function submitForm(Request $request, $code)
    {
        $form = FormBuilder::where('code', $code)->where('is_active', 1)->firstOrFail();

        $fields = $form->form_field()->pluck('name', 'id')->all();
        $rules = [];
        foreach ($form->form_field as $field) {
            // Basic validation, can be enhanced
            $rules['field.' . $field->id] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $response_data = [];
        foreach ($request->field as $field_id => $value) {
            $field_name = $fields[$field_id] ?? 'unknown_field';
            $response_data[$field_name] = $value;
        }

        FormResponse::create([
            'form_id' => $form->id,
            'response' => json_encode($response_data),
        ]);

        return response()->json(['message' => 'Form submitted successfully.']);
    }

    public function getResponses(FormBuilder $form)
    {
        if ($form->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied'], 403);
        }
        return response()->json($form->responses);
    }
}
