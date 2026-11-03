@extends('layouts.patient')

@section('title')
    {{ __("Buy AI Credits") }}
@stop

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Page Title -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">{{ __("AI Credit Packages") }}</h1>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">{{ __("Purchase credits to use the Kunashe AI Health Assistant.") }}</p>
    </div>

    <!-- Wallet Balance Display -->
    <div class="mb-8 text-center">
        <div class="inline-block bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 rounded-full px-6 py-3">
            <span class="font-semibold">{{ __("Your AI Credit Balance:") }}</span>
            <span class="text-xl font-bold ml-2">{{ number_format($userdata->wallet->ai_credits ?? 0) }}</span>
        </div>
        <div class="inline-block bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100 rounded-full px-6 py-3 ml-4">
            <span class="font-semibold">{{ __("Your Money Wallet:") }}</span>
            <span class="text-xl font-bold ml-2">{{ $setting->currency ?? '$' }}{{ number_format($userdata->wallet->balance ?? 0, 2) }}</span>
            <a href="{{ route('medical.wallet') }}" class="ml-4 text-sm font-semibold underline hover:text-green-600 dark:hover:text-green-300">{{ __("Manage Wallet") }}</a>
        </div>
    </div>

    @if(Session::has('message'))
        @php $alertClass = Session::get('alert-class', 'alert-info') == 'alert-success' ? 'bg-green-100 dark:bg-green-200 text-green-700 dark:text-green-800' : 'bg-red-100 dark:bg-red-200 text-red-700 dark:text-red-800'; @endphp
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg p-4 text-sm flex justify-between items-center {{ $alertClass }}" role="alert">
            <span>{{ Session::get('message') }}</span>
            <button @click="show = false" class="font-bold">&times;</button>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($creditPackages as $package)
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 flex flex-col">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white text-center">{{ $package->name }}</h3>
                <div class="mt-4 text-center text-gray-900 dark:text-white">
                    <span class="text-4xl font-bold">{{ $package->credits }}</span>
                    <span class="text-lg text-gray-500 dark:text-gray-400"> Credits</span>
                </div>
                <p class="text-center text-xl font-semibold text-primary-600 dark:text-primary-400 mt-2">{{ $setting->currency ?? '$' }}{{ number_format($package->price, 2) }}</p>

                <div class="mt-6 text-center text-gray-600 dark:text-gray-400 flex-grow">
                    <p>{{ $package->description }}</p>
                </div>

                <div class="mt-8">
                    @php
                        $hasSufficientBalance = isset($userdata->wallet) && $userdata->wallet->balance >= $package->price;
                    @endphp

                    @if($hasSufficientBalance)
                        <form action="{{ route('patient.purchase_credits.wallet') }}" method="POST">
                            @csrf
                            <input type="hidden" name="package_id" value="{{ $package->id }}">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                {{ __("Buy with Wallet") }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('medical.wallet') }}" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400">
                            {{ __("Top-up to Buy") }}
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <p class="text-gray-500 dark:text-gray-400">{{__('No AI credit packages are available at the moment.')}}</p>
            </div>
        @endforelse
    </div>
</div>
@stop