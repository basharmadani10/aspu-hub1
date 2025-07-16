@extends('layouts.admin')

@section('title', 'Manage Specializations')

@section('content')
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Manage Specializations</h2>
        <a href="{{ route('admin.specializations.create') }}" class="px-5 py-2 font-semibold text-white bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <span>+</span> Add New Specialization
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md">
        <div class="p-4">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs font-semibold text-gray-500 uppercase border-b">
                        <th class="p-3">ID</th>
                        <th class="p-3">Name</th>
                        <th class="p-3">Description</th>
                        <th class="p-3">For University</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($specializations as $specialization) {{-- هنا يتم استخدام المتغير $specializations --}}
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800">{{ $specialization->SpecializationID }}</td>
                            <td class="p-3 text-gray-600">{{ $specialization->name }}</td>
                            <td class="p-3 text-gray-600">{{ Str::limit($specialization->description, 50) }}</td>
                            <td class="p-3 text-gray-600">
                                @if($specialization->is_for_university)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>
                                @endif
                            </td>
                            <td class="p-3 text-sm text-center space-x-2">
                                <a href="{{ route('admin.specializations.show', $specialization->SpecializationID) }}" class="px-3 py-1 font-medium text-indigo-600 bg-indigo-100 rounded-md hover:bg-indigo-200">View</a>
                                <a href="{{ route('admin.specializations.edit', $specialization->SpecializationID) }}" class="px-3 py-1 font-medium text-blue-600 bg-blue-100 rounded-md hover:bg-blue-200">Edit</a>
                                <form action="{{ route('admin.specializations.destroy', $specialization->SpecializationID) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this specialization? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 font-medium text-red-600 bg-red-100 rounded-md hover:bg-red-200">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">No specializations found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
