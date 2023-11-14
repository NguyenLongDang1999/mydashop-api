<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admins\CategoryRequest;
use Illuminate\Http\JsonResponse;
use App\Models\Category;

class CategoryController extends Controller
{
    private string $path;
    private Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
        $this->path = 'category';
    }

    public function index(Request $request): JsonResponse
    {
        $input = $request->input();

        try {
            return response()->json(
                $this->category->getListDatatable($input)
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
                $this->category->getCategoryList()
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $validatedData['image_uri'] = storageUploadFile($this->path, $validatedData['slug'], $request);
            $this->category->create($validatedData);

            return response()->json(['message' => 'success'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            return response()->json(
                $this->category->whereId($id)->firstOrFail([
                    'id',
                    'name',
                    'slug',
                    'image_uri',
                    'parent_id',
                    'description',
                    'status',
                    'popular',
                    'meta_title',
                    'meta_description'
                ])
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(CategoryRequest $request, string $id): JsonResponse
    {
        $data = $this->category->findOrFail($id);
        $validatedData = $request->validated();

        try {
            $validatedData['image_uri'] = storageUploadFile($this->path, $validatedData['slug'], $request);
            $data->update($validatedData);

            return response()->json(['message' => 'success'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function remove(string $id): JsonResponse
    {
        $data = $this->category->findOrFail($id);

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
