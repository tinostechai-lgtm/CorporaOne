<?php
// app/Http/Controllers/PurchaseReturnController.php

namespace App\Http\Controllers;

use App\Models\PurchaseReturn;
use App\Models\ProductService;
use App\Models\Vender;
use App\Models\Utility;
use App\Models\StockReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnController extends Controller
{
    /**
     * Display a listing of purchase returns
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage purchase return')) {
            $venders = Vender::where('created_by', '=', Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $venders->prepend('Select Vendor', '');
            
            $status = [
                'pending' => 'Pending',
                'approved' => 'Approved',
                'completed' => 'Completed',
                'rejected' => 'Rejected',
                'cancelled' => 'Cancelled'
            ];
            
            $query = PurchaseReturn::where('created_by', '=', Auth::user()->creatorId());

            // Apply filters if provided
            if ($request->has('vender') && !empty($request->vender)) {
                $query->where('supplier', $request->vender);
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            if ($request->has('date') && !empty($request->date)) {
                $query->whereDate('return_date', $request->date);
            }

            $purchaseReturns = $query->orderBy('id', 'desc')->get();

            return view('purchase_return.index', compact('purchaseReturns', 'status', 'venders'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new purchase return
     */
    public function create()
    {
        if (Auth::user()->can('create purchase return')) {
            // Get vendors for dropdown
            $venders = Vender::where('created_by', Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $venders->prepend('Select Vendor', '');

            // Get products for dropdown
            $products = ProductService::where('created_by', Auth::user()->creatorId())
                ->where('type', '!=', 'service')
                ->get(['id', 'name', 'purchase_price']);

            return view('purchase_return.create', compact('venders', 'products'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created purchase return
     */
    public function store(Request $request)
    {
        if (Auth::user()->can('create purchase return')) {
            $validator = Validator::make($request->all(), [
                'supplier' => 'required|string',
                'return_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.product_name' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.product_id' => 'nullable|exists:product_services,id',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first())->withInput();
            }

            DB::beginTransaction();
            try {
                // Calculate total amount and prepare items array
                $totalAmount = 0;
                $items = [];
                
                foreach ($request->items as $item) {
                    $itemTotal = $item['quantity'] * $item['price'];
                    $totalAmount += $itemTotal;
                    
                    $items[] = [
                        'product_name' => $item['product_name'],
                        'product_id' => $item['product_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $itemTotal
                    ];
                }

                // Create purchase return
                $purchaseReturn = new PurchaseReturn();
                $purchaseReturn->supplier = $request->supplier;
                $purchaseReturn->return_date = $request->return_date;
                $purchaseReturn->items = json_encode($items);
                $purchaseReturn->total_amount = $totalAmount;
                $purchaseReturn->description = $request->description;
                $purchaseReturn->status = 'pending';
                $purchaseReturn->created_by = Auth::user()->creatorId();
                $purchaseReturn->save();

                // Update stock for each returned item (if product_id is provided)
                foreach ($items as $item) {
                    if (isset($item['product_id']) && !empty($item['product_id'])) {
                        // Deduct from stock (items being returned to supplier)
                        Utility::total_quantity('minus', $item['quantity'], $item['product_id']);
                        
                        // Add stock report
                        $type = 'purchase_return';
                        $type_id = $purchaseReturn->id;
                        $description = $item['quantity'] . ' ' . __('quantity returned to supplier') . ' - ' . $item['product_name'];
                        Utility::addProductStock($item['product_id'], -$item['quantity'], $type, $description, $type_id);
                    }
                }

                DB::commit();

                return redirect()->route('purchase-return.index')
                    ->with('success', __('Purchase return created successfully.'));

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage())->withInput();
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified purchase return
     */
    public function show($id)
    {
        if (Auth::user()->can('show purchase return')) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Purchase Return Not Found.'));
            }

            $purchaseReturn = PurchaseReturn::find($id);

            if (!$purchaseReturn || $purchaseReturn->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Purchase Return not found or permission denied.'));
            }

            // Decode items from JSON
            $items = json_decode($purchaseReturn->items, true);
            
            // Get supplier/vendor details if available
            $vendor = Vender::where('name', $purchaseReturn->supplier)
                ->where('created_by', Auth::user()->creatorId())
                ->first();

            return view('purchase_return.show', compact('purchaseReturn', 'items', 'vendor'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified purchase return
     */
    public function edit($id)
    {
        if (Auth::user()->can('edit purchase return')) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Purchase Return Not Found.'));
            }

            $purchaseReturn = PurchaseReturn::find($id);

            if (!$purchaseReturn || $purchaseReturn->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Purchase Return not found or permission denied.'));
            }

            // Only allow editing if status is pending
            if ($purchaseReturn->status != 'pending') {
                return redirect()->back()->with('error', __('Only pending returns can be edited.'));
            }

            // Get vendors for dropdown
            $venders = Vender::where('created_by', Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            $venders->prepend('Select Vendor', '');

            // Get products for dropdown
            $products = ProductService::where('created_by', Auth::user()->creatorId())
                ->where('type', '!=', 'service')
                ->get(['id', 'name', 'purchase_price']);

            // Decode items from JSON
            $items = json_decode($purchaseReturn->items, true);

            return view('purchase_return.edit', compact('purchaseReturn', 'venders', 'products', 'items'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified purchase return
     */
    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit purchase return')) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Purchase Return Not Found.'));
            }

            $purchaseReturn = PurchaseReturn::find($id);

            if (!$purchaseReturn || $purchaseReturn->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Purchase Return not found or permission denied.'));
            }

            // Only allow updating if status is pending
            if ($purchaseReturn->status != 'pending') {
                return redirect()->back()->with('error', __('Only pending returns can be updated.'));
            }

            $validator = Validator::make($request->all(), [
                'supplier' => 'required|string',
                'return_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.product_name' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.product_id' => 'nullable|exists:product_services,id',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first())->withInput();
            }

            DB::beginTransaction();
            try {
                // First, reverse old stock changes
                $oldItems = json_decode($purchaseReturn->items, true);
                foreach ($oldItems as $item) {
                    if (isset($item['product_id']) && !empty($item['product_id'])) {
                        // Add back to stock (reverse the original deduction)
                        Utility::total_quantity('plus', $item['quantity'], $item['product_id']);
                        
                        // Delete old stock reports
                        StockReport::where('type', 'purchase_return')
                            ->where('type_id', $purchaseReturn->id)
                            ->where('product_id', $item['product_id'])
                            ->delete();
                    }
                }

                // Calculate new total amount and prepare new items
                $totalAmount = 0;
                $newItems = [];
                
                foreach ($request->items as $item) {
                    $itemTotal = $item['quantity'] * $item['price'];
                    $totalAmount += $itemTotal;
                    
                    $newItems[] = [
                        'product_name' => $item['product_name'],
                        'product_id' => $item['product_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $itemTotal
                    ];
                }

                // Update purchase return
                $purchaseReturn->supplier = $request->supplier;
                $purchaseReturn->return_date = $request->return_date;
                $purchaseReturn->items = json_encode($newItems);
                $purchaseReturn->total_amount = $totalAmount;
                $purchaseReturn->description = $request->description;
                $purchaseReturn->save();

                // Apply new stock changes
                foreach ($newItems as $item) {
                    if (isset($item['product_id']) && !empty($item['product_id'])) {
                        // Deduct from stock
                        Utility::total_quantity('minus', $item['quantity'], $item['product_id']);
                        
                        // Add new stock report
                        $type = 'purchase_return';
                        $type_id = $purchaseReturn->id;
                        $description = $item['quantity'] . ' ' . __('quantity returned to supplier') . ' - ' . $item['product_name'];
                        Utility::addProductStock($item['product_id'], -$item['quantity'], $type, $description, $type_id);
                    }
                }

                DB::commit();

                return redirect()->route('purchase-return.index')
                    ->with('success', __('Purchase return updated successfully.'));

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage())->withInput();
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update status of purchase return
     */
    public function updateStatus(Request $request, $id)
    {
        if (Auth::user()->can('edit purchase return')) {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,approved,completed,rejected,cancelled'
            ]);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $purchaseReturn = PurchaseReturn::find($id);
            
            if (!$purchaseReturn || $purchaseReturn->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Purchase Return not found or permission denied.'));
            }

            $oldStatus = $purchaseReturn->status;
            $newStatus = $request->status;

            DB::beginTransaction();
            try {
                // Handle stock adjustments based on status changes
                if ($newStatus == 'rejected' || $newStatus == 'cancelled') {
                    if (in_array($oldStatus, ['approved', 'completed'])) {
                        // Reverse stock changes if it was previously approved/completed
                        $items = json_decode($purchaseReturn->items, true);
                        foreach ($items as $item) {
                            if (isset($item['product_id']) && !empty($item['product_id'])) {
                                // Add back to stock
                                Utility::total_quantity('plus', $item['quantity'], $item['product_id']);
                                
                                // Update stock report
                                $description = $item['quantity'] . ' ' . __('quantity restored from rejected return');
                                Utility::addProductStock($item['product_id'], $item['quantity'], 'purchase_return_reversal', $description, $purchaseReturn->id);
                            }
                        }
                    }
                } elseif (in_array($newStatus, ['approved', 'completed'])) {
                    if ($oldStatus == 'pending') {
                        // Stock was already deducted at creation, so no change needed
                        // Just update status
                    } elseif (in_array($oldStatus, ['rejected', 'cancelled'])) {
                        // Re-apply stock changes
                        $items = json_decode($purchaseReturn->items, true);
                        foreach ($items as $item) {
                            if (isset($item['product_id']) && !empty($item['product_id'])) {
                                // Deduct from stock again
                                Utility::total_quantity('minus', $item['quantity'], $item['product_id']);
                                
                                // Update stock report
                                $description = $item['quantity'] . ' ' . __('quantity returned after approval');
                                Utility::addProductStock($item['product_id'], -$item['quantity'], 'purchase_return', $description, $purchaseReturn->id);
                            }
                        }
                    }
                }

                $purchaseReturn->status = $newStatus;
                $purchaseReturn->save();

                DB::commit();

                return redirect()->back()->with('success', __('Purchase return status updated successfully.'));

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified purchase return
     */
    public function destroy($id)
    {
        if (Auth::user()->can('delete purchase return')) {
            try {
                $id = Crypt::decrypt($id);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Purchase Return Not Found.'));
            }

            $purchaseReturn = PurchaseReturn::find($id);

            if (!$purchaseReturn || $purchaseReturn->created_by != Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Purchase Return not found or permission denied.'));
            }

            DB::beginTransaction();
            try {
                // Reverse stock changes if the return was approved/completed
                if (in_array($purchaseReturn->status, ['approved', 'completed'])) {
                    $items = json_decode($purchaseReturn->items, true);
                    foreach ($items as $item) {
                        if (isset($item['product_id']) && !empty($item['product_id'])) {
                            // Add back to stock
                            Utility::total_quantity('plus', $item['quantity'], $item['product_id']);
                            
                            // Delete stock reports
                            StockReport::where('type', 'purchase_return')
                                ->where('type_id', $purchaseReturn->id)
                                ->where('product_id', $item['product_id'])
                                ->delete();
                        }
                    }
                }

                // Delete the return
                $purchaseReturn->delete();

                DB::commit();

                return redirect()->route('purchase-return.index')
                    ->with('success', __('Purchase return deleted successfully.'));

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Get products for AJAX request
     */
    public function getProducts(Request $request)
    {
        $products = ProductService::where('created_by', Auth::user()->creatorId())
            ->where('type', '!=', 'service')
            ->get(['id', 'name', 'purchase_price as price']);

        return response()->json($products);
    }

    /**
     * Get product details by ID
     */
    public function getProduct($id)
    {
        $product = ProductService::where('id', $id)
            ->where('created_by', Auth::user()->creatorId())
            ->first(['id', 'name', 'purchase_price as price']);

        if ($product) {
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    }

    /**
     * Print purchase return
     */
    public function printReturn($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Purchase Return Not Found.'));
        }

        $purchaseReturn = PurchaseReturn::find($id);

        if (!$purchaseReturn || $purchaseReturn->created_by != Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Purchase Return not found or permission denied.'));
        }

        $items = json_decode($purchaseReturn->items, true);
        $settings = Utility::settings();
        
        // Get company settings
        $companyName = $settings['company_name'] ?? config('app.name');
        $companyEmail = $settings['company_email'] ?? '';
        $companyPhone = $settings['company_telephone'] ?? '';
        $companyAddress = $settings['company_address'] ?? '';
        
        return view('purchase_return.print', compact('purchaseReturn', 'items', 'settings', 
            'companyName', 'companyEmail', 'companyPhone', 'companyAddress'));
    }

    /**
     * Export purchase return as PDF
     */
    public function pdf($id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Purchase Return Not Found.'));
        }

        $purchaseReturn = PurchaseReturn::find($id);

        if (!$purchaseReturn || $purchaseReturn->created_by != Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Purchase Return not found or permission denied.'));
        }

        $items = json_decode($purchaseReturn->items, true);
        $settings = Utility::settings();
        
        // Get company settings
        $companyName = $settings['company_name'] ?? config('app.name');
        $companyEmail = $settings['company_email'] ?? '';
        $companyPhone = $settings['company_telephone'] ?? '';
        $companyAddress = $settings['company_address'] ?? '';

        $pdf = \PDF::loadView('purchase_return.pdf', compact('purchaseReturn', 'items', 'settings',
            'companyName', 'companyEmail', 'companyPhone', 'companyAddress'));

        return $pdf->download('purchase-return-' . $purchaseReturn->id . '.pdf');
    }

    /**
     * Generate return number
     */
    public function pReturnNumber()
    {
        $latest = PurchaseReturn::where('created_by', '=', Auth::user()->creatorId())
            ->latest()
            ->first();
        
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }
}