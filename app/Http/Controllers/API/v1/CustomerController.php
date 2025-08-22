<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (Auth::user()->can('manage customer')) {
            $customers = Customer::where('created_by', Auth::user()->creatorId())->get();
            return CustomerResource::collection($customers);
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
        if (Auth::user()->can('create customer')) {
            $rules = [
                'name' => 'required|string|max:255',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('customers')->where(function ($query) {
                        return $query->where('created_by', Auth::user()->creatorId());
                    })
                ],
                'billing_name' => 'required|string|max:255',
                'shipping_name' => 'required|string|max:255',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $user = Auth::user();
            $creator = User::find($user->creatorId());
            $plan = Plan::find($creator->plan);
            $total_customers = $user->countCustomers();

            if ($total_customers >= $plan->max_customers && $plan->max_customers != -1) {
                return response()->json(['error' => __('Your user limit is over, Please upgrade plan.')], 403);
            }

            $customer = new Customer();
            $customer->customer_id = $this->customerNumber();
            $customer->name = $request->name;
            $customer->contact = $request->contact;
            $customer->email = $request->email;
            $customer->tax_number = $request->tax_number;
            $customer->created_by = $user->creatorId();
            $customer->billing_name = $request->billing_name;
            $customer->billing_country = $request->billing_country;
            $customer->billing_state = $request->billing_state;
            $customer->billing_city = $request->billing_city;
            $customer->billing_phone = $request->billing_phone;
            $customer->billing_zip = $request->billing_zip;
            $customer->billing_address = $request->billing_address;
            $customer->shipping_name = $request->shipping_name;
            $customer->shipping_country = $request->shipping_country;
            $customer->shipping_state = $request->shipping_state;
            $customer->shipping_city = $request->shipping_city;
            $customer->shipping_phone = $request->shipping_phone;
            $customer->shipping_zip = $request->shipping_zip;
            $customer->shipping_address = $request->shipping_address;
            $customer->balance = $request->balance ?? 0;
            $customer->save();

            return new CustomerResource($customer);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Customer $customer)
    {
        if (Auth::user()->can('show customer') && $customer->created_by == Auth::user()->creatorId()) {
            return new CustomerResource($customer);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Customer $customer)
    {
        if (Auth::user()->can('edit customer') && $customer->created_by == Auth::user()->creatorId()) {
            $rules = [
                'name' => 'required|string|max:255',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('customers')->ignore($customer->id)->where(function ($query) {
                        return $query->where('created_by', Auth::user()->creatorId());
                    })
                ],
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $customer->update($request->all());

            return new CustomerResource($customer);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Customer $customer)
    {
        if (Auth::user()->can('delete customer') && $customer->created_by == Auth::user()->creatorId()) {
            $customer->delete();
            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    private function customerNumber()
    {
        $latest = Customer::where('created_by', '=', Auth::user()->creatorId())->latest('customer_id')->first();
        if(!$latest)
        {
            $setting = \App\Models\Utility::settings();
            return (isset($setting['customer_starting_number']) ? $setting['customer_starting_number'] : 1);
        }

        return $latest->customer_id + 1;
    }
}
