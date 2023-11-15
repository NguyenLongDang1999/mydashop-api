<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValues extends Model
{
    use HasFactory;

    public $table = 'attribute_values';

    public $timestamps = false;

    protected $fillable = [
        'value',
        'attribute_id'
    ];
}
