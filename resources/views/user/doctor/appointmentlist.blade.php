@extends('layouts.doctor')
@section('title')
{{__('Appointment List')}}
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
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')
<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Appointment List')}}</h1>
</div>

@if(Session::has('message'))
<div class="mb-4 p-4 text-sm {{ Session::get('alert-class', 'text-blue-700 bg-blue-100 dark:bg-blue-200 dark:text-blue-800') }} rounded-lg" role="alert">
    {{ Session::get('message') }}
</div>
@endif

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200">{{__('Appointment List')}}</h3>
        <div class="w-full sm:w-auto">
            <select onchange="if (this.value) window.location.href=this.value;" class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring focus:ring-primary-600 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
               <option value="{{ route('doctorappointment') }}" {{ !request()->has('status') || request('status') == '' ? 'selected' : '' }}>{{__("Any Status")}}</option>
               <option value="{{ route('doctorappointment', ['status' => 1]) }}" {{ request('status') == '1' ? 'selected' : '' }}>{{__("Received")}}</option>
               <option value="{{ route('doctorappointment', ['status' => 2]) }}" {{ request('status') == '2' ? 'selected' : '' }}>{{__("Approved")}}</option>
               <option value="{{ route('doctorappointment', ['status' => 3]) }}" {{ request('status') == '3' ? 'selected' : '' }}>{{__("In Process")}}</option>
               <option value="{{ route('doctorappointment', ['status' => 4]) }}" {{ request('status') == '4' ? 'selected' : '' }}>{{__("Completed")}}</option>
               <option value="{{ route('doctorappointment', ['status' => 5]) }}" {{ request('status') == '5' ? 'selected' : '' }}>{{__("Rejected")}}</option>
               <option value="{{ route('doctorappointment', ['status' => 0]) }}" {{ request('status') === '0' ? 'selected' : '' }}>{{__("Absent")}}</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
       @if(count($appointmentdata)>0)
          @foreach($appointmentdata as $am)
              <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-200 flex flex-col">
                  <div class="flex-grow">
                      <div class="flex items-start gap-4">
                          <a href="{{url('appointment_detail',$am->id)}}">
                              <figure class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0">
                                @if($am->patientls && $am->patientls->profile_pic!="")
                                    <img src="{{asset('upload/profile').'/'.$am->patientls->profile_pic}}" alt="{{$am->patientls->name}}" class="object-cover w-full h-full">
                                @else
                                     <img src="{{asset('upload/profile/profile.png')}}" alt="Default Patient" class="object-cover w-full h-full">
                                @endif
                              </figure>
                          </a>

                          <div class="flex-grow">
                              <div class="flex justify-between items-start">
                                  <div>
                                       <h4 class="text-md font-semibold text-gray-800 dark:text-gray-100">{{$am->patientls ? $am->patientls->name : __('Unknown Patient')}}</h4>
                                       <p class="text-xs text-gray-500 dark:text-gray-400">{{date("F d, Y",strtotime($am->date))}} at {{$am->slot_name}}</p>
                                       @if($am->medical_aid_name || $am->payment_type == 3)
                                           <div class="mt-1 flex flex-wrap gap-1 items-center">
                                               <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs font-semibold">
                                                   🏥 {{ $am->medical_aid_name ?: 'Medical Aid' }} ({{ $am->medical_aid_number }}-{{ $am->medical_aid_suffix ?? '00' }})
                                               </span>
                                               @if($am->medical_aid_card_photo)
                                                   <a href="{{ asset('upload/medical_aid_cards/'.$am->medical_aid_card_photo) }}" target="_blank" class="text-xs text-primary-600 underline font-semibold ml-1">
                                                       🪪 View Card Photo
                                                   </a>
                                               @endif
                                           </div>
                                       @endif
                                  </div>
                                  @php
                                      $statusText = __('Absent');
                                      $statusClass = 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300';
                                      if($am->status=='1'){ $statusText = __("Received"); $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100'; }
                                      else if($am->status=='2'){ $statusText = __("Approved"); $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'; }
                                      else if($am->status=='3'){ $statusText = __("In Process"); $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100'; }
                                      else if($am->status=='4'){ $statusText = __("Completed"); $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'; }
                                      else if($am->status=='5'){ $statusText = __("Rejected"); $statusClass = 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100'; }
                                  @endphp
                                  <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{$statusClass}}">
                                      {{$statusText}}
                                  </span>
                              </div>
                              <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-2">{{$am->user_description}}</p>
                          </div>
                      </div>
                  </div>

                  <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex flex-wrap gap-2">
                    @if ($am->status == '1')
                        <a href="{{ url('changeappointment') . '/3/' . $am->id }}" class="px-3 py-1.5 text-xs font-medium text-center text-white bg-green-500 rounded-md hover:bg-green-600 focus:ring-4 focus:outline-none focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"><i class="fas fa-check mr-1"></i>{{ __('Accept') }}</a>
                        <a href="{{ url('changeappointment') . '/5/' . $am->id }}" class="px-3 py-1.5 text-xs font-medium text-center text-white bg-red-500 rounded-md hover:bg-red-600 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"><i class="fas fa-times mr-1"></i>{{ __('Cancel') }}</a>
                    @endif
                    <a href="{{ url('doctorchat/'.$am->id) }}" class="px-3 py-1.5 text-xs font-medium text-center text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"><i class="far fa-comments mr-1"></i> {{__("Chat")}}</a>
                    </div>
                    <a href="{{url('appointment_detail',$am->id)}}" class="text-xs text-primary-600 hover:underline">{{__('View Details')}} <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
              </div>
          @endforeach
       @else
            <div class="lg:col-span-2 xl:col-span-3 text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">{{__("No appointments found.")}}</p>
            </div>
       @endif
    </div>

    <div class="mt-8">
        {{$appointmentdata->links()}}
    </div>
</div>

@endsection
