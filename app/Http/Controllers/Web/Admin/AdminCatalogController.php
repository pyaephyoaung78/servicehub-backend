<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:255'], 'service_category_id' => ['nullable', 'integer', 'exists:service_categories,id'], 'is_active' => ['nullable', 'boolean']]);
        $services = Service::query()->with('category')->withCount('bookings')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($serviceQuery) => $serviceQuery->where('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->when($filters['service_category_id'] ?? null, fn ($query, $category) => $query->where('service_category_id', $category))
            ->when(isset($filters['is_active']), fn ($query) => $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->latest()->paginate(20)->withQueryString();
        return view('admin.catalog.index', ['services' => $services, 'categories' => ServiceCategory::query()->withCount('services')->orderBy('name')->get()]);
    }

    public function createService(): View { return view('admin.catalog.service-form', ['service' => null, 'categories' => $this->categories(), 'isEditing' => false]); }
    public function editService(Service $service): View { return view('admin.catalog.service-form', ['service' => $service, 'categories' => $this->categories(), 'isEditing' => true]); }
    public function storeService(StoreServiceRequest $request): RedirectResponse { $data = $request->validated(); $service = Service::create([...$data, 'slug' => Str::slug($data['name']), 'is_active' => $data['is_active'] ?? true]); return redirect()->route('admin.catalog.index')->with('success', "Service {$service->name} created."); }
    public function updateService(UpdateServiceRequest $request, Service $service): RedirectResponse { $data = $request->validated(); $service->update([...$data, 'slug' => Str::slug($data['name']), 'is_active' => $data['is_active'] ?? false]); return redirect()->route('admin.catalog.index')->with('success', 'Service updated.'); }
    public function toggleService(Service $service): RedirectResponse { $service->update(['is_active' => ! $service->is_active]); return redirect()->route('admin.catalog.index')->with('success', 'Service availability updated.'); }
    public function storeCategory(StoreServiceCategoryRequest $request): RedirectResponse { $data = $request->validated(); ServiceCategory::create([...$data, 'slug' => Str::slug($data['name']), 'is_active' => $data['is_active'] ?? true]); return redirect()->route('admin.catalog.index')->with('success', 'Category created.'); }
    public function updateCategory(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory): RedirectResponse { $data = $request->validated(); $serviceCategory->update([...$data, 'slug' => Str::slug($data['name']), 'is_active' => $data['is_active'] ?? false]); return redirect()->route('admin.catalog.index')->with('success', 'Category updated.'); }
    private function categories() { return ServiceCategory::query()->where('is_active', true)->orderBy('name')->get(); }
}
