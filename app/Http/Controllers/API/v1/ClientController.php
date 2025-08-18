<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class ClientController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->can('manage client')) {
            $clients = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'client')->get();
            return ClientResource::collection($clients);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->can('create client')) {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|min:6',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $creator = User::find($user->creatorId());
            $total_clients = User::where('created_by', $user->creatorId())->where('type', 'client')->count();
            $plan = Plan::find($creator->plan);

            if ($total_clients < $plan->max_clients || $plan->max_clients == -1) {
                $role = Role::findByName('client');
                $client = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'type' => 'client',
                    'lang' => $creator->lang,
                    'created_by' => $user->creatorId(),
                    'email_verified_at' => date('Y-m-d H:i:s'),
                    'is_enable_login' => 1,
                ]);
                $client->assignRole($role);

                // Send Email
                $setings = Utility::settings();
                if (isset($setings['new_client']) && $setings['new_client'] == 1) {
                    $clientArr = [
                        'client_name' => $client->name,
                        'client_email' => $client->email,
                        'client_password' => $request->password,
                    ];
                    Utility::sendEmailTemplate('new_client', [$client->email], $clientArr);
                }

                return new ClientResource($client);
            } else {
                return response()->json(['error' => __('Your user limit is over, Please upgrade plan.')], 403);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function show(User $client)
    {
        $user = Auth::user();
        if ($user->can('show client') && $client->created_by == $user->creatorId()) {
            return new ClientResource($client);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function update(Request $request, User $client)
    {
        $user = Auth::user();
        if ($user->can('edit client') && $client->created_by == $user->creatorId()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $client->id,
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $client->name = $request->name;
            $client->email = $request->email;
            if ($request->has('password') && !empty($request->password)) {
                $client->password = Hash::make($request->password);
            }
            $client->save();

            return new ClientResource($client);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function destroy(User $client)
    {
        $user = Auth::user();
        if ($user->can('delete client') && $client->created_by == $user->creatorId()) {
            if ($client->clientDeals()->count() > 0 || $client->clientEstimations()->count() > 0 || $client->clientContracts()->count() > 0) {
                return response()->json(['error' => __('This client has associated deals, estimations, or contracts and cannot be deleted.')], 400);
            }
            $client->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function resetPassword(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->can('edit client')) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|confirmed|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $client = User::where('id', $id)->where('created_by', $user->creatorId())->where('type', 'client')->first();
            if ($client) {
                $client->password = Hash::make($request->password);
                $client->save();
                return response()->json(['message' => 'Client password updated successfully.']);
            } else {
                return response()->json(['error' => 'Client not found.'], 404);
            }
        } else {
            return response()->json(['error' => 'Permission denied.'], 403);
        }
    }
}
