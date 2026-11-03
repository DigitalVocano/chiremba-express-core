@extends('user.layout') {{-- Ensure this layout includes Tailwind CSS, Alpine.js, and Font Awesome --}}

@section('title')
{{__('Privacy Policy')}}
@stop

@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}"/> {{-- Changed to current URL for dynamic meta --}}
<meta property="og:title" content="{{__('Privacy Policy')}} | {{ config('app.name') }}"/> {{-- Better title for SEO --}}
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/> {{-- Use config app name --}}
<meta property="og:description" content="{{__('Read Chiremba Express\'s Privacy Policy to understand how we collect, use, and protect your personal information.')}}"/> {{-- Specific description --}}
<meta property="og:keyword" content="{{__('privacy policy, Chiremba Express, data protection, personal information, terms and conditions')}}"/> {{-- Specific keywords --}}
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop

@section('content')

{{-- Hero Section (from previous refactoring, if applicable, for consistency) --}}
{{-- If you have a shared hero component, use it here. Otherwise, a simple header: --}}
<section class="relative w-full py-16 sm:py-20 lg:py-24 overflow-hidden bg-cover bg-center"
    style="background-image: url({{ asset('kumbacare/img/carousel-2.jpg') }});">
    <div class="absolute inset-0 bg-gradient-to-r from-primary-700 to-secondary-500 opacity-70"></div>
    <div class="relative container mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white mb-4 animated slideInDown">
            {{__('Privacy Policy')}}
        </h1>
        <nav aria-label="breadcrumb" class="animated slideInDown">
            <ol class="flex justify-center items-center space-x-2 text-white text-lg sm:text-xl font-medium uppercase">
                <li class="flex items-center">
                    <a class="hover:text-primary-600 transition-colors duration-200" href="{{url('/')}}">{{__('Home')}}</a>
                    <i class="fas fa-chevron-right text-sm ml-2"></i>
                </li>
                <li class="text-primary-600" aria-current="page">{{__('Privacy Policy')}}</li>
            </ol>
        </nav>
    </div>
</section>

---

