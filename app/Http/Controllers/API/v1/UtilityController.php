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
use App\Models\User;
use App\Models\ProjectTask;

class UtilityController extends Controller
{
    public function getWorkload(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        return response()->json($user->workload);
    }

    public function getMyOpenTasks(Request $request)
    {
        $user = $request->user();

        $tasks = ProjectTask::whereRaw("find_in_set('".$user->id."',assign_to)")
            ->with('project') // Eager load the project relationship
            ->join('task_stages', 'project_tasks.stage_id', '=', 'task_stages.id')
            ->where('task_stages.complete', '!=', 1)
            ->select('project_tasks.*') // Avoid selecting columns from the joined table
            ->orderBy('project_tasks.end_date', 'asc')
            ->get();

        return response()->json($tasks);
    }

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
