<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BillAccount;
use App\Models\BillPayment;
use App\Models\ChartOfAccount;
use App\Models\CustomField;
use App\Models\Payment;
use App\Models\ProductServiceCategory;
use App\Models\Transaction;
use App\Models\TransactionLines;
use App\Models\Utility;
use App\Models\Vender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage payment')) {
            $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $vender->prepend('Select Vendor', '');

            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('Select Account', '');

            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())
            ->where('type', '=', 'expense')->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            $query = Payment::where('created_by', '=', \Auth::user()->creatorId());

            if (count(explode('to', $request->date)) > 1) {
                $date_range = explode(' to ', $request->date);
                $query->whereBetween('date', $date_range);
            } elseif (!empty($request->date)) {
                $date_range = [$request->date, $request->date];
                $query->whereBetween('date', $date_range);
            }

            if (!empty($request->vender)) {
                $query->where('vender_id', '=', $request->vender);
            }
            if (!empty($request->account)) {
                $query->where('account_id', '=', $request->account);
            }

            if (!empty($request->category)) {
                $query->where('category_id', '=', $request->category);
            }

            $payments = $query->with(['bankAccount','vender','category'])->get();

            return view('payment.index', compact('payments', 'account', 'category', 'vender'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create payment')) {
            $venders = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $venders->prepend('--', null);

            $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'expense')->get()->pluck('name', 'id');
            $categories->prepend('Select Category', '');

            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('payment.create', compact('venders', 'categories', 'accounts'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create payment')) {

            $validator = \Validator::make(
                $request->all(), [
                    'date' => 'required',
                    'amount' => 'required|numeric',
                    'account_id' => 'required',
                    'vender_id' => 'required',
                    'category_id' => 'required',
                    'add_receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $payment = new Payment();
            $payment->date = $request->date;
            $payment->amount = $request->amount;
            $payment->account_id = $request->account_id;
            $payment->vender_id = $request->vender_id;
            $payment->category_id = $request->category_id;
            $payment->payment_method = 0;
            $payment->reference = $request->reference;
            $payment->description = $request->description;
            $payment->created_by = \Auth::user()->creatorId();

            // Handle file upload directly to public directory
            if ($request->hasFile('add_receipt')) {
                $file = $request->file('add_receipt');
                $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\-]/', '', $file->getClientOriginalName());
                
                // Create directory if it doesn't exist
                $uploadPath = public_path('uploads/payment');
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0777, true);
                }
                
                // Move file to public directory
                $file->move($uploadPath, $filename);
                $payment->add_receipt = $filename;
            }

            $payment->save();

            $chartAccountId = ChartOfAccount::find($request->account_id);

            $accountId = BankAccount::find($payment->account_id);
            $data = [
                'account_id' => $accountId->chart_account_id,
                'transaction_type' => 'Debit',
                'transaction_amount' => $payment->amount,
                'reference' => 'Payment',
                'reference_id' => $payment->id,
                'reference_sub_id' => 0,
                'date' => $payment->date,
            ];
            Utility::addTransactionLines($data , 'create');

            $account = new BillAccount();
            $account->chart_account_id = $chartAccountId;
            $account->price = $request->amount;
            $account->description = $request->description;
            $account->type = 'Payment';
            $account->ref_id = $payment->id;
            $account->save();

            $category = ProductServiceCategory::where('id', $request->category_id)->first();
            $payment->payment_id = $payment->id;
            $payment->type = 'Payment';
            $payment->category = $category->name;
            $payment->user_id = $payment->vender_id;
            $payment->user_type = 'Vender';
            $payment->account = $request->account_id;

            Transaction::addTransaction($payment);

            $vender = Vender::where('id', $request->vender_id)->first();
            if (!empty($vender)) {
                Utility::userBalance('vendor', $vender->id, $request->amount, 'debit');
            }

            Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');

            //For Notification
            $setting = Utility::settings(\Auth::user()->creatorId());

            //Twilio Notification
            if (isset($setting['twilio_payment_notification']) && $setting['twilio_payment_notification'] == 1) {
                $vender = Vender::find($request->vender_id);
                $paymentNotificationArr = [
                    'payment_amount' => \Auth::user()->priceFormat($request->amount),
                    'vendor_name' =>  $vender != null  ? $vender->name : '',
                    'payment_type' => 'Payment',
                ];
                Utility::send_twilio_msg($vender->contact, 'bill_payment', $paymentNotificationArr);
            }

            return redirect()->route('payment.index')->with('success', __('Payment successfully created'));

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(Payment $payment)
    {
        if (\Auth::user()->can('edit payment')) {
            $venders = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $venders->prepend('--', 0);

            $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'expense')->get()->pluck('name', 'id');
            $categories->prepend('Select Category', '');

            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('payment.edit', compact('venders', 'categories', 'accounts', 'payment'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Payment $payment)
    {
        if (\Auth::user()->can('edit payment')) {

            $validator = \Validator::make(
                $request->all(), [
                    'date' => 'required',
                    'amount' => 'required|numeric',
                    'account_id' => 'required',
                    'vender_id' => 'required',
                    'category_id' => 'required',
                    'add_receipt' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:2048',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            
            $vender = Vender::where('id', $request->vender_id)->first();
            if (!empty($vender)) {
                Utility::userBalance('vendor', $vender->id, $payment->amount, 'credit');
            }
            Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

            $payment->date = $request->date;
            $payment->amount = $request->amount;
            $payment->account_id = $request->account_id;
            $payment->vender_id = $request->vender_id;
            $payment->category_id = $request->category_id;
            $payment->payment_method = 0;
            $payment->reference = $request->reference;
            $payment->description = $request->description;

            // Handle file upload
            if ($request->hasFile('add_receipt')) {
                // Delete old file
                if (!empty($payment->add_receipt)) {
                    $oldFilePath = public_path('uploads/payment/' . $payment->add_receipt);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
                
                // Upload new file
                $file = $request->file('add_receipt');
                $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\-]/', '', $file->getClientOriginalName());
                
                // Create directory if it doesn't exist
                $uploadPath = public_path('uploads/payment');
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0777, true);
                }
                
                // Move file to public directory
                $file->move($uploadPath, $filename);
                $payment->add_receipt = $filename;
            }

            $payment->save();

            $accountId = BankAccount::find($payment->account_id);
            $data = [
                'account_id' => $accountId->chart_account_id,
                'transaction_type' => 'Debit',
                'transaction_amount' => $payment->amount,
                'reference' => 'Payment',
                'reference_id' => $payment->id,
                'reference_sub_id' => 0,
                'date' => $payment->date,
            ];
            Utility::addTransactionLines($data , 'edit');

            $category = ProductServiceCategory::where('id', $request->category_id)->first();
            $payment->category = $category->name;
            $payment->payment_id = $payment->id;
            $payment->type = 'Payment';
            $payment->account = $request->account_id;
            Transaction::editTransaction($payment);

            if (!empty($vender)) {
                Utility::userBalance('vendor', $vender->id, $request->amount, 'debit');
            }

            Utility::bankAccountBalance($request->account_id, $request->amount, 'debit');

            return redirect()->route('payment.index')->with('success', __('Payment Updated Successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function receiptPdf($receipt_id)
    {
        $settings = Utility::settings();

        try {
            $receiptId = Crypt::decrypt($receipt_id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Payment Receipt Not Found.'));
        }

        $payment = Payment::where('id', $receiptId)
            ->where('created_by', \Auth::user()->creatorId())
            ->with(['vender', 'bankAccount'])
            ->first();

        if (!$payment) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $vendor = $payment->vender;
        $account = $payment->bankAccount;

        $totalAmount = $payment->amount;
        $items = [['name' => 'Payment Receipt', 'quantity' => 1, 'price' => $totalAmount, 'description' => $payment->description]];

        $color = '#' . ($settings['payment_template_color'] ?? $settings['bill_color'] ?? 'ffffff');
        $font_color = Utility::getFontColor($color);
        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $payment_logo = Utility::getValByName('payment_logo');
        $img = !empty($payment_logo) ? Utility::get_file('payment_logo/' . $payment_logo) : asset($logo . '/' . ($company_logo ?? 'logo-dark.png'));

        return view('payment.templates.payment_receipt_template1', compact(
            'payment', 'vendor', 'account', 'settings', 'color', 'font_color', 'img', 'items', 'totalAmount'
        ));
    }

    public function destroy(Payment $payment)
    {
        if (\Auth::user()->can('delete payment')) {
            if ($payment->created_by == \Auth::user()->creatorId()) {
                if (!empty($payment->add_receipt)) {
                    $file_path = public_path('uploads/payment/' . $payment->add_receipt);
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }

                TransactionLines::where('reference_id', $payment->id)->where('reference', 'Payment')->delete();

                $payment->delete();
                $type = 'Payment';
                $user = 'Vender';
                Transaction::destroyTransaction($payment->id, $type, $user);

                if ($payment->vender_id != 0) {
                    Utility::userBalance('vendor', $payment->vender_id, $payment->amount, 'credit');
                }
                Utility::bankAccountBalance($payment->account_id, $payment->amount, 'credit');

                return redirect()->route('payment.index')->with('success', __('Payment successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}