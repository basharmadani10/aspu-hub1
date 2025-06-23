{{-- File: resources/views/admin/profile/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
    <h2 class="text-3xl font-bold text-gray-800 mb-6">My Profile</h2>

    <div class="bg-white rounded-lg shadow-md p-8 mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Profile Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-gray-600 font-medium">First Name:</p>
                <p class="text-lg font-semibold text-gray-900">{{ Auth::user()->first_name }}</p>
            </div>
            <div>
                <p class="text-gray-600 font-medium">Last Name:</p>
                <p class="text-lg font-semibold text-gray-900">{{ Auth::user()->last_name }}</p>
            </div>
            <div>
                <p class="text-gray-600 font-medium">Email:</p>
                <p class="text-lg font-semibold text-gray-900">{{ Auth::user()->email }}</p>
            </div>
            <div>
                <p class="text-gray-600 font-medium">Phone Number:</p>
                <p class="text-lg font-semibold text-gray-900">{{ Auth::user()->phone_number ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 font-medium">Country:</p>
                <p class="text-lg font-semibold text-gray-900">{{ Auth::user()->country ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 font-medium">Current Location:</p>
                <p class="text-lg font-semibold text-gray-900">{{ Auth::user()->current_location ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 font-medium">Gender:</p>
                <p class="text-lg font-semibold text-gray-900">{{ Auth::user()->gender ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 font-medium">Birth Date:</p>
                <p class="text-lg font-semibold text-gray-900">{{ Auth::user()->birth_date ? \Carbon\Carbon::parse(Auth::user()->birth_date)->format('Y-m-d') : 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-gray-600 font-medium">Bio:</p>
                <p class="text-lg font-semibold text-gray-900">{{ Auth::user()->bio ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="mt-6">
            <a href="{{ route('admin.profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                Edit Profile Information
            </a>
        </div>
    </div>


    <div class="bg-white rounded-lg shadow-md p-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Change Password</h3>

        <form action="{{ route('admin.profile.update-password') }}" method="POST">
            @csrf
            @method('PUT') {{-- Use PUT method for update operations --}}

            <div class="mb-4">
                <label for="current_password" class="block text-gray-700 text-sm font-bold mb-2">Current Password:</label>
                <input type="password" name="current_password" id="current_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('current_password') border-red-500 @enderror" required>
                @error('current_password')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">New Password:</label>
                <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror" required>
                @error('password')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm New Password:</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                    Change Password
                </button>
            </div>
        </form>
    </div>
@endsection
