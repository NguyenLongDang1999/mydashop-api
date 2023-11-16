<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    public $table = 'slider';

    protected $fillable = [
        'name',
        'slug',
        'image_uri',
        'description',
        'status'
    ];

    public function getListDatatable($input): array
    {
        $query = Slider::select('id', 'name', 'slug', 'image_uri', 'status', 'description')
            ->when(isset($input['name']), function (Builder $query) use ($input) {
                $query->where('name', 'ilike', '%' . $input['name'] . '%');
            })
            ->when(isset($input['status']), function (Builder $query) use ($input) {
                $query->where('status', $input['status']);
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
