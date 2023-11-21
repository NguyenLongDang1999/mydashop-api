<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public $table = 'product';

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'category_id',
        'brand_id',
        'image_uri',
        'price',
        'special_price',
        'special_price_type',
        'selling_price',
        'quantity',
        'status',
        'popular',
        'technical_specifications',
        'short_description',
        'description',
        'meta_title',
        'meta_description'
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_related', 'product_id', 'product_related_id');
    }

    public function upsellProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_upsell', 'product_id', 'product_upsell_id');
    }

    public function crossSellProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_cross_sell', 'product_id', 'product_cross_sell_id');
    }

    public function getListDatatable($input): array
    {
        $query = Product::select('id', 'sku', 'name', 'category_id', 'brand_id', 'price', 'special_price', 'special_price_type', 'selling_price', 'image_uri', 'status', 'popular')
            ->with([
                'brand:id,name,image_uri',
                'category:id,name,image_uri',
            ])
            ->when(isset($input['sku']), function (Builder $query) use ($input) {
                $query->where('sku', 'like', '%' . $input['sku'] . '%');
            })
            ->when(isset($input['name']), function (Builder $query) use ($input) {
                $query->where('name', 'like', '%' . $input['name'] . '%');
            })
            ->when(isset($input['category_id']), function (Builder $query) use ($input) {
                $query->where('category_id', $input['category_id']);
            })
            ->when(isset($input['brand_id']), function (Builder $query) use ($input) {
                $query->where('brand_id', $input['brand_id']);
            })
            ->when(isset($input['status']), function (Builder $query) use ($input) {
                $query->where('status', $input['status']);
            })
            ->when(isset($input['popular']), function (Builder $query) use ($input) {
                $query->where('popular', $input['popular']);
            });

        $data['aggregations'] = $query->count();

        $data['data'] = $query
            ->latest()
            ->skip(($input['page'] - 1) * $input['pageSize'])
            ->take($input['pageSize'])
            ->get();

        return $data;
    }

    public function getProductList(): array
    {
        $getProductList = Product::select('id', 'name', 'image_uri')->get();
        $optionList = [];

        foreach ($getProductList as $item) {
            $optionList[] = [
                'id' => $item->id,
                'name' => $item->name,
                'image_uri' => $item->image_uri
            ];
        }

        return $optionList;
    }

    public function getProductDetail(string $slug)
    {
        return Product::whereSlug($slug)
            ->with([
                'productAttributes.productAttributeValues.attributeValues:id,value',
                'productAttributes.attribute:id,name',
                'brand:id,name',
                'category:id,name,slug',
            ])
            ->where('status', config('constants.status.active'))
            ->firstOrFail([
                'id',
                'sku',
                'name',
                'slug',
                'image_uri',
                'in_stock',
                'category_id',
                'brand_id',
                'price',
                'selling_price',
                'special_price',
                'special_price_type',
                'total_rating',
                'description',
                'short_description',
                'technical_specifications',
                'meta_title',
                'meta_description'
            ]);
    }
}
