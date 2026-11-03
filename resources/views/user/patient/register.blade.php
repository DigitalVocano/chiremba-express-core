@extends('user.layout')
@section('title')
    {{__('User Register')}}
@stop
@section('meta-data')
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{__('KumbaCare')}}"/>
    <meta property="og:title" content="{{__('User Register')}}"/>
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
        <div x-data="registerForm()" class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 sm:p-10 rounded-lg shadow-2xl">
            <div>
                <img class="mx-auto h-12 w-auto" src="{{ asset('image_web/').'/'.$setting->logo }}" alt="{{__('KumbaCare')}}">
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                    Create Account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    Already have an account? 
                    <a href="{{url('patientlogin')}}" class="font-medium text-primary-600 hover:text-lime-600">
                        Login here
                    </a>
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

            <div id="registererror" class="text-sm text-red-600 dark:text-red-400"></div>
            <div id="recaptcha-container"></div>

            <form id="mainRegisterForm" class="mt-8 space-y-6" action="{{url('userpostregister')}}" method="post" @submit.prevent="handleRegister">
                @csrf
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                        <input type="text" name="name" placeholder="John Doe" required value="{{old('name')}}"
                               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
                        @if ($errors->has('name'))
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('name') }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mobile Number</label>
                        <input type="text" name="phone" x-model="phone" placeholder="+26377..." required
                               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
                        @if ($errors->has('phone'))
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('phone') }}</p>
                        @endif
                    </div>
                    
                    <div x-show="otpSent" x-cloak>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enter 6-digit OTP</label>
                        <input type="text" x-model="otpCode" placeholder="123456"
                               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
                    </div>
                    
                    <div x-show="!otpSent">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address (Optional)</label>
                        <input type="email" name="email" placeholder="Optional" value="{{old('email')}}"
                               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
                        @if ($errors->has('email'))
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('email') }}</p>
                        @endif
                    </div>
                    <div x-show="!otpSent">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input type="password" name="password" id="pwd" placeholder="Enter password"
                               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
                        @if ($errors->has('password'))
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('password') }}</p>
                        @endif
                    </div>
                    <div x-show="!otpSent">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="cpwd" placeholder="Confirm password"
                               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
                        <span id="cpwd_error" class="text-xs text-red-600 dark:text-red-400 mt-1"></span>
                        @if ($errors->has('password_confirmation'))
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('password_confirmation') }}</p>
                        @endif
                    </div>
                    <div>
                        <label for="referred_by_code" class="sr-only">{{__('Referral Code (Optional)')}}</label>
                        <input type="text" name="referred_by_code" id="referred_by_code" placeholder="{{__('Referral Code (Optional)')}}" value="{{old('referred_by_code')}}"
                               class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 focus:outline-none focus:ring-primary-600 focus:border-primary-600 focus:z-10 sm:text-sm"/>
                        @if ($errors->has('referred_by_code'))
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $errors->first('referred_by_code') }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="agree" name="agree" type="checkbox" value="1" required
                           class="h-4 w-4 text-primary-600 focus:ring-primary-600 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:checked:bg-primary-600 dark:focus:ring-offset-gray-800">
                    <label for="agree" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                        {{__('I accept')}} <a href="{{url('/')}}" class="font-medium text-primary-600 hover:text-lime-600">{{__('terms')}}</a> {{__('and')}} <a href="{{url('/')}}" class="font-medium text-primary-600 hover:text-lime-600">{{__('conditions')}}</a> {{__('and general policy')}}
                    </label>
                </div>
                @if ($errors->has('agree'))
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first('agree') }}</p>
                @endif

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800 transition-all">
                        <span x-show="isLoading" class="mr-2"><i class="fas fa-spinner fa-spin"></i></span>
                        <span x-text="buttonText"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('footer_scripts')
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

    if (!firebase.apps.length && firebaseConfig.apiKey) firebase.initializeApp(firebaseConfig);

    document.addEventListener('alpine:init', () => {
        Alpine.data('registerForm', () => ({
            phone: '{{old('phone')}}',
            otpSent: false,
            otpCode: '',
            isLoading: false,
            confirmationResult: null,

            init() {
                window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {'size': 'invisible'});
            },

            get buttonText() {
                if (this.isLoading) return 'Please wait...';
                if (!this.otpSent && this.phone) return 'Verify Phone & Register';
                if (this.otpSent) return 'Confirm OTP & Register';
                return 'Register Now';
            },

            async handleRegister(e) {
                if (document.getElementById('pwd').value !== document.getElementById('cpwd').value) {
                    document.getElementById('cpwd_error').textContent = "Passwords do not match!";
                    return;
                }
                this.isLoading = true;
                let formattedPhone = this.phone.startsWith('+') ? this.phone : '+263' + this.phone;
                if (!this.otpSent) {
                    try {
                        this.confirmationResult = await firebase.auth().signInWithPhoneNumber(formattedPhone, window.recaptchaVerifier);
                        this.otpSent = true;
                        alert("OTP sent to " + formattedPhone);
                    } catch (error) { alert("Error sending OTP: " + error.message); }
                    this.isLoading = false;
                } else {
                    try {
                        await this.confirmationResult.confirm(this.otpCode);
                        document.getElementById('mainRegisterForm').submit();
                    } catch (error) { alert("Invalid OTP code"); this.isLoading = false; }
                }
            }
        }));
    });
</script>
@stop
