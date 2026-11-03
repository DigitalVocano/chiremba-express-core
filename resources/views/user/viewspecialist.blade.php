@extends('user.layout')
@section('title')
Specialist
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<meta property="og:title" content="Find the Right Specialist Online with Chiremba Express"/>
<meta property="og:image" content="{{asset('public/image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="Connect with top specialists in Zimbabwe through Chiremba Express. Book appointments, consult online, and receive expert care."/>
<meta property="og:keyword" content="specialist, healthcare, online consultation, Zimbabwe, Chiremba Express"/>
<link rel="shortcut icon" href="{{asset('public/image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')

<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: url({{ asset('kumbacare/img/carousel-1.jpg') }}) center center/cover no-repeat;">
    <div class="container py-5 text-center">
        <h1 class="display-3 text-white mb-3 animated slideInDown">Our Specialists</h1>
        <nav aria-label="breadcrumb animated slideInDown">
            <ol class="breadcrumb text-uppercase mb-0 justify-content-center">
                <li class="breadcrumb-item"><a class="text-white" href="{{url('/')}}">Home</a></li>
                <li class="breadcrumb-item text-primary-500 active" aria-current="page">Specialists</li>
            </ol>
        </nav>
    </div>
</div>
<section class="py-16 bg-gray-50 dark:bg-gray-900">
   <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            @foreach($department as $d)
                <div class="group bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden transition-shadow duration-300 hover:shadow-2xl flex flex-col">
                    <div class="p-6 text-center flex-grow">
                        <div class="relative inline-block mb-4">
                            <div class="absolute -inset-2 bg-primary-100 dark:bg-primary-900/20 rounded-full transform group-hover:scale-110 transition-transform duration-300"></div>
                            <img src="{{asset('upload/services').'/'.$d->icon}}" alt="{{ $d->name }} Icon" class="relative h-16 w-16">
                            {{-- START: Doctor Count Badge --}}
                            @if($d->doctors_count > 0)
                                <span class="absolute top-0 right-0 -mt-2 -mr-2 px-2 py-1 text-xs font-bold text-white bg-green-500 rounded-full dark:bg-green-600 z-10">
                                    {{ $d->doctors_count }}
                                </span>
                            @endif
                            {{-- END: Doctor Count Badge --}}
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $d->name }}</h3>
                        {{-- Removed original doctor count paragraph --}}
                    </div>
                    <div class="mt-auto border-t border-gray-200 dark:border-gray-700">
                        <a href="{{url('searchdoctor?type=').$d->id}}" class="block w-full text-center py-3 px-4 text-primary-600 dark:text-primary-400 font-semibold hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            View Specialists <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
   </div>
</section>

@stop
@section('footer')
@stop