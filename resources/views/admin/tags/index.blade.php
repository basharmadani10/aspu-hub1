@extends('layouts.admin')

@section('title', 'Tags Management')

@section('content')
<div class="flex items-center justify-between mb-8">
    <h2 class="text-3xl font-bold text-gray-800">Tags Management</h2>
    <a href="{{ route('admin.tags.create') }}" class="px-5 py-2 font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <span>+</span> Add New Tag
    </a>
</div>

@if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
        {{ session('success') }}
    </div>
@endif

<div class="overflow-hidden bg-white rounded-lg shadow-md">
    <table class="w-full text-left">
        <thead>
            <tr class="text-sm font-semibold text-gray-500 uppercase bg-gray-100">
                <th class="p-4">Tag Name</th>
                <th class="p-4 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            @forelse($tags as $tag)
                <tr class="border-b border-gray-200 hover:bg-indigo-50">
                    <td class="p-4 font-medium">{{ $tag->name }}</td>
                    <td class="p-4 text-center space-x-2">
                        <a href="{{ route('admin.tags.edit', $tag->id) }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">Edit</a>
                        <form action="{{ route('admin.tags.destroy', $tag->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this tag?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="p-4 text-center text-gray-500">No tags found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination Links --}}
<div class="mt-6">
    {{ $tags->links() }}
</div>
@endsection
