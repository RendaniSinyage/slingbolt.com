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
use App\Listeners\CreateClient as CreateClientListener;
use App\Listeners\CreateDeal as CreateDealListener;
use App\Listeners\CreateInvoice as CreateInvoiceListener;
use App\Listeners\UpdateClient as UpdateClientListener;
use App\Listeners\UpdateDeal as UpdateDealListener;
use App\Listeners\UpdateInvoice as UpdateInvoiceListener;
use App\Listeners\UpdateUser as UpdateUserListener;
use App\Listeners\DeleteUser as DeleteUserListener;
use App\Listeners\DeleteInvoice as DeleteInvoiceListener;
use App\Listeners\SentInvoice as SentInvoiceListener;
use App\Listeners\ResentInvoice as ResentInvoiceListener;
use App\Listeners\PaymentReminderInvoice as PaymentReminderInvoiceListener;
use App\Listeners\CreateProposalListener;
use App\Listeners\UpdateProposalListener;
use App\Listeners\DestroyProposal as DestroyProposalListener;
use App\Listeners\SentProposal as SentProposalListener;
use App\Listeners\ResentProposal as ResentProposalListener;
use App\Listeners\StatusChangeProposal as StatusChangeProposalListener;
use App\Listeners\ConvertToInvoice as ConvertToInvoiceListener;
use App\Listeners\DuplicateProposal as DuplicateProposalListener;
use App\Listeners\CreatePaymentInvoice as CreatePaymentInvoiceListener;
use App\Listeners\CreateRole as CreateRoleListener;
use App\Listeners\UpdateRole as UpdateRoleListener;
use App\Listeners\DeleteRole as DeleteRoleListener;
use App\Listeners\CreateWarehouse as CreateWarehouseListener;
use App\Listeners\UpdateWarehouse as UpdateWarehouseListener;
use App\Listeners\DeleteWarehouse as DeleteWarehouseListener;
use App\Listeners\CreateContract as CreateContractListener;
use App\Listeners\UpdateContract as UpdateContractListener;
use App\Listeners\DeleteContract as DeleteContractListener;
use App\Listeners\CreateCustomer as CreateCustomerListener;
use App\Listeners\UpdateCustomer as UpdateCustomerListener;
use App\Listeners\DeleteCustomer as DeleteCustomerListener;
use App\Listeners\CreateEmployee as CreateEmployeeListener;
use App\Listeners\UpdateEmployee as UpdateEmployeeListener;
use App\Listeners\DeleteEmployee as DeleteEmployeeListener;
use App\Listeners\CreateBill as CreateBillListener;
use App\Listeners\UpdateBill as UpdateBillListener;
use App\Listeners\DeleteBill as DeleteBillListener;
use App\Listeners\CreateExpense as CreateExpenseListener;
use App\Listeners\CreateBankAccount as CreateBankAccountListener;
use App\Listeners\UpdateBankAccount as UpdateBankAccountListener;
use App\Listeners\DeleteBankAccount as DeleteBankAccountListener;
use App\Listeners\CreateRevenue as CreateRevenueListener;
use App\Listeners\CreateAllowance as CreateAllowanceListener;
use App\Listeners\UpdateAllowance as UpdateAllowanceListener;
use App\Listeners\DeleteAllowance as DeleteAllowanceListener;
use App\Listeners\CreateAllowanceOption as CreateAllowanceOptionListener;
use App\Listeners\UpdateAllowanceOption as UpdateAllowanceOptionListener;
use App\Listeners\DeleteAllowanceOption as DeleteAllowanceOptionListener;
use App\Listeners\CreateAnnouncement as CreateAnnouncementListener;
use App\Listeners\UpdateAnnouncement as UpdateAnnouncementListener;
use App\Listeners\DeleteAnnouncement as DeleteAnnouncementListener;
use App\Listeners\CreateAppraisal as CreateAppraisalListener;
use App\Listeners\UpdateAppraisal as UpdateAppraisalListener;
use App\Listeners\DeleteAppraisal as DeleteAppraisalListener;
use App\Listeners\CreateManualAttendance as CreateManualAttendanceListener;
use App\Listeners\EmployeeClockIn as EmployeeClockInListener;
use App\Listeners\EmployeeClockOut as EmployeeClockOutListener;
use App\Listeners\CreateAward as CreateAwardListener;
use App\Listeners\UpdateAward as UpdateAwardListener;
use App\Listeners\DeleteAward as DeleteAwardListener;
use App\Listeners\CreateAwardType as CreateAwardTypeListener;
use App\Listeners\UpdateAwardType as UpdateAwardTypeListener;
use App\Listeners\DeleteAwardType as DeleteAwardTypeListener;
use App\Listeners\CreateBranch as CreateBranchListener;
use App\Listeners\UpdateBranch as UpdateBranchListener;
use App\Listeners\DeleteBranch as DeleteBranchListener;
use App\Listeners\CreateBudget as CreateBudgetListener;
use App\Listeners\UpdateBudget as UpdateBudgetListener;
use App\Listeners\DeleteBudget as DeleteBudgetListener;
use App\Listeners\CreateBugStatus as CreateBugStatusListener;
use App\Listeners\UpdateBugStatus as UpdateBugStatusListener;
use App\Listeners\DeleteBugStatus as DeleteBugStatusListener;
use App\Listeners\OrderBugStatus as OrderBugStatusListener;
use App\Listeners\CreateCommission as CreateCommissionListener;
use App\Listeners\UpdateCommission as UpdateCommissionListener;
use App\Listeners\DeleteCommission as DeleteCommissionListener;
use App\Listeners\CreateCompanyPolicy as CreateCompanyPolicyListener;
use App\Listeners\UpdateCompanyPolicy as UpdateCompanyPolicyListener;
use App\Listeners\DeleteCompanyPolicy as DeleteCompanyPolicyListener;
use App\Listeners\CreateCompetencies as CreateCompetenciesListener;
use App\Listeners\UpdateCompetencies as UpdateCompetenciesListener;
use App\Listeners\DeleteCompetencies as DeleteCompetenciesListener;
use App\Listeners\CreateComplaint as CreateComplaintListener;
use App\Listeners\UpdateComplaint as UpdateComplaintListener;
use App\Listeners\DeleteComplaint as DeleteComplaintListener;
use App\Listeners\CreateAsset as CreateAssetListener;
use App\Listeners\UpdateAsset as UpdateAssetListener;
use App\Listeners\DeleteAsset as DeleteAssetListener;
use App\Listeners\CreateChartOfAccount as CreateChartOfAccountListener;
use App\Listeners\UpdateChartOfAccount as UpdateChartOfAccountListener;
use App\Listeners\DeleteChartOfAccount as DeleteChartOfAccountListener;
use App\Listeners\CreateChartOfAccountType as CreateChartOfAccountTypeListener;
use App\Listeners\UpdateChartOfAccountType as UpdateChartOfAccountTypeListener;
use App\Listeners\DeleteChartOfAccountType as DeleteChartOfAccountTypeListener;
use App\Listeners\StoreComplianceSettings as StoreComplianceSettingsListener;
use App\Listeners\CreateContractType as CreateContractTypeListener;
use App\Listeners\UpdateContractType as UpdateContractTypeListener;
use App\Listeners\DeleteContractType as DeleteContractTypeListener;
use App\Listeners\UserCreate;
use App\Listeners\VerifyReCaptchaTokenLis;
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
