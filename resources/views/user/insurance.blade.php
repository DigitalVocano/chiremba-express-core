@extends('user.layout')
@section('title')
OneHealth Insurance
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<meta property="og:title" content="OneHealth Insurance: Affordable Healthcare for All Zimbabweans | {{ config('app.name') }}"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="Discover OneHealth Insurance by Chiremba Express. Affordable healthcare for just $1/month, including free consultations and medication cover. Join today!"/>
<meta property="og:keyword" content="OneHealth, Chiremba Express, health insurance, affordable healthcare, Zimbabwe, medical aid"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')

<!-- Page Header Start -->
<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: url({{ asset('kumbacare/img/carousel-1.jpg') }}) center center/cover no-repeat;">
    <div class="container py-5 text-center">
        <h1 class="display-3 text-white mb-3 animated slideInDown">{{__('OneHealth Insurance')}}</h1>

    </div>
</div>
<!-- Page Header End -->

<!-- Insurance Details Start -->
<div class="container-xxl py-16">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="wow fadeInUp" data-wow-delay="0.1s">
                <p class="text-primary-500 font-semibold uppercase tracking-wider">OneHealth Insurance</p>
                <h1 class="text-4xl font-bold text-gray-800 dark:text-white mt-2 mb-4">Affordable Healthcare for All Zimbabweans</h1>
                <p class="text-gray-600 dark:text-gray-400 mb-4">OneHealth Insurance is a pioneering healthcare solution designed to make quality healthcare accessible to all Zimbabweans. Our mission is to empower individuals and families by providing affordable, comprehensive health coverage.</p>
                <p class="text-gray-600 dark:text-gray-400 mb-6">By offering a low monthly premium of just $1, we aim to break down financial barriers and ensure that everyone has the opportunity to receive the medical care they need. With our nationwide network of healthcare providers and real-time claims processing, we strive to deliver a seamless and hassle-free experience.</p>
           <div class="text-center">
    <a href="{{ url('subscription_plan') }}" class="inline-block bg-primary-500 text-white font-semibold py-3 px-6 rounded-md hover:bg-primary-600 transition-colors">Join Now for $1/Month</a>
</div>

           </div>
            <div class="wow fadeInUp flex justify-center items-center" data-wow-delay="0.5s">
                {{-- Display the user's GIF directly, styled for a clean look --}}
                <img src="{{asset('kumbacare/img/insurance-1.webp')}}" class="max-w-xs md:max-w-sm rounded-lg shadow-xl" alt="OneHealth Insurance purchase on mobile">
            </div>
        </div>
    </div>
</div>
<!-- Insurance Details End -->

<!-- Benefits Start -->
<div class="container-fluid bg-gray-50 dark:bg-gray-800 py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="text-primary-500 font-semibold uppercase tracking-wider">Our Benefits</p>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mt-2">Why Join OneHealth Insurance?</h1>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="wow fadeInUp" data-wow-delay="0.1s">
                <div class="bg-white dark:bg-gray-700 text-center h-full p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-300 mb-6">
                        <i class="fa fa-stethoscope fa-2x"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">Free Consultation Cover</h4>
                    <p class="text-gray-600 dark:text-gray-400">Get unlimited consultations with general practitioners, council clinics, and hospital outpatient services.</p>
                </div>
            </div>
            <div class="wow fadeInUp" data-wow-delay="0.3s">
                <div class="bg-white dark:bg-gray-700 text-center h-full p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-300 mb-6">
                        <i class="fa fa-pills fa-2x"></i>
                    </div>
                    <h4 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">$30 Medication Cover</h4>
                    <p class="text-gray-600 dark:text-gray-400">Receive $30 worth of medication every month, redeemable at any pharmacy in Zimbabwe.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Benefits End -->

<!-- How It Works Start -->
<div class="container-xxl py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="text-primary-500 font-semibold uppercase tracking-wider">Process</p>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mt-2">How to Get Started</h1>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="relative p-6 wow fadeInUp" data-wow-delay="0.1s">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gray-100 dark:text-gray-700 font-bold text-9xl z-0">1</div>
                <div class="relative z-10">
                    <div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-300 mx-auto mb-4">
                        <i class="fab fa-whatsapp fa-2x"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Join via WhatsApp</h3>
                    <p class="text-gray-600 dark:text-gray-400">Register for OneHealth Insurance quickly and easily through WhatsApp.</p>
                </div>
            </div>
            <div class="relative p-6 wow fadeInUp" data-wow-delay="0.3s">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gray-100 dark:text-gray-700 font-bold text-9xl z-0">2</div>
                <div class="relative z-10">
                    <div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-300 mx-auto mb-4">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Subscribe for $1</h3>
                    <p class="text-gray-600 dark:text-gray-400">Pay your affordable monthly premium of just $1 to activate your coverage.</p>
                </div>
            </div>
            <div class="relative p-6 wow fadeInUp" data-wow-delay="0.5s">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gray-100 dark:text-gray-700 font-bold text-9xl z-0">3</div>
                <div class="relative z-10">
                    <div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-300 mx-auto mb-4">
                        <i class="fas fa-hand-holding-medical fa-2x"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Access Your Benefits</h3>
                    <p class="text-gray-600 dark:text-gray-400">After a 3-month waiting period, visit any general practitioner or clinic and use your benefits.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- How It Works End -->

<!-- FAQ Start -->
<div class="container-fluid bg-gray-50 dark:bg-gray-800 py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="text-primary-500 font-semibold uppercase tracking-wider">FAQ</p>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mt-2">Frequently Asked Questions</h1>
        </div>
        <div class="max-w-3xl mx-auto wow fadeInUp" data-wow-delay="0.3s" x-data="{ active: 1 }">
            <div class="space-y-4">
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 1 ? null : 1)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">How much does OneHealth Insurance cost?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 1 }"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div x-show="active === 1" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>OneHealth Insurance offers a very affordable monthly premium of just $1. This small investment provides you with significant healthcare benefits.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 2 ? null : 2)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">What is the waiting period?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 2 }"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div x-show="active === 2" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>There is a 3-month waiting period from the date of registration. This means you can start utilizing most of the benefits after 3 months of continuous membership.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 3 ? null : 3)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">How do I pay my premiums?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 3 }"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div x-show="active === 3" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>Paying your monthly premium is simple and convenient. You can easily pay via WhatsApp, making it hassle-free to stay up-to-date with your coverage.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <button @click="active = (active === 4 ? null : 4)" class="w-full flex items-center justify-between text-left p-6">
                        <span class="text-lg font-semibold text-gray-800 dark:text-white">Where can I use my OneHealth Insurance?</span>
                        <span class="text-primary-500 transform transition-transform" :class="{ 'rotate-180': active === 4 }"><i class="fas fa-chevron-down"></i></span>
                    </button>
                    <div x-show="active === 4" x-collapse>
                        <div class="p-6 pt-0 text-gray-600 dark:text-gray-300">
                            <p>OneHealth Insurance provides nationwide coverage. You can access healthcare services at any registered healthcare provider across Zimbabwe, ensuring you have peace of mind wherever you go.</p>
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