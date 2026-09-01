<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\TransactionLines;
use App\Models\Utility;
use App\Models\Plan;
use App\Models\User;
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
            // Get the company user (not the logged-in user if they are admin)
            $companyId = \Auth::user()->creatorId();
            $companyUser = User::find($companyId);
            
            // Get the company's plan
            $plan = Plan::find($companyUser->plan);
            
            // Get enabled account IDs from the plan
            $enabledAccountIds = [];
            if ($plan && $plan->enabled_accounts) {
                $enabledAccountIds = json_decode($plan->enabled_accounts, true);
            }
            
            // If no enabled accounts in plan, get all accounts created by super admin (created_by = 1)
            // This is the fallback for plans that don't have account restrictions
            if (empty($enabledAccountIds)) {
                // Get all accounts from super admin (default accounts)
                $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.parent'))
                    ->where('parent', '=', 0)
                    ->where('created_by', 1) // Super admin accounts
                    ->orderBy('code')
                    ->get()
                    ->toArray();

                $subAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name , chart_of_accounts.id, chart_of_accounts.code , chart_of_account_parents.account'));
                $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
                $subAccounts->where('chart_of_accounts.parent', '!=', 0);
                $subAccounts->where('chart_of_accounts.created_by', 1); // Super admin accounts
                $subAccounts->orderBy('chart_of_accounts.code');
                $subAccounts = $subAccounts->get()->toArray();
            } else {
                // Get only enabled accounts for this plan
                $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.parent'))
                    ->where('parent', '=', 0)
                    ->where('created_by', 1) // Super admin accounts
                    ->whereIn('chart_of_accounts.id', $enabledAccountIds)
                    ->orderBy('code')
                    ->get()
                    ->toArray();

                $subAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name , chart_of_accounts.id, chart_of_accounts.code , chart_of_account_parents.account'));
                $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
                $subAccounts->where('chart_of_accounts.parent', '!=', 0);
                $subAccounts->where('chart_of_accounts.created_by', 1); // Super admin accounts
                $subAccounts->whereIn('chart_of_accounts.id', $enabledAccountIds);
                $subAccounts->orderBy('chart_of_accounts.code');
                $subAccounts = $subAccounts->get()->toArray();
            }

            $journalId = $this->journalNumber();

            return view('journalEntry.create', compact('chartAccounts', 'subAccounts', 'journalId'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create journal entry')) {
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
            
            // Get the company's plan for validation
            $companyId = \Auth::user()->creatorId();
            $companyUser = User::find($companyId);
            $plan = Plan::find($companyUser->plan);
            $enabledAccountIds = [];
            if ($plan && $plan->enabled_accounts) {
                $enabledAccountIds = json_decode($plan->enabled_accounts, true);
            }
            
            // Validate that all selected accounts are enabled in the plan (only if plan has restrictions)
            if (!empty($enabledAccountIds)) {
                foreach ($accounts as $account) {
                    if (!in_array($account['account'], $enabledAccountIds)) {
                        return redirect()->back()->with('error', __('One or more selected accounts are not enabled in your plan.'));
                    }
                }
            }

            $totalDebit = 0;
            $totalCredit = 0;
            for ($i = 0; $i < count($accounts); $i++) {
                $debit = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                $credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                if ($debit > 0 && $credit > 0) {
                    return redirect()->back()->with('error', __('An account cannot have both debit and credit amounts.'));
                }
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

                $bankAccounts = BankAccount::where('chart_account_id', '=', $accounts[$i]['account'])->get();
                if (!empty($bankAccounts)) {
                    foreach ($bankAccounts as $bankAccount) {
                        $old_balance = $bankAccount->opening_balance;
                        $new_balance = $old_balance;
                        if ($journalItem->debit > 0) {
                            $new_balance = $old_balance + $journalItem->debit;
                        }
                        if ($journalItem->credit > 0) {
                            $new_balance = $old_balance - $journalItem->credit;
                        }
                        $bankAccount->opening_balance = $new_balance;
                        $bankAccount->save();
                    }
                }

                $data = [
                    'account_id' => $accounts[$i]['account'],
                    'transaction_type' => ($journalItem->debit > 0) ? 'Debit' : 'Credit',
                    'transaction_amount' => ($journalItem->debit > 0) ? $journalItem->debit : $journalItem->credit,
                    'reference' => 'Journal',
                    'reference_id' => $journal->id,
                    'reference_sub_id' => $journalItem->id,
                    'date' => $journal->date,
                ];
                Utility::addTransactionLines($data, 'create');
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
            // Get the company's plan
            $companyId = \Auth::user()->creatorId();
            $companyUser = User::find($companyId);
            $plan = Plan::find($companyUser->plan);
            
            // Get enabled account IDs from the plan
            $enabledAccountIds = [];
            if ($plan && $plan->enabled_accounts) {
                $enabledAccountIds = json_decode($plan->enabled_accounts, true);
            }
            
            // Get parent accounts
            if (empty($enabledAccountIds)) {
                $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.parent'))
                    ->where('parent', '=', 0)
                    ->where('created_by', 1)
                    ->orderBy('code')
                    ->get()
                    ->toArray();

                $subAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name , chart_of_accounts.id, chart_of_accounts.code , chart_of_account_parents.account'));
                $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
                $subAccounts->where('chart_of_accounts.parent', '!=', 0);
                $subAccounts->where('chart_of_accounts.created_by', 1);
                $subAccounts->orderBy('chart_of_accounts.code');
                $subAccounts = $subAccounts->get()->toArray();
            } else {
                $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.parent'))
                    ->where('parent', '=', 0)
                    ->where('created_by', 1)
                    ->whereIn('chart_of_accounts.id', $enabledAccountIds)
                    ->orderBy('code')
                    ->get()
                    ->toArray();

                $subAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name , chart_of_accounts.id, chart_of_accounts.code , chart_of_account_parents.account'));
                $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
                $subAccounts->where('chart_of_accounts.parent', '!=', 0);
                $subAccounts->where('chart_of_accounts.created_by', 1);
                $subAccounts->whereIn('chart_of_accounts.id', $enabledAccountIds);
                $subAccounts->orderBy('chart_of_accounts.code');
                $subAccounts = $subAccounts->get()->toArray();
            }

            return view('journalEntry.edit', compact('chartAccounts', 'journalEntry', 'subAccounts'));
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
                
                // Get the company's plan for validation
                $companyId = \Auth::user()->creatorId();
                $companyUser = User::find($companyId);
                $plan = Plan::find($companyUser->plan);
                $enabledAccountIds = [];
                if ($plan && $plan->enabled_accounts) {
                    $enabledAccountIds = json_decode($plan->enabled_accounts, true);
                }
                
                // Validate that all selected accounts are enabled in the plan
                if (!empty($enabledAccountIds)) {
                    foreach ($accounts as $account) {
                        if (isset($account['account']) && !in_array($account['account'], $enabledAccountIds)) {
                            return redirect()->back()->with('error', __('One or more selected accounts are not enabled in your plan.'));
                        }
                    }
                }

                $totalDebit = 0;
                $totalCredit = 0;
                for ($i = 0; $i < count($accounts); $i++) {
                    $debit = isset($accounts[$i]['debit']) ? $accounts[$i]['debit'] : 0;
                    $credit = isset($accounts[$i]['credit']) ? $accounts[$i]['credit'] : 0;
                    if ($debit > 0 && $credit > 0) {
                        return redirect()->back()->with('error', __('An account cannot have both debit and credit amounts.'));
                    }
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

                    $bankAccounts = BankAccount::where('chart_account_id', '=', $accounts[$i]['account'])->get();
                    if (!empty($bankAccounts)) {
                        foreach ($bankAccounts as $bankAccount) {
                            $old_balance = $bankAccount->opening_balance;
                            $new_balance = $old_balance;
                            if ($journalItem->debit > 0) {
                                $new_balance = $old_balance + $journalItem->debit;
                            }
                            if ($journalItem->credit > 0) {
                                $new_balance = $old_balance - $journalItem->credit;
                            }
                            $bankAccount->opening_balance = $new_balance;
                            $bankAccount->save();
                        }
                    }

                    $data = [
                        'account_id' => $accounts[$i]['account'],
                        'transaction_type' => ($journalItem->debit > 0) ? 'Debit' : 'Credit',
                        'transaction_amount' => ($journalItem->debit > 0) ? $journalItem->debit : $journalItem->credit,
                        'reference' => 'Journal',
                        'reference_id' => $journalEntry->id,
                        'reference_sub_id' => $journalItem->id,
                        'date' => $journalEntry->date,
                    ];
                    Utility::addTransactionLines($data, 'edit');
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

                TransactionLines::where('reference_id', $journalEntry->id)->where('reference', 'Journal')->delete();

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
        $latest = JournalEntry::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
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