@extends('admin.layout')
@section('title')
{{("Save Doctors Schedule")}} | {{("admin")}} {{("Save Doctors Schedule")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Save Doctors Schedule")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{url('admin/doctors')}}" class="text-primary-500 hover:underline">{{("Doctors")}}</a>
               / {{("Save Doctors Schedule")}}
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-3/4">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  <form action="{{url('admin/savescheduledata')}}" method="post" class="space-y-6">
                     {{csrf_field()}}
                     <input type="hidden" name="doc_id" id="doc_id" value="{{$id}}">

                     <div x-data="{ openDay: null }" class="space-y-2">
                        @php $days = [__("Monday"), __("Tuesday"), __("Wednesday"), __("Thursday"), __("Friday"), __("Saturday"), __("Sunday")]; @endphp
                        @for($i=0; $i<7; $i++)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-md">
                           <input type="hidden" name="arr[]" id="day_id_{{$i}}" value="{{$i}}">
                           <button type="button" @click="openDay = (openDay === {{$i}} ? null : {{$i}})" class="w-full flex justify-between items-center p-4 text-left text-gray-800 dark:text-gray-200">
                              <h6 class="font-semibold">{{$days[$i]}}</h6>
                              <i class="uil" :class="openDay === {{$i}} ? 'uil-angle-up' : 'uil-angle-down'"></i>
                           </button>
                           <div x-show="openDay === {{$i}}" x-cloak class="p-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                              <div class="flex justify-end">
                                 <button type="button" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700" onclick="addnewslot('{{$i}}')">{{("Add Time")}}</button>
                              </div>
                              <div id="day_{{$i}}" class="space-y-4">
                                 @php $j=0; $temp=0; @endphp
                                 @foreach($data as $d)
                                    @if(isset($d) && $d->day_id == $i)
                                       <div class="border border-gray-200 dark:border-gray-600 p-4 rounded-md" id="slotdiv{{$i}}{{$j}}">
                                          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                             <div>
                                                <label for="start_time_{{$i}}_{{$j}}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Start Time")}}</label>
                                                <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" id="start_time_{{$i}}_{{$j}}" name="arr[{{$i}}][start_time][{{$j}}]" value="{{$d->start_time ?? ''}}" onchange="checkduration('{{$i}}','{{$j}}')">
                                             </div>
                                             <div>
                                                <label for="end_time_{{$i}}_{{$j}}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("End Time")}}</label>
                                                <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" id="end_time_{{$i}}_{{$j}}" name="arr[{{$i}}][end_time][]" onchange="checkduration('{{$i}}','{{$j}}')" value="{{$d->end_time ?? ''}}">
                                             </div>
                                             <div>
                                                <label for="duration_{{$i}}_{{$j}}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Duration")}}</label>
                                                <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" name="arr[{{$i}}][duration][{{$j}}]" id="duration_{{$i}}_{{$j}}" onchange="getslot(this.value,'{{$i}}','{{$j}}')">
                                                   {!! html_entity_decode($d->options) !!}
                                                </select>
                                             </div>
                                             <div>
                                                @if($j != 0)
                                                   <button type="button" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700" onclick="removescdehule('{{$i}}','{{$j}}')">{{("delete")}}</button>
                                                @endif
                                             </div>
                                          </div>
                                          <div class="mt-4 flex flex-wrap gap-2" id="slot_{{$i}}_{{$j}}">
                                             @if(isset($d->getslotls))
                                                @foreach($d->getslotls as $slot)
                                                   <span class="bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-primary-900 dark:text-primary-300">{{$slot->slot}}</span>
                                                @endforeach
                                             @endif
                                          </div>
                                       </div>
                                       @php $j++; $temp=1; @endphp
                                    @endif
                                 @endforeach
                                 @if($temp == 0)
                                    <div class="border border-gray-200 dark:border-gray-600 p-4 rounded-md" id="slotdiv{{$i}}0">
                                       <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                          <div>
                                             <label for="start_time_{{$i}}_0" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Start Time")}}</label>
                                             <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" id="start_time_{{$i}}_0" name="arr[{{$i}}][start_time][0]" onchange="checkduration('{{$i}}','0')">
                                          </div>
                                          <div>
                                             <label for="end_time_{{$i}}_0" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("End Time")}}</label>
                                             <input type="time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" id="end_time_{{$i}}_0" name="arr[{{$i}}][end_time][]" onchange="checkduration('{{$i}}','0')">
                                          </div>
                                          <div>
                                             <label for="duration_{{$i}}_0" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Duration")}}</label>
                                             <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" name="arr[{{$i}}][duration][0]" id="duration_{{$i}}_0" onchange="getslot(this.value,'{{$i}}',0)">
                                                <option value="">{{("Select Duration")}}</option>
                                             </select>
                                          </div>
                                       </div>
                                       <div class="mt-4 flex flex-wrap gap-2" id="slot_{{$i}}_0"></div>
                                    </div>
                                 @endif
                              </div>
                              <input type="hidden" id="total_slot_day_{{$i}}" value="{{$j}}">
                           </div>
                        </div>
                        @endfor
                     </div>
                     <div class="flex justify-end pt-4">
                        @if(Session::get("is_demo")=='0')
                           <button type="button" onclick="disablebtn()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">{{('Submit')}}</button>
                        @else
                           <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">{{("Submit")}}</button>
                        @endif
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@stop
@section('footer')
<input type="hidden" id="start1val" value='{{("Please Select Start Time First")}}'>
<input type="hidden" id="sge" value='{{("Start Time is greater than end time")}}'>
<input type="hidden" id="sequale" value='{{("Start Time equals end time")}}'>
<input type="hidden" id="selduration" value='{{("Please Select Any Duration")}}'>
<input type="hidden" id="startvaltext" value='{{("Start Time")}}'>
<input type="hidden" id="endvaltext" value='{{("End Time")}}'>
<input type="hidden" id="durationval" value='{{("Duration")}}'>
<input type="hidden" id="seldurationval" value='{{("Select Duration")}}'>
<input type="hidden" id="deletetext" value='{{("delete")}}'>
@stop
