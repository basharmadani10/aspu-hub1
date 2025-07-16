@extends('layouts.admin')

@section('title', 'Specialization Details: ' . $specialization->name)

@section('content')
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Specialization Details: <span class="text-indigo-700">{{ $specialization->name }}</span></h2>
        <a href="{{ route('admin.specializations.index') }}" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition duration-150 ease-in-out">
            &larr; Back to Specializations List
        </a>
    </div>

    <div class="bg-white shadow-xl rounded-xl p-8 mb-8 border border-gray-200">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-3">Specialization Information</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-lg font-semibold text-gray-700 mb-2">Specialization ID:</p>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">{{ $specialization->SpecializationID }}</p>
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-700 mb-2">Name:</p>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">{{ $specialization->name }}</p>
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-700 mb-2">For University:</p>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">
                    @if($specialization->is_for_university)
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                    @else
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-700 mb-2">Created At:</p>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">{{ $specialization->created_at->format('M d, Y H:i A') }}</p>
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-700 mb-2">Last Updated At:</p>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">{{ $specialization->updated_at->format('M d, Y H:i A') }}</p>
            </div>
        </div>

        <div class="mt-6">
            <p class="text-lg font-semibold text-gray-700 mb-2">Description:</p>
            <div class="bg-gray-50 p-4 rounded-md border border-gray-200 min-h-[80px]">
                <p class="text-gray-800 text-base leading-relaxed">{{ $specialization->description }}</p>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-4 justify-end">
            <a href="{{ route('admin.specializations.edit', $specialization->SpecializationID) }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Edit Specialization
            </a>
            <form action="{{ route('admin.specializations.destroy', $specialization->SpecializationID) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this specialization? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Delete Specialization
                </button>
            </form>
        </div>
    </div>
</main>
@endsection
