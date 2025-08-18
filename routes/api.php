<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\ExternalUserController;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController;
use Laravel\Passport\Http\Controllers\ClientController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use App\Http\Controllers\API\v1\AnnouncementController;
use App\Http\Controllers\API\v1\AttendanceEmployeeController;
use App\Http\Controllers\API\v1\AwardController;
use App\Http\Controllers\API\v1\AwardTypeController;
use App\Http\Controllers\API\v1\BankAccountController;
use App\Http\Controllers\API\v1\BankTransferController;
use App\Http\Controllers\API\v1\BillController;
use App\Http\Controllers\API\v1\BranchController;
use App\Http\Controllers\API\v1\BudgetController;
use App\Http\Controllers\API\v1\CompanyPolicyController;
use App\Http\Controllers\API\v1\ComplaintController;
use App\Http\Controllers\API\v1\ContractController;
use App\Http\Controllers\API\v1\CustomerController;
use App\Http\Controllers\API\v1\DealController;
use App\Http\Controllers\API\v1\DepartmentController;
use App\Http\Controllers\API\v1\DesignationController;
use App\Http\Controllers\API\v1\EventController;
use App\Http\Controllers\API\v1\ExpenseController;
use App\Http\Controllers\API\v1\FlutterwavePaymentController;
use App\Http\Controllers\API\v1\HolidayController;
use App\Http\Controllers\API\v1\InvoiceController;
use App\Http\Controllers\API\v1\EmployeeController;
use App\Http\Controllers\API\v1\LeadController;
use App\Http\Controllers\API\v1\LeadStageController;
use App\Http\Controllers\API\v1\LeaveController;
use App\Http\Controllers\API\v1\LeaveTypeController;
use App\Http\Controllers\API\v1\MilestoneController;
use App\Http\Controllers\API\v1\OzowController;
use App\Http\Controllers\API\v1\PayFastController;
use App\Http\Controllers\API\v1\PaymentController;
use App\Http\Controllers\API\v1\PaypalController;
use App\Http\Controllers\API\v1\PaystackPaymentController;
use App\Http\Controllers\API\v1\PermissionController;
use App\Http\Controllers\API\v1\PosApiController;
use App\Http\Controllers\API\v1\ProductServiceController;
use App\Http\Controllers\API\v1\ProjectController;
use App\Http\Controllers\API\v1\ProjectTaskController;
use App\Http\Controllers\API\v1\ProjectstagesController;
use App\Http\Controllers\API\v1\PromotionController;
use App\Http\Controllers\API\v1\ProposalController;
use App\Http\Controllers\API\v1\QuotationController;
use App\Http\Controllers\API\v1\QuoteController;
use App\Http\Controllers\API\v1\ResignationController;
use App\Http\Controllers\API\v1\RevenueController;
use App\Http\Controllers\API\v1\RoleController;
use App\Http\Controllers\API\v1\StageController;
use App\Http\Controllers\API\v1\TaskStageController;
use App\Http\Controllers\API\v1\TaxController;
use App\Http\Controllers\API\v1\TerminationController;
use App\Http\Controllers\API\v1\TerminationTypeController;
use App\Http\Controllers\API\v1\TimesheetController;
use App\Http\Controllers\API\v1\TransferController;
use App\Http\Controllers\API\v1\TravelController;
use App\Http\Controllers\API\v1\UserController as ApiUserController;
use App\Http\Controllers\API\v1\VenderController;
use App\Http\Controllers\API\v1\WarningController;
use App\Http\Controllers\API\v1\AllowanceController as V1AllowanceController;
use App\Http\Controllers\API\v1\AllowanceOptionController;
use App\Http\Controllers\API\v1\JobApiController;
use App\Http\Controllers\API\v1\DeductionOptionController;
use App\Http\Controllers\API\v1\LoanController as V1LoanController;
use App\Http\Controllers\API\v1\LoanOptionController;
use App\Http\Controllers\API\v1\OtherPaymentController;
use App\Http\Controllers\API\v1\PayslipController;
use App\Http\Controllers\API\v1\SaturationDeductionController;
use App\Http\Controllers\API\v1\SetSalaryController;
use App\Http\Controllers\API\v1\UtilityController;
use App\Http\Controllers\API\v1\ProjectExpenseController;
use App\Http\Controllers\API\v1\CommissionController;
use App\Http\Controllers\API\v1\BiometricAttendanceController;
use App\Http\Controllers\API\v1\BugStatusController;
use App\Http\Controllers\API\v1\ClientController;
use App\Http\Controllers\API\v1\ComplianceSettingsController;
use App\Http\Controllers\API\v1\CouponController;
use App\Http\Controllers\API\v1\CustomFieldController;
use App\Http\Controllers\API\v1\CustomerCreditNotesController;
use App\Http\Controllers\API\v1\CustomerDebitNotesController;
use App\Http\Controllers\API\v1\DashboardController;
use App\Http\Controllers\API\v1\DoubleEntryReportController;
use App\Http\Controllers\API\v1\DucumentUploadController;
use App\Http\Controllers\API\v1\FormBuilderController;
use App\Http\Controllers\API\v1\PlanController;
use App\Http\Controllers\API\v1\PlanRequestController;
use App\Http\Controllers\API\v1\ProductStockController;
use App\Http\Controllers\API\v1\ProjectReportController;
use App\Http\Controllers\API\v1\ReferralProgramController;
use App\Http\Controllers\API\v1\WarehouseTransferController;

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

