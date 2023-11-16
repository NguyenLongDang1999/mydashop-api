<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admins\AttributeRequest;
use App\Http\Requests\Admins\FlashSaleRequest;
use App\Models\FlashSale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlashSaleController extends Controller
{
    private FlashSale $flashSale;

    public function __construct(FlashSale $flashSale)
    {
        $this->flashSale = $flashSale;
    }

    public function index(Request $request): JsonResponse
    {
        $input = $request->input();

        try {
            return response()->json(
                $this->flashSale->getListDatatable($input)
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(FlashSaleRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            $data = $this->flashSale->create($validatedData);
            $data->flashSales()->attach(json_decode($validatedData['product_id']));

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
        $data = $this->flashSale->findOrFail($id);

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
