<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    use HasFactory;

    public $table = 'flash_sale';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'discount_percentage'
    ];

    public function getListDatatable($input): array
    {
        $query = FlashSale::select('id', 'name')
            ->when(isset($input['name']), function (Builder $query) use ($input) {
                $query->where('name', 'ilike', '%' . $input['name'] . '%');
            });

        $data['aggregations'] = $query->count();

        $data['data'] = $query
            ->latest()
            ->skip(($input['page'] - 1) * $input['pageSize'])
            ->take($input['pageSize'])
            ->get();

        return $data;
    }
}
