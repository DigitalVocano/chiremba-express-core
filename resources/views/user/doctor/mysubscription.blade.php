@extends('layouts.doctor')
@section('title')
{{__('My Subscription')}}
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{__('KumbaCare')}}"/>
<meta property="og:title" content="{{__('KumbaCare')}}"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{__('KumbaCare')}}"/>
<meta property="og:description" content="{{__('meta_description')}}"/>
<meta property="og:keyword" content="{{__('Meta Keyword')}}"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-4 md:p-6">
        <div class="container mx-auto">
            {{-- Page Header --}}
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">{{ __("My Subscription") }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __("View your current plan details and manage your subscription.") }}</p>
            </div>

            {{-- Main Content Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                @if($subscription && $subscription->subscription)
                    {{-- Active Subscription View --}}
                    <div>
                        <div class="p-6 sm:p-8">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase">{{ __("Current Plan") }}</span>
                                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $subscription->subscription->name }}</h2>
                                </div>
                                <div class="mt-4 sm:mt-0">
                                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">
                                        <i class="uil uil-check-circle mr-1"></i>
                                        {{ __("Active") }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 text-sm">
                                {{-- Plan Details --}}
                                <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="p-3 bg-primary-100 dark:bg-primary-900/50 rounded-full text-primary-600 dark:text-primary-300">
                                        <i class="uil uil-pricetag-alt text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-500 dark:text-gray-400">{{ __("Price") }}</p>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $setting->currency ?? '$' }}{{ number_format($subscription->amount, 2) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="p-3 bg-primary-100 dark:bg-primary-900/50 rounded-full text-primary-600 dark:text-primary-300">
                                        <i class="uil uil-wallet text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-500 dark:text-gray-400">{{ __("Payment Method") }}</p>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $subscription->payment_type == 0 ? 'Wallet' : 'Other' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="p-3 bg-primary-100 dark:bg-primary-900/50 rounded-full text-primary-600 dark:text-primary-300">
                                        <i class="uil uil-calendar-alt text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-500 dark:text-gray-400">{{ __("Purchased On") }}</p>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse($subscription->date)->format('F j, Y') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="p-3 bg-primary-100 dark:bg-primary-900/50 rounded-full text-primary-600 dark:text-primary-300">
                                        <i class="uil uil-hourglass text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-500 dark:text-gray-400">{{ __("Expires On") }}</p>
                                        @if($expiryDate)
                                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $expiryDate->format('F j, Y') }}</p>
                                        @else
                                            <p class="font-semibold text-red-500">{{ __("N/A") }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Plan Features --}}
                            <div class="mt-8 border-t dark:border-gray-700 pt-6">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __("Plan Features") }}</h3>
                                <ul class="space-y-3 text-gray-600 dark:text-gray-400">
                                    @forelse($subscription->subscription->features as $feature)
                                        <li class="flex items-start">
                                            <i class="uil uil-check-circle text-green-500 mr-2 mt-1"></i>
                                            <span>{{ $feature->name }}: <strong>{{ $feature->value }}</strong></span>
                                        </li>
                                    @empty
                                        <li class="text-gray-500">{{ __("This plan has no specific features listed.") }}</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 text-right">
                            <a href="{{ route('doctor.subscription_plan') }}" class="inline-block bg-primary-600 text-white font-semibold py-2 px-5 rounded-lg hover:bg-primary-700 transition-colors text-sm">
                                {{ __("View & Change Plan") }}
                            </a>
                        </div>
                    </div>
                @else
                    {{-- No Active Subscription View --}}
                    <div class="text-center p-8 sm:p-12">
                        <div class="mx-auto w-20 h-20 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-full text-primary-500">
                            <i class="uil uil-exclamation-octagon text-5xl"></i>
                        </div>
                        <h2 class="mt-6 text-2xl font-bold text-gray-800 dark:text-white">{{ __("No Active Subscription") }}</h2>
                        <p class="mt-2 text-gray-600 dark:text-gray-400 max-w-md mx-auto">{{ __("You do not have an active subscription plan. Choose a plan to unlock all features.") }}</p>
                        <div class="mt-8">
                            <a href="{{ route('doctor.subscription_plan') }}" class="inline-block bg-primary-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-primary-700 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                {{ __("View Subscription Plans") }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
