<?php

namespace App\Models;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProductService extends Model
{
    protected $table = 'product_services';

    protected $fillable = [
        'name',
        'sku',
        'sale_price',
        'purchase_price',
        'tax_id',
        'category_id',
        'unit_id',
        'type',
        'sale_chartaccount_id',
        'expense_chartaccount_id',
        'pro_image',
        'pro_image_thumb',
        'quantity',
        'description',
        'created_by',
    ];

    protected $casts = [
        'pro_image' => 'string',
        'pro_image_thumb' => 'string',
        'sale_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
    ];

    protected $appends = [
        'image_url', 
        'thumb_url',
        'current_stock', 
        'formatted_sale_price', 
        'formatted_purchase_price',
        'stock_status',
        'stock_value',
        'has_image',
    ];

    /**
     * Get the category that owns the product
     */
    public function category()
    {
        return $this->belongsTo(ProductServiceCategory::class, 'category_id');
    }

    /**
     * Get the unit that owns the product
     */
    public function unit()
    {
        return $this->belongsTo(ProductServiceUnit::class, 'unit_id');
    }

    /**
     * Get the taxes for the product
     */
    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'product_service_taxes', 'product_service_id', 'tax_id');
    }

    /**
     * Get the warehouse products
     */
    public function warehouseProducts()
    {
        return $this->hasMany(WarehouseProduct::class, 'product_id');
    }

    /**
     * Get the stock reports
     */
    public function stockReports()
    {
        return $this->hasMany(StockReport::class, 'product_id');
    }

    /**
     * Get the product stocks
     */
    public function productStocks()
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }

    /**
     * Get the image URL with cache busting
     */
    public function getImageUrl()
    {
        if (empty($this->pro_image)) {
            return $this->getDefaultPlaceholder();
        }

        // Check if file exists in public path
        $publicPath = public_path('uploads/pro_image/' . $this->pro_image);
        if (file_exists($publicPath)) {
            return asset('uploads/pro_image/' . $this->pro_image) . '?v=' . filemtime($publicPath);
        }

        // Check if file exists in storage
        $storagePath = storage_path('app/public/uploads/pro_image/' . $this->pro_image);
        if (file_exists($storagePath)) {
            return Storage::url('uploads/pro_image/' . $this->pro_image) . '?v=' . filemtime($storagePath);
        }

        return $this->getDefaultPlaceholder();
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbUrl()
    {
        if (!empty($this->pro_image_thumb)) {
            $thumbPath = public_path('uploads/pro_image/' . $this->pro_image_thumb);
            if (file_exists($thumbPath)) {
                return asset('uploads/pro_image/' . $this->pro_image_thumb) . '?v=' . filemtime($thumbPath);
            }
        }
        
        // Fallback to main image or placeholder
        return $this->getImageUrl();
    }

    /**
     * Get default placeholder image (SVG data URI)
     */
    public function getDefaultPlaceholder()
    {
        return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50' viewBox='0 0 50 50'%3E%3Ccircle cx='25' cy='25' r='25' fill='%23f8f9fa'/%3E%3Ctext x='50%25' y='55%25' dy='.3em' text-anchor='middle' font-family='Arial' font-size='18px' fill='%236c757d'%3E🛒%3C/text%3E%3C/svg%3E";
    }

    /**
     * Check if image exists
     */
    public function hasImage()
    {
        if (empty($this->pro_image)) {
            return false;
        }

        $paths = [
            public_path('uploads/pro_image/' . $this->pro_image),
            storage_path('app/public/uploads/pro_image/' . $this->pro_image),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete image files
     */
    public function deleteImage($thumbOnly = false)
    {
        $fields = $thumbOnly ? ['pro_image_thumb'] : ['pro_image', 'pro_image_thumb'];
        
        foreach ($fields as $field) {
            if (!empty($this->{$field})) {
                $paths = [
                    public_path('uploads/pro_image/' . $this->{$field}),
                    storage_path('app/public/uploads/pro_image/' . $this->{$field}),
                ];
                
                foreach ($paths as $path) {
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }
        }
    }

    /**
     * Get current stock from warehouse
     */
    public function getCurrentStockAttribute()
    {
        return $this->warehouseProducts()
            ->where('created_by', Auth::user()->creatorId())
            ->sum('quantity');
    }

    /**
     * Get formatted sale price
     */
    public function getFormattedSalePriceAttribute()
    {
        return Auth::user()->priceFormat($this->sale_price);
    }

    /**
     * Get formatted purchase price
     */
    public function getFormattedPurchasePriceAttribute()
    {
        return Auth::user()->priceFormat($this->purchase_price);
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute()
    {
        $stock = $this->current_stock;
        if ($stock <= 0) {
            return 'Out of Stock';
        } elseif ($stock < 10) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    /**
     * Get stock value
     */
    public function getStockValueAttribute()
    {
        return $this->current_stock * $this->purchase_price;
    }

    /**
     * Get has image attribute
     */
    public function getHasImageAttribute()
    {
        return $this->hasImage();
    }

    /**
     * Get image url attribute
     */
    public function getImageUrlAttribute()
    {
        return $this->getImageUrl();
    }

    /**
     * Get thumb url attribute
     */
    public function getThumbUrlAttribute()
    {
        return $this->getThumbUrl();
    }

    /**
     * Get warehouse product quantity
     */
    public function warehouseProduct($product_id, $warehouse_id = null)
    {
        if ($warehouse_id) {
            $quantity = WarehouseProduct::where('product_id', $product_id)
                ->where('warehouse_id', $warehouse_id)
                ->where('created_by', Auth::user()->creatorId())
                ->value('quantity');
            return $quantity ?? 0;
        }
        
        return $this->warehouseProducts()
            ->where('created_by', Auth::user()->creatorId())
            ->sum('quantity');
    }

    /**
     * Get total product quantity from all warehouses
     */
    public function getTotalProductQuantity()
    {
        return $this->warehouseProducts()
            ->where('created_by', Auth::user()->creatorId())
            ->sum('quantity');
    }

    /**
     * Scope for getting all products
     */
    public function scopeGetallproducts($query)
    {
        return $query->where('created_by', Auth::user()->creatorId());
    }

    /**
     * Scope for products with low stock
     */
    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->whereHas('warehouseProducts', function ($q) use ($threshold) {
            $q->select(DB::raw('sum(quantity) as total'))
                ->having('total', '<', $threshold)
                ->where('created_by', Auth::user()->creatorId());
        });
    }

    /**
     * Scope for out of stock products
     */
    public function scopeOutOfStock($query)
    {
        return $query->whereDoesntHave('warehouseProducts', function ($q) {
            $q->where('quantity', '>', 0)
                ->where('created_by', Auth::user()->creatorId());
        });
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::deleting(function ($product) {
            // Delete images
            $product->deleteImage();
            
            // Delete related records
            $product->warehouseProducts()->delete();
            $product->stockReports()->delete();
            $product->productStocks()->delete();
        });

        static::created(function ($product) {
            if ($product->quantity > 0) {
                ProductStock::create([
                    'product_id' => $product->id,
                    'quantity' => $product->quantity,
                    'type' => 'initial',
                    'description' => 'Initial stock',
                    'created_by' => $product->created_by
                ]);
            }
        });
    }
}