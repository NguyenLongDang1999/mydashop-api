<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admins\ProductRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    private string $path;
    private Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
        $this->path = 'product';
    }

    public function index(Request $request): JsonResponse
    {
        $input = $request->input();

        try {
            return response()->json(
                $this->product->getListDatatable($input)
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $validatedData['image_uri'] = storageUploadFile($this->path, $validatedData['slug'], $request);

            DB::beginTransaction();

            $this->product->create($validatedData);

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
            $data = $this->product->whereId($id)->firstOrFail([
                'id',
                'sku',
                'name',
                'slug',
                'category_id',
                'brand_id',
                'price',
                'quantity',
                'special_price',
                'special_price_type',
                'image_uri',
                'short_description',
                'description',
                'technical_specifications',
                'status',
                'popular',
                'meta_title',
                'meta_description'
            ]);

            $data['technical_specifications'] = json_decode($data['technical_specifications'], true);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(ProductRequest $request, string $id): JsonResponse
    {
        $data = $this->product->findOrFail($id);
        $validatedData = $request->validated();

        try {
            $validatedData['image_uri'] = storageUploadFile($this->path, $validatedData['slug'], $request);

            DB::beginTransaction();

            $data->update($validatedData);

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
        $data = $this->product->findOrFail($id);

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
