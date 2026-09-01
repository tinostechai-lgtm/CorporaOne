<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Pos;
use App\Models\PosPayment;
use App\Models\PosProduct;
use App\Models\ProductService;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\StockReport;
use App\Models\User;
use App\Models\Utility;
use App\Models\warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id = 0)
    {
        if (Auth::user()->can('manage pos')) {
            $customers = Customer::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'name');
            $customers->prepend('Walk-in-customer', '');
            $warehouses = warehouse::select('*', \DB::raw("CONCAT(name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $user = Auth::user();
            
            // Get the last segment from URL to use as session key
            $lastsegment = request()->segment(count(request()->segments()));
            
            $details = [
                'pos_id' => $user->posNumberFormat($this->invoicePosNumber()),
                'customer' => $customers != null ? $customers->toArray() : [],
                'user' => $user != null ? $user->toArray() : [],
                'date' => date('Y-m-d'),
                'pay' => 'show',
            ];

            if ($id != 0 && is_numeric($id)) {
                $quotation = Quotation::find($id);
                
                if ($quotation) {
                    $customerId = $quotation->customer_id;
                    $customerObj = Customer::find($customerId);
                    $customer = $customerObj ? $customerObj->name : '';
                    $warehouseId = $quotation->warehouse_id;
                    $quotationProduct = QuotationProduct::where('quotation_id', $id)->get();
                } else {
                    $customer = '';
                    $warehouseId = '';
                }
            } else {
                $customer = '';
                $warehouseId = '';
            }
            
            return view('pos.index', compact('customers', 'warehouses', 'details', 'customer', 'warehouseId', 'id', 'lastsegment'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try {
            $sess = session()->get('pos');

            if (!Auth::user()->can('manage pos')) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }

            if (!isset($sess) || empty($sess) || count($sess) == 0) {
                return response()->json(['error' => __('Add some products to cart!')], 404);
            }

            $user = Auth::user();
            $settings = Utility::settings();

            // Validate customer
            if (empty($request->vc_name)) {
                return response()->json(['error' => __('Please select a customer')], 400);
            }

            // Validate warehouse
            if (empty($request->warehouse_name)) {
                return response()->json(['error' => __('Please select a warehouse')], 400);
            }

            $customer = Customer::where('name', '=', $request->vc_name)
                ->where('created_by', $user->creatorId())
                ->first();

            if (!$customer) {
                return response()->json(['error' => __('Customer not found!')], 404);
            }

            $warehouse = warehouse::where('id', '=', $request->warehouse_name)
                ->where('created_by', $user->creatorId())
                ->first();

            if (!$warehouse) {
                return response()->json(['error' => __('Warehouse not found!')], 404);
            }

            $posNumber = $this->invoicePosNumber();
            
            // Prepare customer details HTML
            $customerName = $customer->name ?? 'Walk-in Customer';
            $customerPhone = $customer->billing_phone ?? '';
            $customerAddress = $customer->billing_address ?? '';
            $customerCity = $customer->billing_city ?? '';
            $customerState = $customer->billing_state ?? '';
            $customerCountry = $customer->billing_country ?? '';
            $customerZip = $customer->billing_zip ?? '';

            $customerdetails = "<h6 class='text-dark'>" . ucfirst($customerName) . 
                "<p class='m-0 h6 font-weight-normal'>" . $customerPhone . "</p>" .
                "<p class='m-0 h6 font-weight-normal'>" . $customerAddress . "</p>" .
                "<p class='m-0 h6 font-weight-normal'>" . $customerCity . 
                ($customerState ? ", " . $customerState : "") . "</p>" .
                "<p class='m-0 h6 font-weight-normal'>" . $customerCountry . "</p>" .
                "<p class='m-0 h6 font-weight-normal'>" . $customerZip . "</p></h6>";

            $warehouseName = $warehouse->name ?? '';
            $warehousedetails = "<h7 class='text-dark'>" . ucfirst($warehouseName) . "</h7>";

            // Prepare shipping details
            $shippingPhone = $customer->shipping_phone ?? '';
            $shippingAddress = $customer->shipping_address ?? '';
            $shippingCity = $customer->shipping_city ?? '';
            $shippingState = $customer->shipping_state ?? '';
            $shippingCountry = $customer->shipping_country ?? '';
            $shippingZip = $customer->shipping_zip ?? '';

            $shippdetails = "<h6 class='text-dark'><b>" . ucfirst($customerName) . "</b>" .
                "<p class='m-0 h6 font-weight-normal'>" . $shippingPhone . "</p>" .
                "<p class='m-0 h6 font-weight-normal'>" . $shippingAddress . "</p>" .
                "<p class='m-0 h6 font-weight-normal'>" . $shippingCity . 
                ($shippingState ? ", " . $shippingState : "") . "</p>" .
                "<p class='m-0 h6 font-weight-normal'>" . $shippingCountry . "</p>" .
                "<p class='m-0 h6 font-weight-normal'>" . $shippingZip . "</p></h6>";

            // Prepare company details
            $companyName = $settings['company_name'] ?? '';
            $companyTelephone = $settings['company_telephone'] ?? '';
            $companyAddress = $settings['company_address'] ?? '';
            $companyCity = $settings['company_city'] ?? '';
            $companyState = $settings['company_state'] ?? '';
            $companyCountry = $settings['company_country'] ?? '';
            $companyZipcode = $settings['company_zipcode'] ?? '';

            $userdetails = "<h6 class='text-dark'><b>" . ucfirst($user->name) . "</b>" .
                "<p class='m-0 font-weight-normal'>" . $companyName . 
                ($companyTelephone ? ", " . $companyTelephone : "") . "</p>" .
                "<p class='m-0 font-weight-normal'>" . $companyAddress . "</p>" .
                "<p class='m-0 h6 font-weight-normal'>" . $companyCity . 
                ($companyState ? ", " . $companyState : "") . "</p>" .
                "<p class='m-0 font-weight-normal'>" . $companyCountry . "</p>" .
                "<p class='m-0 font-weight-normal'>" . $companyZipcode . "</p></h6>";

            $details = [
                'pos_id' => $user->posNumberFormat($posNumber),
                'customer' => [
                    'name' => $customer->name,
                    'details' => $customerdetails,
                    'shippdetails' => $shippdetails
                ],
                'warehouse' => [
                    'name' => $warehouse->name,
                    'details' => $warehousedetails,
                    'id' => $warehouse->id
                ],
                'user' => [
                    'name' => $user->name,
                    'details' => $userdetails
                ],
                'date' => date('Y-m-d'),
                'pay' => 'show',
            ];

            $mainsubtotal = 0;
            $sales = [];

            foreach ($sess as $key => $value) {
                $price = floatval($value['price'] ?? 0);
                $quantity = intval($value['quantity'] ?? 1);
                $tax = floatval($value['tax'] ?? 0);
                $subtotal = $price * $quantity;
                $taxAmount = ($subtotal * $tax) / 100;
                
                $sales['data'][$key] = [
                    'name' => $value['name'] ?? '',
                    'quantity' => $quantity,
                    'price' => $this->formatPrice($price),
                    'tax' => $tax . '%',
                    'product_tax' => $value['product_tax'] ?? '-',
                    'tax_amount' => $this->formatPrice($taxAmount),
                    'subtotal' => $this->formatPrice($subtotal + $taxAmount)
                ];
                
                $mainsubtotal += ($subtotal + $taxAmount);
            }

            $discount = !empty($request->discount) ? floatval($request->discount) : 0;
            $total = $mainsubtotal - $discount;
            
            $sales['discount'] = $this->formatPrice($discount);
            $sales['sub_total'] = $this->formatPrice($mainsubtotal);
            $sales['total'] = $this->formatPrice($total);

            return view('pos.show', compact('sales', 'details', 'request'));

        } catch (\Exception $e) {
            Log::error('POS Create Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $discount = floatval($request->discount ?? 0);

            if (!Auth::user()->can('manage pos')) {
                return response()->json(['error' => __('Permission denied.')], 403);
            }

            $user_id = Auth::user()->creatorId();
            
            // Get customer ID
            $customer = Customer::where('name', '=', $request->vc_name)
                ->where('created_by', $user_id)
                ->first();
            
            if (!$customer) {
                return response()->json(['error' => __('Customer not found!')], 404);
            }
            
            $customer_id = $customer->id;
            
            // Get warehouse ID
            $warehouse = warehouse::where('id', '=', $request->warehouse_name)
                ->where('created_by', $user_id)
                ->first();
            
            if (!$warehouse) {
                return response()->json(['error' => __('Warehouse not found!')], 404);
            }
            
            $pos_id = $this->invoicePosNumber();
            $sales = session()->get('pos');

            if (isset($sales) && !empty($sales) && count($sales) > 0) {
                
                // Check if POS already exists
                $existingPos = DB::table('pos')->where('pos_id', $pos_id)->where('created_by', $user_id)->first();
                if ($existingPos) {
                    return response()->json(['code' => 200, 'success' => __('Payment is already completed!')]);
                }
                
                // Create POS
                $pos = new Pos();
                $pos->pos_id = $pos_id;
                $pos->customer_id = $customer_id;
                $pos->warehouse_id = $request->warehouse_name;
                $pos->pos_date = date('Y-m-d');
                $pos->created_by = $user_id;
                $pos->save();

                // Update quotation if exists
                if($request->quotation_id != 0 && $request->quotation_id != '0') {
                    $quotation = Quotation::where('id', $request->quotation_id)->first();
                    if ($quotation) {
                        $quotation->is_converted = 1;
                        $quotation->converted_pos_id = $pos->id;
                        $quotation->save();
                    }
                }
                
                // Process each item
                foreach ($sales as $key => $value) {
                    $product_id = $value['id'];

                    $product = ProductService::where('id', $product_id)
                        ->where('created_by', $user_id)
                        ->first();

                    if ($product) {
                        $original_quantity = (int) $product->quantity;
                        $product_quantity = $original_quantity - intval($value['quantity']);
                        ProductService::where('id', $product_id)->update(['quantity' => $product_quantity]);
                    }

                    $tax_id = $value['product_tax_id'] ?? '';

                    $positems = new PosProduct();
                    $positems->pos_id = $pos->id;
                    $positems->product_id = $product_id;
                    $positems->price = floatval($value['price'] ?? 0);
                    $positems->quantity = intval($value['quantity'] ?? 1);
                    $positems->tax = $tax_id;
                    $positems->discount = $discount;
                    $positems->save();

                    // Update warehouse quantity
                    if (method_exists('App\Models\Utility', 'warehouse_quantity')) {
                        Utility::warehouse_quantity('minus', $positems->quantity, $positems->product_id, $request->warehouse_name);
                    }

                    // Update stock report
                    $type = 'pos';
                    $type_id = $pos->id;
                    StockReport::where('type', '=', 'pos')->where('type_id', '=', $pos->id)->delete();
                    $description = $positems->quantity . '  ' . __(' quantity sold in pos') . ' ' . \Auth::user()->posNumberFormat($pos->pos_id);
                    
                    if (method_exists('App\Models\Utility', 'addProductStock')) {
                        Utility::addProductStock($positems->product_id, $positems->quantity, $type, $description, $type_id);
                    }
                }

                // Create payment record
                $posPayment = new PosPayment();
                $posPayment->pos_id = $pos->id;
                $posPayment->date = $request->date ?? date('Y-m-d');

                $mainsubtotal = 0;
                foreach ($sales as $value) {
                    $mainsubtotal += floatval($value['subtotal'] ?? 0);
                }
                
                $posPayment->amount = $mainsubtotal;
                $posPayment->discount = $discount;
                $posPayment->discount_amount = $mainsubtotal - $discount;
                $posPayment->save();

                // Clear the cart session
                session()->forget('pos');

                return response()->json([
                    'code' => 200,
                    'success' => __('Payment completed successfully!'),
                    'pos_id' => $pos->id,
                    'invoice_number' => $pos->pos_id
                ]);
                
            } else {
                return response()->json(['code' => 404, 'error' => __('Items not found!')], 404);
            }
            
        } catch (\Exception $e) {
            Log::error('POS Store Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Format price with currency symbol
     */
    private function formatPrice($price)
    {
        $settings = Utility::settings();
        $currencySymbol = $settings['site_currency_symbol'] ?? '$';
        return $currencySymbol . number_format(floatval($price), 2);
    }

    public function show($ids)
    {
        if (\Auth::user()->can('show pos') || \Auth::user()->type == 'company') {
            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('Pos Not Found.'));
            }

            $pos = Pos::find($id);

            if ($pos && $pos->created_by == \Auth::user()->creatorId()) {
                $posPayment = PosPayment::where('pos_id', $pos->id)->first();
                $customer = $pos->customer;
                $iteams = $pos->items;

                return view('pos.view', compact('pos', 'customer', 'iteams', 'posPayment'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function invoicePosNumber()
    {
        if (Auth::user()->can('manage pos')) {
            $latest = Pos::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
            return $latest ? $latest->pos_id + 1 : 1;
        } else {
            return 1;
        }
    }

    public function report()
    {
        if (\Auth::user()->can('manage pos')) {
            $posPayments = Pos::where('created_by', '=', \Auth::user()->creatorId())->with(['customer', 'warehouse'])->get();
            return view('pos.report', compact('posPayments'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function barcode()
    {
        if (\Auth::user()->can('manage pos')) {
            $productServices = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get();
            $barcode = [
                'barcodeType' => Auth::user()->barcodeType(),
                'barcodeFormat' => Auth::user()->barcodeFormat(),
            ];
            return view('pos.barcode', compact('productServices', 'barcode'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function setting()
    {
        if (\Auth::user()->can('manage pos')) {
            $settings = Utility::settings();
            return view('pos.setting', compact('settings'));
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function BarcodesettingStore(Request $request)
    {
        $request->validate([
            'barcode_type' => 'required',
            'barcode_format' => 'required',
        ]);

        $post['barcode_type'] = $request->barcode_type;
        $post['barcode_format'] = $request->barcode_format;

        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`, `created_by`) values (?, ?, ?) 
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$data, $key, \Auth::user()->id]
            );
        }
        return redirect()->back()->with('success', 'Barcode setting successfully updated.');
    }

    public function printBarcode()
    {
        if (\Auth::user()->can('manage pos')) {
            $warehouses = warehouse::select('*', \DB::raw("CONCAT(name) AS name"))
                ->where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
            return view('pos.print', compact('warehouses'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function getproduct(Request $request)
    {
        if ($request->warehouse_id == 0) {
            $productServices = WarehouseProduct::where('product_id', '=', $request->warehouse_id)
                ->where('created_by', '=', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        } else {
            $productServicesId = WarehouseProduct::where('created_by', '=', \Auth::user()->creatorId())
                ->where('warehouse_id', $request->warehouse_id)
                ->get()
                ->pluck('product_id')
                ->toArray();
            $productServices = ProductService::whereIn('id', $productServicesId)
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        }
        return response()->json($productServices);
    }

    public function receipt(Request $request)
    {
        if (!empty($request->product_id)) {
            $productServices = ProductService::whereIn('id', $request->product_id)->get();
            $quantity = $request->quantity;
            $barcode = [
                'barcodeType' => Auth::user()->barcodeType() == '' ? 'code128' : Auth::user()->barcodeType(),
                'barcodeFormat' => Auth::user()->barcodeFormat() == '' ? 'css' : Auth::user()->barcodeFormat(),
            ];
        } else {
            return redirect()->back()->with('error', 'Product is required.');
        }
        return view('pos.receipt', compact('productServices', 'barcode', 'quantity'));
    }

    public function cartdiscount(Request $request)
    {
        $sess = session()->get('pos');
        $subtotal = !empty($sess) ? array_sum(array_column($sess, 'subtotal')) : 0;
        $discount = $request->discount ?? 0;
        $total = $subtotal - $discount;
        $total = $this->formatPrice($total);
        return response()->json(['total' => $total], 200);
    }

    public function pos($pos_id)
    {
        $settings = Utility::settings();
        $posId = Crypt::decrypt($pos_id);
        $pos = Pos::where('id', $posId)->first();

        if (!$pos) {
            return redirect()->back()->with('error', __('POS not found.'));
        }

        $posPayment = PosPayment::where('pos_id', $pos->id)->first();

        $data = DB::table('settings')->where('created_by', '=', $pos->created_by)->get();
        foreach ($data as $row) {
            $settings[$row->name] = $row->value;
        }

        $customer = $pos->customer;

        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate = 0;
        $totalDiscount = 0;
        $taxesData = [];
        $items = [];

        foreach ($pos->items as $product) {
            $item = new \stdClass();
            $item->name = !empty($product->product()) ? $product->product()->name : '';
            $item->quantity = $product->quantity;
            $item->tax = $product->tax;
            $item->discount = $product->discount;
            $item->price = $product->price;
            $item->description = $product->description;
            $totalQuantity += $item->quantity;
            $totalRate += $item->price;
            $totalDiscount += $item->discount;
            
            $taxes = Utility::tax($product->tax);
            $itemTaxes = [];
            
            if (!empty($item->tax)) {
                foreach ($taxes as $tax) {
                    $taxPrice = Utility::taxRate($tax->rate, $item->price, $item->quantity);
                    $totalTaxPrice += $taxPrice;

                    $itemTax['name'] = $tax->name;
                    $itemTax['rate'] = $tax->rate . '%';
                    $itemTax['price'] = Utility::priceFormat($settings, $taxPrice);
                    $itemTaxes[] = $itemTax;

                    if (array_key_exists($tax->name, $taxesData)) {
                        $taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
                    } else {
                        $taxesData[$tax->name] = $taxPrice;
                    }
                }
                $item->itemTax = $itemTaxes;
            } else {
                $item->itemTax = [];
            }
            $items[] = $item;
        }

        $pos->itemData = $items;
        $pos->totalTaxPrice = $totalTaxPrice;
        $pos->totalQuantity = $totalQuantity;
        $pos->totalRate = $totalRate;
        $pos->totalDiscount = $totalDiscount;
        $pos->taxesData = $taxesData;

        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $pos_logo = Utility::getValByName('pos_logo');
        
        if (isset($pos_logo) && !empty($pos_logo)) {
            $img = Utility::get_file('pos_logo/') . $pos_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        $color = '#' . ($settings['pos_color'] ?? '4f46e5');
        $font_color = Utility::getFontColor($color);

        return view('pos.templates.' . ($settings['pos_template'] ?? 'template1'), 
            compact('pos', 'posPayment', 'color', 'settings', 'customer', 'img', 'font_color'));
    }

    public function previewPos($template, $color)
    {
        $objUser = \Auth::user();
        $settings = Utility::settings();

        $pos = new Pos();
        $posPayment = new PosPayment();
        $posPayment->amount = 360;
        $posPayment->discount = 100;

        $customer = new \stdClass();
        $customer->email = '<Email>';
        $customer->shipping_name = '<Customer Name>';
        $customer->shipping_country = '<Country>';
        $customer->shipping_state = '<State>';
        $customer->shipping_city = '<City>';
        $customer->shipping_phone = '<Customer Phone Number>';
        $customer->shipping_zip = '<Zip>';
        $customer->shipping_address = '<Address>';
        $customer->billing_name = '<Customer Name>';
        $customer->billing_country = '<Country>';
        $customer->billing_state = '<State>';
        $customer->billing_city = '<City>';
        $customer->billing_phone = '<Customer Phone Number>';
        $customer->billing_zip = '<Zip>';
        $customer->billing_address = '<Address>';

        $totalTaxPrice = 0;
        $taxesData = [];
        $items = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $item = new \stdClass();
            $item->name = 'Item ' . $i;
            $item->quantity = 1;
            $item->tax = 5;
            $item->discount = 50;
            $item->price = 100;

            $itemTaxes = [];
            foreach (['Tax 1', 'Tax 2'] as $k => $tax) {
                $taxPrice = 10;
                $totalTaxPrice += $taxPrice;
                $itemTax['name'] = 'Tax ' . $k;
                $itemTax['rate'] = '10 %';
                $itemTax['price'] = '$10';
                $itemTaxes[] = $itemTax;
                
                if (array_key_exists('Tax ' . $k, $taxesData)) {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                } else {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[] = $item;
        }

        $pos->pos_id = 1;
        $pos->issue_date = date('Y-m-d H:i:s');
        $pos->itemData = $items;
        $pos->totalTaxPrice = 60;
        $pos->totalQuantity = 3;
        $pos->totalRate = 300;
        $pos->totalDiscount = 10;
        $pos->taxesData = $taxesData;
        $pos->created_by = $objUser->creatorId();

        $preview = 1;
        $color = '#' . $color;
        $font_color = Utility::getFontColor($color);

        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $settings_data = \App\Models\Utility::settingsById($pos->created_by);
        $pos_logo = $settings_data['pos_logo'] ?? '';

        if (isset($pos_logo) && !empty($pos_logo)) {
            $img = Utility::get_file('pos_logo/') . $pos_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        return view('pos.templates.' . $template, 
            compact('pos', 'preview', 'color', 'img', 'settings', 'customer', 'font_color', 'posPayment'));
    }

    public function savePosTemplateSettings(Request $request)
    {
        $post = $request->all();
        unset($post['_token']);

        if (isset($post['pos_template']) && (!isset($post['pos_color']) || empty($post['pos_color']))) {
            $post['pos_color'] = "ffffff";
        }

        if ($request->pos_logo) {
            $dir = 'pos_logo/';
            $pos_logo = \Auth::user()->id . '_pos_logo.png';
            $validation = ['mimes:' . 'png', 'max:' . '20480'];
            $path = Utility::upload_file($request, 'pos_logo', $pos_logo, $dir, $validation);
            
            if ($path['flag'] == 0) {
                return redirect()->back()->with('error', __($path['msg']));
            }
            $post['pos_logo'] = $pos_logo;
        }

        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`, `created_by`) values (?, ?, ?) 
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$data, $key, \Auth::user()->creatorId()]
            );
        }

        return redirect()->back()->with('success', __('POS Setting updated successfully'));
    }

    public function printView(Request $request)
    {
        $sess = session()->get('pos');
        $user = Auth::user();
        $settings = Utility::settings();

        $customer = Customer::where('name', '=', $request->vc_name)
            ->where('created_by', $user->creatorId())
            ->first();
            
        $warehouse = warehouse::where('id', '=', $request->warehouse_name)
            ->where('created_by', $user->creatorId())
            ->first();

        $details = [
            'pos_id' => $user->posNumberFormat($this->invoicePosNumber()),
            'customer' => $customer ? $customer->toArray() : [],
            'warehouse' => $warehouse ? $warehouse->toArray() : [],
            'user' => $user->toArray(),
            'date' => date('Y-m-d'),
            'pay' => 'show',
        ];

        // Prepare HTML details
        $customerName = $customer->name ?? 'Walk-in Customer';
        $customerdetails = "<h6 class='text-dark'>" . ucfirst($customerName) . "</h6>";
        $warehousedetails = "<h7 class='text-dark'>" . ucfirst($warehouse->name ?? '') . "</h7>";
        $shippdetails = $customer ? "<h6 class='text-dark'>" . ucfirst($customerName) . "</h6>" : '-';

        $userdetails = "<h6 class='text-dark'><b>" . ucfirst($user->name) . "</b></h6>";

        $details['customer']['details'] = $customerdetails;
        $details['warehouse']['details'] = $warehousedetails;
        $details['customer']['shippdetails'] = $shippdetails;
        $details['user']['details'] = $userdetails;

        $mainsubtotal = 0;
        $sales = [];

        if (!empty($sess)) {
            foreach ($sess as $key => $value) {
                $subtotal = floatval($value['price'] ?? 0) * intval($value['quantity'] ?? 1);
                $tax = ($subtotal * floatval($value['tax'] ?? 0)) / 100;
                
                $sales['data'][$key]['name'] = $value['name'] ?? '';
                $sales['data'][$key]['quantity'] = $value['quantity'] ?? 1;
                $sales['data'][$key]['price'] = $this->formatPrice($value['price'] ?? 0);
                $sales['data'][$key]['tax'] = ($value['tax'] ?? 0) . '%';
                $sales['data'][$key]['product_tax'] = $value['product_tax'] ?? '-';
                $sales['data'][$key]['tax_amount'] = $this->formatPrice($tax);
                $sales['data'][$key]['subtotal'] = $this->formatPrice($subtotal + $tax);
                $mainsubtotal += ($subtotal + $tax);
            }
        }

        $discount = !empty($request->discount) ? floatval($request->discount) : 0;
        $sales['discount'] = $this->formatPrice($discount);
        $sales['sub_total'] = $this->formatPrice($mainsubtotal);
        $sales['total'] = $this->formatPrice($mainsubtotal - $discount);

        $productServices = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get();
        $barcode = [
            'barcodeType' => Auth::user()->barcodeType(),
            'barcodeFormat' => Auth::user()->barcodeFormat(),
        ];

        return view('pos.printview', compact('details', 'sales', 'customer', 'productServices', 'barcode'));
    }
}