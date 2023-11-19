<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    use HasFactory;

    public $table = 'product_attribute';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'attribute_id',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function productAttributeValues()
    {
        return $this->hasMany(ProductAttributeValues::class);
    }
}
