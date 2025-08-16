<?php

use App\Http\Controllers\ExternalUserController;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController;
use Laravel\Passport\Http\Controllers\ClientController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use App\Http\Controllers\API\v1\DealController;
use App\Http\Controllers\API\v1\InvoiceController;
use App\Http\Controllers\API\v1\EmployeeController;
use App\Http\Controllers\API\v1\UtilityController;
use App\Http\Controllers\API\v1\QuoteController;
use App\Http\Controllers\API\v1\BillController;
use App\Http\Controllers\API\v1\ProjectController;
use App\Http\Controllers\API\v1\ProjectTaskController;
use App\Http\Controllers\API\v1\LeaveController;
use App\Http\Controllers\API\v1\LeadController;
use App\Http\Controllers\API\v1\MilestoneController;
use App\Http\Controllers\API\v1\ProjectExpenseController;
use App\Http\Controllers\API\v1\PayslipController;
use App\Http\Controllers\API\v1\SetSalaryController;
use App\Http\Controllers\API\v1\AllowanceController;
use App\Http\Controllers\API\v1\CommissionController;
use App\Http\Controllers\API\v1\LoanController;
use App\Http\Controllers\API\v1\SaturationDeductionController;
use App\Http\Controllers\API\v1\OtherPaymentController;
use App\Http\Controllers\API\v1\CustomerController;
use App\Http\Controllers\API\v1\VenderController;
use App\Http\Controllers\API\v1\ProductServiceController;
use App\Http\Controllers\API\v1\BranchController;
use App\Http\Controllers\API\v1\DepartmentController;
use App\Http\Controllers\API\v1\DesignationController;
use App\Http\Controllers\API\v1\TaxController;
use App\Http\Controllers\API\v1\BankAccountController;
use App\Http\Controllers\API\v1\PaymentController;
use App\Http\Controllers\API\v1\ProposalController;
use App\Http\Controllers\API\v1\QuotationController;
use App\Http\Controllers\API\v1\ContractController;
use App\Http\Controllers\API\v1\BillController;
use App\Http\Controllers\API\v1\ExpenseController;
use App\Http\Controllers\API\v1\RevenueController;
use App\Http\Controllers\API\v1\BudgetController;
use App\Http\Controllers\API\v1\ProjectstagesController;
use App\Http\Controllers\API\v1\TimesheetController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// OAuth2 endpoints
Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])->name('passport.token');
Route::get('/oauth/tokens', [AuthorizedAccessTokenController::class, 'forUser'])->name('passport.tokens.index');
Route::delete('/oauth/tokens/{token_id}', [AuthorizedAccessTokenController::class, 'destroy'])->name('passport.tokens.destroy');
Route::get('/oauth/clients', [ClientController::class, 'forUser'])->name('passport.clients.index');
Route::post('/oauth/clients', [ClientController::class, 'store'])->name('passport.clients.store');
Route::put('/oauth/clients/{client_id}', [ClientController::class, 'update'])->name('passport.clients.update');
Route::delete('/oauth/clients/{client_id}', [ClientController::class, 'destroy'])->name('passport.clients.destroy');
Route::get('/oauth/personal-access-tokens', [PersonalAccessTokenController::class, 'forUser'])->name('passport.personal.tokens.index');
Route::post('/oauth/personal-access-tokens', [PersonalAccessTokenController::class, 'store'])->name('passport.personal.tokens.store');
Route::delete('/oauth/personal-access-tokens/{token_id}', [PersonalAccessTokenController::class, 'destroy'])->name('passport.personal.tokens.destroy');


