<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admins\CouponsRequest;
use App\Models\Coupons;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    private Coupons $coupons;

    public function __construct(Coupons $coupons)
    {
        $this->coupons = $coupons;
    }

    public function index(Request $request): JsonResponse
    {
        $input = $request->input();

        try {
            return response()->json(
                $this->coupons->getListDatatable($input)
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(CouponsRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $this->coupons->create($validatedData);

            return response()->json(['message' => 'success'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(CouponsRequest $request, string $id): JsonResponse
    {
        $data = $this->coupons->findOrFail($id);
        $validatedData = $request->validated();

        try {
            $data->update($validatedData);

            return response()->json(['message' => 'success'], 204);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(string $id): JsonResponse
    {
        $data = $this->coupons->findOrFail($id);

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
