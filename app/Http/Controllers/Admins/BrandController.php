<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admins\BrandRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
{
    private string $path;
    private Brand $brand;

    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
        $this->path = 'brand';
    }

    public function index(Request $request): JsonResponse
    {
        $input = $request->input();

        try {
            return response()->json(
                $this->brand->getListDatatable($input)
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
                $this->brand->getBrandList()
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(BrandRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $validatedData['image_uri'] = storageUploadFile($this->path, $validatedData['name'], $request);

            DB::beginTransaction();

            $data = $this->brand->create($validatedData);
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
            $data = $this->brand->whereId($id)->firstOrFail([
                'id',
                'name',
                'slug',
                'image_uri',
                'description',
                'status',
                'popular',
                'meta_title',
                'meta_description'
            ]);

            $data['category_id'] = $data->categories()->pluck('id')->toArray();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(BrandRequest $request, string $id): JsonResponse
    {
        $data = $this->brand->findOrFail($id);
        $validatedData = $request->validated();

        try {
            $validatedData['image_uri'] = storageUploadFile($this->path, $validatedData['name'], $request);

            DB::beginTransaction();

            $data->update($validatedData);
            $data->categories()->sync(json_decode($validatedData['category_id']));

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
        $data = $this->brand->findOrFail($id);

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
