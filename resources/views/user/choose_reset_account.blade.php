@extends('user.layout')
@section('title')
    {{__('Choose Account')}}
@stop

@section('content')
<section class="py-16 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">{{__('Multiple Accounts Found')}}</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    We found multiple accounts associated with the email <strong>{{ $email }}</strong>.
                    Please select the account for which you want to reset the password.
                </p>
            </div>

            <div class="mt-8 space-y-4">
                @foreach($accounts as $account)
                    <a href="{{ route('send.reset.link', ['type' => $account['role_id'], 'id' => $account['id']]) }}"
                       class="block w-full p-4 text-left bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/50 border border-transparent hover:border-primary-500 transition-all duration-200">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $account['name'] }}</div>
                        <div class="text-sm text-primary-600 dark:text-primary-400 font-medium uppercase tracking-wider">{{ $account['type'] }}</div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ url('forgotpassword') }}" class="text-sm text-gray-500 hover:text-primary-500 underline">
                    {{__('Try a different email address')}}
                </a>
            </div>
        </div>
    </div>
</section>
@stop