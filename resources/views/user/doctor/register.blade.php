@extends('user.layout')
@section('title')
    Profile Registration
@stop
@section('meta-data')
    <meta property="og:type" content="website" />
    <meta property="og:url" content="KumbaCare" />
    <meta property="og:title" content="Profile Registration" />
    <meta property="og:image" content="{{ asset('image_web/') . '/' . $setting->favicon }}" />
    <meta property="og:image:width" content="250px" />
    <meta property="og:image:height" content="250px" />
    <meta property="og:site_name" content="KumbaCare" />
    <meta property="og:description" content="Register as a Doctor or Pharmacy on KumbaCare." />
    <meta property="og:keyword" content="doctor registration, pharmacy registration, healthcare provider signup" />
    <link rel="shortcut icon" href="{{ asset('image_web/') . '/' . $setting->favicon }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div x-data="{ tab: 'doctor' }" class="max-w-md w-full bg-white dark:bg-gray-800 p-8 sm:p-10 rounded-lg shadow-2xl">
            <div class="text-center">
                <img class="mx-auto h-12 w-auto" src="{{ asset('image_web/') . '/' . $setting->logo }}" alt="KumbaCare">
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                    Create Account
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    Already a provider? 
                    <a href="{{ url('profilelogin') }}" class="font-medium text-primary-200 hover:text-200-600">
                        Login here
                    </a>
                </p>
            </div>

            <div class="mt-6">
                @if (Session::has('message'))
                    <div class="p-4 text-sm {{ Session::get('alert-class', 'text-green-700 bg-green-100 dark:bg-green-700 dark:text-green-100') }} rounded-lg"
                        role="alert">
                        {{ Session::get('message') }}
                    </div>
                @endif
                @if (Session::has('error'))
                    <div class="p-4 text-sm {{ Session::get('alert-class', 'text-red-700 bg-red-100 dark:bg-red-700 dark:text-red-100') }} rounded-lg"
                        role="alert">
                        {{ Session::get('error') }}
                    </div>
                @endif
            </div>

            <div id="registererror" class="text-sm text-red-600 dark:text-red-400"></div>
            <div id="recaptcha-container"></div>

            <!-- Tab Buttons -->
            <div class="mt-8 flex border-b border-gray-200 dark:border-gray-700">
                <button @click="tab = 'doctor'" :class="{ 'border-primary-200 text-primary-600': tab === 'doctor', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': tab !== 'doctor' }" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm focus:outline-none transition-colors duration-200 ease-in-out">
                    Doctor
                </button>
                <button @click="tab = 'pharmacy'" :class="{ 'border-primary-200 text-primary-600': tab === 'pharmacy', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': tab !== 'pharmacy' }" class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm focus:outline-none transition-colors duration-200 ease-in-out">
                    Pharmacy
                </button>
            </div>

            {{-- Doctor Registration Form --}}
            <div x-show="tab === 'doctor'" x-transition class="registration-section pt-8">
                <form action="{{ url('postdoctorregister') }}" method="post" class="space-y-6" id="doctor-register-form">
                    @csrf
                    @include('user.doctor.partials.registration-fields', ['prefix' => 'doctor'])
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-400 hover:bg-secondary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-200 dark:focus:ring-offset-gray-800 transition-all">
                            Register as Doctor
                        </button>
                    </div>
                </form>
            </div>

            {{-- Pharmacy Registration Form --}}
            <div x-show="tab === 'pharmacy'" x-transition style="display: none;" class="registration-section pt-8">
                <form action="{{ url('postpharmacyregister') }}" method="post" class="space-y-6" id="pharmacy-register-form">
                    @csrf
                    @include('user.doctor.partials.registration-fields', ['prefix' => 'pharmacy'])
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-400 hover:bg-secondary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-200 dark:focus:ring-offset-gray-800 transition-all">
                            Register as Pharmacy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
<!-- Include Firebase for potential phone verification on this page in the future -->
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
    if (!firebase.apps.length) firebase.initializeApp(firebaseConfig);
</script>
@stop
