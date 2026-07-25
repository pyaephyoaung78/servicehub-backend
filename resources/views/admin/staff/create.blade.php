@extends('admin.layouts.app')

@section('title', 'Add Staff')
@section('page_title', 'Add Staff')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <a href="{{ route('admin.staff.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-700 bg-teal-900/70 px-2.5 py-1.5 text-xs font-semibold text-teal-50 transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-950 active:translate-y-px">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
        Back to staff
    </a>
    <p class="mt-5 text-sm font-medium text-teal-300">Workforce operations</p>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Create staff account</h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-50/85">Create a staff login and assign the services they can perform.</p>
</section>

@include('admin.staff.form', [
'formAction' => route('admin.staff.store'),
'formMethod' => 'POST',
'isEditing' => false,
])
@endsection