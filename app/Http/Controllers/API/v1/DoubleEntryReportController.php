<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Traits\BalanceSheetReport;
use App\Traits\PayablesReport;
use App\Traits\ProfitLossReport;
use App\Traits\SalesReceivable;
use App\Traits\SalesReport;
use App\Traits\TrialBalanceReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoubleEntryReportController extends Controller
{
    use TrialBalanceReport, BalanceSheetReport, ProfitLossReport, SalesReport, SalesReceivable, PayablesReport;

    public function ledger(Request $request)
    {
        if (Auth::user()->can('ledger report')) {
            $start = $request->input('start_date', date('Y-m-01'));
            $end = $request->input('end_date', date('Y-m-t'));
            $account_id = $request->input('account');

            $query = ChartOfAccount::where('created_by', Auth::user()->creatorId());
            if ($account_id) {
                $query->where('id', $account_id);
            }
            $chart_accounts = $query->get();

            $report_data = [];
            foreach ($chart_accounts as $account) {
                // This logic needs to be implemented based on how ledger data is calculated.
                // For demonstration, returning account info.
                $report_data[] = [
                    'account_name' => $account->name,
                    'account_code' => $account->code,
                    'transactions' => [], // Placeholder for transaction data
                ];
            }

            return response()->json($report_data);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function balanceSheet(Request $request)
    {
        if (Auth::user()->can('balance sheet report')) {
            $start = $request->input('start_date', '1900-01-01');
            $end = $request->input('end_date', date('Y-m-d'));

            $types = ChartOfAccountType::where('created_by', Auth::user()->creatorId())->whereIn('name', ['Assets', 'Liabilities', 'Equity'])->get();
            $totalAccounts = [];
            foreach ($types as $type) {
                $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();
                $subTypeArray = $this->buildSubTypeArray($type, $subTypes, $start, $end);
                $totalAccounts[$type->name] = $subTypeArray;
            }

            return response()->json($totalAccounts);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function profitLoss(Request $request)
    {
        if (Auth::user()->can('loss & profit report')) {
            $start = $request->input('start_date', date('Y-03-01'));
            $end = $request->input('end_date', date('Y-m-d'));

            $types = ChartOfAccountType::where('created_by', Auth::user()->creatorId())->whereIn('name', ['Income', 'Expenses', 'Costs of Goods Sold'])->get();
            $totalAccounts = $this->processProfitLossData($types, $start, $end);

            return response()->json($totalAccounts);
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function trialBalance(Request $request)
    {
        if (Auth::user()->can('trial balance report')) {
            $start = $request->input('start_date', '1900-01-01');
            $end = $request->input('end_date', date('Y-m-d'));

            $types = $this->getAccountTypes();
            $totalAccounts = $this->processAccountTypes($types, $start, $end);

            return response()->json($totalAccounts);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function salesReport(Request $request)
    {
        if (Auth::user()->can('sales report')) {
            $start = $request->input('start_date', date('Y-03-01'));
            $end = $request->input('end_date', date('Y-m-d'));

            $invoiceItems = $this->getInvoiceItems($start, $end);
            $invoiceCustomers = $this->getInvoiceCustomers($start, $end);

            return response()->json([
                'by_item' => $invoiceItems,
                'by_customer' => $invoiceCustomers,
            ]);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function assetsRegister(Request $request)
    {
        if (Auth::user()->can('manage assets') || Auth::user()->can('balance sheet report')) {
            $start = $request->input('start_date', '1900-01-01');
            $end = $request->input('end_date', date('Y-m-d'));

            $assetTypes = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->where('name', 'Assets')->get();
            $assetAccountsData = [];

            foreach ($assetTypes as $type) {
                $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();
                foreach ($subTypes as $subType) {
                    $accounts = ChartOfAccount::where('sub_type', $subType->id)->where('created_by', \Auth::user()->creatorId())->get();
                    foreach ($accounts as $account) {
                        // Logic to calculate asset details needs to be implemented here
                        $assetAccountsData[] = [
                            'account_name' => $account->name,
                            'account_code' => $account->code,
                            // other details
                        ];
                    }
                }
            }
            return response()->json($assetAccountsData);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function receivablesReport(Request $request)
    {
        if (Auth::user()->can('manage receivables')) {
            $start = $request->input('start_date', '1900-01-01');
            $end = $request->input('end_date', date('Y-m-d'));

            $data = [
                'customers' => $this->getCustomers(),
                'receivable_customers' => $this->getReceivableCustomers($start, $end),
                'receivable_summary' => $this->getReceivableSummaries($start, $end),
                'receivable_details' => $this->getReceivableDetails($start, $end),
                'aging_summary' => $this->getAgingSummaries($start, $end),
                'aging_details' => $this->getAgingDetails($start, $end),
            ];

            return response()->json($data);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }

    public function payablesReport(Request $request)
    {
        if (Auth::user()->can('manage payables')) {
            $start = $request->input('start_date', '1900-01-01');
            $end = $request->input('end_date', date('Y-m-d'));

            $data = [
                'vendors' => $this->getVendor(),
                'payable_vendors' => $this->getPayableVendors($start, $end),
                'payable_summary' => $this->getPayableSummaries($start, $end),
                'payable_details' => $this->getPayableDetails($start, $end),
                'aging_summary' => $this->getPayableAgingSummaries($start, $end),
                'aging_details' => $this->getPayableAgingDetails($start, $end),
            ];

            return response()->json($data);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
    }
}
