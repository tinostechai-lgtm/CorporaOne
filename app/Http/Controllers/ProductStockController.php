<?php

namespace App\Http\Controllers;

use App\Models\ProductService;
use App\Models\ProductStock;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductStockController extends Controller
{
    /**
     * Display product stock list
     */
    public function index()
    {
        if (!Auth::user()->can('manage product & service')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $productServices = ProductService::with('warehouseProducts')
            ->where('created_by', Auth::user()->creatorId())
            ->where('type', 'product')
            ->latest()
            ->get()
            ->map(function ($product) {

                $mainStock = $product->quantity ?? 0;
                $warehouseStock = $product->warehouseProducts->sum('quantity');
                $totalStock = $mainStock + $warehouseStock;

                $product->main_stock = $mainStock;
                $product->warehouse_stock = $warehouseStock;
                $product->total_stock = $totalStock;

                if ($totalStock <= 0) {
                    $product->stock_status = 'out';
                } elseif ($totalStock < 10) {
                    $product->stock_status = 'low';
                } else {
                    $product->stock_status = 'in';
                }

                return $product;
            });

        return view('productstock.index', compact('productServices'));
    }

    /**
     * Show stock update form
     */
    public function edit($id)
    {
        if (!Auth::user()->can('edit product & service')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $productService = ProductService::findOrFail($id);

        if ($productService->created_by != Auth::user()->creatorId()) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        return view('productstock.edit', compact('productService'));
    }

    /**
     * Update stock manually
     */
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('edit product & service')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $productService = ProductService::findOrFail($id);

        if ($productService->created_by != Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // Update quantity safely
        $productService->increment('quantity', $request->quantity);

        // Log stock movement
        $type        = 'manually';
        $type_id     = 0;
        $description = $request->quantity . ' ' . __('quantity added manually');

        Utility::addProductStock(
            $productService->id,
            $request->quantity,
            $type,
            $description,
            $type_id
        );

        return redirect()
            ->route('productstock.index')
            ->with('success', __('Product quantity updated successfully.'));
    }
}