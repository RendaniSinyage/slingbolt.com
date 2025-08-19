<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage permission')) {
            // In a multi-tenant system, permissions might not be tied to a created_by ID
            // as they are often globally defined by the application.
            // We will return all permissions.
            $permissions = Permission::all();
            return PermissionResource::collection($permissions);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
