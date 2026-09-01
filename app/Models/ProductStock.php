<?php
// app/Models/ProductStock.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'type', // 'purchase', 'sale', 'return', 'adjustment', 'manually'
        'reference_id',
        'description',
        'created_by'
    ];

    protected $casts = [
        'quantity' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(ProductService::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get stock movements for a product
     */
    public static function getProductMovements($productId, $warehouseId = null)
    {
        $query = self::where('product_id', $productId)
            ->where('created_by', Auth::user()->creatorId())
            ->orderBy('created_at', 'desc');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get();
    }

    /**
     * Get current stock for a product
     */
    public static function getCurrentStock($productId, $warehouseId = null)
    {
        $query = self::where('product_id', $productId)
            ->where('created_by', Auth::user()->creatorId());

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->sum('quantity');
    }
}