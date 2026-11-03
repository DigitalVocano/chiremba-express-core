@extends('user.layout')
@section('title')
Search Doctors
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<meta property="og:title" content="Search Doctors | {{ config('app.name') }}"/>
<meta property="og:image" content="{{asset('public/image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="Find the best doctors and specialists near you. Search by name, specialization, or location."/>
<meta property="og:keyword" content="doctor, search, find, specialist, location, online, consultation, healthcare, {{ config('app.name') }}"/>
<link rel="shortcut icon" href="{{asset('public/image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Find the best doctors and specialists near you. Search by name, specialization, or location.">
<meta name="keywords" content="doctor, search, find, specialist, location, online, consultation, healthcare, {{ config('app.name') }}">
@stop
@section('content')
@if(count($doctors) > 0)
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "itemListElement": [
    @foreach($doctors as $key => $dl)
    {
      "@type": "ListItem",
      "position": {{$key + 1}},
      "item": {
        "@type": "Physician",
        "name": "{{$dl->name}}",
        "image": "{{asset('upload/doctors').'/'.$dl->image}}",
        "@id": "{{ url('viewdoctor', $dl->slug) }}",
        "url": "{{ url('viewdoctor', $dl->slug) }}",
        "telephone": "{{$dl->phoneno}}",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "{{$dl->address}}",
          "addressLocality": "{{isset($dl->cityls->name)?$dl->cityls->name:''}}",
          "addressRegion": "{{isset($dl->statels->name)?$dl->statels->name:''}}",
          "postalCode": "{{$dl->pincode}}",
          "addressCountry": "{{isset($dl->countryls->name)?$dl->countryls->name:''}}"
        },
        "geo": {
          "@type": "GeoCoordinates",
          "latitude": "{{$dl->lat}}",
          "longitude": "{{$dl->lon}}"
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
            "opens": "{{$dl->working_time}}",
            "closes": "{{$dl->working_time}}"
        },
        "medicalSpecialty": "{{isset($dl->departmentls->name)?$dl->departmentls->name:''}}",
        "aggregateRating": {
          "@type": "AggregateRating",
          "ratingValue": "{{round($dl->avgratting,1)}}",
          "reviewCount": "{{$dl->totalreview}}"
        }
      }
    } @if(!$loop->last), @endif
    @endforeach
  ]
}
</script>
@endif
<!-- Page Header Start -->
<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: url({{ asset('kumbacare/img/carousel-1.jpg') }}) center center/cover no-repeat;">
    <div class="container py-5 text-center">
        <h1 class="display-3 text-white mb-3 animated slideInDown">Search Doctors</h1>
    </div>
</div>
<!-- Page Header End -->

<section class="py-16">
    <div class="container mx-auto px-4">
        {{-- Search/Filter Form --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg mb-12">
            <form action="{{url('searchdoctor')}}" method="get">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label for="term" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Doctor Name or Specialty</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" name="term" value="{{$term}}" id="term" placeholder="e.g., Dr. John Doe or Cardiology" class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Specialty</label>
                        <select name="type" id="type" onchange="this.form.submit()" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                            <option value="">All Specialties</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ $type == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">Showing {{count($doctors)}} of {{$totalDoctors->count()}} Results</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @forelse($doctors as $doctor)
                @include('user.partials.doctor-card', ['doctor' => $doctor])
                    @empty
            <div class="md:col-span-3 text-center py-16">
                <i class="fas fa-user-md fa-4x text-gray-300 dark:text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200">No Doctors Found</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Try adjusting your search terms or filters.</p>
            </div>
                    @endforelse
                </div>

                @php
                    $appends = [];
                    if (!empty($term)) { $appends['term'] = $term; }
                    if (!empty($type)) { $appends['type'] = $type; }
                @endphp
        <div class="mt-12 flex justify-center">
                    {{ $doctors->appends($appends)->links() }}
        </div>
    </div>
</section>
@stop
@section('footer')
@stop
