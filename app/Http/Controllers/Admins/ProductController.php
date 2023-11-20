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

    public function dataList(): JsonResponse
    {
        try {
            return response()->json(
                $this->product->getProductList()
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

            $product = $this->product->create($validatedData);

            if ($validatedData['attributes']) {
                $attributes = json_decode($validatedData['attributes'], true);

                foreach ($attributes as $attributeItem) {
                    $product->productAttributes()->create([
                        'attribute_id' => $attributeItem['id'],
                    ])->productAttributeValues()->createMany(
                        array_map(fn ($valueId) => ['attribute_value_id' => $valueId], $attributeItem['attribute_value_id'])
                    );
                }
            }

            if (isset($validatedData['product_related']) && is_array($validatedData['product_related'])) {
                $product->relatedProducts()->attach(json_decode($validatedData['product_related']));
            }

            if (isset($validatedData['product_upsell']) && is_array($validatedData['product_upsell'])) {
                $product->upsellProducts()->attach(json_decode($validatedData['product_upsell']));
            }

            if (isset($validatedData['product_cross_sell']) && is_array($validatedData['product_cross_sell'])) {
                $product->crossSellProducts()->attach(json_decode($validatedData['product_cross_sell']));
            }

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
            $product = $this->product
                ->with([
                    'productAttributes.productAttributeValues',
                    'productAttributes.attribute:id,name',
                    'relatedProducts:id',
                    'upsellProducts:id',
                    'crossSellProducts:id'
                ])
                ->select([
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
                ])
                ->findOrFail($id);

            $data = $product->toArray();

            $attributes = [];

            foreach ($data['product_attributes'] as $item) {
                $attrId = $item['attribute']['id'];
                $attrValueName = $item['attribute']['name'];

                $attrValues = array_column($item['product_attribute_values'], 'attribute_value_id');

                if (!isset($attributes[$attrId])) {
                    $attributes[$attrId] = [
                        'values' => $attrValues,
                        'name' => $attrValueName,
                        'id' => $attrId,
                    ];
                } else {
                    $attributes[$attrId]['values'] = array_merge($attributes[$attrId]['values'], $attrValues);
                    $attributes[$attrId]['name'] = $attrValueName;
                }
            }

            $data['attributes'] = array_values($attributes);
            $data['technical_specifications'] = json_decode($data['technical_specifications'], true);

            $data['related_products'] = isset($data['related_products']) ? collect($data['related_products'])->pluck('id')->toArray() : [];
            $data['upsell_products'] = isset($data['upsell_products']) ? collect($data['upsell_products'])->pluck('id')->toArray() : [];
            $data['cross_sell_products'] = isset($data['cross_sell_products']) ? collect($data['cross_sell_products'])->pluck('id')->toArray() : [];

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

            if ($validatedData['attributes']) {
                $attributes = json_decode($validatedData['attributes'], true);

                $data->productAttributes()->delete();

                foreach ($attributes as $attributeItem) {
                    $productAttribute = $data->productAttributes()->create([
                        'attribute_id' => $attributeItem['id'],
                    ]);

                    if (isset($attributeItem['attribute_value_id']) && is_array($attributeItem['attribute_value_id'])) {
                        $attributeValues = array_map(fn ($valueId) => ['attribute_value_id' => $valueId], $attributeItem['attribute_value_id']);
                        $productAttribute->productAttributeValues()->createMany($attributeValues);
                    }
                }
            }

            if (isset($validatedData['product_related']) && !empty($validatedData['product_related'])) {
                $data->relatedProducts()->sync(json_decode($validatedData['product_related']));
            }

            if (isset($validatedData['product_upsell']) && !empty($validatedData['product_upsell'])) {
                $data->upsellProducts()->sync(json_decode($validatedData['product_upsell']));
            }

            if (isset($validatedData['product_cross_sell']) && !empty($validatedData['product_cross_sell'])) {
                $data->crossSellProducts()->sync(json_decode($validatedData['product_cross_sell']));
            }

            DB::commit();

            return response()->json(['message' => 'success'], 204);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function remove(string $id): JsonResponse
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
