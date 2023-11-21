<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    private Slider $slider;

    public function __construct(Slider $slider)
    {
        $this->slider = $slider;
    }

    public function dataList(): JsonResponse
    {
        try {
            return response()->json($this->slider->getSliderList());
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
