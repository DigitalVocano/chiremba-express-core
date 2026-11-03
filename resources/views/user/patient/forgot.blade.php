@extends('layouts.guest')
@section('title')
 {{__('Forgot Password')}}
@stop
@section('meta-data')
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{__('KumbaCare')}}"/>
    <meta property="og:title" content="{{__('Forgot Password')}}"/>
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
    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 sm:p-10 rounded-lg shadow-2xl">
            <div>
                <img class="mx-auto h-12 w-auto" src="{{ asset('image_web/').'/'.$setting->logo }}" alt="{{__('KumbaCare')}}">
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                    {{__('Forgot Password')}}
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    {{__('Or')}}
                    <a href="{{url('patientlogin')}}" class="font-medium text-primary-600 hover:text-primary-600">
                        {{__('login to your account')}}
                    </a>
                </p>
            </div>

            @if(Session::has('message'))
            <div class="p-4 text-sm {{ Session::get('alert-class', 'text-green-700 bg-green-100 dark:bg-green-700 dark:text-green-100') }} rounded-lg" role="alert">
                {{ Session::get('message') }}
            </div>
            @endif
             @if(Session::has('error'))
            <div class="p-4 text-sm {{ Session::get('alert-class', 'text-red-700 bg-red-100 dark:bg-red-700 dark:text-red-100') }} rounded-lg" role="alert">
                {{ Session::get('error') }}
            </div>
            @endif

            <form class="mt-8 space-y-6" action="{{url('postforgotpassword')}}" method="post">
                @csrf
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email-address" class="sr-only">{{__('Email')}}</label>
                        <input id="email-address" name="email" type="email" autocomplete="email" required
                               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"
                               placeholder="{{__('Enter Email Address')}}">
                        @if ($errors->has('email'))
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('email') }}</p>
                        @endif
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 dark:focus:ring-offset-gray-800">
                        {{__('Send Password Reset Link')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop
