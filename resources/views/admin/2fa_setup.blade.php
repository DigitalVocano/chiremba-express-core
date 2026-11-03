@extends('admin.layout')
@section('title')
Two-Factor Authentication Setup | Admin Dashboard
@stop

@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-lime-600"></i> Two-Factor Authentication (2FA)
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Secure your administrator account using Google Authenticator, Authy, or any TOTP 2FA app.
                    </p>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ url('admin/dashboard') }}" class="text-blue-600 hover:underline">Dashboard</a> / 2FA Security
                </div>
            </div>

            <div class="flex justify-center">
                <div class="w-full lg:w-3/4">
                    @if(Session::has('message'))
                        <div class="mb-6 rounded-xl bg-blue-50 border border-blue-200 p-4 text-sm text-blue-800 flex justify-between items-center shadow-sm">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-info text-blue-600"></i>
                                <span>{{ Session::get('message') }}</span>
                            </div>
                        </div>
                    @endif

                    @if(Session::has('error'))
                        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-800 flex justify-between items-center shadow-sm">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                                <span>{{ Session::get('error') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700">
                        @if($isEnabled)
                            <!-- 2FA Active View -->
                            <div class="text-center py-6">
                                <div class="w-20 h-20 bg-lime-100 text-lime-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
                                    <i class="fa-solid fa-check text-4xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">2FA is Currently Enabled</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-md mx-auto">
                                    Your account is protected with 2-Factor Authentication. Every time you log in, you will be prompted for a 6-digit code from your authenticator app.
                                </p>

                                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-center">
                                    <form action="{{ route('admin.2fa.disable') }}" method="POST">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Are you sure you want to disable 2FA? Your account will be less secure.');" 
                                                class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-xl shadow-lg transition-all duration-200 flex items-center gap-2">
                                            <i class="fa-solid fa-power-off"></i> Disable 2FA Authentication
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <!-- 2FA Setup Flow -->
                            <div class="space-y-8">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Step 1: Scan QR Code with Authenticator App</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        Open <strong>Google Authenticator</strong>, <strong>Authy</strong>, or <strong>Microsoft Authenticator</strong> on your phone and scan the QR code below:
                                    </p>

                                    <div class="flex flex-col sm:flex-row items-center gap-6 mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600">
                                        <div class="bg-white p-3 rounded-xl shadow-md border border-gray-200">
                                            <img src="{{ $qrCodeUrl }}" alt="2FA QR Code" class="w-44 h-44 object-contain">
                                        </div>
                                        <div class="space-y-3 text-center sm:text-left">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Manual Secret Key</p>
                                            <code class="text-base font-mono font-bold bg-white dark:bg-gray-800 text-blue-900 dark:text-lime-400 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 inline-block tracking-widest">
                                                {{ $secret }}
                                            </code>
                                            <p class="text-xs text-gray-500">
                                                If you cannot scan the QR code, manually type this 16-character key into your app.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Step 2: Enter Verification Code</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                                        Enter the 6-digit verification code generated by your authenticator app to enable 2FA:
                                    </p>

                                    <form action="{{ route('admin.2fa.enable') }}" method="POST" class="max-w-md space-y-4">
                                        @csrf
                                        <input type="hidden" name="secret" value="{{ $secret }}">

                                        <div>
                                            <label for="code" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">6-Digit App Code</label>
                                            <input type="text" name="code" id="code" required maxlength="6" 
                                                   class="w-full text-center tracking-[0.4em] font-mono text-xl py-3 px-4 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-lime-500 focus:border-transparent" 
                                                   placeholder="000000" autocomplete="off">
                                        </div>

                                        <button type="submit" class="w-full py-3.5 px-6 bg-gradient-to-r from-blue-900 to-lime-600 hover:from-blue-950 hover:to-lime-700 text-white font-bold text-sm rounded-xl shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                                            <i class="fa-solid fa-lock"></i> Enable 2-Factor Authentication
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