{{-- Privacy Policy Content Section (Refactored from about-style-two) --}}
<section class="py-16 md:py-24 bg-white dark:bg-gray-800 transition-colors duration-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
        <div class="flex flex-col lg:flex-row items-start lg:space-x-12"> {{-- Changed to flex for better control --}}
            <div class="w-full">
                <div class="max-w-4xl mx-auto lg:mx-0"> {{-- Centered on smaller screens, left-aligned on large --}}
                    <div class="text-center lg:text-left mb-10">
                        <p class="text-sm font-semibold uppercase text-primary-600 dark:text-primary-400 mb-2">{{__('Our Commitment')}}</p>
                        <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white leading-tight">
                            {{__('Your Data Privacy is Our Priority')}}
                        </h2>
                    </div>

                    {{-- Dynamic privacy content --}}
                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed text-lg">
                        {!! $data->trems !!} {{-- Assuming 'trems' holds the privacy policy content --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

---

{{-- FAQ Section --}}
<section class="py-16 md:py-24 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
        <div class="flex flex-col lg:flex-row items-center lg:space-x-12">
            <div class="w-full lg:w-1/2 mb-12 lg:mb-0">
                <div class="relative flex justify-center lg:justify-start">
                    <div class="absolute inset-0 bg-primary-100 dark:bg-primary-900 rounded-full scale-105 opacity-50 transform translate-x-1/4 translate-y-1/4 hidden lg:block"></div>
                    <figure class="relative z-10">
                        <img src="{{asset('front_pro/assets/images/resource/faq-1.png')}}" alt="Frequently Asked Questions" class="rounded-lg shadow-lg max-w-full h-auto">
                    </figure>
                </div>
            </div>
            <div class="w-full lg:w-1/2">
                <div class="text-center lg:text-left mb-8">
                    <p class="text-sm font-semibold uppercase text-primary-600 dark:text-primary-400 mb-2">{{__('Got Questions?')}}</p>
                    <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white leading-tight">
                        {{__('Frequently Asked Questions.')}}
                    </h2>
                </div>
                <div class="space-y-4">
                    {{-- FAQ Item 1 --}}
                    <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-100 dark:border-gray-700">
                        <button @click="open = !open" :aria-expanded="open ? 'true' : 'false'"
                                class="flex justify-between items-center w-full p-5 text-left text-lg font-semibold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
                            <h4>{{__('How do I contact customer service?')}}</h4>
                            <span :class="{'rotate-180': open, 'rotate-0': !open}" class="transform transition-transform duration-300">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </button>
                        <div x-show="open" x-collapse.duration.300ms class="px-5 pb-5 text-gray-700 dark:text-gray-300">
                            <p>{{__('Ans1')}}</p>
                        </div>
                    </div>

                    {{-- FAQ Item 2 --}}
                    <div x-data="{ open: true }" class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-100 dark:border-gray-700">
                        <button @click="open = !open" :aria-expanded="open ? 'true' : 'false'"
                                class="flex justify-between items-center w-full p-5 text-left text-lg font-semibold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
                            <h4>{{__('Do doctors pay for good reviews?')}}</h4>
                            <span :class="{'rotate-180': open, 'rotate-0': !open}" class="transform transition-transform duration-300">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </button>
                        <div x-show="open" x-collapse.duration.300ms class="px-5 pb-5 text-gray-700 dark:text-gray-300">
                            <p>{{__('Ans2')}}</p>
                        </div>
                    </div>

                    {{-- FAQ Item 3 --}}
                    <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-100 dark:border-gray-700">
                        <button @click="open = !open" :aria-expanded="open ? 'true' : 'false'"
                                class="flex justify-between items-center w-full p-5 text-left text-lg font-semibold text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg">
                            <h4>{{__('Why didn\'t my review get posted?')}}</h4>
                            <span :class="{'rotate-180': open, 'rotate-0': !open}" class="transform transition-transform duration-300">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </button>
                        <div x-show="open" x-collapse.duration.300ms class="px-5 pb-5 text-gray-700 dark:text-gray-300">
                            <p>{{__('Ans3')}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

---

{{-- Call to Action / Newsletter Section (Refactored from agent-section) --}}
<section class="py-16 md:py-24 bg-blue-50 dark:bg-blue-950 transition-colors duration-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-6xl">
        <div class="bg-primary-600 dark:bg-primary-800 rounded-xl shadow-xl p-8 sm:p-12 text-white overflow-hidden relative">
            <div class="absolute inset-0 opacity-10 bg-black pointer-events-none"></div> {{-- Subtle overlay --}}
            <div class="relative z-10 flex flex-col lg:flex-row justify-between items-center lg:space-x-8 space-y-8 lg:space-y-0">
                
                {{-- Emergency Call --}}
                <div class="w-full lg:w-1/2 text-center lg:text-left">
                    <h3 class="text-3xl font-bold mb-4">{{__('Emergency Call')}}</h3>
                    <div class="flex items-center justify-center lg:justify-start space-x-4">
                        <div class="p-3 bg-white dark:bg-gray-900 rounded-full text-primary-600 dark:text-primary-400 shadow-md">
                            <i class="fas fa-phone text-2xl"></i>
                        </div>
                        <div>
                            <span class="block text-sm opacity-80">{{__('Telephone')}}</span>
                            <h3 class="text-2xl font-bold"><a href="tel:{{$setting->phone}}" class="hover:underline">{{$setting->phone}}</a></h3>
                        </div>
                    </div>
                </div>

                {{-- Newsletter Signup --}}
                <div class="w-full lg:w-1/2 text-center lg:text-right">
                    <h3 class="text-3xl font-bold mb-4">{{__('Sign up for Newsletter today')}}</h3>
                    <form action="#" method="post" class="w-full max-w-lg lg:ml-auto flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                        <div class="relative flex-grow">
                            <input type="email" name="email" id="emailnews" placeholder="{{__('Your email address')}}" required=""
                                   class="w-full px-5 py-3 rounded-md border-0 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-600 shadow-sm">
                        </div>
                        <button type="button" onclick="addnewsletter('emailnews')"
                                class="flex-shrink-0 px-6 py-3 bg-primary-700 hover:bg-primary-800 text-white font-semibold rounded-md shadow-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-600">
                            {{__('Submit now')}}
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>

@stop

@section('footer')
   
@stop