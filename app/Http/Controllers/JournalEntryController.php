<?php

namespace App\Http\Controllers;

use App\Models\AddTransactionLine;
use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Utility;
use Illuminate\Http\Request;

class JournalEntryController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage journal entry')) {
            $journalEntries = JournalEntry::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('journalEntry.index', compact('journalEntries'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create journal entry')) {
            $accountTypes = ChartOfAccountType::where('created_by', '=', \Auth::user()->creatorId())->get();

            $chartAccounts = [];

            foreach ($accountTypes as $type) {
                $accountTypes = ChartOfAccountSubType::where('type', $type->id)
                    ->where('created_by', '=', \Auth::user()->creatorId())
                    // ADDED: Exclude AR/AP from journal entry interface (same as edit method)
                    ->whereNotIn('name', ['Accounts Receivable', 'Accounts Payable'])
                    ->get();

                $temp = [];

                foreach ($accountTypes as $accountType) {
                    $chartOfAccounts = ChartOfAccount::where('sub_type', $accountType->id)->where('parent', '=', 0)
                        ->where('created_by', '=', \Auth::user()->creatorId())
                        ->get();

                    $incomeSubAccounts = ChartOfAccount::where('sub_type', $accountType->id)->where('parent', '!=', 0)
                        ->where('created_by', '=', \Auth::user()->creatorId())
                        ->get();

                    $tempData = [
                        'account_name'      => $accountType->name,
                        'chart_of_accounts' => [],
                        'subAccounts'       => [],
                    ];
                    foreach ($chartOfAccounts as $chartOfAccount) {
                        $tempData['chart_of_accounts'][] = [
                            'id'             => $chartOfAccount->id,
                            'account_number' => $chartOfAccount->account_number,
                            'account_name'   => $chartOfAccount->name,
                        ];
                    }

                    foreach ($incomeSubAccounts as $chartOfAccount) {
                        $tempData['subAccounts'][] = [
                            'id'             => $chartOfAccount->id,
                            'account_number' => $chartOfAccount->account_number,
                            'account_name'   => $chartOfAccount->name,
                            'parent'         => $chartOfAccount->parent,
                            'parent_account' => !empty($chartOfAccount->parentAccount) ? $chartOfAccount->parentAccount->account : 0,
                        ];
                    }
                    $temp[$accountType->id] = $tempData;
                }

                $chartAccounts[$type->name] = $temp;
            }

            $journalId = $this->journalNumber();

            return view('journalEntry.create', compact('chartAccounts', 'journalId'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create invoice')) {
            $validator = \Validator::make(
                $request->all(), [
                    'date' => 'required',
                    'accounts' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $accounts = $request->accounts;

            $totalDebit = 0;
            $totalCredit = 0;
            for ($i = 0; $i < count($accounts); $i++) {
                $debit = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                $credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            if ($totalCredit != $totalDebit) {
                return redirect()->back()->with('error', __('Debit and Credit must be Equal.'));
            }

            $journal = new JournalEntry();
            $journal->journal_id = $this->journalNumber();
            $journal->date = $request->date;
            $journal->reference = $request->reference;
            $journal->description = $request->description;
            $journal->created_by = \Auth::user()->creatorId();
            $journal->save();

            for ($i = 0; $i < count($accounts); $i++) {
                $journalItem = new JournalItem();
                $journalItem->journal = $journal->id;
                $journalItem->account = $accounts[$i]['account'];
                $journalItem->description = $accounts[$i]['description'];
                $journalItem->debit = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                $journalItem->credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                $journalItem->save();

                // Update bank account balances if applicable
                $bankAccounts = BankAccount::where('chart_account_id', '=', $accounts[$i]['account'])->get();
                if (!empty($bankAccounts)) {
                    foreach ($bankAccounts as $bankAccount) {
                        $old_balance = $bankAccount->opening_balance;
                        if ($journalItem->debit > 0) {
                            $new_balance = $old_balance - $journalItem->debit;
                        }
                        if ($journalItem->credit > 0) {
                            $new_balance = $old_balance + $journalItem->credit;
                        }
                        if (isset($new_balance)) {
                            $bankAccount->opening_balance = $new_balance;
                            $bankAccount->save();
                        }
                    }
                }

                // Record transaction lines for the specified accounts only
                if (isset($accounts[$i]['debit']) && $accounts[$i]['debit'] > 0) {
                    $data = [
                        'account_id'         => $accounts[$i]['account'],
                        'transaction_type'   => 'debit',
                        'transaction_amount' => $accounts[$i]['debit'],
                        'reference'          => 'Journal Entry',
                        'reference_id'       => $journal->id,
                        'reference_sub_id'   => $journalItem->id,
                        'date'               => $journal->date,
                    ];
                    Utility::addTransactionLines($data);
                }

                if (isset($accounts[$i]['credit']) && $accounts[$i]['credit'] > 0) {
                    $data = [
                        'account_id'         => $accounts[$i]['account'],
                        'transaction_type'   => 'credit',
                        'transaction_amount' => $accounts[$i]['credit'],
                        'reference'          => 'Journal Entry',
                        'reference_id'       => $journal->id,
                        'reference_sub_id'   => $journalItem->id,
                        'date'               => $journal->date,
                    ];
                    Utility::addTransactionLines($data);
                }

                // REMOVED: All automatic Accounts Payable/Receivable creation logic
                // Journal entries now only record what the user actually specifies
            }

            return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function show(JournalEntry $journalEntry)
    {
        if (\Auth::user()->can('show journal entry')) {
            if ($journalEntry->created_by == \Auth::user()->creatorId()) {
                $accounts = $journalEntry->accounts;
                $settings = Utility::settings();

                return view('journalEntry.view', compact('journalEntry', 'accounts', 'settings'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(JournalEntry $journalEntry)
    {
        if (\Auth::user()->can('edit journal entry')) {
            $accountTypes = ChartOfAccountType::where('created_by', '=', \Auth::user()->creatorId())->get();

            $chartAccounts = [];

            foreach ($accountTypes as $type) {
                $accountTypes = ChartOfAccountSubType::where('type', $type->id)
                    ->where('created_by', '=', \Auth::user()->creatorId())
                    ->whereNotIn('name', ['Accounts Receivable' , 'Accounts Payable'])
                    ->get();

                $temp = [];

                foreach ($accountTypes as $accountType) {
                    $chartOfAccounts = ChartOfAccount::where('sub_type', $accountType->id)->where('parent', '=', 0)
                        ->where('created_by', '=', \Auth::user()->creatorId())
                        ->get();

                    $incomeSubAccounts = ChartOfAccount::where('sub_type', $accountType->id)->where('parent', '!=', 0)
                    ->where('created_by', '=', \Auth::user()->creatorId())
                    ->get();

                    $tempData = [
                        'account_name'      => $accountType->name,
                        'chart_of_accounts' => [],
                        'subAccounts'       => [],
                    ];
                    foreach ($chartOfAccounts as $chartOfAccount) {
                        $tempData['chart_of_accounts'][] = [
                            'id'             => $chartOfAccount->id,
                            'account_number' => $chartOfAccount->account_number,
                            'account_name'   => $chartOfAccount->name,
                        ];
                    }

                    foreach ($incomeSubAccounts as $chartOfAccount) {
                        $tempData['subAccounts'][] = [
                            'id'             => $chartOfAccount->id,
                            'account_number' => $chartOfAccount->account_number,
                            'account_name'   => $chartOfAccount->name,
                            'parent'         => $chartOfAccount->parent,
                            'parent_account' => !empty($chartOfAccount->parentAccount) ? $chartOfAccount->parentAccount->account : 0,
                        ];
                    }
                    $temp[$accountType->id] = $tempData;
                }

                $chartAccounts[$type->name] = $temp;
            }

            return view('journalEntry.edit', compact('chartAccounts', 'journalEntry'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, JournalEntry $journalEntry)
    {
        if (\Auth::user()->can('edit journal entry')) {
            if ($journalEntry->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(), [
                        'date' => 'required',
                        'accounts' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $accounts = $request->accounts;
                $totalDebit = 0;
                $totalCredit = 0;
                for ($i = 0; $i < count($accounts); $i++) {
                    $debit = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                    $credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                    $totalDebit += $debit;
                    $totalCredit += $credit;
                }

                if ($totalCredit != $totalDebit) {
                    return redirect()->back()->with('error', __('Debit and Credit must be Equal.'));
                }

                $journalEntry->date = $request->date;
                $journalEntry->reference = $request->reference;
                $journalEntry->description = $request->description;
                $journalEntry->created_by = \Auth::user()->creatorId();
                $journalEntry->save();

                // Clean up existing transaction lines for this journal entry
                AddTransactionLine::where('reference_id',$journalEntry->id)->where('reference', 'Journal Entry')->delete();

                for ($i = 0; $i < count($accounts); $i++) {
                    $journalItem = JournalItem::find($accounts[$i]['id']);

                    if ($journalItem == null) {
                        $journalItem = new JournalItem();
                        $journalItem->journal = $journalEntry->id;
                    }

                    if (isset($accounts[$i]['account'])) {
                        $journalItem->account = $accounts[$i]['account'];
                    }

                    $journalItem->description = $accounts[$i]['description'];
                    $journalItem->debit = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                    $journalItem->credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                    $journalItem->save();

                    // Update bank account balances if applicable
                    $bankAccounts = BankAccount::where('chart_account_id', '=', $accounts[$i]['account'])->get();
                    if (!empty($bankAccounts)) {
                        foreach ($bankAccounts as $bankAccount) {
                            $old_balance = $bankAccount->opening_balance;
                            if ($journalItem->debit > 0) {
                                $new_balance = $old_balance - $journalItem->debit;
                            }
                            if ($journalItem->credit > 0) {
                                $new_balance = $old_balance + $journalItem->credit;
                            }
                            if (isset($new_balance)) {
                                $bankAccount->opening_balance = $new_balance;
                                $bankAccount->save();
                            }
                        }
                    }

                    // Record transaction lines for the specified accounts only
                    if (isset($accounts[$i]['debit']) && $accounts[$i]['debit'] > 0) {
                        $data = [
                            'account_id'         => $accounts[$i]['account'],
                            'transaction_type'   => 'debit',
                            'transaction_amount' => $accounts[$i]['debit'],
                            'reference'          => 'Journal Entry',
                            'reference_id'       => $journalEntry->id,
                            'reference_sub_id'   => $journalItem->id,
                            'date'               => $journalEntry->date,
                        ];
                        Utility::addTransactionLines($data);
                    }

                    if (isset($accounts[$i]['credit']) && $accounts[$i]['credit'] > 0) {
                        $data = [
                            'account_id'         => $accounts[$i]['account'],
                            'transaction_type'   => 'credit',
                            'transaction_amount' => $accounts[$i]['credit'],
                            'reference'          => 'Journal Entry',
                            'reference_id'       => $journalEntry->id,
                            'reference_sub_id'   => $journalItem->id,
                            'date'               => $journalEntry->date,
                        ];
                        Utility::addTransactionLines($data);
                    }

                    // REMOVED: All automatic Accounts Payable/Receivable creation logic
                    // Journal entries now only record what the user actually specifies
                }

                return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(JournalEntry $journalEntry)
    {

        if (\Auth::user()->can('delete journal entry')) {
            if ($journalEntry->created_by == \Auth::user()->creatorId()) {
                $journalEntry->delete();

                JournalItem::where('journal', '=', $journalEntry->id)->delete();

                AddTransactionLine::where('reference_id',$journalEntry->id)->where('reference', 'Journal Entry')->delete();

                return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function journalNumber()
    {
        $settings = Utility::settings();
        $journal_starting_number = isset($settings['journal_starting_number']) ? (int)$settings['journal_starting_number'] : 1;

        $latest = JournalEntry::where('created_by', '=', \Auth::user()->creatorId())->latest('journal_id')->first();
        if (!$latest) {
            return $journal_starting_number;
        }

        return $latest->journal_id + 1;
    }

    public function accountDestroy(Request $request)
    {

        if (\Auth::user()->can('delete journal entry')) {
            JournalItem::where('id', '=', $request->id)->delete();

            return redirect()->back()->with('success', __('Journal entry account successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function journalDestroy($item_id)
    {
        if (\Auth::user()->can('delete journal entry')) {
            $journal = JournalItem::find($item_id);
            $journal->delete();

            return redirect()->back()->with('success', __('Journal account successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
