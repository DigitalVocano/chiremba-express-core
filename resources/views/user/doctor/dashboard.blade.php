@extends('layouts.doctor')
@section('title')
{{__('Doctor Dashboard')}}
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{__('KumbaCare')}}"/>
<meta property="og:title" content="{{__('KumbaCare')}}"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{__('KumbaCare')}}"/>
<meta property="og:description" content="{{__('meta_description')}}"/>
<meta property="og:keyword" content="{{__('Meta Keyword')}}"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
{{-- Viewport is in main layout --}}
@stop
@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Doctor Dashboard')}}</h1>
    </div>

@if(isset($is_subscribed) && !$is_subscribed)
    <div class="p-4 mb-6 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-200 dark:text-yellow-800" role="alert">
        <span class="font-medium">{{__('Subscription Required!')}}</span> {{__('You do not have an active subscription. Please subscribe to access all features.')}}
        <a href="{{ route('doctor.subscription_plan') }}" class="ml-2 font-semibold underline hover:text-yellow-900">{{__('Subscribe Now')}}</a>
    </div>
@endif

@if (session('success'))
    <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
        {{ session('error') }}
    </div>
@endif

    <div class="space-y-6">
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                <div class="flex-shrink-0">
                    <div class="text-tele-dark-blue dark:text-primary-600 text-3xl">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
                <div class="flex-grow ml-4 text-right">
                    <div class="text-2xl font-bold text-tele-dark-blue dark:text-gray-200">{{$totalappointment}}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{__('Total Appointment')}}</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                <div class="flex-shrink-0">
                    <div class="text-yellow-500 dark:text-yellow-400 text-3xl">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                <div class="flex-grow ml-4 text-right">
                    <div class="text-2xl font-bold text-tele-dark-blue dark:text-gray-200">{{$totalreview}}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{__('Total Review')}}</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                <div class="flex-shrink-0">
                    <div class="text-green-500 dark:text-green-400 text-3xl">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                </div>
                <div class="flex-grow ml-4 text-right">
                    <div class="text-2xl font-bold text-tele-dark-blue dark:text-gray-200">{{$totalnewappointment}}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{__('New Appointments')}}</div>
                </div>
            </div>
            {{-- Wallet Balance Card --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                <div class="flex-shrink-0">
                    <div class="text-purple-500 dark:text-purple-400 text-3xl">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="flex-grow ml-4 text-right">
                    <div class="text-2xl font-bold text-tele-dark-blue dark:text-gray-200">
                        {{ $setting->currency }}{{ number_format($doctordata->wallet->balance ?? 0, 2) }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{__('Wallet Balance')}}</div>
                </div>
            </div>
        </div>

        {{-- Patients Appointments List --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
                <h3 class="text-xl font-semibold text-tele-dark-blue dark:text-gray-200">{{__('Patients Appointments')}}</h3>
                <div class="flex space-x-2">
                         @if($type==2)
	                         <a href="{{url('doctordashboard?type=2')}}" class="px-4 py-2 rounded transition bg-primary-600 text-white hover:bg-primary-700">{{__('past')}}</a>
	                     @else
	                         <a href="{{url('doctordashboard?type=2')}}" class="px-4 py-2 rounded transition bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">{{__('past')}}</a>
	                     @endif
	                     @if(!isset($type))
	                         <a href="{{url('doctordashboard')}}" class="px-4 py-2 rounded transition bg-primary-600 text-white hover:bg-primary-700">{{__('Today')}}</a>
	                     @else
	                         <a href="{{url('doctordashboard')}}" class="px-4 py-2 rounded transition bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">{{__('Today')}}</a>
	                     @endif
	                     @if($type==3)
	                          <a href="{{url('doctordashboard?type=3')}}" class="px-4 py-2 rounded transition bg-primary-600 text-white hover:bg-primary-700">{{__('Upcoming')}}</a>
	                     @else
	                          <a href="{{url('doctordashboard?type=3')}}" class="px-4 py-2 rounded transition bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">{{__('Upcoming')}}</a>
	                     @endif
                  </div>
               </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="appointmentsTable">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                           <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{__("Patient Name")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{__("Date")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{__("Phone")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{__("Status")}}</th>

                           </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                          @if(count($bookdata)>0)
                            @foreach($bookdata as $bo)
		                           <tr>
		                              <td class="px-6 py-4 whitespace-nowrap">
		                                 <div class="flex items-center">
		                                    <div class="flex-shrink-0 h-10 w-10">
		                                    	@if(isset($bo->patientls)&&$bo->patientls->profile_pic!="")
		                                    	<img class="h-10 w-10 rounded-full object-cover" src="{{asset('upload/profile/').'/'.$bo->patientls->profile_pic}}" alt="">
		                                    	@else
		                                    	  <img class="h-10 w-10 rounded-full object-cover" src="{{asset('upload/profile/profile.png')}}" alt="">
		                                    	@endif
		                                    </div>
		                                    <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-200">{{$bo->patientls->name}}</div>
                                            </div>
		                                 </div>
		                              </td>
		                              <td class="px-6 py-4 whitespace-nowrap">
		                                 <div class="text-sm text-gray-900 dark:text-gray-200">{{date("F d,Y",strtotime($bo->date))}}</div>
		                                 <div class="text-xs text-gray-500 dark:text-gray-400">{{$bo->slot_name}}</div>
		                              </td>
		                              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
		                                 {{$bo->phone}}
		                              </td>
		                              <td class="px-6 py-4 whitespace-nowrap">
		                                 @php
                                    if($bo->status=='1'){
                                         $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100'; $statusText = __("Received");
                                    }else if($bo->status=='2'){
                                         $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'; $statusText = __("Approved");
                                    }else if($bo->status=='3'){
                                         $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100'; $statusText = __("In Process");
                                    }
                                    else if($bo->status=='4'){
                                         $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'; $statusText = __("Completed");
                                    }
                                    else if($bo->status=='5'){
                                         $statusClass = 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100'; $statusText = __("Rejected");
                                    }else{
                                         $statusClass = 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200'; $statusText = __("Absent");
                                    }
                                    @endphp
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{$statusClass}}">
                                        {{ $statusText }}
                                    </span>
		                              </td>

		                           </tr>
                            @endforeach
                           @else
                             <tr><td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">{{__("No Data Found")}}</td></tr>
                           @endif
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
        </div>
    </div>
@stop
@section('footer')
@stop