// Public Recruitment Routes
Route::get('v1/jobs/career/{id}/{lang}', [JobApiController::class, 'career']);
Route::get('v1/jobs/requirement/{code}/{lang}', [JobApiController::class, 'jobRequirement']);
Route::post('v1/jobs/apply/{code}/{lang}', [JobApiController::class, 'jobApplyData']);

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
    Route::get('v1/productservices/search', [ProductServiceController::class, 'searchProducts']);
    Route::get('v1/productservices/{id}/warehouse', [ProductServiceController::class, 'warehouseDetail']);
    Route::apiResource('v1/productservices', ProductServiceController::class);

    // HRM Core
    Route::apiResource('v1/branches', BranchController::class);
    Route::apiResource('v1/departments', DepartmentController::class);
    Route::apiResource('v1/designations', DesignationController::class);

    // HRM Payroll
    Route::apiResource('v1/allowanceoptions', AllowanceOptionController::class);
    Route::apiResource('v1/loanoptions', LoanOptionController::class);
    Route::apiResource('v1/deductionoptions', DeductionOptionController::class);

    // HRM Time & Attendance
    Route::apiResource('v1/holidays', HolidayController::class);
    Route::apiResource('v1/leavetypes', LeaveTypeController::class);
    Route::get('v1/attendances', [AttendanceEmployeeController::class, 'index']);
    Route::post('v1/attendances/clockin', [AttendanceEmployeeController::class, 'clockIn']);
    Route::post('v1/attendances/clockout', [AttendanceEmployeeController::class, 'clockOut']);
    Route::post('v1/attendances', [AttendanceEmployeeController::class, 'store']);

    // HRM Employee Lifecycle
    Route::apiResource('v1/awardtypes', AwardTypeController::class);
    Route::apiResource('v1/awards', AwardController::class);
    Route::apiResource('v1/transfers', TransferController::class);
    Route::apiResource('v1/resignations', ResignationController::class);
    Route::apiResource('v1/terminationtypes', TerminationTypeController::class);
    Route::apiResource('v1/terminations', TerminationController::class);
    Route::apiResource('v1/promotions', PromotionController::class);
    Route::apiResource('v1/leads', LeadController::class);
    Route::apiResource('v1/deals', DealController::class);
    Route::apiResource('v1/lead-stages', LeadStageController::class);
    Route::apiResource('v1/stages', StageController::class);
    Route::apiResource('v1/task-stages', TaskStageController::class);
    Route::apiResource('v1/projects', ProjectController::class);
    Route::apiResource('v1/projects.tasks', ProjectTaskController::class)->shallow();
    Route::apiResource('v1/projects.milestones', MilestoneController::class)->shallow();
    Route::apiResource('v1/invoices', InvoiceController::class);
    Route::apiResource('v1/payments', PaymentController::class);
    Route::apiResource('v1/complaints', ComplaintController::class);
    Route::apiResource('v1/warnings', WarningController::class);
    Route::apiResource('v1/travels', TravelController::class);
    Route::apiResource('v1/announcements', AnnouncementController::class);
    Route::apiResource('v1/company-policies', CompanyPolicyController::class);
    Route::apiResource('v1/events', EventController::class);
    Route::apiResource('v1/bank-transfers', BankTransferController::class);
    Route::post('v1/paystack/plan/pay', [PaystackPaymentController::class, 'planPayWithPaystack'])->name('api.plan.pay.with.paystack');
    Route::get('v1/paystack/plan/status/{pay_id}/{plan}', [PaystackPaymentController::class, 'getPaymentStatus'])->name('api.plan.get.status');
    Route::post('v1/paypal/plan/pay', [PaypalController::class, 'planPayWithPaypal'])->name('api.plan.pay.with.paypal');
    Route::get('v1/paypal/plan/status/{plan_id}', [PaypalController::class, 'planGetPaymentStatus'])->name('api.plan.get.payment.status');
    Route::post('v1/payfast/plan/pay', [PayFastController::class, 'planPayWithPayfast'])->name('api.plan.pay.with.payfast');
    Route::get('v1/payfast/payment/success/{success}', [PayFastController::class, 'getPaymentStatus'])->name('api.payfast.payment.success');
    Route::post('v1/flutterwave/plan/pay', [FlutterwavePaymentController::class, 'planPayWithFlutterwave'])->name('api.plan.pay.with.flaterwave');
    Route::get('v1/flutterwave/plan/status/{pay_id}/{plan}', [FlutterwavePaymentController::class, 'getPaymentStatus'])->name('api.plan.flaterwave.status');
    Route::post('v1/ozow/plan/pay', [OzowController::class, 'planPayWithOzow'])->name('api.plan.pay.with.ozow');
    Route::get('v1/ozow/plan/status', [OzowController::class, 'planGetOzowStatus'])->name('api.plan.ozow.status');
    Route::apiResource('v1/appraisals', 'AppraisalController');
    Route::apiResource('v1/overtimes', 'OvertimeController');
    Route::apiResource('v1/payslips', 'PayslipController');
    Route::apiResource('v1/timetrackers', 'TimeTrackerController');
    Route::apiResource('v1/supports', 'SupportController');
    Route::apiResource('v1/meetings', 'MeetingController');
    Route::apiResource('v1/bank-transfer-payments', 'BankTransferPaymentController');
    Route::apiResource('v1/benefit-payments', 'BenefitPaymentController');
    Route::post('v1/stripe/payment', 'StripePaymentController@stripePost')->name('api.stripe.post');

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

    // Admin
    Route::apiResource('v1/users', ApiUserController::class);
    Route::apiResource('v1/roles', RoleController::class);
    Route::get('v1/permissions', [PermissionController::class, 'index']);

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
    Route::apiResource('v1/allowances', V1AllowanceController::class);

    // HRM - Commissions
    Route::apiResource('v1/commissions', CommissionController::class);

    // HRM - Loans
    Route::apiResource('v1/loans', V1LoanController::class);

    // HRM - Saturation Deductions
    Route::apiResource('v1/saturation-deductions', SaturationDeductionController::class);

    // HRM - Other Payments
    Route::apiResource('v1/other-payments', OtherPaymentController::class);

    // Recruitment
    Route::apiResource('v1/jobs', JobApiController::class);

    // POS
    Route::get('pos', [PosApiController::class, 'index'])->name('pos.index');
    Route::get('pos/products', [PosApiController::class, 'getProducts'])->name('pos.products');
    Route::post('pos', [PosApiController::class, 'store'])->name('pos.store');
    Route::get('pos/report', [PosApiController::class, 'report'])->name('pos.report');
    Route::get('pos/{id}', [PosApiController::class, 'show'])->name('pos.show');

    // Biometric Attendance
    Route::get('v1/biometric-attendance', [BiometricAttendanceController::class, 'index']);
    Route::post('v1/biometric-attendance', [BiometricAttendanceController::class, 'store']);
    Route::post('v1/biometric-attendance/sync', [BiometricAttendanceController::class, 'syncAll']);

    // Bug Status
    Route::apiResource('v1/bug-status', BugStatusController::class);
    Route::post('v1/bug-status/order', [BugStatusController::class, 'order']);

    // Clients
    Route::apiResource('v1/clients', ClientController::class);
    Route::post('v1/clients/{id}/reset-password', [ClientController::class, 'resetPassword']);

    // Compliance Settings
    Route::get('v1/compliance-settings', [ComplianceSettingsController::class, 'index']);
    Route::post('v1/compliance-settings', [ComplianceSettingsController::class, 'store']);

    // Coupons
    Route::apiResource('v1/coupons', CouponController::class);
    Route::post('v1/coupons/apply', [CouponController::class, 'apply']);

    // Custom Fields
    Route::apiResource('v1/custom-fields', CustomFieldController::class);

    // Customer Credit Notes
    Route::apiResource('v1/customer-credit-notes', CustomerCreditNotesController::class);
    Route::post('v1/credit-invoice/items', [CustomerCreditNotesController::class, 'getItems']);
    Route::post('v1/credit-invoice/item-price', [CustomerCreditNotesController::class, 'getItemPrice']);

    // Customer Debit Notes
    Route::apiResource('v1/customer-debit-notes', CustomerDebitNotesController::class);
    Route::post('v1/debit-bill/items', [CustomerDebitNotesController::class, 'getItems']);
    Route::post('v1/debit-bill/item-price', [CustomerDebitNotesController::class, 'getItemPrice']);

    // Dashboard
    Route::get('v1/dashboard/account', [DashboardController::class, 'account_dashboard']);
    Route::get('v1/dashboard/project', [DashboardController::class, 'project_dashboard']);
    Route::get('v1/dashboard/hrm', [DashboardController::class, 'hrm_dashboard']);
    Route::get('v1/dashboard/crm', [DashboardController::class, 'crm_dashboard']);
    Route::get('v1/dashboard/pos', [DashboardController::class, 'pos_dashboard']);
    Route::post('v1/dashboard/stop-tracker', [DashboardController::class, 'stopTracker']);

    // Double Entry Reports
    Route::get('v1/reports/ledger', [DoubleEntryReportController::class, 'ledger']);
    Route::get('v1/reports/balance-sheet', [DoubleEntryReportController::class, 'balanceSheet']);
    Route::get('v1/reports/profit-loss', [DoubleEntryReportController::class, 'profitLoss']);
    Route::get('v1/reports/trial-balance', [DoubleEntryReportController::class, 'trialBalance']);
    Route::get('v1/reports/sales', [DoubleEntryReportController::class, 'salesReport']);
    Route::get('v1/reports/assets-register', [DoubleEntryReportController::class, 'assetsRegister']);
    Route::get('v1/reports/receivables', [DoubleEntryReportController::class, 'receivablesReport']);
    Route::get('v1/reports/payables', [DoubleEntryReportController::class, 'payablesReport']);

    // Document Uploads
    Route::apiResource('v1/document-uploads', DucumentUploadController::class);

    // Form Builder
    Route::apiResource('v1/forms', FormBuilderController::class);
    Route::get('v1/forms/{form}/fields', [FormBuilderController::class, 'getFields']);
    Route::post('v1/forms/{form}/fields', [FormBuilderController::class, 'addField']);
    Route::post('v1/forms/{code}/submit', [FormBuilderController::class, 'submitForm']);
    Route::get('v1/forms/{form}/responses', [FormBuilderController::class, 'getResponses']);

    // Plans
    Route::apiResource('v1/plans', PlanController::class);
    Route::post('v1/plans/{plan}/assign', [PlanController::class, 'assign']);
    Route::post('v1/plans/{plan}/trial', [PlanController::class, 'trial']);
    Route::post('v1/plans/{plan}/disable', [PlanController::class, 'disable']);

    // Plan Requests
    Route::get('v1/plan-requests', [PlanRequestController::class, 'index']);
    Route::post('v1/plan-requests', [PlanRequestController::class, 'store']);
    Route::put('v1/plan-requests/{id}', [PlanRequestController::class, 'update']);
    Route::delete('v1/plan-requests', [PlanRequestController::class, 'destroy']);

    // Product Stock
    Route::get('v1/product-stock', [ProductStockController::class, 'index']);
    Route::put('v1/product-stock/{id}', [ProductStockController::class, 'update']);

    // Project Reports
    Route::get('v1/project-reports', [ProjectReportController::class, 'index']);
    Route::get('v1/project-reports/{id}', [ProjectReportController::class, 'show']);

    // Referral Program
    Route::prefix('v1/admin')->middleware(['auth.superadmin'])->group(function () {
        Route::get('referral-program', [ReferralProgramController::class, 'adminIndex']);
        Route::post('referral-program/settings', [ReferralProgramController::class, 'adminStore']);
        Route::put('referral-program/requests/{id}', [ReferralProgramController::class, 'handleRequest']);
    });
    Route::get('v1/referral-program', [ReferralProgramController::class, 'companyIndex']);
    Route::post('v1/referral-program/requests', [ReferralProgramController::class, 'requestAmountStore']);
    Route::delete('v1/referral-program/requests/{id}', [ReferralProgramController::class, 'requestAmountCancel']);

    // Warehouse Transfers
    Route::apiResource('v1/warehouse-transfers', WarehouseTransferController::class);
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