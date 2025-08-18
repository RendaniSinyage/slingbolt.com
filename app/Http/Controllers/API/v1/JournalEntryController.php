<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use Illuminate\Http\Request;
use App\Http\Resources\JournalEntryResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use App\Models\Utility;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('manage journal entry')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $journalEntries = JournalEntry::where('created_by', '=', Auth::user()->creatorId())->get();

        return JournalEntryResource::collection($journalEntries);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('create journal entry')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'date' => 'required|date',
                'accounts' => 'required|array',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $accounts = $request->accounts;
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($accounts as $account) {
            $totalDebit += $account['debit'] ?? 0;
            $totalCredit += $account['credit'] ?? 0;
        }

        if ($totalCredit != $totalDebit) {
            return response()->json(['error' => 'Debit and Credit must be Equal.'], 422);
        }

        $journal = new JournalEntry();
        $journal->journal_id = $this->journalNumber();
        $journal->date = $request->date;
        $journal->reference = $request->reference;
        $journal->description = $request->description;
        $journal->created_by = Auth::user()->creatorId();
        $journal->save();

        foreach ($accounts as $account) {
            $journalItem = new JournalItem();
            $journalItem->journal = $journal->id;
            $journalItem->account = $account['account'];
            $journalItem->description = $account['description'];
            $journalItem->debit = $account['debit'] ?? 0;
            $journalItem->credit = $account['credit'] ?? 0;
            $journalItem->save();
        }

        return (new JournalEntryResource($journal->load('accounts')))->additional(['message' => 'Journal entry successfully created.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JournalEntry  $journalEntry
     * @return \Illuminate\Http\Response
     */
    public function show(JournalEntry $journalEntry)
    {
        if (Gate::denies('show journal entry')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($journalEntry->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        return new JournalEntryResource($journalEntry->load('accounts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JournalEntry  $journalEntry
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JournalEntry $journalEntry)
    {
        if (Gate::denies('edit journal entry')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($journalEntry->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $validator = \Validator::make(
            $request->all(), [
                'date' => 'sometimes|required|date',
                'accounts' => 'sometimes|required|array',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $accounts = $request->accounts;
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($accounts as $account) {
            $totalDebit += $account['debit'] ?? 0;
            $totalCredit += $account['credit'] ?? 0;
        }

        if ($totalCredit != $totalDebit) {
            return response()->json(['error' => 'Debit and Credit must be Equal.'], 422);
        }

        $journalEntry->date = $request->date;
        $journalEntry->reference = $request->reference;
        $journalEntry->description = $request->description;
        $journalEntry->save();

        JournalItem::where('journal', $journalEntry->id)->delete();
        foreach ($accounts as $account) {
            $journalItem = new JournalItem();
            $journalItem->journal = $journalEntry->id;
            $journalItem->account = $account['account'];
            $journalItem->description = $account['description'];
            $journalItem->debit = $account['debit'] ?? 0;
            $journalItem->credit = $account['credit'] ?? 0;
            $journalItem->save();
        }

        return (new JournalEntryResource($journalEntry->load('accounts')))->additional(['message' => 'Journal entry successfully updated.']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JournalEntry  $journalEntry
     * @return \Illuminate\Http\Response
     */
    public function destroy(JournalEntry $journalEntry)
    {
        if (Gate::denies('delete journal entry')) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        if ($journalEntry->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => 'Permission denied.'], 403);
        }

        $journalEntry->delete();
        JournalItem::where('journal', '=', $journalEntry->id)->delete();

        return response()->json(['message' => 'Journal entry successfully deleted.']);
    }

    private function journalNumber()
    {
        $latest = JournalEntry::where('created_by', '=', Auth::user()->creatorId())->latest('journal_id')->first();
        if (!$latest) {
            return 1;
        }

        return $latest->journal_id + 1;
    }
}
