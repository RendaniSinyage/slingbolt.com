<?php

namespace App\Http\Controllers;

use App\Exports\BalanceSheetExport;
use App\Exports\ProfitLossExport;
use App\Exports\SalesReportExport;
use App\Exports\TrialBalancExport;
use App\Exports\ReceivableExport;
use App\Exports\PayableExport;
use Illuminate\Support\Facades\DB;
use App\Exports\AssetsRegisterExport;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\User;
use App\Traits\BalanceSheetReport;
use App\Traits\PayablesReport;
use App\Traits\ProfitLossReport;
use App\Traits\SalesReceivable;
use App\Traits\SalesReport;
use App\Traits\TrialBalanceReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DoubleEntryReportController extends Controller
{
    use TrialBalanceReport;
    use BalanceSheetReport;
    use ProfitLossReport;
    use SalesReport;
    use SalesReceivable;
    use PayablesReport;

    public function getReportView($request, $view, $defaultView = 'vertical')
    {
        $validViews = ['vertical', 'horizontal'];
        $viewType   = $request->view ?? $view;

        if (in_array($viewType, $validViews)) {
            return $viewType;
        }
        return $defaultView;
    }

    public function ledgerSummary(Request $request, $account = '')
    {

        if (\Auth::user()->can('ledger report')) {

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = date('Y-m-01');
                $end = date('Y-m-t');
            }
            if (!empty($request->account)) {
                $chart_accounts = ChartOfAccount::where('id', $request->account)->where('created_by', \Auth::user()->creatorId())->get();
                $accounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
                ->where('parent', '=', 0)
                ->where('created_by', \Auth::user()->creatorId())->get()
                ->toarray();

            } else {
                $chart_accounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->get();
                $accounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
                ->where('parent', '=', 0)
                ->where('created_by', \Auth::user()->creatorId())->get()
                ->toarray();
            }

            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account');
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();

            $balance = 0;
            $debit = 0;
            $credit = 0;
            $filter['balance'] = $balance;
            $filter['credit'] = $credit;
            $filter['debit'] = $debit;
            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;
            return view('doubleentry_report.ledger_summary', compact('filter', 'chart_accounts', 'accounts', 'subAccounts'));

        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function balanceSheet(Request $request, $view = '', $collapseview = 'expand')
    {
        if (\Auth::user()->can('balance sheet report')) {
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = '1900-01-01';
                $end = date('d F Y');
            }
            $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->whereIn('name', ['Assets', 'Liabilities', 'Equity'])->get();
            $totalAccounts = [];
            foreach ($types as $type) {
                $subTypes     = ChartOfAccountSubType::where('type', $type->id)->get();
                $subTypeArray = $this->buildSubTypeArray($type, $subTypes, $start, $end);
                $totalAccounts[$type->name] = $subTypeArray;
                $mainTypeIds        = $types->pluck('id')->toArray();
                $otherAccounts      = $this->getOtherAccounts($mainTypeIds, $start, $end);
                $balanceTotal       = 0;
                $currentYearEarning = [];
                foreach ($otherAccounts as $account) {
                    $balance       = $account->totalCredit - $account->totalDebit;
                    $balanceTotal += $balance;
                }
                if ($balanceTotal != 0) {
                    $currentYearEarning[] = [[
                        'account_id'   => null,
                        'account_code' => null,
                        'account_name' => 'Current Year Earnings',
                        'account'      => '',
                        'totalCredit'  => 0,
                        'totalDebit'   => 0,
                        'netAmount'    => $balanceTotal,
                    ]];
                    $totalAccounts['Equity'][] = [
                        'account' => $currentYearEarning,
                    ];
                }
            }

            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;

            $viewType                 = $this->getReportView($request, $view);
            return view('doubleentry_report.balance_sheet' . ($viewType === 'horizontal' ? '_horizontal' : ''), compact('filter', 'totalAccounts', 'collapseview'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function balanceSheetExport(Request $request)
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = '1900-01-01';
            $end = date('Y-m-t');
        }

        $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->whereIn('name', ['Assets', 'Liabilities', 'Equity'])->get();
        $totalAccounts = [];
        foreach ($types as $type) {
            $subTypes     = ChartOfAccountSubType::where('type', $type->id)->get();
            $subTypeArray = $this->buildSubTypeArray($type, $subTypes, $start, $end);
            $totalAccounts[$type->name] = $subTypeArray;
            $mainTypeIds        = $types->pluck('id')->toArray();
            $otherAccounts      = $this->getOtherAccounts($mainTypeIds, $start, $end);
            $balanceTotal       = 0;
            $currentYearEarning = [];
            foreach ($otherAccounts as $account) {
                $balance       = $account->totalCredit - $account->totalDebit;
                $balanceTotal += $balance;
            }
            if ($balanceTotal != 0) {
                $currentYearEarning[] = [[
                    'account_id'   => null,
                    'account_code' => null,
                    'account_name' => 'Current Year Earnings',
                    'account'      => '',
                    'totalCredit'  => 0,
                    'totalDebit'   => 0,
                    'netAmount'    => $balanceTotal,
                ]];
                $totalAccounts['Equity'][] = [
                    'account' => $currentYearEarning,
                ];
            }
        }

        $companyName = User::where('id', \Auth::user()->creatorId())->first();
        $companyName = $companyName->name;

        $name = 'balance_sheet_' . date('d F Y i:h:s');
        $data = Excel::download(new BalanceSheetExport($totalAccounts, $start, $end, $companyName), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function profitLoss(Request $request, $view = '', $collapseView = 'expand')
    {
        if (\Auth::user()->can('loss & profit report')) {
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
               // For current tax year (1 March to current date)
               if (date('n') >= 3) {
                   // We're in March-December, so current tax year started this year
                   $start = date('Y-03-01');
               } else {
                   // We're in Jan-Feb, so current tax year started last year
                   $start = date('Y-03-01', strtotime('-1 year'));
               }
                $end = date('d F Y');
            }
            $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->whereIn('name', ['Income', 'Expenses', 'Costs of Goods Sold'])->get();

            $totalAccounts = $this->processProfitLossData($types, $start, $end);

            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;

            $viewType = $this->getReportView($request, $view);
            return view('doubleentry_report.profit_loss' . ($viewType === 'horizontal' ? '_horizontal' : ''),
                compact('filter', 'totalAccounts', 'collapseView'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profitLossExport(Request $request)
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            // For current tax year (1 March to current date)
            if (date('n') >= 3) {
                // We're in March-December, so current tax year started this year
                $start = date('Y-03-01');
            } else {
                // We're in Jan-Feb, so current tax year started last year
                $start = date('Y-03-01', strtotime('-1 year'));
            }
            $end = date('d F Y');
        }

        $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->whereIn('name', ['Income', 'Expenses', 'Costs of Goods Sold'])->get();

        $totalAccounts = $this->processProfitLossData($types, $start, $end);

        $companyName = User::where('id', \Auth::user()->creatorId())->first();
        $companyName = $companyName->name;

        $name = 'profit & loss_' . date('d F Y i:h:s');
        $data = Excel::download(new ProfitLossExport($totalAccounts, $start, $end, $companyName), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function trialBalanceSummary(Request $request, $view = "expand")
    {
        if (\Auth::user()->can('trial balance report')) {

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = '1900-01-01';
                $end = date('d F Y');
            }

            $types = $this->getAccountTypes();
            $totalAccounts = $this->processAccountTypes($types, $start, $end);
            $filter['startDateRange'] = $start;
            $filter['endDateRange'] = $end;
            return view('doubleentry_report.trial_balance', compact('filter', 'totalAccounts', 'view'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function trialBalanceExport(Request $request)
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = '1900-01-01';
            $end = date('d F Y');
        }

        $types         = $this->getAccountTypes();
        $totalAccounts = $this->processAccountTypes($types, $start, $end);

        $companyName = User::where('id', \Auth::user()->creatorId())->first();
        $companyName = $companyName->name;

        $name = 'trial_balance_' . date('d F Y i:h:s');
        $data = Excel::download(new TrialBalancExport($totalAccounts, $start, $end, $companyName), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function salesReport(Request $request)
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
           // For current tax year (1 March to current date)
           if (date('n') >= 3) {
               // We're in March-December, so current tax year started this year
               $start = date('Y-03-01');
           } else {
               // We're in Jan-Feb, so current tax year started last year
               $start = date('Y-03-01', strtotime('-1 year'));
           }
            $end = date('d F Y');
        }

        $invoiceItems     = $this->getInvoiceItems($start, $end);
        $invoiceCustomers = $this->getInvoiceCustomers($start, $end);

        $filter['startDateRange'] = $start;
        $filter['endDateRange'] = $end;

        return view('doubleentry_report.sales_report', compact('filter', 'invoiceItems', 'invoiceCustomers'));
    }

    public function salesReportExport(Request $request)
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            // For current tax year (1 March to current date)
            if (date('n') >= 3) {
                // We're in March-December, so current tax year started this year
                $start = date('Y-03-01');
            } else {
                // We're in Jan-Feb, so current tax year started last year
                $start = date('Y-03-01', strtotime('-1 year'));
            }
            $end = date('d F Y');
        }
        if ($request->report == '#item') {
            $invoiceItems     = $this->getInvoiceItems($start, $end);
            $reportName = 'Item';
        } else {
            $invoiceItems = $this->getInvoiceCustomers($start, $end);
            $reportName = 'Customer';
        }
        $companyName = User::where('id', \Auth::user()->creatorId())->first();
        $companyName = $companyName->name;

        $name = 'Sales By ' . $reportName . '_ ' . date('d F Y i:h:s');
        $data = Excel::download(new SalesReportExport($invoiceItems, $start, $end, $companyName, $reportName), $name . '.xlsx');
        ob_end_clean();

        return $data;

    }

// ============================================================================
// ASSETS REGISTER - NEW FUNCTIONALITY
// ============================================================================

public function assetsRegister(Request $request)
{
    if (\Auth::user()->can('manage assets') || \Auth::user()->can('balance sheet report')) {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            // Assets register should show from inception (all assets ever purchased)
            $start = '1900-01-01';
            $end = date('d F Y');
        }

        // Get all asset accounts
        $assetTypes = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())
                        ->where('name', 'Assets')
                        ->get();

        $assetAccounts = [];
        $totalAssetValue = 0;
        $totalDepreciation = 0;
        $netAssetValue = 0;

        foreach ($assetTypes as $type) {
            $subTypes = ChartOfAccountSubType::where('type', $type->id)
                            ->whereIn('name', ['Non-current Asset', 'Current Asset'])
                            ->get();

            foreach ($subTypes as $subType) {
                $accounts = ChartOfAccount::where('sub_type', $subType->id)
                                ->where('created_by', \Auth::user()->creatorId())
                                ->where('is_enabled', 1)
                                ->get();

                foreach ($accounts as $account) {
                    // Get account balance
                    $balance = $this->getAccountBalance($account->id, $start, $end);

                    if ($balance != 0) {
                        $isDepreciationAccount = strpos(strtolower($account->name), 'depreciation') !== false
                                              || strpos(strtolower($account->name), 'accum') !== false;

                        $assetAccounts[] = [
                            'account_id' => $account->id,
                            'account_code' => $account->code,
                            'account_name' => $account->name,
                            'sub_type' => $subType->name,
                            'balance' => $balance,
                            'is_depreciation' => $isDepreciationAccount,
                            'purchase_date' => $this->getFirstTransactionDate($account->id),
                        ];

                        if ($isDepreciationAccount) {
                            $totalDepreciation += abs($balance); // Depreciation is usually negative
                        } else {
                            $totalAssetValue += $balance;
                        }
                    }
                }
            }
        }

        $netAssetValue = $totalAssetValue - $totalDepreciation;

        $filter['startDateRange'] = $start;
        $filter['endDateRange'] = $end;

        return view('doubleentry_report.assets_register', compact(
            'filter', 'assetAccounts', 'totalAssetValue', 'totalDepreciation', 'netAssetValue'
        ));
    } else {
        return redirect()->back()->with('error', __('Permission Denied.'));
    }
}

public function assetsRegisterExport(Request $request)
{
    if (\Auth::user()->can('manage assets') || \Auth::user()->can('balance sheet report')) {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = '1900-01-01';
            $end = date('d F Y');
        }

        // Get all asset accounts (same logic as assetsRegister method)
        $assetTypes = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())
                        ->where('name', 'Assets')
                        ->get();

        $assetAccounts = [];
        $totalAssetValue = 0;
        $totalDepreciation = 0;

        foreach ($assetTypes as $type) {
            $subTypes = ChartOfAccountSubType::where('type', $type->id)
                            ->whereIn('name', ['Non-current Asset', 'Current Asset'])
                            ->get();

            foreach ($subTypes as $subType) {
                $accounts = ChartOfAccount::where('sub_type', $subType->id)
                                ->where('created_by', \Auth::user()->creatorId())
                                ->where('is_enabled', 1)
                                ->get();

                foreach ($accounts as $account) {
                    // Get account balance
                    $balance = $this->getAccountBalance($account->id, $start, $end);

                    if ($balance != 0) {
                        $isDepreciationAccount = strpos(strtolower($account->name), 'depreciation') !== false
                                              || strpos(strtolower($account->name), 'accum') !== false;

                        $assetAccounts[] = [
                            'account_id' => $account->id,
                            'account_code' => $account->code,
                            'account_name' => $account->name,
                            'sub_type' => $subType->name,
                            'balance' => $balance,
                            'is_depreciation' => $isDepreciationAccount,
                            'purchase_date' => $this->getFirstTransactionDate($account->id),
                        ];

                        if ($isDepreciationAccount) {
                            $totalDepreciation += abs($balance);
                        } else {
                            $totalAssetValue += $balance;
                        }
                    }
                }
            }
        }

        $netAssetValue = $totalAssetValue - $totalDepreciation;

        // Get company name for the export
        $companyName = User::where('id', \Auth::user()->creatorId())->first()->name;
        $name = 'assets_register_' . date('d F Y_H-i-s');

        // Create the export data structure
        $exportData = [
            'assetAccounts' => $assetAccounts,
            'totalAssetValue' => $totalAssetValue,
            'totalDepreciation' => $totalDepreciation,
            'netAssetValue' => $netAssetValue,
            'start_date' => $start,
            'end_date' => $end,
            'company_name' => $companyName
        ];

        // Download the Excel file
        $data = Excel::download(new AssetsRegisterExport($exportData), $name . '.xlsx');
        ob_end_clean();

        return $data;
    } else {
        return redirect()->back()->with('error', __('Permission Denied.'));
    }
}

// Helper methods for assets register
private function getAccountBalance($accountId, $start, $end)
{
    $transactions = DB::table('add_transaction_lines')
        ->where('account_id', $accountId)
        ->where('created_by', \Auth::user()->creatorId())
        ->where('date', '>=', $start)
        ->where('date', '<=', $end)
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();

    if ($transactions) {
        return ($transactions->total_debit - $transactions->total_credit);
    }

    return 0;
}

private function getFirstTransactionDate($accountId)
{
    $firstTransaction = DB::table('add_transaction_lines')
        ->where('account_id', $accountId)
        ->where('created_by', \Auth::user()->creatorId())
        ->orderBy('date', 'asc')
        ->first();

    return $firstTransaction ? $firstTransaction->date : null;
}

    public function ReceivablesReport(Request $request)
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = '1900-01-01';
            $end = date('d F Y');
        }

        $customers           = $this->getCustomers();
        $receivableCustomers = $this->getReceivableCustomers($start, $end);
        $receivableSummaries = $this->getReceivableSummaries($start, $end);
        $receivableDetails   = $this->getReceivableDetails($start, $end);
        $agingSummaries      = $this->getAgingSummaries($start, $end);
        $agingDetails        = $this->getAgingDetails($start, $end);

        $moreThan45 = $agingDetails['moreThan45'] ?? [];
        $days31to45 = $agingDetails['days31to45'] ?? [];
        $days16to30 = $agingDetails['days16to30'] ?? [];
        $days1to15  = $agingDetails['days1to15'] ?? [];
        $currents   = $agingDetails['currents'] ?? [];

        $filter['startDateRange'] = $start;
        $filter['endDateRange'] = $end;

        return view('doubleentry_report.receivable_report', compact('filter', 'receivableCustomers', 'receivableSummaries', 'receivableDetails', 'agingSummaries', 'currents', 'days1to15', 'days16to30', 'days31to45', 'moreThan45'));
    }

public function receivableExport(Request $request)
{
    if(\Auth::user()->can('manage receivables'))
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = date('Y-01-01');
            $end = date('Y-m-d');
        }

        $tabType = $request->report; // The active tab (#customer_balance, #receivable_summary, etc.)

        $authUser = \Auth::user()->creatorId();
        $user = \App\Models\User::find($authUser);

        // Get the data using the same methods as in ReceivablesReport
        switch($tabType) {
            case '#customer_balance':
                $data = $this->getReceivableCustomers($start, $end);
                $fileName = 'receivable_customer_balance_';
                break;

            case '#receivable_summary':
                $data = $this->getReceivableSummaries($start, $end);
                $fileName = 'receivable_summary_';
                break;

            case '#receivable_details':
                $data = $this->getReceivableDetails($start, $end);
                $fileName = 'receivable_details_';
                break;

            case '#aging_summary':
                $data = $this->getAgingSummaries($start, $end);
                $fileName = 'aging_summary_';
                break;

            case '#aging_details':
                // For aging details, get all the aging arrays
                $agingDetails = $this->getAgingDetails($start, $end);
                $data = [
                    'moreThan45' => $agingDetails['moreThan45'] ?? [],
                    'days31to45' => $agingDetails['days31to45'] ?? [],
                    'days16to30' => $agingDetails['days16to30'] ?? [],
                    'days1to15' => $agingDetails['days1to15'] ?? [],
                    'currents' => $agingDetails['currents'] ?? []
                ];
                $fileName = 'aging_details_';
                break;

            default:
                $data = $this->getReceivableCustomers($start, $end);
                $fileName = 'receivable_customer_balance_';
                $tabType = '#customer_balance';
                break;
        }

        $name = $fileName . date('Y-m-d_H-i-s');
        $exportData = Excel::download(new ReceivableExport($data, $start, $end, $user->name, $tabType), $name . '.xlsx');
        ob_end_clean();

        return $exportData;
    }
    else
    {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}

    public function PayablesReport(Request $request)
    {
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $start = $request->start_date;
            $end = $request->end_date;
        } else {
            $start = '1900-01-01';
            $end = date('d F Y');
        }

        $vendor           = $this->getVendor();
        $payableVendors   = $this->getPayableVendors($start, $end);
        $payableSummaries = $this->getPayableSummaries($start, $end);
        $payableDetails   = $this->getPayableDetails($start, $end);
        $agingSummaries   = $this->getPayableAgingSummaries($start, $end);
        $agingDetails     = $this->getPayableAgingDetails($start, $end);

        $moreThan45 = $agingDetails['moreThan45'] ?? [];
        $days31to45 = $agingDetails['days31to45'] ?? [];
        $days16to30 = $agingDetails['days16to30'] ?? [];
        $days1to15  = $agingDetails['days1to15'] ?? [];
        $currents   = $agingDetails['currents'] ?? [];

        $filter['startDateRange'] = $start;
        $filter['endDateRange'] = $end;

        return view('doubleentry_report.payable_report', compact('filter', 'payableVendors','payableSummaries', 'payableDetails', 'agingSummaries', 'moreThan45', 'days31to45', 'days16to30', 'days1to15', 'currents', 'vendor'));
    }

    // Add this method to your DoubleEntryReportController

    public function payableExport(Request $request)
    {
        if(\Auth::user()->can('manage receivables'))
        {
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end = $request->end_date;
            } else {
                $start = date('Y-01-01');
                $end = date('Y-m-d');
            }

            $tabType = $request->report; // The active tab (#vendor_balance, #payable_summary, #payable_details)

            $authUser = \Auth::user()->creatorId();
            $user = \App\Models\User::find($authUser);

            // Get the data using the same methods as in PayablesReport
            switch($tabType) {
                case '#vendor_balance':
                    $data = $this->getPayableVendors($start, $end);
                    $fileName = 'payable_vendor_balance_';
                    break;

                case '#payable_summary':
                    $data = $this->getPayableSummaries($start, $end);
                    $fileName = 'payable_summary_';
                    break;

                case '#payable_details':
                    $data = $this->getPayableDetails($start, $end);
                    $fileName = 'payable_details_';
                    break;

                default:
                    $data = $this->getPayableVendors($start, $end);
                    $fileName = 'payable_vendor_balance_';
                    $tabType = '#vendor_balance';
                    break;
            }

            $name = $fileName . date('Y-m-d_H-i-s');
            $exportData = Excel::download(new PayableExport($data, $start, $end, $user->name, $tabType), $name . '.xlsx');
            ob_end_clean();

            return $exportData;
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
