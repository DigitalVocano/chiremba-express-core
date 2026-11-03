@extends('layouts.doctor')
{{__('Appointment Detail')}}
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
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Appointment Detail')}}</h1>
</div>

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    @if(Session::has('message'))
    <div class="mb-4 p-4 text-sm {{ Session::get('alert-class', 'text-blue-700 bg-blue-100 dark:bg-blue-200 dark:text-blue-800') }} rounded-lg" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif

    @if($viewappointment && $viewappointment->doctorls)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <img src="{{asset('upload/doctors').'/'.$viewappointment->doctorls->image}}" alt="{{$viewappointment->doctorls->name}}" class="w-full h-auto rounded-lg shadow">
            </div>
            <div class="md:col-span-2 space-y-3 text-gray-700 dark:text-gray-300">
                <p>
                    <strong class="font-semibold">{{__("Doctor Name")}}:</strong>
                    {{$viewappointment->doctorls->name}}
                </p>
                <p>
                    <strong class="font-semibold">{{__("Appointment Time")}}:</strong>
                    {{ \Carbon\Carbon::parse($viewappointment->date)->format(\App\Helpers\Helper::getShortFormat()) }} {{$viewappointment->slot_name}}
                </p>
                <p>
                    <strong class="font-semibold">{{__("Phone no")}}:</strong>
                    {{$viewappointment->phone}}
                </p>
                <p>
                    <strong class="font-semibold">{{__("description")}}:</strong>
                    {{$viewappointment->user_description}}
                </p>
                <div>
                    <strong class="font-semibold">{{__("Appointment Status")}}:</strong>
                    @php
                        $statusText = __('Absent');
                        $statusClass = 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300';
                        if($viewappointment->status=='1'){ $statusText = __("Received"); $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100'; }
                        else if($viewappointment->status=='2'){ $statusText = __("Approved"); $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'; }
                        else if($viewappointment->status=='3'){ $statusText = __("In Process"); $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100'; }
                        else if($viewappointment->status=='4'){ $statusText = __("Completed"); $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'; }
                        else if($viewappointment->status=='5'){ $statusText = __("Rejected"); $statusClass = 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100'; }
                    @endphp
                    <span class="ml-2 px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{$statusClass}}">
                       <?php  if($viewappointment->status=='1'){
                             echo __("Received");
                        }else if($viewappointment->status=='2'){
                             echo __("Approved");
                        }else if($viewappointment->status=='3'){
                             echo __("In Process");
                        }
                        else if($viewappointment->status=='4'){
                             echo __("Completed");
                        }
                        else if($viewappointment->status=='5'){
                             echo __("Rejected");
                        }else{
                             echo __("Absent");
                        } ?>
                    </span>
                </p>
            </div>
        </div>
    @else
        <p class="text-center text-gray-500 dark:text-gray-400">{{__('Appointment details not found.')}}</p>
    @endif
</div>
@endsection