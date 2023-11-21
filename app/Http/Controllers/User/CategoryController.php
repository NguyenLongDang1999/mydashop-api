<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function dataList(): JsonResponse
    {
        try {
            return response()->json($this->category->getCategoryPopular());
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dataListNested(): JsonResponse
    {
        try {
            return response()->json($this->category->getCategoryNestedList());
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function dataDetail(string $slug, Request $request): JsonResponse
    {
        try {
            $input = $request->input();

            return response()->json($this->category->getCategoryDetail($slug, $input));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
