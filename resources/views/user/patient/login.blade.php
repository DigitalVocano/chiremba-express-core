@extends('user.layout')
@section('title')
    {{__('User Login')}}
@stop
@section('meta-data')
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{__('KumbaCare')}}"/>
    <meta property="og:title" content="{{__('User Login')}}"/>
    <meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
    <meta property="og:image:width" content="250px"/>
    <meta property="og:image:height" content="250px"/>
    <meta property="og:site_name" content="{{__('KumbaCare')}}"/>
    <meta property="og:description" content="{{__('meta_description')}}"/>
    <meta property="og:keyword" content="{{__('Meta Keyword')}}"/>
    <link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
    {{-- <meta name="viewport" content="width=device-width, initial-scale=1"> --}}
@stop
@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div x-data="loginForm('patient')" class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 sm:p-10 rounded-lg shadow-2xl">
            <div>
                <img class="mx-auto h-12 w-auto" src="{{ asset('image_web/').'/'.$setting->logo }}" alt="{{__('KumbaCare')}}">
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                    Welcome
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400"> {{-- Removed 'Or' as it's not needed here --}}
                    Enter your email or phone number to continue.
                </p>
            </div>

            @if(Session::has('message'))
            <div class="p-4 text-sm {{ Session::get('alert-class', 'text-green-700 bg-green-100 dark:bg-green-700 dark:text-green-100') }} rounded-lg" role="alert">
                {{ Session::get('message') }}
            </div>
            @endif
             @if(Session::has('error'))
            <div class="p-4 text-sm {{ Session::get('alert-class', 'text-red-700 bg-red-100 dark:bg-red-700 dark:text-red-100') }} rounded-lg" role="alert">
                {{ Session::get('error') }}
            </div>
            @endif

            <div id="error" class="text-sm text-red-600 dark:text-red-400"></div> {{-- For JS errors --}}
            <div id="recaptcha-container"></div>

            <form id="mainLoginForm" class="mt-8 space-y-6" action="{{url('postloginuser')}}" method="post" @submit.prevent="handleLogin">
                @csrf
                <input type="hidden" name="auth_type" x-model="authType">
                <input type="hidden" name="login_type" value="1">
                
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email or Mobile Number</label>
                        <input name="email" type="text" autocomplete="email" required x-model="loginId" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200"
                               placeholder="Email Address or Mobile Number (+26377...)">
                        @if ($errors->has('email'))
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    {{-- Password Field (Used for both Email and Mobile Number login with password) --}}
                    <div x-show="authType === 'password'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 mt-4">{{__('Password')}}</label>
                        <input name="password" type="password" autocomplete="current-password" :required="authType === 'password'" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200 mt-1"
                               placeholder="{{__('Enter password')}}">
                        @if ($errors->has('password'))
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('password') }}</p>
                        @endif
                    </div>
                    
                    {{-- OTP Code Field --}}
                    <div x-show="authType === 'otp' && otpSent" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 mt-4">Enter 6-digit OTP</label>
                        <input type="text" name="otp" x-model="otpCode" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200 mt-1"
                               placeholder="123456">
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <div class="flex items-center" x-show="authType === 'password'">
                        <input id="remember-me" name="rem_me" type="checkbox" value="1" {{ isset($_COOKIE['rem_me']) ? 'checked' : '' }} class="h-4 w-4 text-primary-500 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:checked:bg-primary-500 dark:focus:ring-offset-gray-800">
                        <label for="remember-me" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                            {{__('Remember me')}}
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="{{url('forgotpassword')}}" class="font-medium text-primary-500 hover:text-primary-600">
                            {{__("Forgot Password")}}?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800 transition-all">
                        <span x-show="isLoading" class="mr-2"><i class="fas fa-spinner fa-spin"></i></span>
                        <span x-text="buttonText"></span>
                    </button>
                </div>

                {{-- Option to switch between Password and SMS OTP --}}
                <div class="text-center mt-3">
                    <button type="button" @click="toggleAuthType" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                        <span x-show="authType === 'password'">Login using SMS OTP code instead</span>
                        <span x-show="authType === 'otp'">Login using Password instead</span>
                    </button>
                </div>

                {{-- Social Login (Google) --}}
                <div class="mt-6">
                     <a href="{{ route('auth/google') }}" class="w-full flex items-center justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-gray-800">
                        <img class="h-5 w-5 mr-2" src="https://www.svgrepo.com/show/355037/google.svg" alt="Google sign-in">
                        {{__('Login with Google')}}
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('footer_scripts')
<!-- Firebase Compat Libraries -->
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-auth-compat.js"></script>
<script>
    const firebaseConfig = {
        apiKey: "{{ env('FIREBASE_WEB_API_KEY', '') }}",
        authDomain: "{{ env('FIREBASE_AUTH_DOMAIN', '') }}",
        databaseURL: "{{ env('FIREBASE_DATABASE_URL', '') }}",
        projectId: "{{ env('FIREBASE_PROJECT_ID', '') }}",
        storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET', '') }}",
        messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID', '') }}",
        appId: "{{ env('FIREBASE_APP_ID', '') }}",
        measurementId: "{{ env('FIREBASE_MEASUREMENT_ID', '') }}"
    };

    if (!firebase.apps.length && firebaseConfig.apiKey) {
        firebase.initializeApp(firebaseConfig);
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('loginForm', () => ({
            loginId: '{{isset($_COOKIE['email'])?$_COOKIE['email']:''}}',
            otpSent: false,
            otpCode: '',
            authType: 'password',
            isLoading: false,
            confirmationResult: null,
            
            init() {
                window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                    'size': 'invisible',
                });
            },

            get buttonText() {
                if (this.isLoading) return 'Please wait...';
                if (this.authType === 'password') return 'Login';
                if (!this.otpSent) return 'Send OTP Code';
                return 'Verify & Login';
            },

            toggleAuthType() {
                if (this.authType === 'password') {
                    this.authType = 'otp';
                } else {
                    this.authType = 'password';
                    this.otpSent = false;
                }
            },

            async handleLogin() {
                if (this.authType === 'password') {
                    document.getElementById('mainLoginForm').submit();
                    return;
                }

                // Phone Login via SMS OTP Flow
                if (!this.loginId) {
                    alert("Please enter your mobile number");
                    return;
                }

                this.isLoading = true;
                let phone = this.loginId.startsWith('+') ? this.loginId : '+263' + this.loginId.replace(/^0/, '');

                if (!this.otpSent) {
                    try {
                        const res = await fetch('/api/send-otp', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ phone }) }); const data = await res.json(); if (String(data.success) === '1') { this.otpSent = true; alert(data.message || ('OTP sent to ' + phone)); } else { throw new Error(data.message || 'Failed to send OTP'); }
                    } catch (error) {
                        alert("Error sending OTP: " + error.message);
                    }
                    this.isLoading = false;
                } else {
                    try {
                        if (!this.otpCode || this.otpCode.trim().length < 4) { throw new Error('Invalid OTP'); }
                        document.getElementById('mainLoginForm').submit();
                    } catch (error) {
                        alert("Invalid OTP code");
                        this.isLoading = false;
                    }
                }
            }
        }));
    });
</script>
@stop
