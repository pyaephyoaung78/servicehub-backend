@extends('admin.layouts.app')

@section('title', 'Edit Staff')
@section('page_title', 'Edit Staff')

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.staff.show', $staffProfile) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
            ← Back to staff profile
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Edit Staff Profile</h1>
        <p class="mt-1 text-slate-500">Update contact information, service skills, or availability.</p>
    </div>

    @include('admin.staff.form', [
        'formAction' => route('admin.staff.update', $staffProfile),
        'formMethod' => 'PUT',
        'isEditing' => true,
    ])
@endsection
