<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeValues extends Model
{
    use HasFactory;

    public $table = 'product_attribute_values';

    public $timestamps = false;

    protected $fillable = [
        'product_attribute_id',
        'attribute_value_id'
    ];

    public function attributeValues()
    {
        return $this->belongsTo(AttributeValues::class, 'attribute_value_id');
    }
}
