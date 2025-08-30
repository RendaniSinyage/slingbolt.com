<?php

namespace App\Providers;

use App\Events\CreateUser;
use App\Events\CreateClient;
use App\Events\CreateDeal;
use App\Events\CreateInvoice;
use App\Events\UpdateClient;
use App\Events\UpdateDeal;
use App\Events\UpdateInvoice;
use App\Events\UpdateUser;
use App\Events\DeleteUser;
use App\Events\DeleteInvoice;
use App\Events\SentInvoice;
use App\Events\ResentInvoice;
use App\Events\PaymentReminderInvoice;
use App\Events\CreateProposal;
use App\Events\UpdateProposal;
use App\Events\DestroyProposal;
use App\Events\SentProposal;
use App\Events\ResentProposal;
use App\Events\StatusChangeProposal;
use App\Events\ConvertToInvoice;
use App\Events\DuplicateProposal;
use App\Events\CreatePaymentInvoice;
use App\Events\CreateRole;
use App\Events\UpdateRole;
use App\Events\DeleteRole;
use App\Events\CreateWarehouse;
use App\Events\UpdateWarehouse;
use App\Events\DeleteWarehouse;
use App\Events\CreateContract;
use App\Events\UpdateContract;
use App\Events\DeleteContract;
use App\Events\CreateCustomer;
use App\Events\UpdateCustomer;
use App\Events\DeleteCustomer;
use App\Events\CreateEmployee;
use App\Events\UpdateEmployee;
use App\Events\DeleteEmployee;
use App\Events\CreateBill;
use App\Events\UpdateBill;
use App\Events\DeleteBill;
use App\Events\CreateExpense;
use App\Events\CreateBankAccount;
use App\Events\UpdateBankAccount;
use App\Events\DeleteBankAccount;
use App\Events\CreateRevenue;
use App\Events\CreateAllowance;
use App\Events\UpdateAllowance;
use App\Events\DeleteAllowance;
use App\Events\CreateAllowanceOption;
use App\Events\UpdateAllowanceOption;
use App\Events\DeleteAllowanceOption;
use App\Events\CreateAnnouncement;
use App\Events\UpdateAnnouncement;
use App\Events\DeleteAnnouncement;
use App\Events\CreateAppraisal;
use App\Events\UpdateAppraisal;
use App\Events\DeleteAppraisal;
use App\Events\CreateManualAttendance;
use App\Events\EmployeeClockIn;
use App\Events\EmployeeClockOut;
use App\Events\CreateAward;
use App\Events\UpdateAward;
use App\Events\DeleteAward;
use App\Events\CreateAwardType;
use App\Events\UpdateAwardType;
use App\Events\DeleteAwardType;
use App\Events\CreateBranch;
use App\Events\UpdateBranch;
use App\Events\DeleteBranch;
use App\Events\CreateBudget;
use App\Events\UpdateBudget;
use App\Events\DeleteBudget;
use App\Events\CreateBugStatus;
use App\Events\UpdateBugStatus;
use App\Events\DeleteBugStatus;
use App\Events\OrderBugStatus;
use App\Events\CreateCommission;
use App\Events\UpdateCommission;
use App\Events\DeleteCommission;
use App\Events\CreateCompanyPolicy;
use App\Events\UpdateCompanyPolicy;
use App\Events\DeleteCompanyPolicy;
use App\Events\CreateCompetencies;
use App\Events\UpdateCompetencies;
use App\Events\DeleteCompetencies;
use App\Events\CreateComplaint;
use App\Events\UpdateComplaint;
use App\Events\DeleteComplaint;
use App\Events\CreateAsset;
use App\Events\UpdateAsset;
use App\Events\DeleteAsset;
use App\Events\CreateChartOfAccount;
use App\Events\UpdateChartOfAccount;
use App\Events\DeleteChartOfAccount;
use App\Events\CreateChartOfAccountType;
use App\Events\UpdateChartOfAccountType;
use App\Events\DeleteChartOfAccountType;
use App\Events\StoreComplianceSettings;
use App\Events\CreateContractType;
use App\Events\UpdateContractType;
use App\Events\DeleteContractType;
use App\Events\VerifyReCaptchaToken;
use App\Listeners\CreateClientListener;
use App\Listeners\CreateDealListener;
use App\Listeners\CreateInvoiceListener;
use App\Listeners\UpdateClientListener;
use App\Listeners\UpdateDealListener;
use App\Listeners\UpdateInvoiceListener;
use App\Listeners\UpdateUserListener;
use App\Listeners\DeleteUserListener;
use App\Listeners\DeleteInvoiceListener;
use App\Listeners\SentInvoiceListener;
use App\Listeners\ResentInvoiceListener;
use App\Listeners\PaymentReminderInvoiceListener;
use App\Listeners\CreateProposalListener;
use App\Listeners\UpdateProposalListener;
use App\Listeners\DestroyProposalListener;
use App\Listeners\SentProposalListener;
use App\Listeners\ResentProposalListener;
use App\Listeners\StatusChangeProposalListener;
use App\Listeners\ConvertToInvoiceListener;
use App\Listeners\DuplicateProposalListener;
use App\Listeners\CreatePaymentInvoiceListener;
use App\Listeners\CreateRoleListener;
use App\Listeners\UpdateRoleListener;
use App\Listeners\DeleteRoleListener;
use App\Listeners\CreateWarehouseListener;
use App\Listeners\UpdateWarehouseListener;
use App\Listeners\DeleteWarehouseListener;
use App\Listeners\CreateContractListener;
use App\Listeners\UpdateContractListener;
use App\Listeners\DeleteContractListener;
use App\Listeners\CreateCustomerListener;
use App\Listeners\UpdateCustomerListener;
use App\Listeners\DeleteCustomerListener;
use App\Listeners\CreateEmployeeListener;
use App\Listeners\UpdateEmployeeListener;
use App\Listeners\DeleteEmployeeListener;
use App\Listeners\CreateBillListener;
use App\Listeners\UpdateBillListener;
use App\Listeners\DeleteBillListener;
use App\Listeners\CreateExpenseListener;
use App\Listeners\CreateBankAccountListener;
use App\Listeners\UpdateBankAccountListener;
use App\Listeners\DeleteBankAccountListener;
use App\Listeners\CreateRevenueListener;
use App\Listeners\CreateAllowanceListener;
use App\Listeners\UpdateAllowanceListener;
use App\Listeners\DeleteAllowanceListener;
use App\Listeners\CreateAllowanceOptionListener;
use App\Listeners\UpdateAllowanceOptionListener;
use App\Listeners\DeleteAllowanceOptionListener;
use App\Listeners\CreateAnnouncementListener;
use App\Listeners\UpdateAnnouncementListener;
use App\Listeners\DeleteAnnouncementListener;
use App\Listeners\CreateAppraisalListener;
use App\Listeners\UpdateAppraisalListener;
use App\Listeners\DeleteAppraisalListener;
use App\Listeners\CreateManualAttendanceListener;
use App\Listeners\EmployeeClockInListener;
use App\Listeners\EmployeeClockOutListener;
use App\Listeners\CreateAwardListener;
use App\Listeners\UpdateAwardListener;
use App\Listeners\DeleteAwardListener;
use App\Listeners\CreateAwardTypeListener;
use App\Listeners\UpdateAwardTypeListener;
use App\Listeners\DeleteAwardTypeListener;
use App\Listeners\CreateBranchListener;
use App\Listeners\UpdateBranchListener;
use App\Listeners\DeleteBranchListener;
use App\Listeners\CreateBudgetListener;
use App\Listeners\UpdateBudgetListener;
use App\Listeners\DeleteBudgetListener;
use App\Listeners\CreateBugStatusListener;
use App\Listeners\UpdateBugStatusListener;
use App\Listeners\DeleteBugStatusListener;
use App\Listeners\OrderBugStatusListener;
use App\Listeners\CreateCommissionListener;
use App\Listeners\UpdateCommissionListener;
use App\Listeners\DeleteCommissionListener;
use App\Listeners\CreateCompanyPolicyListener;
use App\Listeners\UpdateCompanyPolicyListener;
use App\Listeners\DeleteCompanyPolicyListener;
use App\Listeners\CreateCompetenciesListener;
use App\Listeners\UpdateCompetenciesListener;
use App\Listeners\DeleteCompetenciesListener;
use App\Listeners\CreateComplaintListener;
use App\Listeners\UpdateComplaintListener;
use App\Listeners\DeleteComplaintListener;
use App\Listeners\CreateAssetListener;
use App\Listeners\UpdateAssetListener;
use App\Listeners\DeleteAssetListener;
use App\Listeners\CreateChartOfAccountListener;
use App\Listeners\UpdateChartOfAccountListener;
use App\Listeners\DeleteChartOfAccountListener;
use App\Listeners\CreateChartOfAccountTypeListener;
use App\Listeners\UpdateChartOfAccountTypeListener;
use App\Listeners\DeleteChartOfAccountTypeListener;
use App\Listeners\StoreComplianceSettingsListener;
use App\Listeners\CreateContractTypeListener;
use App\Listeners\UpdateContractTypeListener;
use App\Listeners\DeleteContractTypeListener;
use App\Listeners\UserCreateListener;
use App\Listeners\VerifyReCaptchaTokenListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        VerifyReCaptchaToken::class => [
            VerifyReCaptchaTokenLis::class,
        ],
        CreateUser::class => [
            UserCreate::class,
        ],
        CreateClient::class => [
            CreateClientListener::class,
        ],
        CreateDeal::class => [
            CreateDealListener::class,
        ],
        CreateInvoice::class => [
            CreateInvoiceListener::class,
        ],
        UpdateUser::class => [
            UpdateUserListener::class,
        ],
        UpdateClient::class => [
            UpdateClientListener::class,
        ],
        UpdateDeal::class => [
            UpdateDealListener::class,
        ],
        UpdateInvoice::class => [
            UpdateInvoiceListener::class,
        ],
        DeleteUser::class => [
            DeleteUserListener::class,
        ],
        DeleteInvoice::class => [
            DeleteInvoiceListener::class,
        ],
        SentInvoice::class => [
            SentInvoiceListener::class,
        ],
        ResentInvoice::class => [
            ResentInvoiceListener::class,
        ],
        PaymentReminderInvoice::class => [
            PaymentReminderInvoiceListener::class,
        ],
        CreateProposal::class => [
            CreateProposalListener::class,
        ],
        UpdateProposal::class => [
            UpdateProposalListener::class,
        ],
        DestroyProposal::class => [
            DestroyProposalListener::class,
        ],
        SentProposal::class => [
            SentProposalListener::class,
        ],
        ResentProposal::class => [
            ResentProposalListener::class,
        ],
        StatusChangeProposal::class => [
            StatusChangeProposalListener::class,
        ],
        ConvertToInvoice::class => [
            ConvertToInvoiceListener::class,
        ],
        DuplicateProposal::class => [
            DuplicateProposalListener::class,
        ],
        CreatePaymentInvoice::class => [
            CreatePaymentInvoiceListener::class,
        ],
        CreateRole::class => [
            CreateRoleListener::class,
        ],
        UpdateRole::class => [
            UpdateRoleListener::class,
        ],
        DeleteRole::class => [
            DeleteRoleListener::class,
        ],
        CreateWarehouse::class => [
            CreateWarehouseListener::class,
        ],
        UpdateWarehouse::class => [
            UpdateWarehouseListener::class,
        ],
        DeleteWarehouse::class => [
            DeleteWarehouseListener::class,
        ],
        CreateContract::class => [
            CreateContractListener::class,
        ],
        UpdateContract::class => [
            UpdateContractListener::class,
        ],
        DeleteContract::class => [
            DeleteContractListener::class,
        ],
        CreateCustomer::class => [
            CreateCustomerListener::class,
        ],
        UpdateCustomer::class => [
            UpdateCustomerListener::class,
        ],
        DeleteCustomer::class => [
            DeleteCustomerListener::class,
        ],
        CreateEmployee::class => [
            CreateEmployeeListener::class,
        ],
        UpdateEmployee::class => [
            UpdateEmployeeListener::class,
        ],
        DeleteEmployee::class => [
            DeleteEmployeeListener::class,
        ],
        CreateBill::class => [
            CreateBillListener::class,
        ],
        UpdateBill::class => [
            UpdateBillListener::class,
        ],
        DeleteBill::class => [
            DeleteBillListener::class,
        ],
        CreateExpense::class => [
            CreateExpenseListener::class,
        ],
        CreateBankAccount::class => [
            CreateBankAccountListener::class,
        ],
        UpdateBankAccount::class => [
            UpdateBankAccountListener::class,
        ],
        DeleteBankAccount::class => [
            DeleteBankAccountListener::class,
        ],
        CreateRevenue::class => [
            CreateRevenueListener::class,
        ],
        CreateAllowance::class => [
            CreateAllowanceListener::class,
        ],
        UpdateAllowance::class => [
            UpdateAllowanceListener::class,
        ],
        DeleteAllowance::class => [
            DeleteAllowanceListener::class,
        ],
        CreateAllowanceOption::class => [
            CreateAllowanceOptionListener::class,
        ],
        UpdateAllowanceOption::class => [
            UpdateAllowanceOptionListener::class,
        ],
        DeleteAllowanceOption::class => [
            DeleteAllowanceOptionListener::class,
        ],
        CreateAnnouncement::class => [
            CreateAnnouncementListener::class,
        ],
        UpdateAnnouncement::class => [
            UpdateAnnouncementListener::class,
        ],
        DeleteAnnouncement::class => [
            DeleteAnnouncementListener::class,
        ],
        CreateAppraisal::class => [
            CreateAppraisalListener::class,
        ],
        UpdateAppraisal::class => [
            UpdateAppraisalListener::class,
        ],
        DeleteAppraisal::class => [
            DeleteAppraisalListener::class,
        ],
        CreateManualAttendance::class => [
            CreateManualAttendanceListener::class,
        ],
        EmployeeClockIn::class => [
            EmployeeClockInListener::class,
        ],
        EmployeeClockOut::class => [
            EmployeeClockOutListener::class,
        ],
        CreateAward::class => [
            CreateAwardListener::class,
        ],
        UpdateAward::class => [
            UpdateAwardListener::class,
        ],
        DeleteAward::class => [
            DeleteAwardListener::class,
        ],
        CreateAwardType::class => [
            CreateAwardTypeListener::class,
        ],
        UpdateAwardType::class => [
            UpdateAwardTypeListener::class,
        ],
        DeleteAwardType::class => [
            DeleteAwardTypeListener::class,
        ],
        CreateBranch::class => [
            CreateBranchListener::class,
        ],
        UpdateBranch::class => [
            UpdateBranchListener::class,
        ],
        DeleteBranch::class => [
            DeleteBranchListener::class,
        ],
        CreateBudget::class => [
            CreateBudgetListener::class,
        ],
        UpdateBudget::class => [
            UpdateBudgetListener::class,
        ],
        DeleteBudget::class => [
            DeleteBudgetListener::class,
        ],
        CreateBugStatus::class => [
            CreateBugStatusListener::class,
        ],
        UpdateBugStatus::class => [
            UpdateBugStatusListener::class,
        ],
        DeleteBugStatus::class => [
            DeleteBugStatusListener::class,
        ],
        OrderBugStatus::class => [
            OrderBugStatusListener::class,
        ],
        CreateCommission::class => [
            CreateCommissionListener::class,
        ],
        UpdateCommission::class => [
            UpdateCommissionListener::class,
        ],
        DeleteCommission::class => [
            DeleteCommissionListener::class,
        ],
        CreateCompanyPolicy::class => [
            CreateCompanyPolicyListener::class,
        ],
        UpdateCompanyPolicy::class => [
            UpdateCompanyPolicyListener::class,
        ],
        DeleteCompanyPolicy::class => [
            DeleteCompanyPolicyListener::class,
        ],
        CreateCompetencies::class => [
            CreateCompetenciesListener::class,
        ],
        UpdateCompetencies::class => [
            UpdateCompetenciesListener::class,
        ],
        DeleteCompetencies::class => [
            DeleteCompetenciesListener::class,
        ],
        CreateComplaint::class => [
            CreateComplaintListener::class,
        ],
        UpdateComplaint::class => [
            UpdateComplaintListener::class,
        ],
        DeleteComplaint::class => [
            DeleteComplaintListener::class,
        ],
        CreateAsset::class => [
            CreateAssetListener::class,
        ],
        UpdateAsset::class => [
            UpdateAssetListener::class,
        ],
        DeleteAsset::class => [
            DeleteAssetListener::class,
        ],
        CreateChartOfAccount::class => [
            CreateChartOfAccountListener::class,
        ],
        UpdateChartOfAccount::class => [
            UpdateChartOfAccountListener::class,
        ],
        DeleteChartOfAccount::class => [
            DeleteChartOfAccountListener::class,
        ],
        CreateChartOfAccountType::class => [
            CreateChartOfAccountTypeListener::class,
        ],
        UpdateChartOfAccountType::class => [
            UpdateChartOfAccountTypeListener::class,
        ],
        DeleteChartOfAccountType::class => [
            DeleteChartOfAccountTypeListener::class,
        ],
        StoreComplianceSettings::class => [
            StoreComplianceSettingsListener::class,
        ],
        CreateContractType::class => [
            CreateContractTypeListener::class,
        ],
        UpdateContractType::class => [
            UpdateContractTypeListener::class,
        ],
        DeleteContractType::class => [
            DeleteContractTypeListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
