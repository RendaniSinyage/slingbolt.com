<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\TaskStage;
use App\Models\Timesheet;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Project::query();

        if ($user->type == 'client') {
            $query->where('client_id', $user->id);
        } elseif ($user->type == 'company') {
            $query->where('created_by', $user->creatorId());
            if ($request->has('user_id')) {
                $query->whereHas('users', function ($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                });
            }
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
        } else { // Employee
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($request->has('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $projects = $query->with(['tasks', 'users', 'client'])->orderBy('id', 'desc')->get();
        return response()->json($projects);
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $project = Project::with(['tasks.stage', 'milestones', 'users', 'client'])->find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found.'], 404);
        }

        // Authorization check
        $canView = false;
        if ($user->type == 'company' && $project->created_by == $user->creatorId()) {
            $canView = true;
        } elseif ($user->type == 'client' && $project->client_id == $user->id) {
            $canView = true;
        } elseif ($user->type == 'employee' && $project->users->contains($user)) {
            $canView = true;
        }

        if (!$canView) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $chartData = $this->getProjectChartData($id, $request->input('duration', 'week'));
        $taskStageData = $this->getTaskStageChartData($id);
        $taskPriorityData = $this->getTaskPriorityChartData($id);
        $loggedHours = $this->getLoggedHours($id);

        $report = [
            'project' => $project,
            'charts' => [
                'task_overview' => $chartData,
                'task_stage_distribution' => $taskStageData,
                'task_priority_distribution' => $taskPriorityData,
            ],
            'logged_hours' => $loggedHours,
            'estimated_hours' => $project->tasks->sum('estimated_hrs'),
        ];

        return response()->json($report);
    }

    private function getProjectChartData($projectId, $duration)
    {
        $arrDuration = [];
        if ($duration == 'week') {
            $previous_week = Utility::getFirstSeventhWeekDay(-1);
            foreach ($previous_week['datePeriod'] as $dateObject) {
                $arrDuration[$dateObject->format('Y-m-d')] = $dateObject->format('D');
            }
        }

        $arrTask = ['label' => [], 'data' => []];
        foreach ($arrDuration as $date => $label) {
            $data = ProjectTask::where('project_id', $projectId)->whereDate('updated_at', '=', $date)->count();
            $arrTask['label'][] = __($label);
            $arrTask['data'][] = $data;
        }
        return $arrTask;
    }

    private function getTaskStageChartData($projectId)
    {
        $task_stages = ProjectTask::where('project_id', $projectId)->groupBy('stage_id')->selectRaw('count(id) as count, stage_id')->pluck('count', 'stage_id');
        $data = [];
        foreach ($task_stages as $stageId => $count) {
            $stage = TaskStage::find($stageId);
            if ($stage) {
                $data[] = ['stage' => $stage->name, 'count' => $count];
            }
        }
        return $data;
    }

    private function getTaskPriorityChartData($projectId)
    {
        return ProjectTask::where('project_id', $projectId)->groupBy('priority')->selectRaw('count(id) as count, priority')->pluck('count', 'priority');
    }

    private function getLoggedHours($projectId)
    {
        return Timesheet::where('project_id', $projectId)->sum('time');
    }
}