Route::post('login', [ApiController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('logout', [ApiController::class, 'logout']);
    Route::get('get-projects', [ApiController::class, 'getProjects']);
    Route::post('add-tracker', [ApiController::class, 'addTracker']);
    Route::post('stop-tracker', [ApiController::class, 'stopTracker']);
    Route::post('upload-photos', [ApiController::class, 'uploadImage']);

    // Deals
    Route::get('v1/deals', [DealController::class, 'index']);
    Route::get('v1/deals/{id}', [DealController::class, 'show']);
    Route::post('v1/deals', [DealController::class, 'store']);
    Route::put('v1/deals/{id}', [DealController::class, 'update']);
    Route::delete('v1/deals/{id}', [DealController::class, 'destroy']);

    // Invoices
    Route::get('v1/invoices', [InvoiceController::class, 'index']);
    Route::get('v1/invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('v1/invoices', [InvoiceController::class, 'store']);
    Route::put('v1/invoices/{id}', [InvoiceController::class, 'update']);
    Route::delete('v1/invoices/{id}', [InvoiceController::class, 'destroy']);

    // Employees
    Route::get('v1/employees', [EmployeeController::class, 'index']);
    Route::get('v1/employees/{id}', [EmployeeController::class, 'show']);
    Route::post('v1/employees', [EmployeeController::class, 'store']);
    Route::put('v1/employees/{id}', [EmployeeController::class, 'update']);
    Route::delete('v1/employees/{id}', [EmployeeController::class, 'destroy']);

    // Customers
    Route::apiResource('v1/customers', CustomerController::class);

    // Venders
    Route::apiResource('v1/venders', VenderController::class);

    // Product & Services
    Route::apiResource('v1/productservices', ProductServiceController::class);

    // HRM Core
    Route::apiResource('v1/branches', BranchController::class);
    Route::apiResource('v1/departments', DepartmentController::class);
    Route::apiResource('v1/designations', DesignationController::class);

    // Financials
    Route::apiResource('v1/taxes', TaxController::class);
    Route::apiResource('v1/bank-accounts', BankAccountController::class);
    Route::apiResource('v1/payments', PaymentController::class);
    Route::apiResource('v1/expenses', ExpenseController::class);
    Route::apiResource('v1/revenues', RevenueController::class);

    // Sales
    Route::apiResource('v1/proposals', ProposalController::class);
    Route::apiResource('v1/quotations', QuotationController::class);
    Route::apiResource('v1/contracts', ContractController::class);

    // Project Management
    Route::apiResource('v1/budgets', BudgetController::class);
    Route::apiResource('v1/projectstages', ProjectstagesController::class);
    Route::get('v1/projects/{project}/timesheets', [TimesheetController::class, 'index']);
    Route::post('v1/projects/{project}/timesheets', [TimesheetController::class, 'store']);
    Route::apiResource('v1/timesheets', TimesheetController::class)->except(['index', 'store']);

    // Utilities
    Route::get('v1/utils/invoice-form-data', [UtilityController::class, 'getInvoiceFormData']);
    Route::get('v1/utils/employee-form-data', [UtilityController::class, 'getEmployeeFormData']);
    Route::get('v1/utils/products', [UtilityController::class, 'getProducts']);
    Route::get('v1/utils/venders', [UtilityController::class, 'getVenders']);
    Route::get('v1/users/{id}/workload', [UtilityController::class, 'getWorkload']);
    Route::get('v1/me/tasks', [UtilityController::class, 'getMyOpenTasks']);

    // Quotes
    Route::apiResource('v1/quotes', QuoteController::class);

    // Bills
    Route::apiResource('v1/bills', BillController::class);

    // Projects
    Route::apiResource('v1/projects', ProjectController::class);

    // Project Tasks
    Route::get('v1/projects/{projectId}/tasks', [ProjectTaskController::class, 'index']);
    Route::post('v1/projects/{projectId}/tasks', [ProjectTaskController::class, 'store']);
    Route::get('v1/tasks/{taskId}', [ProjectTaskController::class, 'show']); // a task can be fetched by its own id
    Route::put('v1/tasks/{taskId}', [ProjectTaskController::class, 'update']);
    Route::delete('v1/tasks/{taskId}', [ProjectTaskController::class, 'destroy']);

    // Project Milestones
    Route::get('v1/projects/{projectId}/milestones', [MilestoneController::class, 'index']);
    Route::post('v1/projects/{projectId}/milestones', [MilestoneController::class, 'store']);
    Route::put('v1/milestones/{milestoneId}', [MilestoneController::class, 'update']);
    Route::delete('v1/milestones/{milestoneId}', [MilestoneController::class, 'destroy']);

    // Project Expenses
    Route::get('v1/projects/{projectId}/expenses', [ProjectExpenseController::class, 'index']);
    Route::post('v1/projects/{projectId}/expenses', [ProjectExpenseController::class, 'store']);
    Route::delete('v1/expenses/{expenseId}', [ProjectExpenseController::class, 'destroy']);

    // HRM - Leave
    Route::apiResource('v1/leaves', LeaveController::class)->except(['update']);
    Route::post('v1/leaves/{id}/approve', [LeaveController::class, 'approve']);
    Route::post('v1/leaves/{id}/reject', [LeaveController::class, 'reject']);

    // CRM - Leads
    Route::apiResource('v1/leads', LeadController::class);

    // HRM - Payslip
    Route::apiResource('v1/payslips', PayslipController::class);

    // HRM - Set Salary
    Route::get('v1/employees/{employeeId}/salary', [SetSalaryController::class, 'show']);
    Route::put('v1/employees/{employeeId}/salary', [SetSalaryController::class, 'update']);

    // HRM - Allowances
    Route::apiResource('v1/allowances', AllowanceController::class);

    // HRM - Commissions
    Route::apiResource('v1/commissions', CommissionController::class);

    // HRM - Loans
    Route::apiResource('v1/loans', LoanController::class);

    // HRM - Saturation Deductions
    Route::apiResource('v1/saturation-deductions', SaturationDeductionController::class);

    // HRM - Other Payments
    Route::apiResource('v1/other-payments', OtherPaymentController::class);
});

/*
|--------------------------------------------------------------------------
| External Platform Integration Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['client.credentials'])->group(function () {
    Route::get('/external/check-user', [ExternalUserController::class, 'checkUser']);
    Route::post('/external/create-seller-company', [ExternalUserController::class, 'createSellerCompany']);
    Route::post('/external/link-seller', [ExternalUserController::class, 'linkExistingSeller']);
    Route::get('/external/user-by-external-id', [ExternalUserController::class, 'getUserByExternalId']);
    Route::put('/external/update-user', [ExternalUserController::class, 'updateExternalUser']);
    Route::post('/external/disconnect-user', [ExternalUserController::class, 'disconnectExternalUser']);
    Route::post('/external/bulk-sync-users', [ExternalUserController::class, 'bulkSyncExternalUsers']);
    Route::get('/external/stats', [ExternalUserController::class, 'getExternalUserStats']);
});
