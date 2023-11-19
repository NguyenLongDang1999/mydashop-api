<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupons extends Model
{
    use HasFactory;

    public $table = 'coupons';

    protected $fillable = [
        'name',
        'code',
        'discount',
        'discount_type',
        'expire_date',
        'status'
    ];

    public function getListDatatable($input): array
    {
        $query = Coupons::select('id', 'name', 'code', 'discount', 'expire_date')
            ->when(isset($input['name']), function (Builder $query) use ($input) {
                $query->where('name', 'ilike', '%' . $input['name'] . '%');
            })
            ->when(isset($input['code']), function (Builder $query) use ($input) {
                $query->where('code', 'ilike', '%' . $input['code'] . '%');
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
