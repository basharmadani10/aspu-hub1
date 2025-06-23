{{--
    File: resources/views/admin/tags/edit.blade.php
    Description: This new file provides the form for editing an existing tag.
    It's pre-filled with the current tag data and submits to the update route.
--}}
@extends('layouts.admin')

@section('title', 'Edit Tag')

@section('content')
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Edit Tag: {{ $tag->name }}</h2>
        <a href="{{ route('admin.tags.index') }}" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200">
            &larr; Back to Tags List
        </a>
    </div>

    <div class="p-8 bg-white rounded-lg shadow-md">
        <form action="{{ route('admin.tags.update', $tag->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6">
                <!-- Tag Name -->
                <div>
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-700">Tag Name</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $tag->name) }}"
                        required
                        class="w-full px-4 py-2 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6">
                <button type="submit" class="w-full px-4 py-3 font-semibold text-white bg-indigo-600 rounded-lg md:w-auto hover:bg-indigo-700">Update Tag</button>
            </div>
        </form>
    </div>
</main>
@endsection
