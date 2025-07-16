@extends('layouts.admin')

@section('title', 'User Details: ' . ($user->first_name ?? 'User') . ' ' . ($user->last_name ?? ''))

@section('content')
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 border-b-2 border-indigo-500 pb-3">
        User Details: <span class="text-indigo-700">{{ $user->first_name ?? 'N/A' }} {{ $user->last_name ?? '' }}</span>
    </h1>

    <div class="bg-white shadow-xl rounded-xl p-8 mb-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-3">General Information</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Full Name:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">
                    {{ $user->first_name ?? 'N/A' }} {{ $user->last_name ?? '' }}
                </p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Email:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">
                    {{ $user->email ?? 'N/A' }}
                </p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Phone Number:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">
                    {{ $user->phone_number ?? 'N/A' }}
                </p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Country:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">
                    {{ $user->country ?? 'N/A' }}
                </p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Role:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">
                    {{-- Assuming you have a way to display role name based on roleID --}}
                    Role ID: {{ $user->roleID ?? 'N/A' }}
                </p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Blocked:</h3>
                <p class="text-gray-900 text-base bg-gray-50 p-3 rounded-md border border-gray-200">
                    @if ($user->is_blocked)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Yes</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">No</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Bio:</h3>
            <div class="bg-gray-50 p-4 rounded-md border border-gray-200 min-h-[80px]">
                <p class="text-gray-800 text-base leading-relaxed">{{ $user->bio ?? 'No bio provided.' }}</p>
            </div>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap gap-4 justify-end">
        {{-- Example: Back to the community members list if came from there --}}
        <a href="{{ url()->previous() }}" class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
            <svg class="-ml-1 mr-3 h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
            Back
        </a>
    </div>
@endsection
