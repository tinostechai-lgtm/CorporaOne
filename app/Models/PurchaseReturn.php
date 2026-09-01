<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_return_number',
        'supplier',
        'return_date',
        'description',
        'items',
        'total_amount',
        'status',
        'created_by',
        'vender_id',
        'warehouse_id',
        'ref_no',
        'notes'
    ];

    protected $casts = [
        'items' => 'array',
        'return_date' => 'date',
    ];

    public function scopeAccount($query)
    {
        return $query->where('created_by', Auth::user()->creatorId());
    }

    public function products()
    {
        return $this->hasMany(PurchaseReturnProduct::class, 'purchase_return_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(ProductService::class, 'product_id', 'id');
    }

    public function vender()
    {
        return $this->belongsTo(Vender::class, 'supplier', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
        );
    }
}

