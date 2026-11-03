@extends('user.layout')
@section('title')
{{ $data->name }} - Pharmacy Details
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url('viewpharmacy', $data->slug) }}"/>
<meta property="og:title" content="{{$data->name}} - Pharmacy Details | {{ config('app.name') }}"/>
<meta property="og:image" content="{{asset('upload/doctors').'/'.$data->image}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="{{ substr($data->aboutus, 0, 150) }}"/>
<meta property="og:keyword" content="{{$data->name}}, pharmacy, medicine, Chiremba Express"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">

<meta name="description" content="{{ substr($data->aboutus, 0, 150) }}">
<meta name="keywords" content="{{$data->name}}, pharmacy, medicines, healthcare, {{isset($data->cityls->name)?$data->cityls->name:''}}, {{isset($data->countryls->name)?$data->countryls->name:''}}">

@stop
@section('content')
@if(empty($data))
<div class="container mx-auto text-center py-16">
    <h1 class="text-2xl font-bold">Result Not Found</h1>
</div>
@else

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Pharmacy",
  "name": "{{$data->name}}",
  "image": "{{asset('upload/doctors').'/'.$data->image}}",
  "@id": "{{url('viewpharmacy', $data->slug)}}",
  "url": "{{url('viewpharmacy', $data->slug)}}",
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
<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: url({{ asset('kumbacare/img/carousel-2.jpg') }}) center center/cover no-repeat;">
    <div class="container py-5 text-center">
        <h1 class="display-4 text-white mb-3 animated slideInDown">{{ $data->name }}</h1>
       
    </div>
</div>
<!-- Page Header End -->

<section class="py-8 bg-gray-50 dark:bg-gray-900" x-data="{ prescriptionModal: false }">
   <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
         
         <!-- Left Sidebar -->
         <div class="lg:col-span-1">
            <div class="sticky top-28">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <div class="text-center">
                        @php
                            $initials = \App\Helpers\Helper::getInitials($data->name ?? 'Pharmacy');
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
                        @if(Session::has("user_id") && Session::get("role_id") == '1')
                            <button @click="prescriptionModal = true" type="button" class="w-full flex items-center justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <i class="fas fa-prescription-bottle-alt mr-2"></i> Upload Prescription
                            </button>
                        @else
                            <button type="button" class="w-full flex items-center justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" onclick="pleaselogin()">
                                <i class="fas fa-prescription-bottle-alt mr-2"></i> Upload Prescription
                            </button>
                        @endif
                    </div>
                </div>
            </div>
         </div>

         <!-- Right Content -->
         <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <div x-data="{ tab: 'medicines' }">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                            <button @click="tab = 'medicines'" :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': tab === 'medicines', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-200 dark:hover:border-gray-600': tab !== 'medicines' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Medicines</button>
                            <button @click="tab = 'about'" :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': tab === 'about', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-200 dark:hover:border-gray-600': tab !== 'about' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">About Us</button>
                            <button @click="tab = 'services'" :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': tab === 'services', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-200 dark:hover:border-gray-600': tab !== 'services' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Services</button>
                            <button @click="tab = 'reviews'" :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': tab === 'reviews', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-200 dark:hover:border-gray-600': tab !== 'reviews' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Reviews</button>
                        </nav>
                    </div>

                    <div class="py-6">
                        <div x-show="tab === 'medicines'">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Medicines</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                @forelse($medicine as $dl)
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 flex items-center space-x-4">
                                        @if($dl->image)
                                            <img src="{{asset('upload/pharmacymedicine').'/'.$dl->image}}" alt="{{$dl->name}}" class="w-16 h-16 object-cover rounded-md">
                                        @endif
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-800 dark:text-gray-100">{{$dl->name}}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{$dl->description}}</p>
                                            <p class="font-bold text-center text-primary-600 dark:text-primary-400 mt-1">${{$dl->price}}</p>
                                        </div>
                                        <div>
                                            @if(Session::has("user_id") && Session::get("role_id") == '1')
                                                <a href="{{url('addToCart/'.$dl->id)}}" class="text-primary-500 hover:text-primary-600"><i class="fas fa-cart-plus fa-lg"></i></a>
                                            @else
                                                <button onclick="pleaselogin()" class="text-primary-500 hover:text-primary-600"><i class="fas fa-cart-plus fa-lg"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-gray-500 dark:text-gray-400 sm:col-span-2">No medicines listed for this pharmacy yet.</p>
                                @endforelse
                            </div>
                        </div>
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
                        <div x-show="tab === 'reviews'">
                            @include('user.partials.review-section', ['data' => $data])
                        </div>
                    </div>
                </div>
            </div>
         </div>
      </div>
   </div>

    <!-- Upload Prescription Modal -->
    <div x-show="prescriptionModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="prescriptionModalLabel" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="prescriptionModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="prescriptionModal = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="prescriptionModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{url('upload_pre_order')}}" method="post" enctype="multipart/form-data" id="prescription-form">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/20 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-prescription-bottle-alt text-primary-600 dark:text-primary-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="prescriptionModalLabel">
                                    Upload Prescription
                                </h3>
                                <div class="mt-4 space-y-4">
                                    {{csrf_field()}}
                                    <input type="hidden" name="Pharmacy_id" value="{{ $data->id }}">
                                    <div>
                                        <label for="prescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prescription File</label>
                                        <input type="file" name="prescription" id="prescription" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600">
                                    </div>
                                    <div>
                                        <label for="phone_no1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone No</label>
                                        <input type="text" name="phone_no" id="phone_no1" placeholder="Enter Your Phone number" value="{{Session::get('user_phone_no')}}" required class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                    </div>
                                    <div>
                                        <label for="address1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                                        <input type="text" name="address" id="address1" placeholder="Enter Address" required class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                    </div>
                                    <div>
                                        <label for="message1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                                        <textarea name="message" id="message1" rows="4" placeholder="Enter Your Message" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm text-white">
                            Submit
                        </button>
                        <button @click="prescriptionModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endif
@stop
@section('footer_scripts')
<script type="text/javascript">
    function userfavorite(id){
        $.ajax({
            url: "{{url('userfavorite')}}"+"/"+id,
            success: function(data) {
                var obj=JSON.parse(data);
                if(obj.op==1){
                    $("#favdoc"+id).addClass("activefav");
                }else{
                    $("#favdoc"+id).removeClass("activefav");
                }
                $('#favmsg').html(obj.msg);
                $('#favmsg').addClass(obj.class);
                $('#favmsg').addClass('alert');
                $('#favmsg').addClass('show');
                setTimeout(function(){
                    $("#favmsg").removeClass("alert");
                    $("#favmsg").removeClass(obj.class);
                    $("#favmsg").removeClass("show");
                }, 3000);
            }
        });
    }

    function pleaselogin(){
        Swal.fire({
            title: 'Login Required',
            text: 'You must be logged in to perform this action.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Login Now'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('patientlogin') }}";
            }
        })
    }
</script>
@stop
