<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    public $table = 'category';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'image_uri',
        'status',
        'popular',
        'description',
        'meta_title',
        'meta_description'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }

    public function getListDatatable($input): array
    {
        $query = Category::select('id', 'name', 'parent_id', 'image_uri', 'status', 'popular')
            ->with('parent:id,name,image_uri')
            ->when(isset($input['name']), function (Builder $query) use ($input) {
                $query->where('name', 'ilike', '%' . $input['name'] . '%');
            })
            ->when(isset($input['parent_id']), function (Builder $query) use ($input) {
                $query->where('parent_id', $input['parent_id']);
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

    public function renderTree($categories, $level = 0): array
    {
        $treeResult = [];

        foreach ($categories as $category) {
            $treeResult[] = [
                'id' => $category['id'],
                'name' => str_repeat('|--- ', $level) . $category['name'],
            ];

            if (!empty($category['children'])) {
                $treeResult = array_merge($treeResult, $this->renderTree($category['children'], $level + 1));
            }
        }

        return $treeResult;
    }

    public function getCategoryList(): array
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return $this->renderTree($categories, 0);
    }
}
