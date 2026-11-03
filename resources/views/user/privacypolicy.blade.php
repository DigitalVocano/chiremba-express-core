@extends('user.layout')

@section('title')
{{__('Privacy Policy')}}
@stop

@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<meta property="og:title" content="{{__('Privacy Policy')}} | {{ config('app.name') }}"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="Read Chiremba Express's Privacy Policy to understand how we collect, use, and protect your personal information, ensuring your data security."/>
<meta property="og:keyword" content="privacy policy, Chiremba Express, data protection, personal information, data security, user privacy, telemedicine privacy"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop

@section('content')

<div class="relative w-full py-16 sm:py-20 lg:py-24 overflow-hidden bg-cover bg-center"
    style="background-image: url({{ asset('kumbacare/img/carousel-2.jpg') }});">
    <div class="absolute inset-0 bg-gradient-to-r from-primary-700 to-secondary-500 opacity-70"></div>
    <div class="relative container mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white mb-4 animated slideInDown">
            {{__('Privacy Policy')}}
        </h1>
        <nav aria-label="breadcrumb animated slideInDown">
            <ol class="flex justify-center items-center space-x-2 text-white text-lg sm:text-xl font-medium uppercase">
                <li class="flex items-center">
                    <a class="hover:text-primary-600 transition-colors duration-200" href="{{url('/')}}">{{__('Home')}}</a>
                    <i class="fas fa-chevron-right text-sm ml-2"></i> {{-- Font Awesome icon for separator --}}
                </li>
                <li class="text-primary-600" aria-current="page">{{__('Privacy Policy')}}</li>
            </ol>
        </nav>
    </div>
</div>
---

<div class="py-16 md:py-24 bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
        <div class="text-center mx-auto mb-16 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
            <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 leading-tight">
                {{__('Our Commitment to Your Privacy')}}
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed">
                {{__('At Chiremba Express, we are deeply committed to protecting your privacy and ensuring the security and confidentiality of your personal and health information. This policy outlines how we collect, use, and safeguard your data to provide you with secure and trusted telemedicine services.')}}
            </p>
        </div>
        <div class="flex justify-center">
            <div class="w-full lg:w-10/12 xl:w-8/12 wow fadeInUp" data-wow-delay="0.3s">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 sm:p-10 lg:p-12 border border-gray-100 dark:border-gray-700">
                    {{--
                        The 'prose' class from @tailwindcss/typography plugin is highly recommended here.
                        It will automatically apply beautiful, readable styles to your HTML content
                        (paragraphs, headings, lists, links, etc.) coming from $data->privacy.
                        If you don't have this plugin installed, you might need to add manual
                        Tailwind classes to your content for proper styling.
                    --}}
                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed text-lg">
                        {!! $data->privacy !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('footer')
{{-- Any page-specific footer content or scripts can go here --}}
@stop