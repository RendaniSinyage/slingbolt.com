<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Services\CompanyCleanupService;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->can('manage user')) {
            if ($user->type == 'super admin') {
                $users = User::where('created_by', $user->creatorId())->where('type', 'company')->get();
            } else {
                $users = User::where('created_by', $user->creatorId())->where('type', '!=', 'client')->get();
            }
            return response()->json($users);
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
        if (Auth::user()->can('create user')) {
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:120',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'role' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $user = Auth::user();
            $plan = Plan::find($user->plan);
            $total_user = $user->countUsers();

            if ($total_user >= $plan->max_users && $plan->max_users != -1) {
                return response()->json(['error' => __('Your user limit is over, Please upgrade plan.')], 403);
            }

            $role = Role::find($request->role);
            if (!$role || $role->created_by != $user->creatorId()) {
                return response()->json(['error' => __('Invalid role selected.')], 400);
            }

            $newUser = new User();
            $newUser->name = $request->name;
            $newUser->email = $request->email;
            $newUser->password = Hash::make($request->password);
            $newUser->type = $role->name;
            $newUser->lang = $user->lang;
            $newUser->created_by = $user->creatorId();
            $newUser->save();

            $newUser->assignRole($role);

            if ($newUser->type != 'client') {
                Utility::employeeDetails($newUser->id, $user->creatorId());
            }

            return response()->json($newUser, 201);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        $currentUser = Auth::user();
        if ($currentUser->can('manage user') && ($user->created_by == $currentUser->creatorId() || $currentUser->type == 'super admin')) {
            return response()->json($user);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        if ($currentUser->can('edit user') && ($user->created_by == $currentUser->creatorId() || $currentUser->type == 'super admin')) {
             $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:120',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'role' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $role = Role::find($request->role);
            if (!$role || $role->created_by != $currentUser->creatorId()) {
                return response()->json(['error' => __('Invalid role selected.')], 400);
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->type = $role->name;
            $user->save();

            $user->syncRoles([$role->id]);

            return response()->json($user);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        $currentUser = Auth::user();
        if ($currentUser->can('delete user') && ($user->created_by == $currentUser->creatorId() || $currentUser->type == 'super admin')) {
            if ($user->type == 'company') {
                 try {
                    DB::transaction(function () use ($user) {
                        CompanyCleanupService::cascadeDeleteCompanyData($user->id);
                        $user->delete();
                    });
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Error deleting company: ' . $e->getMessage()], 500);
                }
            } else {
                $user->delete();
            }

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }
}
