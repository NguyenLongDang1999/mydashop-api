<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;

    public $table = 'attribute';

    protected $fillable = [
        'name',
        'slug',
        'status',
        'description',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_attribute', 'attribute_id', 'category_id');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValues::class);
    }

    public function getListDatatable($input): array
    {
        $query = Attribute::select('id', 'name', 'status')
            ->with('categories:id,name,image_uri')
            ->when(isset($input['name']), function (Builder $query) use ($input) {
                $query->where('name', 'ilike', '%' . $input['name'] . '%');
            })
            ->when(isset($input['category_id']), function (Builder $query) use ($input) {
                $query->whereHas('categories', function ($query) use ($input) {
                    $query->where('category_id', $input['category_id']);
                });
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

    public function updateOrCreateMany(array $items): void
    {
        $updatedIds = [];

        foreach ($items as $item) {
            $attributeValue = $this->attributeValues()->updateOrCreate(['value' => $item]);
            $updatedIds[] = $attributeValue->id;
        }

        $this->attributeValues()->whereNotIn('id', $updatedIds)->delete();
    }
}
