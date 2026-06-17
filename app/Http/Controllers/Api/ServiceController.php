<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Service::query()
            ->with('category');

        if ($request->filled('service_category_id')) {
            $query->where('service_category_id', $request->integer('service_category_id'));
        }

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $services = $query
            ->latest()
            ->paginate(20);

        return $this->successResponse(
            'Services retrieved successfully.',
            ServiceResource::collection($services)->response()->getData(true)
        );
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $service = Service::create([
            'service_category_id' => $data['service_category_id'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'base_price' => $data['base_price'],
            'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $service->load('category');

        return $this->successResponse(
            'Service created successfully.',
            [
                'service' => new ServiceResource($service),
            ],
            201
        );
    }

    public function show(Service $service): JsonResponse
    {
        $service->load('category');

        return $this->successResponse(
            'Service retrieved successfully.',
            [
                'service' => new ServiceResource($service),
            ]
        );
    }

    public function update(
        UpdateServiceRequest $request,
        Service $service
    ): JsonResponse {
        $data = $request->validated();

        $service->update([
            'service_category_id' => $data['service_category_id'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'base_price' => $data['base_price'],
            'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
            'is_active' => $data['is_active'] ?? $service->is_active,
        ]);

        $service->load('category');

        return $this->successResponse(
            'Service updated successfully.',
            [
                'service' => new ServiceResource($service->fresh('category')),
            ]
        );
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->update([
            'is_active' => false,
        ]);

        return $this->successResponse(
            'Service deactivated successfully.'
        );
    }
}