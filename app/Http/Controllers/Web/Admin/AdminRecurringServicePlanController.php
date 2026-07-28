<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecurringServicePlan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRecurringServicePlanController extends Controller
{
    public function index(Request $request): View
    {
        $plans = RecurringServicePlan::query()->with('customer')
            ->when($request->filled('active'), fn ($query) => $query->where('is_active', $request->boolean('active')))
            ->when($request->filled('search'), fn ($query) => $query->where(function ($planQuery) use ($request) { $search = $request->string('search')->toString(); $planQuery->where('service_name', 'like', "%{$search}%")->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")); }))
            ->orderBy('next_reminder_at')->paginate(20)->withQueryString();

        return view('admin.service-plans.index', ['plans' => $plans, 'metrics' => ['active' => RecurringServicePlan::query()->where('is_active', true)->count(), 'due' => RecurringServicePlan::query()->where('is_active', true)->where('next_reminder_at', '<=', now())->count()]]);
    }
}
