@extends('user.layout')
@section('title')
Doctor Details
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url('viewdoctor', $data->slug) }}"/>
<meta property="og:title" content="{{$data->name}} - {{ $data->departmentls->name ?? '' }} | {{ config('app.name') }}"/>
<meta property="og:image" content="{{asset('public/upload/doctors').'/'.$data->image}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="Book an appointment with {{$data->name}}, a specialist in {{isset($data->departmentls->name)?$data->departmentls->name:''}}. {{ substr($data->aboutus, 0, 150) }}"/>
<meta property="og:keyword" content="{{$data->name}}, doctor, specialist, {{isset($data->departmentls->name)?$data->departmentls->name:''}}, appointment, Chiremba Express"/>
<link rel="shortcut icon" href="{{asset('public/image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">

<meta name="description" content="Book an appointment with {{$data->name}}, a specialist in {{isset($data->departmentls->name)?$data->departmentls->name:''}}. {{ substr($data->aboutus, 0, 150) }}">
<meta name="keywords" content="{{$data->name}}, doctor, physician, healthcare, telemedicine, {{isset($data->departmentls->name)?$data->departmentls->name:''}}, {{isset($data->cityls->name)?$data->cityls->name:''}}, {{isset($data->countryls->name)?$data->countryls->name:''}}">

@stop
@section('content')
@if(empty($data))
Result Not Found
@else

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Physician",
  "name": "{{$data->name}}",
  "image": "{{asset('public/upload/doctors').'/'.$data->image}}",
  "@id": "{{url('viewdoctor', $data->slug)}}",
  "url": "{{url('viewdoctor', $data->slug)}}",
  "telephone": "{{$data->phoneno}}",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{{$data->address}}",
    "addressLocality": "{{isset($data->cityls->name)?$data->cityls->name:''}}",
    "addressRegion": "{{isset($data->statels->name)?$data->statels->name:''}}",
    "postalCode": "{{$data->pincode}}",
    "addressCountry": "{{isset($data->countryls->name)?$data->countryls->name:''}}"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "{{$data->lat}}",
    "longitude": "{{$data->lon}}"
  },
  "openingHoursSpecification": {
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": [
      "Monday",
      "Tuesday",
      "Wednesday",
      "Thursday",
      "Friday",
      "Saturday",
      "Sunday"
    ],
    "opens": "{{$data->working_time}}",
    "closes": "{{$data->working_time}}"
  },
  "medicalSpecialty": "{{isset($data->departmentls->name)?$data->departmentls->name:''}}",
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{round($data->avgratting,1)}}",
    "reviewCount": "{{$data->totalreview}}"
  }
}
</script>

@php
    $showContactInfo = false;

    // Condition 1: Global setting allows visibility for everyone.
    if (isset($setting) && $setting->doctor_contact_visibility == '1') {
        $showContactInfo = true;
    }

    // Condition 2: If contacts are hidden globally, check for a patient's subscription override.
    if (!$showContactInfo && Session::has("user_id") && Session::get("role_id") == '1') {
        $patient = \App\Models\Patient::find(Session::get('user_id'));
        if ($patient) {
            // Find the patient's latest active subscription
            $activeSubscription = \App\Models\Subscriber::with('subscription.features')
                ->where('subscriber_id', $patient->id)
                ->where('subscriber_type', \App\Models\Patient::class)
                ->where('status', '2') // Active
                ->latest('id')
                ->first();

            if ($activeSubscription && $activeSubscription->subscription) {
                // Check if the subscription is still within its valid duration
                $startDate = new \DateTime($activeSubscription->date);
                $duration = $activeSubscription->subscription->duration;
                $unit = $activeSubscription->subscription->duration_unit;
                if (is_numeric($duration) && !empty($unit)) {
                    // Clone the start date to avoid modifying the original object
                    $expiryDate = (clone $startDate)->modify('+' . $duration . ' ' . \Str::plural($unit, $duration));
                    if ($expiryDate > new \DateTime("now", $startDate->getTimezone())) {
                        // Check if the active subscription has the 'force_contact_visibility' feature.
                        if ($activeSubscription->subscription->features->where('code', 'force_contact_visibility')->isNotEmpty()) {
                            $showContactInfo = true;
                        }
                    }
                }
            }
        }
    }
@endphp

