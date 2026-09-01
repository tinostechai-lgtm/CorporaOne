<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\CustomField;
use App\Exports\ProductServiceExport;
use App\Imports\ProductServiceImport;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
use App\Models\Tax;
use App\Models\Utility;
use App\Models\User;
use App\Models\Vender;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProductServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (Auth::user()->can('manage product & service')) {
            $category = ProductServiceCategory::where('created_by', '=', Auth::user()->creatorId())
                ->where('type', '=', 'Product & Service')
                ->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            try {
                // Build query with pagination
                $query = ProductService::where('created_by', '=', Auth::user()->creatorId());

                if (!empty($request->category)) {
                    $query->where('category_id', $request->category);
                }

                // Use paginate() instead of get() for pagination
                $perPage = $request->input('per_page', 10);
                $productServices = $query->with(['category', 'unit'])->paginate($perPage);

            } catch (\Exception $e) {
                // Log the error for debugging
                \Log::error('Error in ProductServiceController@index: ' . $e->getMessage());
                
                // Fallback: get products with pagination but without eager loading
                $productServices = ProductService::where('created_by', '=', Auth::user()->creatorId())
                    ->paginate(10);
            }

            return view('productservice.index', compact('productServices', 'category'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->can('create product & service')) {
            $customFields = CustomField::where('created_by', '=', Auth::user()->creatorId())->where('module', '=', 'product')->get();
            $category = ProductServiceCategory::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $unit = ProductServiceUnit::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');
            $tax = Tax::where('created_by', '=', Auth::user()->creatorId())->get()->pluck('name', 'id');
            
            $incomeChartAccounts = ChartOfAccount::select(DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id as id'))
                ->leftjoin('chart_of_account_types', 'chart_of_account_types.id', 'chart_of_accounts.type')
                ->where('chart_of_account_types.name', 'income')
                ->where('parent', '=', 0)
                ->where('chart_of_accounts.created_by', Auth::user()->creatorId())->get()
                ->pluck('code_name', 'id');
            $incomeChartAccounts->prepend('Select Account', 0);

            $incomeSubAccounts = ChartOfAccount::select(DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name,chart_of_accounts.id, chart_of_accounts.code, chart_of_account_parents.account'));
            $incomeSubAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $incomeSubAccounts->leftjoin('chart_of_account_types', 'chart_of_account_types.id', 'chart_of_accounts.type');
            $incomeSubAccounts->where('chart_of_account_types.name', 'income');
            $incomeSubAccounts->where('chart_of_accounts.parent', '!=', 0);
            $incomeSubAccounts->where('chart_of_accounts.created_by', Auth::user()->creatorId());
            $incomeSubAccounts = $incomeSubAccounts->get()->toArray();

            $expenseChartAccounts = ChartOfAccount::select(DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id as id'))
                ->join('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                ->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold'])
                ->where('chart_of_accounts.created_by', Auth::user()->creatorId())->get()
                ->pluck('code_name', 'id');
            $expenseChartAccounts->prepend('Select Account', '');

            $expenseSubAccounts = ChartOfAccount::select(DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name,chart_of_accounts.id, chart_of_accounts.code, chart_of_account_parents.account'));
            $expenseSubAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $expenseSubAccounts->leftjoin('chart_of_account_types', 'chart_of_account_types.id', 'chart_of_accounts.type');
            $expenseSubAccounts->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold']);
            $expenseSubAccounts->where('chart_of_accounts.parent', '!=', 0);
            $expenseSubAccounts->where('chart_of_accounts.created_by', Auth::user()->creatorId());
            $expenseSubAccounts = $expenseSubAccounts->get()->toArray();

            return view('productservice.create', compact('category', 'unit', 'tax', 'customFields', 'incomeChartAccounts', 'incomeSubAccounts', 'expenseChartAccounts', 'expenseSubAccounts'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create product & service')) {
            $rules = [
                'name' => 'required|max:191',
                'sku' => 'nullable|max:50|unique:product_services,sku',
                'sale_price' => 'nullable|numeric|min:0',
                'purchase_price' => 'nullable|numeric|min:0',
                'quantity' => 'nullable|integer|min:0',
                'pro_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'category_id' => 'required|exists:product_service_categories,id',
                'unit_id' => 'required|exists:product_service_units,id',
                'type' => 'required|in:product,service',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->route('productservice.index')->with('error', $messages->first());
            }

            $productService = new ProductService();
            $productService->name = $request->name;
            $productService->description = $request->description;
            $productService->sku = $request->sku;
            $productService->sale_price = $request->sale_price ?? 0;
            $productService->purchase_price = $request->purchase_price ?? 0;
            $productService->tax_id = !empty($request->tax_id) ? implode(',', $request->tax_id) : '';
            $productService->unit_id = $request->unit_id;
            
            $productService->quantity = !empty($request->quantity) ? $request->quantity : 0;
            
            $productService->type = $request->type;
            $productService->sale_chartaccount_id = $request->sale_chartaccount_id;
            $productService->expense_chartaccount_id = $request->expense_chartaccount_id;
            $productService->category_id = $request->category_id;

            if (!empty($request->pro_image)) {
                $fileName = time() . '_' . $request->pro_image->getClientOriginalName();
                $productService->pro_image = $fileName;
                $dir = 'uploads/pro_image';
                $path = Utility::upload_file($request, 'pro_image', $fileName, $dir, []);
            }

            $productService->created_by = \Auth::user()->creatorId();
            $productService->save();
            
            if (!empty($request->customField)) {
                CustomField::saveData($productService, $request->customField);
            }

            return redirect()->route('productservice.index')->with('success', __('Product successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        return redirect()->route('productservice.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $productService = ProductService::find($id);

        if (\Auth::user()->can('edit product & service')) {
            if ($productService && $productService->created_by == \Auth::user()->creatorId()) {
                $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'Product & Service')->get()->pluck('name', 'id');
                $unit = ProductServiceUnit::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $tax = Tax::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

                $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'product')->get();
                $productService->customField = CustomField::getData($productService, 'product');
                $productService->tax_id = !empty($productService->tax_id) ? explode(',', $productService->tax_id) : [];
                
                $incomeChartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id as id'))
                    ->leftjoin('chart_of_account_types', 'chart_of_account_types.id', 'chart_of_accounts.type')
                    ->where('chart_of_account_types.name', 'income')
                    ->where('parent', '=', 0)
                    ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())->get()
                    ->pluck('code_name', 'id');
                $incomeChartAccounts->prepend('Select Account', 0);

                $incomeSubAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name,chart_of_accounts.id, chart_of_accounts.code, chart_of_account_parents.account'));
                $incomeSubAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
                $incomeSubAccounts->leftjoin('chart_of_account_types', 'chart_of_account_types.id', 'chart_of_accounts.type');
                $incomeSubAccounts->where('chart_of_account_types.name', 'income');
                $incomeSubAccounts->where('chart_of_accounts.parent', '!=', 0);
                $incomeSubAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
                $incomeSubAccounts = $incomeSubAccounts->get()->toArray();

                $expenseChartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id as id'))
                    ->leftjoin('chart_of_account_types', 'chart_of_account_types.id', 'chart_of_accounts.type')
                    ->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold'])
                    ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())->get()
                    ->pluck('code_name', 'id');
                $expenseChartAccounts->prepend('Select Account', '');

                $expenseSubAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name,chart_of_accounts.id, chart_of_accounts.code, chart_of_account_parents.account'));
                $expenseSubAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
                $expenseSubAccounts->leftjoin('chart_of_account_types', 'chart_of_account_types.id', 'chart_of_accounts.type');
                $expenseSubAccounts->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold']);
                $expenseSubAccounts->where('chart_of_accounts.parent', '!=', 0);
                $expenseSubAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
                $expenseSubAccounts = $expenseSubAccounts->get()->toArray();

                return view('productservice.edit', compact('category', 'unit', 'tax', 'productService', 'customFields', 'incomeChartAccounts', 'expenseChartAccounts', 'incomeSubAccounts', 'expenseSubAccounts'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit product & service')) {
            $productService = ProductService::find($id);
            
            if ($productService && $productService->created_by == \Auth::user()->creatorId()) {
                $rules = [
                    'name' => 'required|max:191',
                    'sku' => 'nullable|max:50|unique:product_services,sku,' . $productService->id,
                    'sale_price' => 'nullable|numeric|min:0',
                    'purchase_price' => 'nullable|numeric|min:0',
                    'quantity' => 'nullable|integer|min:0',
                    'pro_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                    'category_id' => 'required|exists:product_service_categories,id',
                    'unit_id' => 'required|exists:product_service_units,id',
                    'type' => 'required|in:product,service',
                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->route('productservice.index')->with('error', $messages->first());
                }

                $productService->name = $request->name;
                $productService->description = $request->description;
                $productService->sku = $request->sku;
                $productService->sale_price = $request->sale_price ?? 0;
                $productService->purchase_price = $request->purchase_price ?? 0;
                $productService->tax_id = !empty($request->tax_id) ? implode(',', $request->tax_id) : '';
                $productService->unit_id = $request->unit_id;

                $productService->quantity = !empty($request->quantity) ? $request->quantity : 0;
                
                $productService->type = $request->type;
                $productService->sale_chartaccount_id = $request->sale_chartaccount_id;
                $productService->expense_chartaccount_id = $request->expense_chartaccount_id;
                $productService->category_id = $request->category_id;

                if (!empty($request->pro_image)) {
                    // Delete old image if exists
                    if ($productService->pro_image) {
                        $oldImagePath = public_path('uploads/pro_image/' . $productService->pro_image);
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    $fileName = time() . '_' . $request->pro_image->getClientOriginalName();
                    $productService->pro_image = $fileName;
                    $dir = 'uploads/pro_image';
                    $path = Utility::upload_file($request, 'pro_image', $fileName, $dir, []);
                }

                $productService->created_by = \Auth::user()->creatorId();
                $productService->save();
                
                if (!empty($request->customField)) {
                    CustomField::saveData($productService, $request->customField);
                }

                return redirect()->route('productservice.index')->with('success', __('Product successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        if (\Auth::user()->can('delete product & service')) {
            $productService = ProductService::find($id);
            
            if ($productService && $productService->created_by == \Auth::user()->creatorId()) {
                // Delete image if exists
                if (!empty($productService->pro_image)) {
                    $imagePath = public_path('uploads/pro_image/' . $productService->pro_image);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }

                $productService->delete();
                return redirect()->route('productservice.index')->with('success', __('Product successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Export products to Excel
     */
    public function export()
    {
        $name = 'product_service_' . date('Y-m-d i:h:s');
        $data = Excel::download(new ProductServiceExport(), $name . '.xlsx');
        return $data;
    }

    /**
     * Show import form
     */
    public function importFile()
    {
        return view('productservice.import');
    }

    /**
     * Import products from Excel
     */
    public function productserviceImportdata(Request $request)
    {
        session_start();
        $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
        $flag = 0;
        $html .= '<table class="table table-bordered"><tr>';
        
        try {
            $request = $request->data;
            $file_data = $_SESSION['file_data'];
            unset($_SESSION['file_data']);
        } catch (\Throwable $th) {
            $html = '<h3 class="text-danger text-center">Something went wrong, Please try again</h3></br>';
            return response()->json([
                'html' => true,
                'response' => $html,
            ]);
        }

        foreach ($file_data as $key => $row) {
            try {
                $productService = new ProductService();
                $productService->name = $row[$request['name']] ?? '';
                $productService->sku = $row[$request['sku']] ?? '';
                $productService->sale_price = $row[$request['sale_price']] ?? 0;
                $productService->purchase_price = $row[$request['purchase_price']] ?? 0;
                $productService->quantity = (isset($row[$request['type']]) && $row[$request['type']] == 'product') ? ($row[$request['quantity']] ?? 0) : 0;
                $productService->type = $row[$request['type']] ?? 'product';
                $productService->description = $row[$request['description']] ?? '';
                $productService->created_by = \Auth::user()->creatorId();
                $productService->save();
            } catch (\Exception $e) {
                $flag = 1;
                $html .= '<tr>';
                $html .= '<td>' . (isset($row[$request['name']]) ? $row[$request['name']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['sku']]) ? $row[$request['sku']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['sale_price']]) ? $row[$request['sale_price']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['purchase_price']]) ? $row[$request['purchase_price']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['quantity']]) ? $row[$request['quantity']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['tax_id']]) ? $row[$request['tax_id']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['category_id']]) ? $row[$request['category_id']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['unit_id']]) ? $row[$request['unit_id']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['type']]) ? $row[$request['type']] : '-') . '</td>';
                $html .= '<td>' . (isset($row[$request['description']]) ? $row[$request['description']] : '-') . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</table><br />';

        if ($flag == 1) {
            return response()->json([
                'html' => true,
                'response' => $html,
            ]);
        } else {
            return response()->json([
                'html' => false,
                'response' => 'Data Imported Successfully',
            ]);
        }
    }

    /**
     * Show warehouse details for a product
     */
    public function warehouseDetail($id)
    {
        $products = WarehouseProduct::with(['warehouse'])
            ->where('product_id', '=', $id)
            ->where('created_by', '=', \Auth::user()->creatorId())
            ->get();
        return view('productservice.detail', compact('products'));
    }

    /**
     * Empty warehouse cart
     */
    public function warehouseemptyCart(Request $request)
    {
        $session_key = $request->session_key;
        if ($session_key) {
            session()->forget($session_key);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'error' => 'Invalid session key']);
    }

    /**
     * Empty cart
     */
    public function emptyCart(Request $request)
    {
        $session_key = $request->session_key;
        if ($session_key) {
            session()->forget($session_key);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'error' => 'Invalid session key']);
    }

    /**
     * Update cart quantity
     */
    public function updateCart(Request $request)
    {
        if (Auth::user()->can('manage product & service') && $request->ajax()) {
            try {
                $id = $request->id;
                $quantity = $request->quantity;
                $session_key = $request->session_key;
                
                $cart = session()->get($session_key, []);
                
                if (isset($cart[$id])) {
                    $cart[$id]['quantity'] = $quantity;
                    
                    // Recalculate subtotal with tax
                    $subtotal = $cart[$id]["price"] * $quantity;
                    $tax = ($subtotal * $cart[$id]["tax"]) / 100;
                    $cart[$id]["subtotal"] = $subtotal + $tax;
                    
                    session()->put($session_key, $cart);
                    
                    return response()->json([
                        'code' => 200,
                        'status' => 'Success',
                        'success' => __('Cart updated successfully!'),
                        'product' => $cart[$id],
                        'cart' => $cart
                    ]);
                }
                
                return response()->json([
                    'code' => 404,
                    'status' => 'Error',
                    'error' => __('Product not found in cart!')
                ], 404);
                
            } catch (\Exception $e) {
                \Log::error('Error in updateCart: ' . $e->getMessage());
                return response()->json([
                    'code' => 500,
                    'status' => 'Error',
                    'error' => __('Something went wrong!')
                ], 500);
            }
        }
    }

    /**
     * Remove from cart
     */
    public function removeFromCart(Request $request)
    {
        if (Auth::user()->can('manage product & service') && $request->ajax()) {
            try {
                $id = $request->id;
                $session_key = $request->session_key;
                
                $cart = session()->get($session_key, []);
                
                if (isset($cart[$id])) {
                    unset($cart[$id]);
                    session()->put($session_key, $cart);
                    
                    return response()->json([
                        'code' => 200,
                        'status' => 'Success',
                        'success' => __('Item removed from cart successfully!'),
                        'id' => $id,
                        'cart' => $cart
                    ]);
                }
                
                return response()->json([
                    'code' => 404,
                    'status' => 'Error',
                    'error' => __('Product not found in cart!')
                ], 404);
                
            } catch (\Exception $e) {
                \Log::error('Error in removeFromCart: ' . $e->getMessage());
                return response()->json([
                    'code' => 500,
                    'status' => 'Error',
                    'error' => __('Something went wrong!')
                ], 500);
            }
        }
    }

    /**
     * Search products for POS
     */
    public function searchProducts(Request $request)
    {
        $lastsegment = $request->session_key;

        if (Auth::user()->can('manage pos') && $request->ajax() && isset($lastsegment) && !empty($lastsegment)) {
            $output = "";
            $warehouse_id = $request->war_id != 0 ? $request->war_id : null;
            
            try {
                if($request->war_id == '0' || empty($request->war_id)){
                    // Get products from all warehouses for the current user
                    $ids = WarehouseProduct::where('created_by', Auth::user()->creatorId())
                        ->get()
                        ->pluck('product_id')
                        ->unique()
                        ->toArray();

                    if ($request->cat_id !== '' && $request->search == '') {
                        if($request->cat_id == '0'){
                            $products = ProductService::whereIn('id', $ids)
                                ->where('created_by', Auth::user()->creatorId())
                                ->with('unit')
                                ->get();
                        } else {
                            $products = ProductService::where('category_id', $request->cat_id)
                                ->whereIn('id', $ids)
                                ->where('created_by', Auth::user()->creatorId())
                                ->with('unit')
                                ->get();
                        }
                    } else {
                        if($request->cat_id == '0'){
                            $products = ProductService::where('name', 'LIKE', "%{$request->search}%")
                                ->orWhere('sku', 'LIKE', "%{$request->search}%")
                                ->where('created_by', Auth::user()->creatorId())
                                ->with('unit')
                                ->get();
                        } else {
                            $products = ProductService::where('name', 'LIKE', "%{$request->search}%")
                                ->orWhere('sku', 'LIKE', "%{$request->search}%")
                                ->where('category_id', $request->cat_id)
                                ->where('created_by', Auth::user()->creatorId())
                                ->with('unit')
                                ->get();
                        }
                    }
                } else {
                    $ids = WarehouseProduct::where('warehouse_id', $request->war_id)
                        ->pluck('product_id')
                        ->toArray();
                    
                    if($request->cat_id == '0'){
                        $products = ProductService::whereIn('id', $ids)
                            ->where('created_by', Auth::user()->creatorId())
                            ->with('unit')
                            ->get();
                    } else {
                        $products = ProductService::whereIn('id', $ids)
                            ->where('category_id', $request->cat_id)
                            ->where('created_by', Auth::user()->creatorId())
                            ->with('unit')
                            ->get();
                    }
                }

                if (count($products) > 0) {
                    foreach ($products as $product) {
                        $quantity = $product->warehouseProduct($product->id, $request->war_id != 0 ? $request->war_id : null);

                        $unit = (!empty($product->unit)) ? $product->unit->name : '';

                        if (!empty($product->pro_image) && file_exists(public_path('uploads/pro_image/' . $product->pro_image))) {
                            $image_url = 'uploads/pro_image/' . $product->pro_image;
                        } else {
                            $image_url = 'uploads/pro_image/default.png';
                        }
                        
                        if ($request->session_key == 'purchases') {
                            $productprice = $product->purchase_price != 0 ? $product->purchase_price : 0;
                        } else if ($request->session_key == 'pos') {
                            $productprice = $product->sale_price != 0 ? $product->sale_price : 0;
                        } else {
                            $productprice = $product->sale_price != 0 ? $product->sale_price : $product->purchase_price;
                        }

                        // Include warehouse_id in the add-to-cart URL
                        $warehouseParam = $warehouse_id ? '/' . $warehouse_id : '/0';
                        $addToCartUrl = url('add-to-cart/' . $product->id . '/' . $lastsegment . $warehouseParam);

                        $output .= '
                            <div class="col-lg-2 col-md-2 col-sm-3 col-xs-4 col-12">
                                <div class="tab-pane fade show active toacart w-100" data-url="' . $addToCartUrl . '">
                                    <div class="position-relative card">
                                        <img alt="Image placeholder" src="' . asset($image_url) . '" class="card-image avatar shadow hover-shadow-lg" style="height: 6rem; width: 100%; object-fit: cover;">
                                        <div class="p-0 custom-card-body card-body d-flex">
                                            <div class="card-body my-2 p-2 text-left card-bottom-content">
                                                <h6 class="mb-2 text-dark product-title-name">' . $product->name . '</h6>
                                                <h6 class="mb-2 text-dark product-title-name">' . $product->sku . '</h6>
                                                <small class="badge badge-primary mb-0">' . Auth::user()->priceFormat($productprice) . '</small>
                                                <small class="top-badge badge badge-danger mb-0">' . $quantity . ' ' . $unit . '</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ';
                    }

                    return response($output);
                } else {
                    $output = '<div class="card card-body col-12 text-center">
                        <h5>'.__("No Product Available").'</h5>
                    </div>';
                    return response($output);
                }
            } catch (\Exception $e) {
                \Log::error('Error in searchProducts: ' . $e->getMessage());
                return response('<div class="card card-body col-12 text-center"><h5>'.__("Error loading products").'</h5></div>');
            }
        }
    }

    /**
     * Add product to cart
     */
    public function addToCart(Request $request, $id, $session_key, $warehouse_id = null)
    {
        if (Auth::user()->can('manage product & service') && $request->ajax()) {
            try {
                $product = ProductService::find($id);
                
                if (!$product) {
                    return response()->json([
                        'code' => 404,
                        'status' => 'Error',
                        'error' => __('Product not found!'),
                    ], 404);
                }

                $productname = $product->name;

                if ($session_key == 'purchases') {
                    $productprice = $product->purchase_price != 0 ? $product->purchase_price : 0;
                } else if ($session_key == 'pos') {
                    $productprice = $product->sale_price != 0 ? $product->sale_price : 0;
                } else {
                    $productprice = $product->sale_price != 0 ? $product->sale_price : $product->purchase_price;
                }

                // Get tax information
                $taxes = [];
                $totalTaxRate = 0;
                
                if (!empty($product->tax_id)) {
                    $taxIds = explode(',', $product->tax_id);
                    $taxes = Tax::whereIn('id', $taxIds)->get();
                    $totalTaxRate = $taxes->sum('rate');
                }

                $product_tax = '';
                $product_tax_id = [];
                
                foreach ($taxes as $tax) {
                    $product_tax .= !empty($tax) ? "<span class='badge badge-primary'>" . $tax->name . ' (' . $tax->rate . '%)' . "</span><br>" : '';
                    $product_tax_id[] = !empty($tax) ? $tax->id : 0;
                }

                if (empty($product_tax)) {
                    $product_tax = "-";
                }
                
                $producttax = $totalTaxRate;
                $tax = ($productprice * $producttax) / 100;
                $subtotal = $productprice + $tax;
                $cart = session()->get($session_key, []);
                
                // Fix image path
                if (!empty($product->pro_image) && file_exists(public_path('uploads/pro_image/' . $product->pro_image))) {
                    $image_url = 'uploads/pro_image/' . $product->pro_image;
                } else {
                    $image_url = 'uploads/pro_image/default.png';
                }

                // if cart is empty then this the first product
                if (empty($cart)) {
                    $cart = [
                        $id => [
                            "name" => $productname,
                            "quantity" => 1,
                            "price" => $productprice,
                            "id" => $id,
                            "tax" => $producttax,
                            "subtotal" => $subtotal,
                            "product_tax" => $product_tax,
                            "product_tax_id" => !empty($product_tax_id) ? implode(',', $product_tax_id) : 0,
                            "image" => $image_url,
                        ],
                    ];

                    session()->put($session_key, $cart);

                    return response()->json([
                        'code' => 200,
                        'status' => 'Success',
                        'success' => $productname . __(' added to cart successfully!'),
                        'product' => $cart[$id],
                    ]);
                }

                // if cart not empty then check if this product exist then increment quantity
                if (isset($cart[$id])) {
                    $cart[$id]['quantity']++;
                    $cart[$id]['id'] = $id;

                    $subtotal = $cart[$id]["price"] * $cart[$id]["quantity"];
                    $tax = ($subtotal * $cart[$id]["tax"]) / 100;

                    $cart[$id]["subtotal"] = $subtotal + $tax;

                    session()->put($session_key, $cart);

                    return response()->json([
                        'code' => 200,
                        'status' => 'Success',
                        'success' => $productname . __(' added to cart successfully!'),
                        'product' => $cart[$id],
                        'carttotal' => $cart,
                    ]);
                }

                // if item not exist in cart then add to cart with quantity = 1
                $cart[$id] = [
                    "name" => $productname,
                    "quantity" => 1,
                    "price" => $productprice,
                    "id" => $id,
                    "tax" => $producttax,
                    "subtotal" => $subtotal,
                    "product_tax" => $product_tax,
                    "product_tax_id" => !empty($product_tax_id) ? implode(',', $product_tax_id) : 0,
                    "image" => $image_url,
                ];

                session()->put($session_key, $cart);

                return response()->json([
                    'code' => 200,
                    'status' => 'Success',
                    'success' => $productname . __(' added to cart successfully!'),
                    'product' => $cart[$id],
                    'carttotal' => $cart,
                ]);

            } catch (\Exception $e) {
                \Log::error('Error in addToCart: ' . $e->getMessage());
                return response()->json([
                    'code' => 500,
                    'status' => 'Error',
                    'error' => __('Something went wrong!'),
                ], 500);
            }
        }
    }
}