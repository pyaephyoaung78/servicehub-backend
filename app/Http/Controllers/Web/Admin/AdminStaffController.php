<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminStaffController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $staff = StaffProfile::query()
            ->with(['user', 'services.category'])
            ->when(
                $filters['is_active'] ?? null,
                fn ($query, $isActive) => $query->where('is_active', $isActive)
            )
            ->when(
                array_key_exists('is_active', $filters) && $filters['is_active'] === '0',
                fn ($query) => $query->where('is_active', false)
            )
            ->when(
                $filters['is_available'] ?? null,
                fn ($query, $isAvailable) => $query->where('is_available', $isAvailable)
            )
            ->when(
                array_key_exists('is_available', $filters) && $filters['is_available'] === '0',
                fn ($query) => $query->where('is_available', false)
            )
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($staffQuery) use ($search) {
                        $staffQuery
                            ->where('phone', 'like', "%{$search}%")
                            ->orWhereHas(
                                'user',
                                fn ($userQuery) => $userQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                            );
                    });
                }
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.staff.index', [
            'staff' => $staff,
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.create', [
            'services' => $this->availableServices(),
            'staffProfile' => null,
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $staffProfile = DB::transaction(function () use ($data) {
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
                'is_available' => $data['is_available'] ?? true,
            ]);

            $profile->services()->sync($data['service_ids']);

            return $profile;
        });

        return redirect()
            ->route('admin.staff.show', $staffProfile)
            ->with('success', 'Staff account created successfully.');
    }

    public function show(StaffProfile $staffProfile): View
    {
        $staffProfile->load([
            'user',
            'services.category',
            'bookingAssignments.booking',
        ]);

        return view('admin.staff.show', [
            'staffProfile' => $staffProfile,
        ]);
    }

    public function edit(StaffProfile $staffProfile): View
    {
        $staffProfile->load(['user', 'services']);

        return view('admin.staff.edit', [
            'services' => $this->availableServices(),
            'staffProfile' => $staffProfile,
        ]);
    }

    public function update(
        UpdateStaffRequest $request,
        StaffProfile $staffProfile
    ): RedirectResponse {
        $data = $request->validated();

        DB::transaction(function () use ($data, $staffProfile) {
            $staffProfile->user->update([
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

            $staffProfile->update([
                'phone' => $data['phone'],
                'bio' => $data['bio'] ?? null,
                'is_active' => $data['is_active'],
                'is_available' => $data['is_active']
                    ? $data['is_available']
                    : false,
            ]);

            $staffProfile->services()->sync($data['service_ids']);
        });

        return redirect()
            ->route('admin.staff.show', $staffProfile)
            ->with('success', 'Staff account updated successfully.');
    }

    public function destroy(StaffProfile $staffProfile): RedirectResponse
    {
        $staffProfile->update([
            'is_active' => false,
            'is_available' => false,
        ]);

        return redirect()
            ->route('admin.staff.show', $staffProfile)
            ->with('success', 'Staff account deactivated successfully.');
    }

    private function availableServices()
    {
        return Service::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
