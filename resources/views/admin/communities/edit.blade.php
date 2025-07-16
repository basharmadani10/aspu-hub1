@extends('layouts.admin')

@section('title', 'Edit Community: ' . $community->name)

@section('content')
<main class="flex-1 p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Edit Community: {{ $community->name }}</h2>
        <a href="{{ route('admin.communities.index') }}" class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-100 rounded-lg hover:bg-indigo-200 transition duration-150 ease-in-out">
            &larr; Back to Communities List
        </a>
    </div>

    <div class="p-8 bg-white rounded-lg shadow-xl">
        <form action="{{ route('admin.communities.update', $community->id) }}" method="POST">
            @csrf
            @method('PUT') {{-- Use PUT method for updates --}}
            <div class="grid grid-cols-1 gap-6">
                {{-- Community Name --}}
                <div>
                    <label for="name" class="block mb-2 text-sm font-semibold text-gray-700">Community Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $community->name) }}" required class="w-full px-4 py-2.5 text-gray-900 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>




            </div>
            {{-- 🛑 لا يوجد قسم للاختصاصات هنا أيضًا --}}
            {{-- Submit Button --}}
            <div class="mt-8 text-right">
                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-4 0V4a1 1 0 011-1h2a1 1 0 011 1v3m-4 0h4"></path></svg>
                    Update Community
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
