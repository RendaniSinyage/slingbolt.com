<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\ProjectExpense;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectExpenseController extends Controller
{
    public function index(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::find($projectId);

        if (!$project || !$user->can('view project', $project)) {
            return response()->json(['error' => 'Project not found or permission denied.'], 404);
        }

        $expenses = ProjectExpense::where('project_id', $projectId)->get();
        return response()->json($expenses);
    }

    public function store(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::find($projectId);

        if (!$project || !$user->can('create project expense', $project)) {
            return response()->json(['error' => 'Project not found or permission denied.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'amount' => 'required|numeric',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $expense = new ProjectExpense();
        $expense->project_id = $projectId;
        $expense->name = $request->name;
        $expense->amount = $request->amount;
        $expense->date = $request->date;
        $expense->description = $request->description;
        $expense->created_by = $user->creatorId();
        $expense->save();

        return response()->json($expense, 201);
    }

    public function destroy($expenseId)
    {
        $user = request()->user();
        $expense = ProjectExpense::find($expenseId);

        if (!$expense || !$user->can('delete project expense', $expense->project)) {
            return response()->json(['error' => 'Expense not found or permission denied.'], 404);
        }

        $expense->delete();
        return response()->json(['success' => 'Expense successfully deleted.'], 200);
    }
}