<!-- Page Header Start -->
<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: url({{ asset('kumbacare/img/carousel-1.jpg') }}) center center/cover no-repeat;">
    <div class="container py-5 text-center">
        <h1 class="display-4 text-white mb-3 animated slideInDown">{{$data->name}}</h1>
       
    </div>
</div>
<!-- Page Header End -->

<section class="py-16 bg-gray-50 dark:bg-gray-900">
   <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
         
         <!-- Left Sidebar -->
         <div class="lg:col-span-1">
            <div class="sticky top-28">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="text-center">
                        @php
                            $initials = \App\Helpers\Helper::getInitials($data->name ?? 'Doctor');
                            $hasCustomImage = !empty($data->image) && !in_array($data->image, ['default.png', 'user.png', 'profile.png', 'defaultpharmacy.png', 'null']);
                            $path = $hasCustomImage ? asset('upload/doctors/' . $data->image) : '';
                        @endphp
                        <div class="relative w-32 h-32 mx-auto">
                            @if($hasCustomImage)
                                <img class="w-32 h-32 rounded-full mx-auto border-4 border-primary-200 dark:border-primary-700 shadow-md object-cover" 
                                     src="{{$path}}" 
                                     alt="{{ $data->name }}"
                                     onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
                            @endif
                            <div class="w-32 h-32 rounded-full mx-auto border-4 border-primary-200 dark:border-primary-700 shadow-md flex items-center justify-center text-white text-3xl font-extrabold tracking-wider bg-gradient-to-br from-primary-600 via-primary-500 to-emerald-600 select-none shadow-inner"
                                 style="{{ $hasCustomImage ? 'display: none;' : 'display: flex;' }}">
                                {{ $initials }}
                            </div>
                        </div>
                        <h2 class="text-2xl font-bold mt-4 text-gray-900 dark:text-white">{{$data->name}}</h2>
                        <p class="text-primary-600 dark:text-primary-400 font-medium">{{isset($data->departmentls->name)?$data->departmentls->name:''}}</p>
                        <div class="flex items-center justify-center mt-2">
                            @php $rating = round($data->avgratting); @endphp
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                            <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{$data->totalreview ?? 0}} reviews)</span>
                        </div>
                    </div>
                    <div class="mt-6">
                        <div class="flex items-start py-3 border-b border-gray-200 dark:border-gray-700">
                            <i class="fas fa-map-marker-alt text-primary-500 mt-1 w-5 text-center"></i>
                            <span class="ml-3 text-gray-700 dark:text-gray-300">{{ $data->address }}</span>
                        </div>
                        @if($showContactInfo)
                            <div class="flex items-center py-3 border-b border-gray-200 dark:border-gray-700">
                                <i class="fas fa-phone text-primary-500 w-5 text-center"></i>
                                <a href="tel:{{$data->phoneno}}" class="ml-3 text-gray-700 dark:text-gray-300 hover:text-primary-500">{{$data->phoneno}}</a>
                            </div>
                        @endif
                        <div class="flex items-center py-3">
                            <i class="fas fa-clock text-primary-500 w-5 text-center"></i>
                            <span class="ml-3 text-gray-700 dark:text-gray-300">{{ $data->working_time }}</span>
                        </div>
                    </div>
                    <div class="mt-6 space-y-3">
                        @if($showContactInfo)
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{$data->lat}},{{$data->lon}}" target="_blank" class="w-full flex items-center justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <i class="fas fa-directions mr-2"></i> Take Me There
                            </a>
                        @else
                            {{-- This block shows if contact info is hidden and the user is a logged-in patient --}}
                            @if(Session::has("user_id") && Session::get("role_id") == '1')
                                <div class="bg-yellow-100 dark:bg-yellow-900/30 border-l-4 border-yellow-500 text-yellow-700 dark:text-yellow-300 p-4" role="alert">
                                    <p class="font-bold">Unlock Full Access</p>
                                    <p>To view contact details and get directions, please <a href="{{ route('patient.subscription_plan') }}" class="font-semibold underline hover:text-yellow-800 dark:hover:text-yellow-200">upgrade your subscription plan</a>.</p>
                                </div>
                            @endif
                        @endif
                        @if($data->is_fav=='0')
                            @if(empty(Session::has("user_id")))
                                <a href="{{url('patientlogin')}}" id="favdoc{{$data->id}}" class="w-full flex items-center justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="far fa-heart mr-2"></i> {{__('Add to Favorites')}}
                                </a>
                            @else
                                <a href="javascript:void(0);" onclick="userfavorite('{{$data->id}}', this)" id="favdoc{{$data->id}}" class="w-full flex items-center justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="far fa-heart mr-2"></i> {{__('Add to Favorites')}}
                                </a>
                            @endif
                        @else
                            <a href="javascript:void(0);" onclick="userfavorite('{{$data->id}}', this)" class="activefav w-full flex items-center justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" id="favdoc{{$data->id}}">
                                <i class="fas fa-heart mr-2"></i> {{__('Remove from Favorites')}}
                            </a>
                        @endif
                        <div class="relative w-full" x-data="{ open: false }">
                            <button @click="open = !open" class="w-full flex items-center justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" type="button" aria-haspopup="true" :aria-expanded="open">
                                <i class="fas fa-share-alt mr-2"></i> Share Profile
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-full origin-top-right rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none z-10" style="display: none;" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                                <div class="py-1">
                                    <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="https://www.facebook.com/sharer/sharer.php?u={{url('viewdoctor', $data->slug)}}" target="_blank" role="menuitem"><i class="fab fa-facebook-f mr-2"></i> Facebook</a>
                                    <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="https://web.whatsapp.com/send?text={{url('viewdoctor', $data->slug)}}" target="_blank" role="menuitem"><i class="fab fa-whatsapp mr-2"></i> WhatsApp</a>
                                    <a class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600" href="https://twitter.com/intent/tweet?text={{$data->name}}&url={{url('viewdoctor', $data->slug)}}" target="_blank" role="menuitem"><i class="fab fa-twitter mr-2"></i> Twitter</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         </div>

         <!-- Right Content -->
         <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div x-data="{ tab: 'about' }">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                            <button @click="tab = 'about'" :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': tab === 'about', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-200 dark:hover:border-gray-600': tab !== 'about' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">About Us</button>
                            <button @click="tab = 'services'" :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': tab === 'services', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-200 dark:hover:border-gray-600': tab !== 'services' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Services</button>
                            <button @click="tab = 'healthcare'" :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': tab === 'healthcare', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-200 dark:hover:border-gray-600': tab !== 'healthcare' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Health Care</button>
                            <button @click="tab = 'reviews'" :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': tab === 'reviews', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-200 dark:hover:border-gray-600': tab !== 'reviews' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Reviews</button>
                        </nav>
                    </div>

                    <div class="py-6">
                        <div x-show="tab === 'about'">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">About {{$data->name}}</h3>
                            <div class="prose dark:prose-invert max-w-none">
                                <p>{{$data->aboutus}}</p>
                            </div>
                        </div>
                        <div x-show="tab === 'services'">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Services</h3>
                            <div class="prose dark:prose-invert max-w-none">
                                <p>{{$data->services}}</p>
                            </div>
                        </div>
                        <div x-show="tab === 'healthcare'">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Health Care</h3>
                            <div class="prose dark:prose-invert max-w-none">
                                <p>{{$data->healthcare}}</p>
                            </div>
                        </div>
                        <div x-show="tab === 'reviews'">
                            @include('user.partials.review-section', ['data' => $data])
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointment Booking Form -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mt-8">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Book Appointment</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-4">Monday to Sunday: {{$data->working_time}}</p>

                @if(Session::has('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ Session::get('message') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php $currency = explode("-", $setting->currency); @endphp
                {{-- PHP block for fetching wallet balance and consultation fees --}}
                @php
                    $patientWalletBalance = null;
                    $patientWalletUrl = url('userdashboard');
                    $consultationFees = $data->consultation_fees;

                    if (Session::has("user_id") && Session::get("role_id") == '1') {
                        $patient = \App\Models\Patient::with('wallet')->find(Session::get('user_id'));
                        if ($patient && $patient->wallet) {
                            $patientWalletBalance = $patient->wallet->balance;
                        }
                    }
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <!-- Consultation Fee -->
                    <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg text-center border border-blue-200 dark:border-blue-800">
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-300">{{__('Platform Fee')}}</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-200 mt-1">{{ $currency[1] }}{{ number_format($consultationFees, 2) }}</p>
                    </div>

                    <!-- Wallet Balance -->
                    @if(Session::has("user_id") && Session::get("role_id") == '1' && $patientWalletBalance !== null)
                        <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg text-center border border-green-200 dark:border-green-800">
                            <p class="text-sm font-medium text-green-800 dark:text-green-300">{{__('Your Wallet')}}</p>
                            <p class="text-2xl font-bold text-green-900 dark:text-green-200 mt-1">{{ $currency[1] }}{{ number_format($patientWalletBalance, 2) }}</p>
                        </div>
                    @endif
                </div>

                @if(Session::has("user_id") && Session::get("role_id") == '1')
                    @if($patientWalletBalance !== null)
                        @if($patientWalletBalance < $consultationFees)
                            <div class="bg-red-100 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 mb-4" role="alert">
                                <p class="font-bold">{{__('Insufficient Balance')}}</p>
                                <p>{{__('Please')}} <a href="{{ $patientWalletUrl }}" class="font-semibold underline hover:text-red-800 dark:hover:text-red-200">{{__('top up your wallet')}}</a> {{__('to book this appointment.')}}</p>
                            </div>
                            <script>document.addEventListener('DOMContentLoaded', function() { const submitButton = document.querySelector('#payment-form button[type="submit"]'); if (submitButton) { submitButton.disabled = true; submitButton.classList.add('opacity-50', 'cursor-not-allowed'); } });</script>
                        @else
                            <div class="bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 mb-4" role="alert">
                                <p class="font-bold">{{__('Sufficient Balance')}}</p>
                                <p>{{__('You have enough funds in your wallet for this appointment.')}}</p>
                            </div>
                        @endif
                    @else
                        <div class="bg-yellow-100 dark:bg-yellow-900/30 border-l-4 border-yellow-500 text-yellow-700 dark:text-yellow-300 p-4 mb-4" role="alert">
                            <p class="font-bold">{{__('Wallet Not Found')}}</p>
                            <p>{{__('Please')}} <a href="{{ $patientWalletUrl }}" class="font-semibold underline hover:text-yellow-800 dark:hover:text-yellow-200">{{__('visit your dashboard')}}</a> {{__('to set up your wallet.')}}</p>
                        </div>
                        <script>document.addEventListener('DOMContentLoaded', function() { const submitButton = document.querySelector('#payment-form button[type="submit"]'); if (submitButton) { submitButton.disabled = true; submitButton.classList.add('opacity-50', 'cursor-not-allowed'); } });</script>
                    @endif
                @endif

                <form action="{{url('makeappointment')}}" method="post" id="payment-form">
                    @csrf
                    <input type="hidden" name="doctor_id" id="doctor_id" value="{{$data->id}}">
                    <input type="hidden" name="consultation_fees" value="{{$data->consultation_fees}}">
                    <input type="hidden" name="payment_type" value="wallet"> {{-- Force payment type to wallet --}}

                    <div class="mb-4">
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Appointment Date</label>
                        <div class="relative">
                            <input type="text" name="date" id="date" value="{{date('m/d/Y')}}" onchange="slotdivchange(this.value)" class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="slottime" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Time Range</label>
                        <select name="slottime" id="slottime" onchange="slotchange(this.value)" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                            {{-- Options will be populated by JavaScript --}}
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Available Slots</label>
                        <div id="slotdiv" class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                            {{-- Slots will be populated by JavaScript --}}
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="phone_no" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                        <input type="text" name="phone_no" id="phone_no" placeholder="Enter Your Phone number" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                    </div>

                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                        <textarea id="message" rows="4" name="message" placeholder="Enter Your Message" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200"></textarea>
                    </div>

                    <div class="mt-6">
                        @if(Session::has("user_id") && Session::get("role_id") == '1')
                            <button type="submit" class="w-full inline-flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Book Appointment <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        @else
                            <button type="button" onclick="pleaselogin()" class="w-full inline-flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Book Appointment <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        @endif
                    </div>
                </form>
            </div>
         </div>
      </div>
   </div>
</section>
@endif
@stop

@section('footer_scripts')
<script src="https://js.braintreegateway.com/web/dropin/1.23.0/js/dropin.min.js"></script>
<script>
$(document).ready(function() {

    // Removed changeform function as payment type is now fixed to 'wallet'
    // Removed bookshow function as it's no longer needed with direct form submission

    // Removed Braintree initialization as it's not used for direct payment anymore

    // Initialize datepicker and prevent selecting past dates
    $("#date").datepicker({ minDate: 0 });

    // This function will be called when the date changes
    window.slotdivchange = function(date_val) { // Made global for onchange attribute
        $('#slotdiv').html('<p class="text-gray-500 text-sm col-span-full">Loading time ranges...</p>');
        var doctor_id = $("#doctor_id").val();
        // First, get the schedules (time ranges) for the selected date
        $.ajax({
            url: "{{ url('getschedule') }}", // Route without path parameters
            data: { date: date_val, doctor_id: doctor_id }, // Send as query parameters
            dataType: 'json', // Expect a JSON response
            success: function(response) {
                console.log('getschedule response:', response); // ADDED LOG
                var schedules = response;
                var slottimeSelect = $('#slottime');
                slottimeSelect.empty(); // Clear existing options

                if (schedules.length > 0) {
                    $.each(schedules, function(index, schedule) {
                        slottimeSelect.append($('<option>', {
                            value: schedule.id, // schedule.id is the schedule_id
                            text: schedule.start_time + ' - ' + schedule.end_time // Corrected to use start_time and end_time
                        }));
                    });
                    // Trigger change on slottime to load slots for the first schedule
                    // This ensures slots are loaded immediately after schedules are fetched
                    slottimeSelect.trigger('change');
                } else {
                    slottimeSelect.append($('<option>', {
                        value: '',
                        text: 'No schedules found for this date'
                    }));
                    $('#slotdiv').html('<p class="text-red-500 text-sm col-span-full">No schedules available for this date.</p>');
                }
            },
            error: function(xhr) {
                console.error("Error fetching schedules:", xhr.responseText);
                $('#slottime').empty().append($('<option>', { value: '', text: 'Error loading schedules' }));
                $('#slotdiv').html('<p class="text-red-500 text-sm col-span-full">Error loading schedules.</p>');
            }
        });
    };

    // This function will be called when the time range (slottime) changes
    window.slotchange = function(schedule_id_val) { // Made global for onchange attribute
        var doctor_id = $("#doctor_id").val(); // Ensure doctor_id is correctly retrieved
        $('#slotdiv').html('<p class="text-gray-500 text-sm col-span-full">Loading available slots...</p>');
        var date = $("#date").val(); // Get the current date from the date input

        if (!schedule_id_val) { // If no schedule is selected (e.g., "No schedules found")
            $('#slotdiv').html('<p class="text-red-500 text-sm col-span-full">Please select a time range.</p>');
            return;
        }

        // Now, get the slots for the selected schedule
        $.ajax({
            url: "{{ url('getslotlist') }}",
            data: { schedule_id: schedule_id_val, date: date, doctor_id: doctor_id }, // Pass schedule_id
            dataType: 'json', // Expect a JSON response
            success: function(response) {
                console.log('getslotlist response:', response); // ADDED LOG
                var slots = response;
                var slotdiv = $('#slotdiv');
                slotdiv.empty(); // Clear existing slots

                if (slots.length > 0) {
                    $.each(slots, function(index, slot) { // Now 'slots' is directly the array of slot objects
                        var isBooked = slot.is_book == '1';
                        var disabledAttr = isBooked ? 'disabled' : '';
                        var lineThroughClass = isBooked ? 'line-through' : '';
                        var textColorClass = isBooked ? 'text-gray-500 dark:text-gray-400' : 'text-gray-700 dark:text-gray-300';

                        slotdiv.append(`
                            <div class="flex items-center">
                                <input type="radio" value="${slot.id}#${slot.slot}" name="slot" id="slot-${slot.id}" ${disabledAttr} class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                <label for="slot-${slot.id}" class="ml-2 block text-sm font-medium ${textColorClass} ${lineThroughClass}">${slot.slot}</label>
                            </div>
                        `);
                    });
                } else {
                    slotdiv.html('<p class="text-red-500 text-sm col-span-full">No available slots for this time range.</p>');
                }
            },
            error: function(xhr) {
                console.error("Error fetching slots:", xhr.responseText);
                $('#slotdiv').html('<p class="text-red-500 text-sm col-span-full">Error loading slots.</p>');
            }
        });
    };

    // Initial call on document ready to populate schedules and slots for the default date
    // This will trigger the slotdivchange function on page load with the initial date value
    if ($("#date").length) {
        slotdivchange($("#date").val());
    }
});
</script>

@stop
