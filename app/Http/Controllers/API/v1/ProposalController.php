<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProposalResource;
use App\Models\Proposal;
use App\Models\ProposalProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage proposal')) {
            $query = Proposal::where('created_by', Auth::user()->creatorId());

            if ($request->has('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $proposals = $query->with(['customer', 'category'])->get();
            return ProposalResource::collection($proposals);
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
        if (Auth::user()->can('create proposal')) {
            $validator = \Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'issue_date' => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items' => 'required|array|min:1',
                'items.*.item' => 'required|exists:product_services,id',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $proposal = new Proposal();
            $proposal->proposal_id = $this->proposalNumber();
            $proposal->customer_id = $request->customer_id;
            $proposal->status = 0; // Draft status
            $proposal->issue_date = $request->issue_date;
            $proposal->category_id = $request->category_id;
            $proposal->created_by = Auth::user()->creatorId();
            $proposal->save();

            foreach ($request->items as $item) {
                $proposalProduct = new ProposalProduct();
                $proposalProduct->proposal_id = $proposal->id;
                $proposalProduct->product_id = $item['item'];
                $proposalProduct->quantity = $item['quantity'];
                $proposalProduct->tax = $item['tax'] ?? null;
                $proposalProduct->discount = $item['discount'] ?? 0;
                $proposalProduct->price = $item['price'];
                $proposalProduct->description = $item['description'] ?? null;
                $proposalProduct->save();
            }

            return new ProposalResource($proposal->load('items'));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Proposal $proposal)
    {
        if (Auth::user()->can('show proposal') && $proposal->created_by == Auth::user()->creatorId()) {
            return new ProposalResource($proposal->load(['items.product', 'customer', 'category']));
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Proposal $proposal)
    {
        if (Auth::user()->can('edit proposal') && $proposal->created_by == Auth::user()->creatorId()) {
             $validator = \Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'issue_date' => 'required|date',
                'category_id' => 'required|exists:product_service_categories,id',
                'items' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $proposal->customer_id = $request->customer_id;
            $proposal->issue_date = $request->issue_date;
            $proposal->category_id = $request->category_id;
            $proposal->save();

            // Note: For simplicity, this update does not handle line item updates.
            // A full implementation would require logic to add/update/delete line items.

            return new ProposalResource($proposal);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Proposal  $proposal
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Proposal $proposal)
    {
        if (Auth::user()->can('delete proposal') && $proposal->created_by == Auth::user()->creatorId()) {
            $proposal->items()->delete();
            $proposal->delete();

            return response()->json(null, 204);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    private function proposalNumber()
    {
        $latest = Proposal::where('created_by', Auth::user()->creatorId())->latest('proposal_id')->first();
        if(!$latest)
        {
            $setting = \App\Models\Utility::settings();
            return (isset($setting['proposal_starting_number']) ? $setting['proposal_starting_number'] : 1);
        }
        return $latest->proposal_id + 1;
    }
}
