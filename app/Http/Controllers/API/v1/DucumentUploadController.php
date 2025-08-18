<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\DucumentUpload;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class DucumentUploadController extends Controller
{
    public function index()
    {
        if (Auth::user()->can('manage document')) {
            $user = Auth::user();
            if ($user->type == 'company') {
                $documents = DucumentUpload::where('created_by', $user->creatorId())->get();
            } else {
                $userRole = $user->roles->first();
                $documents = DucumentUpload::where('created_by', $user->creatorId())
                    ->where(function ($query) use ($userRole) {
                        $query->where('role', $userRole->id)
                              ->orWhere('role', '0');
                    })->get();
            }
            return response()->json($documents);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create document')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'document' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx|max:20480',
                'role' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $document = new DucumentUpload();
            $document->name = $request->name;

            $fileName = time() . "_" . $request->document->getClientOriginalName();
            $path = Utility::upload_file($request, 'document', $fileName, 'uploads/documentUpload', []);
            if ($path['flag'] == 1) {
                $document->document = $path['url'];
            } else {
                return response()->json(['error' => __($path['msg'])], 500);
            }

            $document->role = $request->role;
            $document->description = $request->description;
            $document->created_by = Auth::user()->creatorId();
            $document->save();

            return response()->json($document, 201);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function show($id)
    {
        if (Auth::user()->can('manage document')) {
            $document = DucumentUpload::where('created_by', Auth::user()->creatorId())->find($id);
            if ($document) {
                return response()->json($document);
            }
            return response()->json(['error' => 'Document not found.'], 404);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit document')) {
            $validator = Validator::make($request->all(), ['name' => 'required']);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $document = DucumentUpload::find($id);
            if (!$document || $document->created_by != Auth::user()->creatorId()) {
                return response()->json(['error' => 'Document not found or permission denied.'], 404);
            }

            $document->name = $request->name;
            $document->role = $request->role;
            $document->description = $request->description;

            if ($request->hasFile('document')) {
                $validator = Validator::make($request->all(), ['document' => 'required|mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx|max:20480']);
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->first()], 422);
                }

                $fileName = time() . "_" . $request->document->getClientOriginalName();
                $path = Utility::upload_file($request, 'document', $fileName, 'uploads/documentUpload', []);
                if ($path['flag'] == 1) {
                    // Optionally delete old file
                    // Storage::delete($document->document);
                    $document->document = $path['url'];
                } else {
                    return response()->json(['error' => __($path['msg'])], 500);
                }
            }

            $document->save();
            return response()->json($document);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete document')) {
            $document = DucumentUpload::find($id);
            if (!$document || $document->created_by != Auth::user()->creatorId()) {
                return response()->json(['error' => 'Document not found or permission denied.'], 404);
            }

            // Optionally delete file from storage
            // Storage::delete($document->document);

            $document->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }
}
