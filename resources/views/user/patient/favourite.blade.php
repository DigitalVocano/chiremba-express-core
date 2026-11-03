@extends('layouts.patient')
@section('title')
    {{__('Favourite Doctors')}}
@stop
@section('meta-data')
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{__('KumbaCare')}}"/>
    <meta property="og:title" content="{{__('Favourite Doctors')}}"/>
    <meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
    <meta property="og:image:width" content="250px"/>
    <meta property="og:image:height" content="250px"/>
    <meta property="og:site_name" content="{{__('KumbaCare')}}"/>
    <meta property="og:description" content="{{__('meta_description')}}"/>
    <meta property="og:keyword" content="{{__('Meta Keyword')}}"/>
    <link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
    {{-- <meta name="viewport" content="width=device-width, initial-scale=1"> --}}
@stop
@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Favourite Doctors')}}</h1>
    </div>

    <div class="flex justify-end mb-6">
        <a href="{{ url('searchdoctor') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            {{__('Find Doctors')}} <i class="fas fa-search ml-2"></i>
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 sm:p-6">
        @if(Session::has('message'))
            <div class="mb-4 p-4 text-sm {{ Session::get('alert-class', 'text-green-700 bg-green-100 dark:bg-green-200 dark:text-green-800') }} rounded-lg" role="alert">
                {{ Session::get('message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($userfavorite as $uf)
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm p-4 flex flex-col transition-all duration-300 hover:shadow-xl">
                <figure class="relative w-full h-48 rounded-md overflow-hidden mb-4 bg-primary-50 dark:bg-primary-900/20">
                    @php
                        $docName = $uf->doctorls->name ?? 'Doctor';
                        $initials = \App\Helpers\Helper::getInitials($docName);
                        $hasCustomImage = !empty($uf->doctorls->image) && !in_array($uf->doctorls->image, ['default.png', 'user.png', 'profile.png', 'defaultpharmacy.png', 'null']);
                        $imgPath = $hasCustomImage ? asset('upload/doctors/' . $uf->doctorls->image) : '';
                    @endphp
                    @if($hasCustomImage)
                        <img src="{{ $imgPath }}" 
                             alt="{{ $docName }}" 
                             class="object-cover w-full h-full"
                             onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='flex';}">
                    @endif
                    <div class="w-full h-full flex items-center justify-center text-white text-3xl font-extrabold tracking-wider bg-gradient-to-br from-primary-600 via-primary-500 to-emerald-600 select-none shadow-inner"
                         style="{{ $hasCustomImage ? 'display: none;' : 'display: flex;' }}">
                        {{ $initials }}
                    </div>
                    <a href="javascript:userfavoritedashboard('{{$uf->doctor_id}}')" class="absolute top-3 right-3 text-red-500 hover:text-red-700 text-xl z-10 bg-white/70 dark:bg-gray-800/70 p-1.5 rounded-full" title="{{ __('Remove from favourites') }}">
                        <i class="fas fa-heart"></i> {{-- Solid heart for favorited --}}
                    </a>
                </figure>
                <div class="flex-grow">
                    <h4 class="text-lg font-semibold text-tele-dark-blue dark:text-gray-200 mb-1">{{ $uf->doctorls->name ?? 'N/A' }}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $uf->doctorls->department_name ?? 'N/A' }}</p>

                    <div class="flex items-center mb-3">
                        <div class="flex text-yellow-400 text-sm mr-2">
                            @php
                                $avgRating = $uf->doctorls->avgratting ?? 0;
                            @endphp
                            @for ($i = 0; $i < 5; $i++)
                                @if ($i < $avgRating)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star text-gray-300 dark:text-gray-500"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">({{ $uf->doctorls->totalreview ?? 0 }})</span>
                    </div>

                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                        <i class="fas fa-map-marker-alt mr-1 text-gray-500 dark:text-gray-400"></i>{{ substr($uf->doctorls->address ?? 'N/A', 0, 40) }}{{ strlen($uf->doctorls->address ?? '') > 40 ? '...' : '' }}
                    </p>
                </div>

                <div class="mt-auto"> {{-- Push button to the bottom --}}
                    <a href="{{url('viewdoctor').'/'.$uf->doctorls->slug}}" class="block w-full text-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-lime-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                        {{__('Book Now')}}
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-8 text-gray-500 dark:text-gray-400">
                <i class="fas fa-box-open text-4xl mb-3"></i>
                <p class="mb-4">{{__('No any doctors In your Favorite list')}}</p>
                <a href="{{ url('searchdoctor') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    {{__('Find Doctors')}} <i class="fas fa-search ml-2"></i>
                </a>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($userfavorite instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-6">
            {{ $userfavorite->links('pagination::tailwind') }}
        </div>
        @endif
    </div>
@stop

@section('footer_scripts')
@stop
