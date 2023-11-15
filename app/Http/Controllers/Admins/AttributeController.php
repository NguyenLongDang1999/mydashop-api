<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admins\AttributeRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Attribute;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    private Attribute $attribute;

    public function __construct(Attribute $attribute)
    {
        $this->attribute = $attribute;
    }

    public function index(Request $request): JsonResponse
    {
        $input = $request->input();

        try {
            return response()->json(
                $this->attribute->getListDatatable($input)
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dataListCategory(string $id): JsonResponse
    {
        try {
            $data = $this->attribute->whereHas('categories', function ($query) use ($id) {
                $query->where('category_id', $id);
            })->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dataListAttributeValues(string $id): JsonResponse
    {
        try {
            $attribute = Attribute::with('attributeValues')->find($id);

            return response()->json($attribute->attributeValues->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->value,
                ];
            }));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(AttributeRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            $data = $this->attribute->create($validatedData);
            $attributeValues = collect(json_decode($validatedData['attribute_value_id']))
                ->map(fn ($item) => ['value' => $item])
                ->toArray();

            $data->attributeValues()->createMany($attributeValues);
            $data->categories()->attach(json_decode($validatedData['category_id']));

            DB::commit();

            return response()->json(['message' => 'success'], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $data = $this->attribute->whereId($id)->firstOrFail([
                'id',
                'name',
                'slug',
                'description',
                'status',
            ]);

            $data['attribute_value_id'] = $data->attributeValues->map->only(['id', 'value'])->toArray();
            $data['category_id'] = $data->categories()->pluck('id')->toArray();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(AttributeRequest $request, string $id): JsonResponse
    {
        $data = $this->attribute->findOrFail($id);
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            $data->update($validatedData);
            $data->categories()->sync(json_decode($validatedData['category_id']));
            $data->updateOrCreateMany(json_decode($validatedData['attribute_value_id']) ?? []);

            DB::commit();

            return response()->json(['message' => 'success'], 204);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(string $id): JsonResponse
    {
        $data = $this->attribute->findOrFail($id);

        try {
            $data->delete();

            return response()->json(['message' => 'success'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
