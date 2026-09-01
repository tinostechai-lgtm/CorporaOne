<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'city_zip',
        'created_by',
    ];

    /**
     * Get warehouse ID by name
     */
    public static function warehouse_id($name)
    {
        $warehouse = self::where('name', $name)
            ->where('created_by', auth()->user()->creatorId())
            ->first();
        
        return $warehouse ? $warehouse->id : 0;
    }

    public function products()
    {
        return $this->hasMany('App\Models\WarehouseProduct', 'warehouse_id');
    }

    public function pos()
    {
        return $this->hasMany('App\Models\Pos', 'warehouse_id');
    }
}
