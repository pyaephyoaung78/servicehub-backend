@extends('admin.layouts.app')

@section('title', 'Loyalty Program')
@section('page_title', 'Loyalty Program')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <p class="text-sm font-medium text-teal-300">Customer retention</p>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Loyalty and referrals</h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-50/85">Manage rewards, review redemptions, and see the points customers are earning through completed work and referrals.</p>
</section>

@if (session('success'))
<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<section class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([['Pending redemptions', $metrics['pending_redemptions'], 'Needs a fulfilment decision'], ['Active rewards', $metrics['active_rewards'], 'Available in the customer app'], ['Points issued', number_format($metrics['points_issued']), 'All earned point credits'], ['Points redeemed', number_format($metrics['points_redeemed']), 'Points currently spent on rewards']] as [$label, $value, $description])
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
        <p class="text-sm text-slate-500">{{ $label }}</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $value }}</p>
        <p class="mt-2 text-xs leading-5 text-slate-500">{{ $description }}</p>
    </div>
    @endforeach
</section>

<div class="grid gap-6 xl:grid-cols-5">
    <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.06)] xl:col-span-2 sm:p-6">
        <h2 class="text-lg font-semibold text-teal-950">Create reward</h2>
        <p class="mt-1 text-sm text-teal-900/70">The team fulfils approved rewards manually, using the redemption code shown in the queue.</p>
        <form method="POST" action="{{ route('admin.loyalty.rewards.store') }}" class="mt-5 space-y-4">
            @csrf
            <label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Reward name</span><input name="name" value="{{ old('name') }}" required class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="e.g. 10% service voucher"></label>
            <label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Points required</span><input type="number" min="1" name="points_cost" value="{{ old('points_cost') }}" required class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="e.g. 500"></label>
            <label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Customer instructions</span><textarea name="description" rows="4" class="w-full rounded-lg border-teal-200 bg-white px-4 py-3 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="What the customer receives and how it is fulfilled">{{ old('description') }}</textarea></label>
            <input type="hidden" name="is_active" value="1">
            <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">Create reward</button>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)] xl:col-span-3">
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6"><h2 class="text-lg font-semibold text-slate-950">Reward catalogue</h2><p class="mt-1 text-sm text-slate-500">Deactivate rewards without affecting previous customer redemptions.</p></div>
        <div class="divide-y divide-slate-100">
            @forelse ($rewards as $reward)
            <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div><p class="font-semibold text-slate-900">{{ $reward->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $reward->points_cost }} points · {{ $reward->description ?: 'No customer instructions added.' }}</p></div>
                <form method="POST" action="{{ route('admin.loyalty.rewards.toggle', $reward) }}">@csrf @method('PATCH')<button class="rounded-lg px-3 py-2 text-xs font-semibold {{ $reward->is_active ? 'border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">{{ $reward->is_active ? 'Deactivate' : 'Activate' }}</button></form>
            </div>
            @empty
            <p class="px-6 py-12 text-center text-sm text-slate-500">Create your first reward to make points meaningful for customers.</p>
            @endforelse
        </div>
    </section>
</div>

<section class="mt-8">
    <form method="GET" class="mb-6 rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.06)] sm:p-6">
        <div class="grid gap-5 md:grid-cols-2"><label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Search</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Customer, reward or redemption code" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600"></label><label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Status</span><select name="status" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600"><option value="">All statuses</option>@foreach ($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label></div>
        <div class="mt-5 flex gap-3"><button class="rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white">Apply filters</button><a href="{{ route('admin.loyalty.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">Reset</a></div>
    </form>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
        <div class="border-b border-slate-200 px-5 py-4 text-sm font-medium text-slate-600 sm:px-6">{{ $redemptions->total() }} redemption{{ $redemptions->total() === 1 ? '' : 's' }} found</div>
        <div class="overflow-x-auto"><table class="w-full min-w-[980px] text-sm"><thead class="bg-teal-950 text-left text-xs font-semibold tracking-wide text-teal-50"><tr><th class="px-6 py-3.5">Customer</th><th class="px-6 py-3.5">Reward</th><th class="px-6 py-3.5">Code</th><th class="px-6 py-3.5">Status</th><th class="px-6 py-3.5 text-center">Decision</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse ($redemptions as $redemption)
            <tr class="align-top transition hover:bg-teal-50/60"><td class="px-6 py-4"><p class="font-semibold text-slate-900">{{ $redemption->customer?->name }}</p><p class="text-xs text-slate-500">{{ $redemption->customer?->email }}</p></td><td class="px-6 py-4"><p class="font-semibold text-slate-900">{{ $redemption->reward?->name }}</p><p class="text-xs text-slate-500">{{ $redemption->points_cost }} points</p></td><td class="px-6 py-4 font-mono text-xs text-slate-700">{{ $redemption->redemption_code }}</td><td class="px-6 py-4"><span class="rounded-full px-3 py-1 text-xs font-medium {{ $redemption->status === 'pending' ? 'bg-amber-50 text-amber-700' : ($redemption->status === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600') }}">{{ strtoupper($redemption->status) }}</span>@if($redemption->review_note)<p class="mt-2 max-w-xs text-xs text-slate-500">{{ $redemption->review_note }}</p>@endif</td><td class="px-6 py-4 text-center">
                @if($redemption->status === 'pending')<div class="flex justify-center gap-2"><form method="POST" action="{{ route('admin.loyalty.redemptions.review', $redemption) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Approve</button></form><form method="POST" action="{{ route('admin.loyalty.redemptions.review', $redemption) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700">Reject & refund</button></form></div>@else<span class="text-xs text-slate-500">Reviewed {{ $redemption->reviewed_at?->format('d M Y') }}</span>@endif
            </td></tr>
            @empty<tr><td colspan="5" class="px-6 py-12 text-center text-slate-500">No reward redemptions match the current filters.</td></tr>@endforelse
        </tbody></table></div>
        @if($redemptions->hasPages())<div class="border-t border-slate-200 px-6 py-4">{{ $redemptions->links() }}</div>@endif
    </div>
</section>
@endsection
