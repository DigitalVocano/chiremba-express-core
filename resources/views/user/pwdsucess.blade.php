@extends('user.layout')
@section('title')
    {{__('Password Changed Successfully')}}
@stop

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 sm:p-10 rounded-lg shadow-2xl text-center">
        <div>
             <img class="mx-auto h-12 w-auto" src="{{ asset('image_web/' . $setting->logo) }}" alt="{{__('Chiremba Express')}}">
            <i class="fa-solid fa-check-circle text-6xl text-green-500 dark:text-green-400 mx-auto mb-6"></i>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            {{__('Success!')}}
        </h2>
        <p class="text-lg text-gray-700 dark:text-gray-300">
            {{__('Your password has been changed successfully!')}}
            @if(isset($msg))
                <br>{{ $msg }}
            @endif
        </p>
        <div class="mt-8">
            <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800 transition-transform transform hover:scale-105">
                {{__('Go to Home')}}
            </a>
        </div>
    </div>
</div>
@endsection
