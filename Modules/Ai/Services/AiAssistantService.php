<?php

namespace Modules\Ai\Services;

use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\TaskStage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AiAssistantService
{
    protected $currentUser;

    public function __construct()
    {
        // In a real app, we would inject the authenticated user.
        // For this context, we'll fetch them statically.
        // This might need adjustment based on how the service is instantiated.
        $this->currentUser = Auth::user();
    }

    /**
     * Process the user's chat message.
     *
     * @param string $message
     * @return string
     */
    public function processMessage(string $message): string
    {
        if ($this->currentUser === null) {
            // Cannot process without a user context.
            return "Error: I am not able to identify who you are. Please ensure you are logged in.";
        }

        // V1: Simple intent recognition based on keywords
        if (preg_match('/(create|new|start) (a|the)? project for (tender )?(.*)/i', $message, $matches)) {
            $projectName = rtrim(trim($matches[4]), '.');
            if (empty($projectName)) {
                return "Please provide a name for the project. For example: 'create a project for the Alpha tender'.";
            }
            return $this->handleCreateProjectIntent($projectName);
        }

        return "I'm sorry, I can only help with creating projects from tenders right now. Please try a message like: 'create a project for the Alpha tender'.";
    }

    /**
     * Handles the logic for the "create project" intent.
     *
     * @param string $projectName
     * @return string
     */
    private function handleCreateProjectIntent(string $projectName): string
    {
        $helper = $this->findProjectHelper();
        if (!$helper) {
            return "I couldn't find a suitable employee to help with this project. Please contact an administrator.";
        }

        $project = $this->createProject($projectName, $helper);
        if (!$project) {
            return "There was an error creating the project. Please try again later.";
        }

        $this->createDefaultTasks($project, $helper);

        return "Success! I have created the project '{$projectName}' and assigned {$helper->name} to help you. I've also added some initial tasks to the project.";
    }

    /**
     * Finds a suitable, available employee to assist with a project.
     *
     * @return \App\Models\User|null
     */
    private function findProjectHelper(): ?User
    {
        $targetDesignations = ['Tender Officer', 'Admin Officer', 'Project Manager'];

        $candidates = Employee::with(['user', 'designation'])
            ->whereHas('designation', function ($query) use ($targetDesignations) {
                $query->whereIn('name', $targetDesignations);
            })
            ->get()
            ->map(function ($employee) {
                return $employee->user;
            })
            ->filter(function ($user) {
                if (!$user) return false; // Filter out employees with no linked user
                return $user->id !== $this->currentUser->id && !$this->isManager($user);
            });

        if ($candidates->isEmpty()) {
            return null;
        }

        return $candidates->sortBy(function ($user) {
            $workload = $user->workload;
            return $workload['open_task_count'] + ($workload['project_count'] * 5);
        })->first();
    }

    /**
     * Checks if a user is a manager.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    private function isManager(User $user): bool
    {
        return User::where('created_by', $user->id)->exists();
    }

    /**
     * Creates a new project.
     *
     * @param string $projectName
     * @param \App\Models\User $helper
     * @return \App\Models\Project
     */
    private function createProject(string $projectName, User $helper): Project
    {
        $project = new Project();
        $project->project_name = $projectName;
        $project->type = 'tender';
        $project->start_date = Carbon::now()->toDateString();
        $project->end_date = Carbon::now()->addMonth()->toDateString();
        $project->budget = 0;
        $project->created_by = $this->currentUser->creatorId();
        $project->status = 'in_progress';
        $project->save();

        $project->users()->attach([$this->currentUser->id, $helper->id]);
        return $project;
    }

    /**
     * Creates default tasks for a new tender project.
     *
     * @param \App\Models\Project $project
     * @param \App\Models\User $helper
     * @return void
     */
    private function createDefaultTasks(Project $project, User $helper): void
    {
        $taskStage = TaskStage::orderBy('order', 'asc')->first();
        if (!$taskStage) {
            return; // Cannot create tasks without a stage
        }

        $tasks = [
            ['name' => 'Find and evaluate potential suppliers and quotes', 'assign_to' => $helper->id],
            ['name' => 'Prepare and draft the tender submission documents', 'assign_to' => $this->currentUser->id],
            ['name' => 'Review final tender documents for compliance', 'assign_to' => $this->currentUser->id],
        ];

        foreach ($tasks as $taskData) {
            ProjectTask::create([
                'name' => $taskData['name'],
                'project_id' => $project->id,
                'stage_id' => $taskStage->id,
                'assign_to' => (string) $taskData['assign_to'],
                'priority' => 'medium',
                'start_date' => Carbon::now()->toDateString(),
                'end_date' => Carbon::now()->addWeek()->toDateString(),
                'created_by' => $this->currentUser->creatorId(),
            ]);
        }
    }
}
