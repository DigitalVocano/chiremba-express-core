@extends('user.layout')
@section('title', 'Shopping Cart')

@section('content')
<div class="bg-gray-100 dark:bg-gray-900 py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-center text-gray-800 dark:text-white mb-8">Shopping Cart</h1>

        @if(Session::has('message'))
            <div class="bg-{{ Session::get('alert-class') == 'alert-success' ? 'green' : 'red' }}-100 border border-{{ Session::get('alert-class') == 'alert-success' ? 'green' : 'red' }}-400 text-{{ Session::get('alert-class') == 'alert-success' ? 'green' : 'red' }}-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ Session::get('message') }}</span>
            </div>
        @endif

        @if($cartItems->isNotEmpty())
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Cart Items ({{ $cartItems->count() }})</h2>
                    <div class="space-y-6">
                        @foreach($groupedCart as $pharmacyId => $items)
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                <h3 class="font-bold text-lg text-primary-600 dark:text-primary-400 mb-3">
                                    <a href="{{ url('viewpharmacy', $pharmacies[$pharmacyId]->slug) }}">{{ $pharmacies[$pharmacyId]->name }}</a>
                                </h3>
                                @foreach($items as $item)
                                    <div class="flex items-center space-x-4 py-2">
                                        <img src="{{ asset('upload/pharmacymedicine/' . $item->attributes->image) }}" alt="{{ $item->name }}" class="w-20 h-20 object-cover rounded-md">
                                        <div class="flex-grow">
                                            <p class="font-semibold text-gray-800 dark:text-white">{{ $item->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Qty: {{ $item->quantity }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-gray-800 dark:text-white">${{ number_format($item->price * $item->quantity, 2) }}</p>
                                            <a href="{{ url('removeCartItem/' . $item->id) }}" class="text-red-500 hover:text-red-700 text-sm font-medium">Remove</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 sticky top-28">
                        <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Order Summary</h2>
                        <div class="space-y-3 border-b border-gray-200 dark:border-gray-700 pb-4">
                            <div class="flex justify-between text-gray-600 dark:text-gray-300">
                                <span>Subtotal</span>
                                <span>${{ number_format($cartTotal, 2) }}</span>
                            </div>
                            {{-- Assuming tax and delivery are calculated or fixed --}}
                            <div class="flex justify-between text-gray-600 dark:text-gray-300">
                                <span>Taxes ({{ $setting->pharmacy_tax ?? 0 }}%)</span>
                                <span>${{ number_format($cartTotal * (($setting->pharmacy_tax ?? 0) / 100), 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-300">
                                <span>Delivery</span>
                                <span>${{ number_format($setting->pharmacy_delivery_charge ?? 0, 2) }}</span>
                            </div>
                        </div>
                        @php
                            $grandTotal = $cartTotal + ($cartTotal * (($setting->pharmacy_tax ?? 0) / 100)) + ($setting->pharmacy_delivery_charge ?? 0);
                        @endphp
                        <div class="flex justify-between font-bold text-lg text-gray-800 dark:text-white py-4">
                            <span>Total</span>
                            <span>${{ number_format($grandTotal, 2) }}</span>
                        </div>

                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-green-800 dark:text-green-200"><i class="fas fa-wallet mr-2"></i>Wallet Balance</span>
                                <span class="font-bold text-lg text-green-800 dark:text-green-200">${{ number_format($walletBalance, 2) }}</span>
                            </div>
                        </div>

                        <form action="{{ route('cart.checkout') }}" method="POST">
                            <div class="mb-4 p-3 rounded-lg border border-purple-200 bg-purple-50 dark:bg-purple-900/20 dark:border-purple-700">
                                <label class="flex items-start space-x-3 cursor-pointer">
                                    <input type="checkbox" name="is_discreet_packaging" value="1" class="mt-1 h-4 w-4 text-purple-600 rounded border-gray-300 focus:ring-purple-500">
                                    <span class="text-sm text-purple-900 dark:text-purple-200">
                                        <strong><i class="fas fa-user-shield mr-1"></i> Confidential SRHR Courier Packaging:</strong>
                                        {{ __('femtech.discreet_packaging_label') }}
                                    </span>
                                </label>
                            </div>
                            @csrf
                            @if($walletBalance >= $grandTotal)
                                <button type="submit" class="w-full bg-primary-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-primary-600 transition-colors">
                                    Pay with Wallet & Place Order
                                </button>
                            @else
                                <div class="text-center">
                                    <p class="text-red-600 dark:text-red-400 mb-4">Insufficient wallet balance to place this order.</p>
                                    <a href="{{ route('userdashboard') }}" class="w-full inline-block bg-yellow-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-yellow-600 transition-colors">
                                        Top Up Wallet
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <i class="fas fa-shopping-cart fa-4x text-gray-300 dark:text-gray-600 mb-4"></i>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Your Cart is Empty</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Looks like you haven't added any medicines yet.</p>
                <a href="{{ url('searchpharmacy') }}" class="mt-6 inline-block bg-primary-500 text-white font-semibold py-2 px-6 rounded-md hover:bg-primary-600 transition-colors">
                    Browse Pharmacies
                </a>
            </div>
        @endif
    </div>
</div>
@endsection