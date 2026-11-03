@extends('layouts.patient')

@section('title')
    {{ __("Subscription Plans") }}
@stop

@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container mx-auto">
            <!-- Page Title -->
            <div class="text-center mb-12 wow fadeInUp" data-wow-delay="0.1s">
                <p class="text-primary-500 font-semibold uppercase tracking-wider">{{ __("Our Subscription Plans") }}</p>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mt-2">{{ __("Unlock Premium Healthcare") }}</h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">{{ __("Choose a plan to access exclusive features like Health Assistant, direct doctor contact, and more. All subscriptions are securely processed through your wallet.") }}</p>
            </div>

            <!-- Wallet Balance Display -->
            <div class="mb-8 text-center">
                <div class="inline-block bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 rounded-full px-6 py-3">
                    <span class="font-semibold">{{ __("Your Wallet Balance:") }}</span>
                    <span class="text-xl font-bold ml-2">{{ $setting->currency ?? '$' }}{{ number_format($user_id->wallet->balance ?? 0, 2) }}</span>
                    <a href="{{ route('medical.wallet') }}" class="ml-4 text-sm font-semibold underline hover:text-blue-600 dark:hover:text-blue-300">{{ __("Manage Wallet") }}</a>
                </div>
            </div>

            @if(Session::has('message'))
                @php $alertClass = Session::get('alert-class', 'bg-blue-100 dark:bg-blue-200 text-blue-700 dark:text-blue-800'); @endphp
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg p-4 text-sm flex justify-between items-center {{ $alertClass }}" role="alert">
                    <span>{{ Session::get('message') }}</span>
                    <button @click="show = false" class="font-bold">&times;</button>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($patientsubscription as $plan)
                    @php
                        $isCurrentPlan = isset($activeSubscription) && $activeSubscription->subscription_id == $plan->id;
                    @endphp
                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 flex flex-col {{ $isCurrentPlan ? 'border-2 border-primary-600' : '' }}">
                        @if($isCurrentPlan)
                            <div class="absolute top-0 right-0 bg-primary-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-lg">
                                {{ __("Current Plan") }}
                            </div>
                        @endif
                        <h3 class="text-2xl font-semibold text-gray-900 dark:text-white text-center">{{ $plan->name }}</h3>
                        <div class="mt-4 text-center text-gray-900 dark:text-white">
                            <span class="text-4xl font-bold">{{ $setting->currency ?? '$' }}{{ number_format($plan->price, 2) }}</span>
                            <span class="text-lg text-gray-500 dark:text-gray-400">/ {{ $plan->duration }} {{ Str::plural($plan->duration_unit, $plan->duration) }}</span>
                        </div>

                        <ul class="mt-8 space-y-4 text-gray-600 dark:text-gray-400 flex-grow text-left">
                            @if($plan->description)
                                <li class="flex items-start">
                                    <i class="uil uil-info-circle text-blue-500 mr-2 mt-1 flex-shrink-0"></i>
                                    <span>{{ $plan->description }}</span>
                                </li>
                            @endif
                            @if(isset($plan->ai_credits) && $plan->ai_credits > 0)
                                <li class="flex items-center">
                                    <i class="uil uil-robot text-blue-500 mr-2 flex-shrink-0"></i>
                                    <span>{{ $plan->ai_credits }} {{ __("AI Credits") }}</span>
                                </li>
                            @endif
                            @foreach($plan->features as $feature)
                                <li class="flex items-start">
                                    <i class="uil uil-check-circle text-blue-500 mr-2 mt-1 flex-shrink-0"></i>
                                    <span><strong>{{ $feature->name }}:</strong> {{ $feature->value }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-8">
                            @if($isCurrentPlan)
                                <button type="button" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 cursor-not-allowed" disabled>
                                    {{ __("Your Current Plan") }}
                                </button>
                            @else
                                @php
                                    $hasSufficientBalance = isset($user_id->wallet) && $user_id->wallet->balance >= $plan->price;
                                    $buttonText = isset($activeSubscription) ? __("Switch Plan") : __("Subscribe with Wallet");
                                    $topUpText = isset($activeSubscription) ? __("Top-up to Switch") : __("Top-up to Subscribe");
                                @endphp

                                @if($hasSufficientBalance)
                                    <form action="{{ route('patient.subscribe.wallet') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                        <input type="hidden" name="amount" value="{{ $plan->price }}">
                                        <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                            {{ $buttonText }}
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('medical.wallet') }}" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400">
                                        {{ $topUpText }}
                                    </a>
                                @endif
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
<script>
    function openPaymentModal(planId, planPrice) {
        document.getElementById('plan_id').value = planId;
        document.getElementById('plan_price').value = planPrice;
        document.getElementById('paymentModal').style.display = 'block';
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
    }
</script>
@stop