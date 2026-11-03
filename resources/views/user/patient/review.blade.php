@extends('layouts.patient')
@section('title')
    My Reviews
@stop
@section('meta-data')
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="KumbaCare"/>
    <meta property="og:title" content="My Reviews"/>
    <meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
    <meta property="og:image:width" content="250px"/>
    <meta property="og:image:height" content="250px"/>
    <meta property="og:site_name" content="{{__('KumbaCare')}}"/>
    <meta property="og:description" content="{{__('meta_description')}}"/>
    <meta property="og:keyword" content="{{__('Meta Keyword')}}"/>
    <link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
@stop
@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">My Reviews</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 sm:p-6">
        <h3 class="text-xl font-semibold text-tele-dark-blue dark:text-gray-200 mb-6">Your Reviews</h3>

        @if(Session::has('message'))
            <div class="mb-4 p-4 text-sm {{ Session::get('alert-class', 'text-green-700 bg-green-100 dark:bg-green-200 dark:text-green-800') }} rounded-lg" role="alert">
                {{ Session::get('message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($datareview as $review)
                @if($review->doctorls)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden flex flex-col transform hover:-translate-y-1 transition-transform duration-300">
                    <div class="p-4 flex-grow">
                        <div class="flex items-start mb-3">
                            <figure class="w-12 h-12 rounded-full overflow-hidden mr-4 flex-shrink-0">
                                <img src="{{ asset('upload/doctors').'/'.$review->doctorls->image }}" alt="{{ $review->doctorls->name ?? 'Doctor' }}" class="object-cover w-full h-full">
                            </figure>
                            <div>
                                <h3 class="text-md font-semibold text-gray-800 dark:text-gray-100">{{ $review->doctorls->name ?? 'N/A' }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $review->doctorls->department_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center mb-3">
                            <div class="flex text-yellow-400 text-sm">
                                @for ($i = 0; $i < 5; $i++)
                                    @if ($i < $review->rating)
                                        <i class="fas fa-star"></i>
                                    @else
                                        <i class="far fa-star text-gray-300 dark:text-gray-500"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="ml-2 text-xs text-gray-600 dark:text-gray-400">({{ $review->rating ?? 0 }}/5)</span>
                        </div>
                        <blockquote class="relative p-3 text-sm text-gray-600 dark:text-gray-300 border-l-4 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 rounded-r-lg">
                            <p class="italic">{{ $review->description ?? 'No review text.' }}</p>
                        </blockquote>
                    </div>
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($review->created_at)->format('F d, Y') }}</p>
                    </div>
                </div>
                @endif
            @empty
            <div class="col-span-full text-center py-12 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <i class="fas fa-star-half-alt text-5xl text-gray-400 dark:text-gray-500 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">You have not submitted any reviews yet.</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($datareview instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-6">
                {{ $datareview->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
@stop
