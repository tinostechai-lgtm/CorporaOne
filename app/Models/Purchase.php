<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'vender_id',
        'warehouse_id',
        'purchase_date',
        'purchase_number',
        'discount_apply',
        'category_id',
        'created_by',
    ];

    public static $statues = [
        'Draft',
        'Sent',
        'Unpaid',
        'Partialy Paid',
        'Paid',
    ];

    // Relationships
    public function vender()
    {
        return $this->belongsTo('App\Models\Vender', 'vender_id');
    }

    public function tax()
    {
        return $this->belongsTo('App\Models\Tax', 'tax_id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\PurchaseProduct', 'purchase_id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\PurchasePayment', 'purchase_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\ProductServiceCategory', 'category_id');
    }

    public function lastPayments()
    {
        return $this->hasOne('App\Models\PurchasePayment', 'purchase_id', 'id')->latest();
    }

    // Calculations
    public function getSubTotal(): float
    {
        return $this->items->sum(function($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getTotalDiscount(): float
    {
        return $this->items->sum('discount');
    }

    public function getTotalTax(): float
    {
        $taxData = Utility::getTaxData();
        
        return $this->items->sum(function($item) use ($taxData) {
            $taxes = 0;
            $taxArr = explode(',', $item->tax);
            
            foreach ($taxArr as $tax) {
                $rate = $taxData[$tax]['rate'] ?? 0;
                $taxes += ($rate / 100) * ($item->price * $item->quantity);
            }
            
            return $taxes;
        });
    }

    public function getTotal(): float
    {
        return ($this->getSubTotal() - $this->getTotalDiscount()) + $this->getTotalTax();
    }

    public function getDue(): float
    {
        return $this->getTotal() - $this->payments->sum('amount');
    }

    // Dashboard-related methods
    public static function getPurchaseData(bool $monthly = false): float
    {
        $query = DB::table('purchase_products')
            ->select(
                'purchase_products.id as purchase_id',
                DB::raw('SUM(purchase_products.quantity) as quantity'),
                DB::raw('SUM(discount) as total_discount'),
                'purchase_products.tax',
                DB::raw('SUM(price) as price')
            )
            ->join('purchases', 'purchase_products.purchase_id', '=', 'purchases.id')
            ->where('purchases.created_by', Auth::user()->creatorId())
            ->groupBy('purchase_products.id');
            
        if ($monthly) {
            $query->whereMonth('purchases.created_at', now()->month);
        }

        $purchaseProducts = $query->get();
        $total = 0;

        $taxData = Utility::getTaxData();
        
        foreach ($purchaseProducts as $purchase) {
            $totalTaxPrice = 0;
            
            if (!empty($purchase->tax)) {
                foreach (explode(',', $purchase->tax) as $tax) {
                    $rate = $taxData[$tax]['rate'] ?? 0;
                    $taxPrice = Utility::taxRate($rate, $purchase->price, $purchase->quantity, $purchase->total_discount);
                    $totalTaxPrice += $taxPrice;
                }
            }

            $total += ($purchase->price * $purchase->quantity) + $totalTaxPrice - $purchase->total_discount;
        }

        return $total;
    }

    public static function totalPurchaseAmount(bool $monthly = false): string
    {
        $amount = self::getPurchaseData($monthly);
        return Auth::user()->priceFormat($amount);
    }

    public static function getPurchaseReportChart(): array
    {
        $data = [
            'label' => [],
            'value' => []
        ];

        // Get data for last 10 days
        for ($i = 9; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateKey = $date->format('dm');
            $dateLabel = $date->format('Y-m-d');

            $total = self::whereDate('created_at', $date)
                ->where('created_by', Auth::user()->creatorId())
                ->with('items') // Eager load items for better performance
                ->get()
                ->sum(function($purchase) {
                    return $purchase->getTotal();
                });

            $data['label'][] = $dateLabel;
            $data['value'][] = $total;
        }

        return $data;
    }
}