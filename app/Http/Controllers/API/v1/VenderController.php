<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\VenderResource;
use App\Models\Vender;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VenderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage vender')) {
            $venders = Vender::where('created_by', Auth::user()->creatorId())->get();
            return VenderResource::collection($venders);
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
        if (Auth::user()->can('create vender')) {
            $rules = [
                'name' => 'required|string|max:255',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('venders')->where(function ($query) {
                        return $query->where('created_by', Auth::user()->creatorId());
                    })
                ],
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $user = Auth::user();
            $creator = User::find($user->creatorId());
            $plan = Plan::find($creator->plan);
            $total_venders = $user->countVenders();

            if ($total_venders >= $plan->max_venders && $plan->max_venders != -1) {
                return response()->json(['error' => __('Your user limit is over, Please upgrade plan.')], 403);
            }

            $vender = new Vender();
            $vender->vender_id = $this->venderNumber();
            $vender->name = $request->name;
            $vender->contact = $request->contact;
            $vender->email = $request->email;
            $vender->tax_number = $request->tax_number;
            $vender->created_by = $user->creatorId();
            $vender->billing_name = $request->billing_name;
            $vender->billing_country = $request->billing_country;
            $vender->billing_state = $request->billing_state;
            $vender->billing_city = $request->billing_city;
            $vender->billing_phone = $request->billing_phone;
            $vender->billing_zip = $request->billing_zip;
            $vender->billing_address = $request->billing_address;
            $vender->shipping_name = $request->shipping_name;
            $vender->shipping_country = $request->shipping_country;
            $vender->shipping_state = $request->shipping_state;
            $vender->shipping_city = $request->shipping_city;
            $vender->shipping_phone = $request->shipping_phone;
            $vender->shipping_zip = $request->shipping_zip;
            $vender->shipping_address = $request->shipping_address;
            $vender->balance = $request->balance ?? 0;
            $vender->save();

            return new VenderResource($vender);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Vender  $vender
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Vender $vender)
    {
        if (Auth::user()->can('show vender') && $vender->created_by == Auth::user()->creatorId()) {
            return new VenderResource($vender);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Vender  $vender
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Vender $vender)
    {
        if (Auth::user()->can('edit vender') && $vender->created_by == Auth::user()->creatorId()) {
            $rules = [
                'name' => 'required|string|max:255',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('venders')->ignore($vender->id)->where(function ($query) {
                        return $query->where('created_by', Auth::user()->creatorId());
                    })
                ],
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $vender->update($request->all());

            return new VenderResource($vender);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Vender  $vender
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Vender $vender)
    {
        if (Auth::user()->can('delete vender') && $vender->created_by == Auth::user()->creatorId()) {
            $vender->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    private function venderNumber()
    {
        $latest = Vender::where('created_by', '=', Auth::user()->creatorId())->latest('vender_id')->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->vender_id + 1;
    }
}
