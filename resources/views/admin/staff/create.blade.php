@extends('admin.layouts.app')

@section('title', 'Add Staff')
@section('page_title', 'Add Staff')

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.staff.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
            ← Back to staff
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Create Staff Account</h1>
        <p class="mt-1 text-slate-500">Create a staff login and assign the services they can perform.</p>
    </div>

    @include('admin.staff.form', [
        'formAction' => route('admin.staff.store'),
        'formMethod' => 'POST',
        'isEditing' => false,
    ])
@endsection
