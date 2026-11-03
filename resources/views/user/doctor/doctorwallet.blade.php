@extends('layouts.doctor')

@section('title')
    {{ __('My Wallet') }}
@stop

@section('content')
<div class="main-content" x-data="{ showTopUpModal: false, paymentMethod: 'paynow' }">
    <div class="page-content p-6">
        <div class="container mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('My Wallet') }}</h1>
                <button @click="showTopUpModal = true" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">
                    <i class="fas fa-plus-circle mr-2"></i> {{ __('Top Up Wallet') }}
                </button>
            </div>

            @if(Session::has('message'))
                <div class="mb-4 p-4 rounded-lg {{ Session::get('alert-class', 'bg-blue-100 text-blue-700') }}">
                    {{ Session::get('message') }}
                </div>
            @endif

            {{-- Balance Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Current Balance') }}</p>
                        <h2 class="text-4xl font-bold text-gray-900 dark:text-white mt-2">
                            {{ $setting->currency ?? '$' }}{{ number_format($wallet->balance, 2) }}
                        </h2>
                    </div>
                    <div class="p-4 bg-primary-100 dark:bg-primary-900 rounded-full text-primary-600 dark:text-primary-300">
                        <i class="fas fa-wallet text-3xl"></i>
                    </div>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('Transaction History') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Description') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Type') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $transaction->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->type == 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($transaction->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $transaction->type == 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->type == 'credit' ? '+' : '-' }}{{ $setting->currency ?? '$' }}{{ number_format($transaction->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">{{ __('No transactions found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Top Up Modal --}}
    <div x-show="showTopUpModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showTopUpModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">{{ __('Top Up Wallet') }}</h3>
                    
                    <form action="{{ route('wallet.topup.initiate') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $doctordata->id }}">
                        <input type="hidden" name="context" value="general_topup">

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Amount') }}</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">{{ $setting->currency ?? '$' }}</span>
                                </div>
                                <input type="number" name="amount" step="0.01" min="1" required class="focus:ring-primary-500 focus:border-primary-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Payment Method') }}</label>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach($paymentGateways as $gateway)
                                    @if(strtolower($gateway->gateway_name) == 'paynow')
                                        <label class="cursor-pointer">
                                            <input type="radio" name="payment_method" value="paynow" x-model="paymentMethod" class="sr-only peer">
                                            <div class="p-4 rounded-lg border-2 hover:bg-gray-50 dark:hover:bg-gray-700 peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-900/20 transition-all text-center">
                                                <span class="font-semibold text-gray-700 dark:text-gray-200">Paynow</span>
                                            </div>
                                        </label>
                                    @endif
                                    @if(strtolower($gateway->gateway_name) == 'ecocash')
                                        <label class="cursor-pointer">
                                            <input type="radio" name="payment_method" value="ecocash" x-model="paymentMethod" class="sr-only peer">
                                            <div class="p-4 rounded-lg border-2 hover:bg-gray-50 dark:hover:bg-gray-700 peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-900/20 transition-all text-center">
                                                <span class="font-semibold text-gray-700 dark:text-gray-200">EcoCash</span>
                                            </div>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- EcoCash Phone Input --}}
                        <div x-show="paymentMethod === 'ecocash'" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('EcoCash Phone Number') }}</label>
                            <input type="text" name="ecocash_phone" value="{{ $doctordata->phoneno }}" class="focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="077...">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Enter the number to be charged.') }}</p>
                        </div>

                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:col-start-2 sm:text-sm">
                                {{ __('Proceed to Pay') }}
                            </button>
                            <button type="button" @click="showTopUpModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop