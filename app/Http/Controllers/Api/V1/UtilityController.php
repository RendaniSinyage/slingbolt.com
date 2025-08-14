<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\ProductServiceCategory;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\ProductService;
use App\Models\Vender;

class UtilityController extends Controller
{
    public function getInvoiceFormData(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->ownerId();

        $customers = Customer::where('created_by', $ownerId)->get(['id', 'name']);
        $categories = ProductServiceCategory::where('created_by', $ownerId)->where('type', 2)->get(['id', 'name']); // type 2 is for invoices

        return response()->json([
            'customers' => $customers,
            'categories' => $categories,
        ]);
    }

    public function getEmployeeFormData(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->ownerId();

        $branches = Branch::where('created_by', $ownerId)->get(['id', 'name']);
        $departments = Department::where('created_by', $ownerId)->get(['id', 'name']);
        $designations = Designation::where('created_by', $ownerId)->get(['id', 'name']);

        return response()->json([
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
        ]);
    }

    public function getProducts(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->ownerId();

        $products = ProductService::where('created_by', $ownerId)->get(['id', 'name', 'sale_price']);

        return response()->json($products);
    }

    public function getVenders(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->ownerId();

        $venders = Vender::where('created_by', $ownerId)->get(['id', 'name']);

        return response()->json($venders);
    }
}
