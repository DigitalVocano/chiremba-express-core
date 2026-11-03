@extends('layouts.doctor')

@section('title')
    {{ __("Subscription Plans") }}
@stop

@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container mx-auto">
            <!-- Page Title -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white">{{ __("Choose Your Plan") }}</h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">{{ __("Select the best plan that suits your needs.") }}</p>
            </div>

            <!-- Wallet Balance Display -->
            <div class="mb-8 text-center">
                <div class="inline-block bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100 rounded-full px-6 py-3">
                    <span class="font-semibold">{{ __("Your Wallet Balance:") }}</span>
                    <span class="text-xl font-bold ml-2">{{ $setting->currency ?? '$' }}{{ number_format($doctordata->wallet->balance ?? 0, 2) }}</span>
                    <a href="{{ route('doctor.wallet') }}" class="ml-4 text-sm font-semibold underline hover:text-green-600 dark:hover:text-green-300">{{ __("Manage Wallet") }}</a>
                </div>
            </div>

            @if(Session::has('message'))
                @php $alertClass = Session::get('alert-class', 'bg-green-100 dark:bg-green-200 text-green-700 dark:text-green-800'); @endphp
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg p-4 text-sm flex justify-between items-center {{ $alertClass }}" role="alert">
                    <span>{{ Session::get('message') }}</span>
                    <button @click="show = false" class="font-bold">&times;</button>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($subscription as $plan)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 flex flex-col">
                        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white text-center">{{ $plan->name }}</h3>
                        <div class="mt-4 text-center text-gray-900 dark:text-white">
                            <span class="text-4xl font-bold">{{ $setting->currency ?? '$' }}{{ number_format($plan->price, 2) }}</span>
                            <span class="text-lg text-gray-500 dark:text-gray-400">/ {{ $plan->duration }} {{ Str::plural($plan->duration_unit, $plan->duration) }}</span>
                        </div>

                        <ul class="mt-8 space-y-4 text-gray-600 dark:text-gray-400 flex-grow">
                            @if($plan->features->isEmpty())
                                <li class="flex items-center">
                                    <i class="uil uil-check-circle text-green-500 mr-2"></i>
                                    <span>{{ __("Basic Access") }}</span>
                                </li>
                            @else
                                @foreach($plan->features as $feature)
                                    <li class="flex items-center">
                                        <i class="uil uil-check-circle text-green-500 mr-2"></i>
                                        <span>
                                            <strong>{{ $feature->name }}:</strong> 
                                            {{ $feature->value }}
                                            @if($feature->description)
                                                <span class="text-xs text-gray-400 dark:text-gray-500 block">{{ $feature->description }}</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            @endif
                        </ul>

                        <div class="mt-8">
                            @php
                                $hasSufficientBalance = isset($doctordata->wallet) && $doctordata->wallet->balance >= $plan->price;
                            @endphp

                            @if($hasSufficientBalance)
                                <form action="{{ route('doctor.subscribe.wallet') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        {{ __("Subscribe with Wallet") }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('doctor.wallet') }}" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400">
                                    {{ __("Top-up to Subscribe") }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@stop

@section('footer')
@stop