<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Pos extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_id',
        'customer_id',
        'warehouse_id',
        'pos_date',
        'category_id',
        'status',
        'shipping_display',
        'created_by',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo('App\Models\Customer', 'customer_id');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }

    public function posPayment()
    {
        return $this->hasOne('App\Models\PosPayment', 'pos_id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\PosProduct', 'pos_id');
    }

    public function taxes()
    {
        return $this->belongsTo('App\Models\Tax', 'tax');
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

    // Dashboard-related methods
    public static function getPosProductsData(bool $monthly = false): float
    {
        $query = DB::table('pos_products')
            ->select(
                'pos_products.id as pos_id',
                DB::raw('SUM(pos_products.quantity) as quantity'),
                DB::raw('SUM(discount) as total_discount'),
                'pos_products.tax',
                DB::raw('SUM(price) as price')
            )
            ->join('pos', 'pos_products.pos_id', '=', 'pos.id')
            ->where('pos.created_by', Auth::user()->creatorId())
            ->groupBy('pos_products.id');
            
        if ($monthly) {
            $query->whereMonth('pos.created_at', now()->month);
        }

        $posProducts = $query->get();
        $total = 0;

        $taxData = Utility::getTaxData();
        
        foreach ($posProducts as $pos) {
            $totalTaxPrice = 0;
            
            if (!empty($pos->tax)) {
                foreach (explode(',', $pos->tax) as $tax) {
                    $rate = $taxData[$tax]['rate'] ?? 0;
                    $taxPrice = Utility::taxRate($rate, $pos->price, $pos->quantity, $pos->total_discount);
                    $totalTaxPrice += $taxPrice;
                }
            }

            $total += ($pos->price * $pos->quantity) + $totalTaxPrice - $pos->total_discount;
        }

        return $total;
    }

    public static function totalPosAmount(bool $monthly = false): string
    {
        $amount = self::getPosProductsData($monthly);
        return Auth::user()->priceFormat($amount);
    }

    public static function getPosReportChart(): array
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
                ->sum(function($pos) {
                    return $pos->getTotal();
                });

            $data['label'][] = $dateLabel;
            $data['value'][] = $total;
        }

        return $data;
    }
}