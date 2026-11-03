@extends('user.layout')

@section('title')
{{__('Contact Us')}}
@stop

@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<meta property="og:title" content="{{__('Contact Us')}} | {{ config('app.name') }}"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="Get in touch with Chiremba Express. We're here to help with any questions or support you need."/>
<meta property="og:keyword" content="contact, support, help, Chiremba Express, telemedicine, Chiremba Express contact, Chiremba Express support"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop

@section('content')

<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: url({{ asset('kumbacare/img/carousel-2.jpg') }}) center center/cover no-repeat;">
    <div class="container py-5 text-center">
        <h1 class="display-3 text-white mb-3 animated slideInDown">{{__('Contact Us')}}</h1>
    
    </div>
</div>
<section class="py-16 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mx-auto mb-12 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 600px;">
            <p class="text-primary-500 font-semibold uppercase tracking-wider">Contact Us</p>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mt-2">{{__('Get In Touch')}}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-4">{{__('Have Any Query? Feel Free To Contact Us.')}} Our team is here to provide the support and answers you need for your telemedicine journey.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div class="wow fadeInUp bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 text-center transform transition-all duration-300 hover:scale-105 hover:shadow-2xl" data-wow-delay="0.1s">
                <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-6">
                    <i class="fa fa-map-marker-alt fa-2x"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Our Address</h3>
                <p class="text-gray-600 dark:text-gray-400">{{$setting->address}}</p>
            </div>

            <a href="tel:{{$setting->phone}}" class="wow fadeInUp bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 text-center transform transition-all duration-300 hover:scale-105 hover:shadow-2xl block" data-wow-delay="0.3s">
                <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-6">
                    <i class="fa fa-phone-alt fa-2x"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Call Us</h3>
                <p class="text-primary-600 dark:text-primary-400">{{$setting->phone}}</p>
            </a> {{-- Changed to text-primary-500 for consistency with other links --}}

            <a href="mailto:{{$setting->email}}" class="wow fadeInUp bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 text-center transform transition-all duration-300 hover:scale-105 hover:shadow-2xl block" data-wow-delay="0.5s">
                <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mb-6">
                    <i class="fa fa-envelope-open fa-2x"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Email Us</h3>
                <p class="text-primary-600 dark:text-primary-400">{{$setting->email}}</p>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <div class="wow fadeInUp" data-wow-delay="0.1s">
                <div class="bg-white dark:bg-gray-800 p-8 md:p-12 rounded-lg shadow-lg">
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">{{__('Send Us a Message')}}</h3>

                    {{-- Alpine.js for form messages --}}
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
                             class="mb-6 rounded-lg p-4
                                @if(Session::get('alert-class', 'alert-info') == 'alert-success') bg-green-100 text-green-700 border border-green-200
                                @elseif(Session::get('alert-class', 'alert-info') == 'alert-danger') bg-red-100 text-red-700 border border-red-200
                                @else bg-blue-100 text-blue-700 border border-blue-200 @endif" role="alert">
                            <div class="flex items-center justify-between">
                                <p class="font-medium">{{ Session::get('message') }}</p>
                                <button type="button" @click="showMessage = false" class="text-xl leading-none font-semibold opacity-75 hover:opacity-100">
                                    &times;
                                </button>
                            </div>
                        </div>
                    @endif

                    <form method="post" action="{{url('savecontact')}}" id="contact-form">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__('Your name')}}</label>
                                <input type="text" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200" name="name" id="name" placeholder="{{__('Your Name')}}" required>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__('Your email')}}</label>
                                <input type="email" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200" name="email" id="email" placeholder="{{__('Your Email')}}" required>
                            </div>
                        </div>
                        <div class="mb-6">
                            <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__('Subject')}}</label>
                            <input type="text" class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200" name="subject" id="subject" placeholder="{{__('Subject')}}" required>
                        </div>
                        <div class="mb-8">
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__('Your Message')}}</label>
                            <textarea class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200 min-h-[150px]" name="message" id="message" rows="5" placeholder="{{__('Your Message')}}" required></textarea>
                        </div>
                        <div>
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-lg font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800 transition-transform transform hover:scale-105">
                                {{__('Send Message')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="wow fadeInUp" data-wow-delay="0.5s">
                <div class="h-full rounded-lg overflow-hidden shadow-lg">
                    {{--
                        IMPORTANT: Replace this with your actual Google Maps embed URL.
                        You'll need a Google Cloud API key and proper embedding for a live map.
                        Example: https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=Your+Company+Address
                    --}}
                    <iframe class="w-full h-full min-h-[400px] lg:min-h-full"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3798.575736554964!2d31.03092031538354!3d-17.82425498782148!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1931a5a8a4d3c5c7%3A0x23f9a721a329a10!2sKumba%20Care!5e0!3m2!1sen!2szw!4v1620000000000!5m2!1sen!2szw"
                        style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
@stop

@section('footer')
@stop
