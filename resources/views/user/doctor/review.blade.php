@extends('layouts.doctor')
@section('title')
{{__('Doctor Reviews')}}
@stop
@section('meta-data')
{{-- Meta data is handled by the main layout --}}
@stop
@section('content')
<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Doctor Reviews')}}</h1>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @if(count($reviewdata)>0)
        @foreach($reviewdata as $review)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 flex flex-col">
                <div class="flex items-start gap-4 mb-4">
                    <figure class="w-14 h-14 rounded-full overflow-hidden flex-shrink-0">
                        @if($review->patientls && $review->patientls->profile_pic)
                            <img src="{{asset('upload/profile').'/'.$review->patientls->profile_pic}}" alt="{{ $review->patientls->name }}" class="object-cover w-full h-full">
                        @else
                            <img src="{{asset('upload/profile/profile.png')}}" alt="Default Patient" class="object-cover w-full h-full">
                        @endif
                    </figure>
                    <div class="flex-grow">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-100">{{ $review->patientls->name ?? __('Unknown Patient') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($review->created_at)->format('F d, Y') }}</p>
                    </div>
                </div>

                <div class="flex items-center mb-3">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $review->rating)
                            <i class="fas fa-star text-yellow-400"></i>
                        @else
                            <i class="fas fa-star text-gray-300 dark:text-gray-600"></i>
                        @endif
                    @endfor
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 flex-grow">
                    {{ $review->description }}
                </p>
            </div>
        @endforeach
    @else
        <div class="md:col-span-2 xl:col-span-3 text-center py-12">
            <div class="text-center p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                <i class="fas fa-star-half-alt text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
                <p class="text-gray-500 dark:text-gray-400">{{__('No Any Review List Get')}}</p>
            </div>
        </div>
    @endif
</div>

<div class="mt-8">
    {{-- Add pagination if needed, assuming $reviewdata is a paginated collection --}}
    {{-- {{ $reviewdata->links() }} --}}
</div>
@stop
