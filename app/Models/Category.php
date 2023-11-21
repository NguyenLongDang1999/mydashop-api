<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Category extends Model
{
    use HasFactory;
    use HasRecursiveRelationships;

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

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'category_brand', 'category_id', 'brand_id');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'category_attribute', 'category_id', 'attribute_id');
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

    public function getCategoryPopular(): array
    {
        return Category::with(['children' => function ($query) {
            $query->where('status', config('constants.status.active'))
                ->where('popular', config('constants.popular.active'))
                ->take(4);
            }])
            ->select('id', 'name', 'slug', 'image_uri')
            ->where('status', config('constants.status.active'))
            ->where('popular', config('constants.popular.active'))
            ->whereNull('parent_id')
            ->take(6)
            ->latest()
            ->get()
            ->toArray();
    }

    public function getCategoryNestedList(): array
    {
        $categories = Category::with('children')
            ->select('id', 'name', 'slug', 'image_uri')
            ->where('status', config('constants.status.active'))
            ->whereNull('parent_id')
            ->latest()
            ->get();

        $result = $categories->map(function ($category) {
            return $this->getNestedChildren($category);
        });

        return $result->toArray();
    }

    private function getNestedChildren($category)
    {
        $result = $category->toArray();

        if ($category->children) {
            $result['children'] = $category->children->map(function ($child) {
                return $this->getNestedChildren($child);
            })->toArray();
        }

        return $result;
    }

    public function getSubCategoryID($categoryId)
    {
        $category = Category::select('id')
            ->where('status', config('constants.status.active'))
            ->find($categoryId);

        if (!$category) {
            return [];
        }

        return $category->descendantsAndSelf()->pluck('id')->all();
    }

    public function getCategoryDetail(string $slug, $input)
    {
        $data = Category::with([
                'brands:id,name,slug',
                'attributes' => function ($query) {
                    $query->select('id', 'name');
                    $query->with('attributeValues');
                },
            ])
            ->whereSlug($slug)
            ->where('status', config('constants.status.active'))
            ->firstOrFail([
                'id',
                'name',
                'slug',
                'image_uri',
                'description',
                'meta_title',
                'meta_description'
            ]);

        $query = Product::with(['category:id,name,slug'])
            ->select([
                'id',
                'name',
                'slug',
                'image_uri',
                'category_id',
                'price',
                'in_stock',
                'special_price',
                'selling_price',
                'short_description',
                'special_price_type',
                'total_rating'
            ])
            ->whereIn('category_id', $this->getSubCategoryID($data['id']))
            ->when(isset($input['brand']), function (Builder $query) use ($input) {
                $query->where('brand_id', $input['brand']);
            })
            ->when(isset($input['attribute']), function (Builder $query) use ($input) {
                $query->whereHas('productAttributes', function (Builder $query) use ($input) {
                    $query->whereHas('productAttributeValues', function (Builder $query) use ($input) {
                        $query->where('attribute_value_id', $input['attribute']);
                    });
                });
            })
            ->when(isset($input['sort']), function (Builder $query) use ($input) {
                $sortConditions = [
                    1 => ['created_at', 'desc'],
                    2 => ['created_at', 'asc'],
                    3 => ['name', 'asc'],
                    4 => ['name', 'desc'],
                    5 => ['selling_price', 'asc'],
                    6 => ['selling_price', 'desc'],
                ];

                if (isset($sortConditions[$input['sort']])) {
                    list($column, $direction) = $sortConditions[$input['sort']];
                    $query->orderBy($column, $direction);
                }
            })
            ->where('status', config('constants.status.active'));

        $data['aggregations'] = $query->count();

        $data['Product'] = $query
            ->skip(($input['page'] - 1) * $input['pageSize'])
            ->take($input['pageSize'])
            ->get()
            ->toArray();

        return $data;
    }
}
