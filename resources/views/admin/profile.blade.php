@extends('admin.layout')
@section('title')
Edit Profile | Admin Dashboard
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Edit Profile</h1>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <a href="{{ url('admin/dashboard') }}" class="text-primary-500 hover:underline">Dashboard</a>
                        / Edit Profile
                    </div>
                </div>
                <div>
                    <a href="{{ url('admin/2fa-setup') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150 shadow-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Two-Factor Auth (2FA) Setup
                    </a>
                </div>
            </div>

            <div class="flex justify-center">
                <div class="w-full lg:w-2/3">
                    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                        @if(Session::has('message'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                            <span>{{ Session::get('message') }}</span>
                            <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                        </div>
                        @endif
                        <form action="{{url('admin/updateaccount')}}" method="post" enctype="multipart/form-data" class="space-y-6">
                            {{csrf_field()}}
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">First Name</label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="first_name" name="first_name" placeholder='Enter Your First Name' value="{{$userdata->first_name}}" required>
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="last_name" name="last_name" placeholder='Enter Last Name' value="{{$userdata->last_name}}" required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400" id="email" name="email" placeholder='Enter Email Address' value="{{$userdata->email}}" readonly>
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" id="phone" name="phone" placeholder='Enter Phone' value="{{$userdata->phone}}" required>
                            </div>
                            <div>
                                <label for="profile_pic" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Picture</label>
                                <div class="mt-2 flex items-center space-x-4">
                                    <img src="{{asset('upload/profile/').'/'.$userdata->profile_pic}}" class="w-24 h-24 object-cover rounded-full border-2 border-gray-300 dark:border-gray-600" alt="Profile Picture" />
                                    <input type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:file:bg-gray-600 file:text-primary-700 dark:file:text-gray-200 hover:file:bg-primary-100 dark:hover:file:bg-gray-500" id="profile_pic" name="profile_pic">
                                </div>
                            </div>
                            <div class="flex justify-end pt-4">
                                @if(Session::get("is_demo")=='0')
                                <button type="button" onclick="disablebtn()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">Submit</button>
                                @else
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">Submit</button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('footer')
@stop
