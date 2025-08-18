<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReferralSettingResource;
use App\Http\Resources\ReferralTransactionResource;
use App\Http\Resources\TransactionOrderResource;
use App\Models\ReferralSetting;
use App\Models\ReferralTransaction;
use App\Models\TransactionOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReferralProgramController extends Controller
{
    // For Super Admin
    public function adminIndex()
    {
        if (Auth::user()->type == 'super admin') {
            $setting = ReferralSetting::first();
            $payRequests = TransactionOrder::where('status', 1)->with('user')->get();
            $transactions = ReferralTransaction::with('user')->get();

            return response()->json([
                'settings' => new ReferralSettingResource($setting),
                'payment_requests' => TransactionOrderResource::collection($payRequests),
                'transactions' => ReferralTransactionResource::collection($transactions),
            ]);
        }
        return response()->json(['error' => 'Permission Denied.'], 403);
    }

    public function adminStore(Request $request)
    {
        if (Auth::user()->type == 'super admin') {
            $validator = Validator::make($request->all(), [
                'percentage' => 'required|numeric|min:0|max:100',
                'minimum_threshold_amount' => 'required|numeric|min:0',
                'guideline' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }

            $setting = ReferralSetting::firstOrCreate(['created_by' => 1]);
            $setting->percentage = $request->percentage;
            $setting->minimum_threshold_amount = $request->minimum_threshold_amount;
            $setting->is_enable = $request->input('is_enable', 0);
            $setting->guideline = $request->guideline;
            $setting->save();

            return new ReferralSettingResource($setting);
        }
        return response()->json(['error' => 'Permission Denied.'], 403);
    }

    public function handleRequest(Request $request, $id)
    {
        if (Auth::user()->type == 'super admin') {
            $transaction = TransactionOrder::find($id);
            if (!$transaction) {
                return response()->json(['error' => 'Request not found.'], 404);
            }
            // 1 for approve, 0 for reject
            $transaction->status = $request->status == 1 ? 2 : 0;
            $transaction->save();

            $message = $request->status == 1 ? 'Request Approved Successfully.' : 'Request Rejected Successfully.';
            return response()->json(['message' => __($message)]);
        }
        return response()->json(['error' => 'Permission Denied.'], 403);
    }

    // For Company/User
    public function companyIndex()
    {
        $user = Auth::user();
        $setting = ReferralSetting::where('created_by', 1)->first();
        $transactions = ReferralTransaction::where('referral_code', $user->referral_code)->get();
        $transactionsOrder = TransactionOrder::where('req_user_id', $user->id)->get();
        $paidAmount = $transactionsOrder->where('status', 2)->sum('req_amount');
        $paymentRequest = $transactionsOrder->where('status', 1)->first();

        return response()->json([
            'settings' => new ReferralSettingResource($setting),
            'transactions' => ReferralTransactionResource::collection($transactions),
            'paid_amount' => $paidAmount,
            'payment_requests' => TransactionOrderResource::collection($transactionsOrder),
            'active_payment_request' => $paymentRequest ? new TransactionOrderResource($paymentRequest) : null,
            'net_commission' => $user->commission_amount - $paidAmount,
        ]);
    }

    public function requestAmountStore(Request $request)
    {
        $user = Auth::user();
        $paidAmount = TransactionOrder::where('req_user_id', $user->id)->where('status', 2)->sum('req_amount');
        $netAmount = $user->commission_amount - $paidAmount;

        $validator = Validator::make($request->all(), [
            'request_amount' => 'required|numeric|min:0|max:' . $netAmount,
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $order = new TransactionOrder();
        $order->req_amount = $request->request_amount;
        $order->req_user_id = $user->id;
        $order->status = 1; // Pending
        $order->date = now();
        $order->save();

        return new TransactionOrderResource($order);
    }

    public function requestAmountCancel(Request $request, $id)
    {
        $user = Auth::user();
        $transaction = TransactionOrder::where('id', $id)->where('req_user_id', $user->id)->where('status', 1)->first();
        if ($transaction) {
            $transaction->delete();
            return response()->json(null, 204);
        }
        return response()->json(['error' => 'Pending request not found.'], 404);
    }
}
