<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage role')) {
            $roles = Role::where('created_by', Auth::user()->creatorId())->with('permissions')->get();
            return response()->json($roles);
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
        if (Auth::user()->can('create role')) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:roles,name,NULL,id,created_by,' . Auth::user()->creatorId(),
                'permissions' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $role = new Role();
            $role->name = $request->name;
            $role->created_by = Auth::user()->creatorId();
            $role->save();

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return response()->json($role->load('permissions'), 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if (Auth::user()->can('manage role')) {
            $role = Role::where('id', $id)->where('created_by', Auth::user()->creatorId())->with('permissions')->first();
            if($role) {
                 return response()->json($role);
            }
            return response()->json(['error' => __('Role not found.')], 404);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit role')) {
            $role = Role::where('id', $id)->where('created_by', Auth::user()->creatorId())->first();
            if(!$role){
                return response()->json(['error' => __('Role not found.')], 404);
            }

            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:roles,name,' . $role->id . ',id,created_by,' . Auth::user()->creatorId(),
                'permissions' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $role->name = $request->name;
            $role->save();

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            } else {
                $role->syncPermissions([]);
            }

            return response()->json($role->load('permissions'));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (Auth::user()->can('delete role')) {
             $role = Role::where('id', $id)->where('created_by', Auth::user()->creatorId())->first();
            if(!$role){
                return response()->json(['error' => __('Role not found.')], 404);
            }
            $role->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
