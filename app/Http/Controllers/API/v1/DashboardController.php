<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Bug;
use App\Models\BugStatus;
use App\Models\Contract;
use App\Models\Deal;
use App\Models\DealTask;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Meeting;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Pos;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Purchase;
use App\Models\Revenue;
use App\Models\Stage;
use App\Models\Tax;
use App\Models\Timesheet;
use App\Models\TimeTracker;
use App\Models\Trainer;
use App\Models\Training;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function account_dashboard()
    {
        if (Auth::user()->can('show account dashboard')) {
            $data['latestIncome'] = Revenue::with(['customer'])->where('created_by', '=', Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();
            $data['latestExpense'] = Payment::with(['vender'])->where('created_by', '=', Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();

            $incomeCategory = ProductServiceCategory::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', 'income')->get();
            $inColor = [];
            $inCategory = [];
            $inAmount = [];
            foreach ($incomeCategory as $category) {
                $inColor[] = '#' . $category->color;
                $inCategory[] = $category->name;
                $inAmount[] = $category->incomeCategoryRevenueAmount();
            }
            $data['incomeCategory'] = ['color' => $inColor, 'name' => $inCategory, 'amount' => $inAmount];

            $expenseCategory = ProductServiceCategory::where('created_by', '=', Auth::user()->creatorId())->where('type', '=', 'expense')->get();
            $exColor = [];
            $exCategory = [];
            $exAmount = [];
            foreach ($expenseCategory as $category) {
                $exColor[] = '#' . $category->color;
                $exCategory[] = $category->name;
                $exAmount[] = $category->expenseCategoryAmount();
            }
            $data['expenseCategory'] = ['color' => $exColor, 'name' => $exCategory, 'amount' => $exAmount];

            $data['incExpBarChartData'] = Auth::user()->getincExpBarChartData();
            $data['incExpLineChartData'] = Auth::user()->getIncExpLineChartDate();

            $data['bankAccountDetail'] = BankAccount::where('created_by', '=', Auth::user()->creatorId())->limit(5)->get();
            $data['recentInvoice'] = Invoice::with('customer')->where('created_by', '=', Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();
            $data['weeklyInvoice'] = Auth::user()->weeklyInvoice();
            $data['monthlyInvoice'] = Auth::user()->monthlyInvoice();
            $data['recentBill'] = Bill::with('vender')->where('created_by', '=', Auth::user()->creatorId())->orderBy('id', 'desc')->limit(5)->get();
            $data['weeklyBill'] = Auth::user()->weeklyBill();
            $data['monthlyBill'] = Auth::user()->monthlyBill();
            $data['goals'] = Goal::where('created_by', '=', Auth::user()->creatorId())->where('is_display', 1)->get();

            $users = User::find(Auth::user()->creatorId());
            $plan = Plan::find($users->plan);
            if ($plan && $plan->storage_limit > 0) {
                $data['storage_limit'] = ($users->storage_limit / $plan->storage_limit) * 100;
            } else {
                $data['storage_limit'] = 0;
            }

            return response()->json($data);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function project_dashboard()
    {
        if (Auth::user()->can('show project dashboard')) {
            $user = Auth::user();
            $user_projects = $user->projects()->pluck('project_id')->toArray();

            $home_data = [];
            $home_data['total_project'] = [
                'total' => count($user_projects),
                'percentage' => Utility::getPercentage($user->projects()->where('status', 'complete')->count(), count($user_projects)),
            ];

            $project_tasks = ProjectTask::whereIn('project_id', $user_projects);
            $home_data['total_task'] = [
                'total' => $project_tasks->count(),
                'percentage' => Utility::getPercentage($project_tasks->where('is_complete', 1)->count(), $project_tasks->count()),
            ];

            $project_expense = Expense::whereIn('project_id', $user_projects)->sum('amount');
            $total_project_amount = $user->projects()->sum('budget');
            $home_data['total_expense'] = [
                'total' => $project_expense,
                'percentage' => Utility::getPercentage($project_expense, $total_project_amount),
            ];

            $home_data['total_user'] = $user->contacts()->count();

            $task_overview = [];
            $timesheet_logged = [];
            foreach (Utility::getLastSevenDays() as $date => $day) {
                $task_overview[$day] = ProjectTask::where('is_complete', 1)->where('marked_at', 'LIKE', $date)->whereIn('project_id', $user_projects)->count();
                $time = Timesheet::whereIn('project_id', $user_projects)->where('date', 'LIKE', $date)->pluck('time')->toArray();
                $timesheet_logged[$day] = str_replace(':', '.', Utility::calculateTimesheetHours($time));
            }
            $home_data['task_overview'] = $task_overview;
            $home_data['timesheet_logged'] = $timesheet_logged;

            $project_status = [];
            foreach (Project::$project_status as $k => $v) {
                $project_status[$k]['total'] = $user->projects()->where('status', $k)->count();
                $project_status[$k]['percentage'] = Utility::getPercentage($project_status[$k]['total'], count($user_projects));
            }
            $home_data['project_status'] = $project_status;

            $home_data['due_project'] = $user->projects()->orderBy('end_date', 'DESC')->limit(5)->get();
            $home_data['due_tasks'] = ProjectTask::where('is_complete', 0)->whereIn('project_id', $user_projects)->orderBy('end_date', 'DESC')->limit(5)->get();
            $home_data['last_tasks'] = ProjectTask::whereIn('project_id', $user_projects)->orderBy('id', 'DESC')->limit(5)->get();

            return response()->json($home_data);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function hrm_dashboard()
    {
        if (Auth::user()->can('show hrm dashboard')) {
            $user = Auth::user();
            $data = [];

            if ($user->type == 'employee') {
                $employee = Employee::where('user_id', '=', $user->id)->first();
                $data['announcements'] = Announcement::orderBy('id', 'desc')->take(5)
                    ->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')
                    ->where('announcement_employees.employee_id', '=', $employee->id)
                    ->orWhere(function ($q) {
                        $q->where('announcements.department_id', '["0"]')->where('announcements.employee_id', '["0"]');
                    })->get();

                $data['events'] = Event::select('title', 'start_date', 'end_date', 'color')
                    ->leftjoin('event_employees', 'events.id', '=', 'event_employees.event_id')
                    ->where('event_employees.employee_id', '=', $employee->id)
                    ->orWhere(function ($q) {
                        $q->where('events.department_id', '["0"]')->where('events.employee_id', '["0"]');
                    })->get();

                $data['meetings'] = Meeting::orderBy('id', 'desc')->take(5)
                    ->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')
                    ->where('meeting_employees.employee_id', '=', $employee->id)
                    ->orWhere(function ($q) {
                        $q->where('meetings.department_id', '["0"]')->where('meetings.employee_id', '["0"]');
                    })->get();

                $data['today_attendance'] = AttendanceEmployee::where('employee_id', $employee->id)->where('date', date('Y-m-d'))->first();
                $data['office_time'] = [
                    'start_time' => Utility::getValByName('company_start_time'),
                    'end_time' => Utility::getValByName('company_end_time'),
                ];
            } else {
                $data['events'] = Event::where('created_by', $user->creatorId())->select('title', 'start_date', 'end_date', 'color')->get();
                $data['announcements'] = Announcement::where('created_by', $user->creatorId())->orderBy('id', 'desc')->take(5)->get();
                $data['total_employees'] = User::where('type', 'employee')->where('created_by', $user->creatorId())->count();
                $data['total_clients'] = User::where('type', 'client')->where('created_by', $user->creatorId())->count();
                $data['total_trainers'] = Trainer::where('created_by', $user->creatorId())->count();
                $data['ongoing_training'] = Training::where('status', 1)->where('created_by', $user->creatorId())->count();
                $data['done_training'] = Training::where('status', 2)->where('created_by', $user->creatorId())->count();
                $data['active_jobs'] = Job::where('status', 'active')->where('created_by', $user->creatorId())->count();
                $data['inactive_jobs'] = Job::where('status', 'in_active')->where('created_by', $user->creatorId())->count();

                $clockedInEmployees = AttendanceEmployee::where('date', date('Y-m-d'))->where('created_by', $user->creatorId())->pluck('employee_id');
                $data['not_clocked_in'] = Employee::where('created_by', $user->creatorId())->whereNotIn('id', $clockedInEmployees)->count();
            }
            return response()->json($data);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function crm_dashboard()
    {
        if (Auth::user()->can('show crm dashboard')) {
            $user = Auth::user();
            $crm_data = [];

            $leads = Lead::where('created_by', $user->creatorId())->get();
            $deals = Deal::where('created_by', $user->creatorId())->get();

            $crm_data['total_leads'] = $leads->count();
            $crm_data['total_deals'] = $deals->count();
            $crm_data['total_contracts'] = Contract::where('created_by', $user->creatorId())->count();

            $lead_stages = LeadStage::where('created_by', $user->creatorId())->orderBy('pipeline_id')->get();
            $lead_status = [];
            foreach ($lead_stages as $stage) {
                $lead_count = $stage->leads()->count();
                $lead_status[] = [
                    'stage' => $stage->name,
                    'total' => $lead_count,
                    'percentage' => Utility::getCrmPercentage($lead_count, $crm_data['total_leads'])
                ];
            }
            $crm_data['lead_status'] = $lead_status;

            $deal_stages = Stage::where('created_by', $user->creatorId())->orderBy('pipeline_id')->get();
            $deal_status = [];
            foreach ($deal_stages as $stage) {
                $deal_count = $stage->deals()->count();
                $deal_status[] = [
                    'stage' => $stage->name,
                    'total' => $deal_count,
                    'percentage' => Utility::getCrmPercentage($deal_count, $crm_data['total_deals'])
                ];
            }
            $crm_data['deal_status'] = $deal_status;

            $crm_data['latest_contracts'] = Contract::with(['clients', 'projects', 'types'])->where('created_by', $user->creatorId())->orderBy('id', 'desc')->limit(5)->get();

            return response()->json($crm_data);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function pos_dashboard()
    {
        if (Auth::user()->can('show pos dashboard')) {
            $pos_data = [];
            $pos_data['monthly_pos_amount'] = Pos::totalPosAmount(true);
            $pos_data['total_pos_amount'] = Pos::totalPosAmount();
            $pos_data['monthly_purchase_amount'] = Purchase::totalPurchaseAmount(true);
            $pos_data['total_purchase_amount'] = Purchase::totalPurchaseAmount();
            $pos_data['purchase_chart'] = Purchase::getPurchaseReportChart();
            $pos_data['pos_chart'] = Pos::getPosReportChart();
            return response()->json($pos_data);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function stopTracker(Request $request)
    {
        if (Auth::user()->isClient()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|max:120',
            'project_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $tracker = TimeTracker::where('created_by', Auth::user()->id)->where('is_active', 1)->first();
        if ($tracker) {
            $tracker->end_time = $request->input('end_time', now());
            $tracker->is_active = 0;
            $tracker->total_time = Utility::diffance_to_time($tracker->start_time, $tracker->end_time);
            $tracker->save();

            return response()->json(['message' => __('Time added successfully.')]);
        }
        return response()->json(['error' => __('Tracker not found.')], 404);
    }
}
