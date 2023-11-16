<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admins\SliderRequest;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    private string $path;
    private Slider $slider;

    public function __construct(Slider $slider)
    {
        $this->slider = $slider;
        $this->path = 'slider';
    }

    public function index(Request $request): JsonResponse
    {
        $input = $request->input();

        try {
            return response()->json(
                $this->slider->getListDatatable($input)
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(SliderRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $validatedData['image_uri'] = storageUploadFile($this->path, $validatedData['slug'], $request);

            $this->slider->create($validatedData);

            return response()->json(['message' => 'success'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(SliderRequest $request, string $id): JsonResponse
    {
        $data = $this->slider->findOrFail($id);
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
        $data = $this->slider->findOrFail($id);

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
