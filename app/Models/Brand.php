<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Brand extends Model
{
    use HasFactory;

    public $table = 'brand';

    protected $fillable = [
        'name',
        'slug',
        'image_uri',
        'status',
        'popular',
        'description',
        'meta_title',
        'meta_description'
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_brand', 'brand_id', 'category_id');
    }

    public function getListDatatable($input): array
    {
        $query = Brand::select('id', 'name', 'image_uri', 'status', 'popular')
        ->with('categories:id,name,image_uri')
        ->when(isset($input['name']), function (Builder $query) use ($input) {
            $query->where('name', 'like', '%' . $input['name'] . '%');
        })
        ->when(isset($input['category_id']), function (Builder $query) use ($input) {
            $query->whereHas('categories', function ($query) use ($input) {
                $query->where('category_id', $input['category_id']);
            });
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

    public function getBrandList(): array
    {
        $getBrandList = Brand::select('id', 'name')->get();
        $optionList = [];

        foreach ($getBrandList as $item) {
            $optionList[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }

        return $optionList;
    }

    public function getBrandListAll()
    {
        return Brand::select('id', 'name', 'image_uri')
            ->where('status', config('constants.status.active'))
            ->get()
            ->toArray();
    }
}
