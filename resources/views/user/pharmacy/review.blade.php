@extends('layouts.pharmacy')
@section('title')
Pharmacy Reviews
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="KumbaCare"/>
<meta property="og:title" content="KumbaCare"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="KumbaCare"/>
<meta property="og:description" content="KumbaCare is a telemedication web app."/>
<meta property="og:keyword" content="telemedication, doctor, patient, pharmacy"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')
{{-- Page Header --}}
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">My Reviews</h1>
</div>

{{-- Reviews Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($reviewdata as $review)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden flex flex-col transform hover:-translate-y-1 transition-transform duration-300">
            <div class="p-4 flex-grow">
                <div class="flex items-start mb-3">
                    <figure class="w-11 h-11 rounded-full overflow-hidden mr-3 flex-shrink-0">
                        @if($review->patientls && $review->patientls->profile_pic)
                            <img src="{{ asset('upload/profile/' . $review->patientls->profile_pic) }}" alt="{{ $review->patientls->name }}" class="object-cover w-full h-full">
                        @else
                            <img src="{{ asset('upload/profile/profile.png') }}" alt="Default Profile" class="object-cover w-full h-full">
                        @endif
                    </figure>
                    <div>
                        <h3 class="text-md font-semibold text-gray-800 dark:text-gray-100">{{ $review->patientls->name ?? 'Unknown Patient' }}</h3>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $review->rating)
                                    <i class="fas fa-star text-yellow-400"></i>
                                @else
                                    <i class="far fa-star text-gray-400"></i>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
                <blockquote class="relative p-4 text-sm text-gray-600 dark:text-gray-300 border-l-4 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 rounded-r-lg">
                    <div class="absolute top-0 left-0 -translate-x-2.5 -translate-y-2.5 text-primary-600/50 text-3xl">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p class="italic">{{ $review->description }}</p>
                </blockquote>
            </div>
            <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 text-right">
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($review->created_at)->format('F d, Y') }}</p>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <i class="fas fa-star-half-alt text-5xl text-gray-400 dark:text-gray-500 mb-4"></i>
            <p class="text-gray-500 dark:text-gray-400">You have not received any reviews yet.</p>
        </div>
    @endforelse
</div>
@stop
