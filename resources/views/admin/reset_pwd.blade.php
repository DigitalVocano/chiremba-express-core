<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>{{ __('Reset Password') }} | {{ __("KumbaCare") }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta content='{{ __("KumbaCare") }}' name="description"/>
    <meta content='{{ __("KumbaCare") }}' name="author"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ url('/') }}"/>
    <meta property="og:title" content='{{ __("KumbaCare") }}'/>
    <meta property="og:image" content="{{ Session::get('favicon') }}"/>
    <meta property="og:image:width" content="250px"/>
    <meta property="og:image:height" content="250px"/>
    <meta property="og:site_name" content='{{ __("KumbaCare") }}'/>
    <meta property="og:description" content='{{ __("meta_description") }}'/>
    <meta property="og:keyword" content='{{ __("Meta Keyword") }}'/>
    <link rel="shortcut icon" href="{{ Session::get('favicon') }}"/>

    <!-- Tailwind CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900">

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 sm:p-10 rounded-lg shadow-2xl">
        <div>
            <img class="mx-auto h-12 w-auto" src="{{ asset('image_web/' . $setting->logo) }}" alt="Logo">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                {{ __('Reset Password') }}
            </h2>
        </div>
        @if(Session::has('message'))
            <div class="rounded-md bg-green-50 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ Session::get('message') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(!isset($msg))
            <form action="{{ url('admin/reset_new_pwd') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="code" value="{{ $code }}">
                <input type="hidden" name="id" value="{{ $id }}">
                <input type="hidden" name="type" value="{{ $type }}">

                <div>
                    <label for="npwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Enter New Password') }}</label>
                    <input type="password" name="npwd" id="npwd" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                </div>

                <div>
                    <label for="rpwd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Enter Re Enter New Password') }}</label>
                    <input type="password" name="rpwd" id="rpwd" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>
        @else
            <p class="text-center text-gray-700 dark:text-gray-300">{{ $msg }}</p>
        @endif
    </div>
</div>

</body>
</html>