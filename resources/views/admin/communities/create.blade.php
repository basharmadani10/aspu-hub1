@extends('layouts.admin')

@section('title', 'Create New Community')

@section('content')
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Create New Community</h2>
        <a href="{{ route('admin.communities.index') }}" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition duration-150 ease-in-out">
            &larr; Back to Communities List
        </a>
    </div>

    <div class="p-8 bg-white rounded-lg shadow-xl">
        <form action="{{ route('admin.communities.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6">
                {{-- Community Name --}}
                <div>
                    <label for="name" class="block mb-2 text-sm font-semibold text-gray-700">Community Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}

            </div>

            {{-- 🛑 هذا القسم تم إزالته بالكامل بناءً على طلبك لعدم خلط الاختصاصات بالمجتمعات --}}
            {{-- <div class="mt-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Specialization Details</h3>
                <div class="mb-4">
                    <label for="specialization_name" class="block mb-2 text-sm font-semibold text-gray-700">Specialization Name</label>
                    <input type="text" name="specialization_name" id="specialization_name" value="{{ old('specialization_name') }}"
                           class="w-full px-4 py-2.5 text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                    @error('specialization_name')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="specialization_description" class="block mb-2 text-sm font-semibold text-gray-700">Specialization Description</label>
                    <input type="text" name="specialization_description" id="specialization_description" value="{{ old('specialization_description') }}"
                           class="w-full px-4 py-2.5 text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                    @error('specialization_description')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="is_for_university" class="block mb-2 text-sm font-semibold text-gray-700">Is for University?</label>
                    <select name="is_for_university" id="is_for_university"
                             class="w-full px-4 py-2.5 text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                        <option value="1" {{ old('is_for_university') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('is_for_university') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('is_for_university')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div> --}}
            {{-- 🛑 نهاية القسم الذي تم إزالته --}}

            {{-- Submit Button --}}
            <div class="mt-8 text-right">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Create Community
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
