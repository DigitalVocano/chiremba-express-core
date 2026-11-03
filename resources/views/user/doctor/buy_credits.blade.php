@extends('layouts.doctor')

@section('title', __('Buy AI Credits'))

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">{{ __('AI Prescription Assistant Credits') }}</h1>

    {{-- Display Success/Error Messages --}}
    @if (Session::has('message'))
        <div class="mb-4 p-4 rounded-md {{ Session::get('alert-class', 'alert-info') == 'alert-success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}" role="alert">
            {{ Session::get('message') }}
        </div>
    @endif

    {{-- Current Balance Card --}}
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">{{ __('Your Current Balance') }}</h2>
        @if ($activeSubscription && !is_null($activeSubscription->remaining_ai_credits))
            <p class="text-4xl font-bold text-primary-600 dark:text-primary-400">
                {{ $activeSubscription->remaining_ai_credits }} <span class="text-xl font-medium text-gray-500 dark:text-gray-400">{{ __('Credits') }}</span>
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ __('These credits are used for the AI Prescription Assistant feature.') }}
            </p>
        @else
            <p class="text-gray-600 dark:text-gray-400">
                {{ __('Your current subscription plan does not include AI features. Please upgrade your plan to use the AI Assistant.') }}
            </p>
            <a href="{{ route('doctor.subscription_plan') }}" class="mt-4 inline-block bg-primary-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-primary-700 transition-colors">
                {{ __('View Subscription Plans') }}
            </a>
        @endif
    </div>

    {{-- Available Packages --}}
    @if ($activeSubscription && !is_null($activeSubscription->remaining_ai_credits))
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6">{{ __('Purchase More Credits') }}</h2>

            @if($creditPackages->isEmpty())
                <div class="text-center py-10 bg-white dark:bg-gray-800 rounded-lg shadow">
                    <p class="text-gray-500 dark:text-gray-400">{{ __('There are no credit packages available for purchase at the moment.') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($creditPackages as $package)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 flex flex-col justify-between border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-shadow">
                            <div>
                                <h3 class="text-lg font-semibold text-primary-700 dark:text-primary-300">{{ $package->name }}</h3>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white my-3">
                                    {{ $package->credits }} <span class="text-lg font-medium text-gray-500 dark:text-gray-400">{{ __('Credits') }}</span>
                                </p>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">{{ $package->description }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">
                                    {{ $setting->currency }}{{ number_format($package->price, 2) }}
                                </p>
                                <form action="{{ route('doctor.purchase_credits.wallet') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="package_id" value="{{ $package->id }}">
                                    <button type="submit" class="w-full bg-primary-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors">
                                        <i class="fas fa-wallet mr-2"></i> {{ __('Purchase with Wallet') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
@endsection

