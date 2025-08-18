<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ProductService;
use App\Models\warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/pos",
     *     summary="Get Initial POS Data",
     *     tags={"POS"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Initial data for POS screen",
     *         @OA\JsonContent(
     *             @OA\Property(property="customers", type="object"),
     *             @OA\Property(property="warehouses", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     )
     * )
     */
    public function index()
    {
        if (Auth::user()->can('manage pos')) {
            $customers = Customer::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $warehouses = warehouse::select('id', 'name')->where('created_by', \Auth::user()->creatorId())->get();

            return response()->json([
                'customers' => $customers,
                'warehouses' => $warehouses,
            ]);
        }

        return response()->json(['error' => __('Permission denied.')], 403);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pos/products",
     *     summary="Get Products by Warehouse",
     *     tags={"POS"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="warehouse_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products in the specified warehouse",
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     )
     * )
     */
    public function getProducts(Request $request)
    {
        if (!Auth::user()->can('manage pos')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $request->validate(['warehouse_id' => 'required|integer']);

        $productServicesId = WarehouseProduct::where('created_by', '=', \Auth::user()->creatorId())
            ->where('warehouse_id', $request->warehouse_id)
            ->get()->pluck('product_id')->toArray();

        $productServices = ProductService::whereIn('id', $productServicesId)->get()->pluck('name', 'id')->toArray();

        return response()->json($productServices);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/pos",
     *     summary="Create a new POS sale",
     *     tags={"POS"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"warehouse_id", "customer_id", "items"},
     *             @OA\Property(property="warehouse_id", type="integer"),
     *             @OA\Property(property="customer_id", type="integer"),
     *             @OA\Property(property="discount", type="number", format="float"),
     *             @OA\Property(property="quotation_id", type="integer"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id", "quantity", "price", "tax"},
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="price", type="number", format="float"),
     *                     @OA\Property(property="tax", type="number", format="float"),
     *                     @OA\Property(property="subtotal", type="number", format="float")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment completed successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('manage pos')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $request->validate([
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'customer_id' => 'required|integer|exists:customers,id',
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:product_services,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $user_id = Auth::user()->creatorId();
        $pos_id = $this->invoicePosNumber();

        $pos = new \App\Models\Pos();
        $pos->pos_id = $pos_id;
        $pos->customer_id = $request->customer_id;
        $pos->warehouse_id = $request->warehouse_id;
        $pos->pos_date = date('Y-m-d');
        $pos->created_by = $user_id;
        $pos->save();

        if ($request->quotation_id) {
            $quotation = \App\Models\Quotation::find($request->quotation_id);
            if ($quotation) {
                $quotation->is_converted = 1;
                $quotation->converted_pos_id = $pos->id;
                $quotation->save();
            }
        }

        $totalAmount = 0;
        foreach ($request->items as $item) {
            $product = \App\Models\ProductService::find($item['id']);
            $product->quantity = $product->quantity - $item['quantity'];
            $product->save();

            $tax_id = \App\Models\ProductService::tax_id($item['id']);

            $positems = new \App\Models\PosProduct();
            $positems->pos_id = $pos->id;
            $positems->product_id = $item['id'];
            $positems->price = $item['price'];
            $positems->quantity = $item['quantity'];
            $positems->tax = $tax_id;
            $positems->discount = $request->discount ?? 0;
            $positems->save();

            \App\Models\Utility::warehouse_quantity('minus', $positems->quantity, $positems->product_id, $request->warehouse_id);

            $description = $positems->quantity . '  ' . __(' quantity sold in pos') . ' ' . \Auth::user()->posNumberFormat($pos->pos_id);
            \App\Models\Utility::addProductStock($positems->product_id, $positems->quantity, 'pos', $description, $pos->id);

            $totalAmount += $item['price'] * $item['quantity'];
        }

        $posPayment = new \App\Models\PosPayment();
        $posPayment->pos_id = $pos->id;
        $posPayment->date = date('Y-m-d');
        $posPayment->amount = $totalAmount;
        $posPayment->discount = $request->discount ?? 0;
        $posPayment->discount_amount = $totalAmount - ($request->discount ?? 0);
        $posPayment->save();

        return response()->json(['success' => __('Payment completed successfully!')], 200);
    }

    private function invoicePosNumber()
    {
        $latest = \App\Models\Pos::where('created_by', '=', \Auth::user()->creatorId())->latest('pos_id')->first();
        return $latest ? $latest->pos_id + 1 : 1;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pos/report",
     *     summary="Get POS sales report",
     *     tags={"POS"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="POS sales report",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Pos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     )
     * )
     */
    public function report()
    {
        if (!\Auth::user()->can('manage pos')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }

        $posPayments = \App\Models\Pos::where('created_by', '=', \Auth::user()->creatorId())->with(['customer', 'warehouse'])->get();

        return response()->json($posPayments);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pos/{id}",
     *     summary="Get a single POS sale",
     *     tags={"POS"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="POS sale details",
     *         @OA\JsonContent(ref="#/components/schemas/Pos")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Permission denied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function show($id)
    {
        if (!\Auth::user()->can('show pos')) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $pos = \App\Models\Pos::with(['customer', 'items', 'items.product'])->find($id);

        if ($pos && $pos->created_by == \Auth::user()->creatorId()) {
            return response()->json($pos);
        }

        return response()->json(['error' => __('Pos Not Found.')], 404);
    }
}
