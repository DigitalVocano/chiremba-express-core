@extends('user.layout')
@section('title')
{{__('About Us')}}
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<meta property="og:title" content="About Chiremba Express: Your Trusted Telemedicine Partner"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="Chiremba Express is Zimbabwe's premier AI-driven telemedicine provider. We connect patients with local doctors and pharmacies for seamless, affordable healthcare."/>
<meta property="og:keyword" content="About Chiremba Express, Best Telemedicine Zimbabwe, AI Healthcare Zimbabwe, Online Doctors Harare, Digital Health Africa"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')

@if(Session::has('message'))
    <div x-data="{ showMessage: true }"
         x-show="showMessage"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-4"
         x-init="setTimeout(() => showMessage = false, 5000)"
         class="mb-6 rounded-lg p-4 {{ Session::get('alert-class', 'bg-blue-100 text-blue-700') == 'alert-success' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' }}" role="alert">
        <p class="font-medium">{{ Session::get('message') }}</p>
    </div>
@endif

<!-- Page Header Start -->
<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: url({{ asset('kumbacare/img/carousel-2.jpg') }}) center center/cover no-repeat;">
    <div class="container py-5 text-center">
        <h1 class="display-3 text-white mb-3 animated slideInDown">{{__('About Us')}}</h1>

    </div>
</div>
<!-- Page Header End -->

<!-- About Section Start -->
<div class="container-xxl py-5">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div class="wow fadeInUp" data-wow-delay="0.1s">
                <p class="text-primary-500 font-semibold text-lg">About Chiremba Express</p>
                <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-4">Your Health, Our Priority</h1>
                <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-300">
                    {!! $data->about !!}
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 flex items-center justify-center">
                            <i class="bi bi-bullseye text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h5 class="text-lg font-bold text-gray-900 dark:text-white">Our Mission</h5>
                            <p class="text-gray-600 dark:text-gray-400">Connecting patients to better health, effortlessly in Africa.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 flex items-center justify-center">
                            <i class="bi bi-eye text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h5 class="text-lg font-bold text-gray-900 dark:text-white">Our Vision</h5>
                            <p class="text-gray-600 dark:text-gray-400">To become a leading world of compassionate care by 2050.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.5s">
                <div class="relative h-96 rounded-lg overflow-hidden shadow-lg">
                    <img class="w-full h-full object-cover" src="{{ asset('kumbacare/img/about-2.jpg') }}" alt="Chiremba Express Telemedicine">
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-6">
                        <h4 class="text-white text-xl font-bold">Local Care, Global Standards</h4>
                        <p class="text-gray-200 text-sm">Pioneering AI-driven healthcare solutions tailored for Zimbabwe.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- About Section End -->

<!-- Core Values Start -->
<div class="container-fluid bg-gray-50 dark:bg-gray-800 py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="text-primary-500 font-semibold uppercase tracking-wider">Our Values</p>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white">Our Core Principles</h1>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="wow fadeInUp" data-wow-delay="0.1s">
                <div class="bg-white dark:bg-gray-700 text-center h-full p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-6">
                        <i class="fa fa-heartbeat fa-2x"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">Patient-Centric Care</h4>
                    <p class="text-gray-600 dark:text-gray-400">We prioritize your needs, ensuring a seamless and supportive healthcare journey from consultation to recovery.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.3s">
                <div class="bg-white dark:bg-gray-700 text-center h-full p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-6">
                        <i class="fa fa-user-shield fa-2x"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">Privacy & Confidentiality</h4>
                    <p class="text-gray-600 dark:text-gray-400">Your data is protected with the highest security standards. We are committed to maintaining the confidentiality of your personal and medical information.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.5s">
                <div class="bg-white dark:bg-gray-700 text-center h-full p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-6">
                        <i class="fa fa-award fa-2x"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">Excellence & Innovation</h4>
                    <p class="text-gray-600 dark:text-gray-400">We continuously strive for excellence by integrating innovative technology to provide you with the best possible healthcare solutions.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Core Values End -->

<!-- Why Choose Us Section Start -->
<div class="container-xxl py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="text-primary-500 font-semibold uppercase tracking-wider">Why Choose Us</p>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white">Revolutionizing Healthcare in Zimbabwe</h1>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="wow fadeInUp" data-wow-delay="0.1s">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 text-center h-full shadow-md hover:shadow-xl transition-shadow duration-300">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-4">
                        <i class="fas fa-robot fa-2x"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 dark:text-white mb-2">AI-Powered Health</h5>
                    <p class="text-gray-600 dark:text-gray-400">Our advanced AI Health Assistant provides instant symptom analysis and generates preliminary medical reports, available 24/7.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.3s">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 text-center h-full shadow-md hover:shadow-xl transition-shadow duration-300">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-4">
                        <i class="fas fa-map-marked-alt fa-2x"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nationwide Reach</h5>
                    <p class="text-gray-600 dark:text-gray-400">From Harare to Bulawayo and beyond, we connect you with trusted local doctors and pharmacies across Zimbabwe.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.5s">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 text-center h-full shadow-md hover:shadow-xl transition-shadow duration-300">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-4">
                        <i class="fas fa-hand-holding-medical fa-2x"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Affordable Care</h5>
                    <p class="text-gray-600 dark:text-gray-400">With plans like OneHealth starting at just $1/month, we ensure quality healthcare is accessible to every Zimbabwean.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.7s">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 text-center h-full shadow-md hover:shadow-xl transition-shadow duration-300">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-4">
                        <i class="fas fa-user-lock fa-2x"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Secure & Private</h5>
                    <p class="text-gray-600 dark:text-gray-400">We prioritize your privacy with state-of-the-art encryption, ensuring your medical data remains confidential and secure.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Why Choose Us Section End -->

<!-- FAQ Start -->
<div class="container-fluid bg-gray-50 dark:bg-gray-800 py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="text-primary-500 font-semibold uppercase tracking-wider">FAQ</p>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white">Frequently Asked Questions</h1>
        </div>
        <div class="max-w-3xl mx-auto wow fadeInUp" data-wow-delay="0.3s" x-data="{ active: 1 }">
            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 1 ? null : 1)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">What is Chiremba Express?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 1 }">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </button>
                    <div x-show="active === 1" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>Chiremba Express is a telemedicine platform that connects patients with healthcare practitioners for online consultations and appointments.</p>
                        </div>
                    </div>
                </div>
                <!-- FAQ Item 2 -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 2 ? null : 2)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">How does Chiremba Express work?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 2 }">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </button>
                    <div x-show="active === 2" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>Patients can sign up and book appointments with practitioners on the platform. Practitioners can then conduct online consultations and provide treatment plans.</p>
                        </div>
                    </div>
                </div>
                <!-- FAQ Item 3 -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 3 ? null : 3)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">What is KumbaCover Advance?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 3 }">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </button>
                    <div x-show="active === 3" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>KumbaCover Advance is an optional program that helps cover out-of-pocket expenses not covered by insurance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FAQ End -->

@stop
@section('footer')
@stop
