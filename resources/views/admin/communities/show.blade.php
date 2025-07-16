@extends('layouts.admin')

{{-- dd($community); // قم بإزالة هذا السطر في بيئة الإنتاج --}}
@section('title', 'Manage Community: ' . $community->name)

@section('content')
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 border-b-2 border-indigo-500 pb-3">
        Manage Community: <span class="text-indigo-700">{{ $community->name }}</span>
    </h1>

    <div class="bg-white shadow-xl rounded-xl p-8 mb-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-3">Community Information</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Community Name:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">{{ $community->name }}</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Subscribers:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">{{ $community->subscribers_count }}</p>
            </div>
            {{-- 🛑 لا يوجد عرض للاختصاص هنا --}}
        </div>



        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Management Actions:</h3>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('admin.communities.members.index', $community->id) }}" class="inline-flex items-center px-5 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5.5 13a3.5 3.5 0 01-.369-6.98l-2.617-1a1 1 0 01.381-1.921l.8-.268A4.5 4.5 0 0113.5 9V4.5a3.5 3.5 0 11.5 6.995l-3.093 1.031A3.504 3.504 0 019.5 13H5.5z"></path></svg>
                    Manage Members
                </a>
                <a href="{{ route('admin.communities.posts.index', $community->id) }}" class="inline-flex items-center px-5 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586A2 2 0 0111.828 3.586L10.586 2.343A2 2 0 009.172 2H4zm8 5a1 1 0 110 2H8a1 1 0 110-2h4z" clip-rule="evenodd"></path></svg>
                    View Posts
                </a>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap gap-4 justify-end">
            <a href="{{ route('admin.communities.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                <svg class="-ml-1 mr-3 h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                Back to Communities
            </a>
        </div>
@endsection
