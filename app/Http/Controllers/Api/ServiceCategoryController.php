<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Http\Resources\ServiceCategoryResource;
use App\Models\ServiceCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = ServiceCategory::query();

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $categories = $query
            ->latest()
            ->paginate(20);

        return $this->successResponse(
            'Service categories retrieved successfully.',
            ServiceCategoryResource::collection($categories)->response()->getData(true)
        );
    }

    public function store(StoreServiceCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $category = ServiceCategory::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $this->successResponse(
            'Service category created successfully.',
            [
                'category' => new ServiceCategoryResource($category),
            ],
            201
        );
    }

    public function show(ServiceCategory $serviceCategory): JsonResponse
    {
        return $this->successResponse(
            'Service category retrieved successfully.',
            [
                'category' => new ServiceCategoryResource($serviceCategory),
            ]
        );
    }

    public function update(
        UpdateServiceCategoryRequest $request,
        ServiceCategory $serviceCategory
    ): JsonResponse {
        $data = $request->validated();

        $serviceCategory->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? $serviceCategory->is_active,
        ]);

        return $this->successResponse(
            'Service category updated successfully.',
            [
                'category' => new ServiceCategoryResource($serviceCategory->fresh()),
            ]
        );
    }

    public function destroy(ServiceCategory $serviceCategory): JsonResponse
    {
        $serviceCategory->update([
            'is_active' => false,
        ]);

        return $this->successResponse(
            'Service category deactivated successfully.'
        );
    }
}