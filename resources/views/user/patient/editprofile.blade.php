@extends('layouts.patient')
@section('title')
    {{__('Edit Profile')}}
@stop
@section('meta-data')
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{__('KumbaCare')}}"/>
    <meta property="og:title" content="{{__('Edit Profile')}}"/>
    <meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
    <meta property="og:image:width" content="250px"/>
    <meta property="og:image:height" content="250px"/>
    <meta property="og:site_name" content="{{__('KumbaCare')}}"/>
    <meta property="og:description" content="{{__('meta_description')}}"/>
    <meta property="og:keyword" content="{{__('Meta Keyword')}}"/>
    <link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
    {{-- <meta name="viewport" content="width=device-width, initial-scale=1"> --}}
@stop
@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Edit Profile')}}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-2xl mx-auto"> {{-- Constrained width and centered --}}
        @if(Session::has('message'))
        <div class="mb-4 p-4 text-sm {{ Session::get('alert-class', 'text-green-700 bg-green-100 dark:bg-green-200 dark:text-green-800') }} rounded-lg" role="alert">
            {{ Session::get('message') }}
        </div>
        @endif

        <form action="{{url('updateuserprofile')}}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="space-y-6">
                {{-- Profile Image Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Profile Picture')}}</label>
                    <div class="mt-2 flex items-center space-x-4">
                        <figure class="w-24 h-24 rounded-full overflow-hidden shadow-md">
                            @if(isset($userdata) && $userdata->profile_pic!="")
                            <img src="{{asset('upload/profile/').'/'.$userdata->profile_pic}}" alt="{{ $userdata->name ?? 'Profile' }}" class="object-cover w-full h-full">
                            @else
                            <img src="{{asset('upload/profile/profile.png')}}" alt="Default Profile" class="object-cover w-full h-full">
                            @endif
                        </figure>
                        <label for="image" class="cursor-pointer bg-white dark:bg-gray-700 py-2 px-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                            <span>{{__('Change')}}</span>
                            <input id="image" name="image" type="file" class="sr-only">
                        </label>
                    </div>
                     @error('image')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Name')}}</label>
                    <input type="text" name="name" id="name" placeholder="{{__('Enter Your First Name')}}" required value="{{isset($userdata->name)?$userdata->name:''}}"
                           class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm">
                     @error('name')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Phone no')}}</label>
                    <input type="text" name="phone" id="phone" placeholder="{{__('Enter Phone')}}" value="{{isset($userdata->phone)?$userdata->phone:''}}" required
                           class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm">
                     @error('phone')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Email')}}</label>
                    <input type="email" name="email" id="email" placeholder="{{__('Enter Email Address')}}" value="{{ (isset($userdata->email) && !str_contains($userdata->email, '@placeholder.chiremba.com')) ? $userdata->email : '' }}"
                           class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm">
                    @if(isset($userdata->email) && str_contains($userdata->email, '@placeholder.chiremba.com'))
                        <p class="mt-1 text-xs text-gray-500">{{__('Logged in via mobile number. You can optionally link a real email address here.')}}</p>
                    @endif
                     @error('email')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center justify-end space-x-4">
                <a href="{{url('userdashboard')}}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">{{__('Cancel')}}</a>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                    {{__('Save Change')}}
                </button>
            </div>
        </form>
    </div>
@stop
