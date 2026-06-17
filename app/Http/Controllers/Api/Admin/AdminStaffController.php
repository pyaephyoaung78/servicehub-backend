<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Http\Resources\StaffProfileResource;
use App\Models\StaffProfile;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStaffController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = StaffProfile::query()
            ->with([
                'user',
                'services.category',
            ]);

        if ($request->filled('service_id')) {
            $serviceId = $request->integer('service_id');

            $query->whereHas(
                'services',
                fn ($serviceQuery) =>
                    $serviceQuery->whereKey($serviceId)
            );
        }

        if ($request->has('is_active')) {
            $query->where(
                'is_active',
                $request->boolean('is_active')
            );
        }

        if ($request->has('is_available')) {
            $query->where(
                'is_available',
                $request->boolean('is_available')
            );
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($staffQuery) use ($search) {
                $staffQuery
                    ->where('phone', 'like', "%{$search}%")
                    ->orWhereHas(
                        'user',
                        fn ($userQuery) =>
                            $userQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )
                    );
            });
        }

        $staff = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return $this->successResponse(
            'Staff retrieved successfully.',
            StaffProfileResource::collection($staff)
                ->response()
                ->getData(true)
        );
    }

    public function store(
        StoreStaffRequest $request
    ): JsonResponse {
        $data = $request->validated();

        $staffProfile = DB::transaction(
            function () use ($data) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'role' => 'staff',
                ]);

                $profile = StaffProfile::create([
                    'user_id' => $user->id,
                    'phone' => $data['phone'],
                    'bio' => $data['bio'] ?? null,
                    'is_active' => $data['is_active'] ?? true,
                    'is_available' =>
                        $data['is_available'] ?? true,
                ]);

                $profile->services()->sync(
                    $data['service_ids']
                );

                return $profile;
            }
        );

        $staffProfile->load([
            'user',
            'services.category',
        ]);

        return $this->successResponse(
            'Staff account created successfully.',
            [
                'staff' => new StaffProfileResource(
                    $staffProfile
                ),
            ],
            201
        );
    }

    public function show(
        StaffProfile $staffProfile
    ): JsonResponse {
        $staffProfile->load([
            'user',
            'services.category',
        ]);

        return $this->successResponse(
            'Staff retrieved successfully.',
            [
                'staff' => new StaffProfileResource(
                    $staffProfile
                ),
            ]
        );
    }

    public function update(
        UpdateStaffRequest $request,
        StaffProfile $staffProfile
    ): JsonResponse {
        $data = $request->validated();

        DB::transaction(
            function () use ($data, $staffProfile) {
                $staffProfile->user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                ]);

                $staffProfile->update([
                    'phone' => $data['phone'],
                    'bio' => $data['bio'] ?? null,
                    'is_active' => $data['is_active'],
                    'is_available' =>
                        $data['is_active']
                            ? $data['is_available']
                            : false,
                ]);

                $staffProfile->services()->sync(
                    $data['service_ids']
                );
            }
        );

        $staffProfile->load([
            'user',
            'services.category',
        ]);

        return $this->successResponse(
            'Staff updated successfully.',
            [
                'staff' => new StaffProfileResource(
                    $staffProfile
                ),
            ]
        );
    }

    public function destroy(
        StaffProfile $staffProfile
    ): JsonResponse {
        $staffProfile->update([
            'is_active' => false,
            'is_available' => false,
        ]);

        return $this->successResponse(
            'Staff account deactivated successfully.'
        );
    }
}