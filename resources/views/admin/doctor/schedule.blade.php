@extends('admin.layout')
@section('title')
Save Doctor's Schedule | Admin
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Save Doctor's Schedule</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{ route('admin.doctors.index') }}" class="text-primary-500 hover:underline">Doctors</a>
               / Save Doctor's Schedule
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-3/4">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  <form id="scheduleForm" action="{{ route('admin.doctors.saveschedule') }}" method="post" class="space-y-6">
                     {{csrf_field()}}
                     <input type="hidden" name="doc_id" id="doc_id" value="{{$id}}">

                     <div x-data="{ openDay: 0 }" class="space-y-2">
                        @php $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"]; @endphp
                        @for($i=0; $i<7; $i++)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-md">
                           <input type="hidden" name="arr[]" id="day_id_{{$i}}" value="{{$i}}">
                           <button type="button" @click="openDay = (openDay === {{$i}} ? null : {{$i}})" class="w-full flex justify-between items-center p-4 text-left text-gray-800 dark:text-gray-200">
                              <h6 class="font-semibold">{{$days[$i]}}</h6>
                              <i class="uil" :class="openDay === {{$i}} ? 'uil-angle-up' : 'uil-angle-down'"></i>
                           </button>
                           <div x-show="openDay === {{$i}}" x-cloak class="p-4 border-t border-gray-200 dark:border-gray-700 space-y-4">
                              <div class="flex justify-end">
                                 <button type="button" class="add-slot-btn inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700" data-day="{{$i}}">Add Time</button>
                              </div>
                              <div id="day_{{$i}}" class="space-y-4">
                                 @php $j=0; $temp=0; @endphp
                                 @foreach($data as $d)
                                    @if(isset($d) && $d->day_id == $i)
                                       <div class="border border-gray-200 dark:border-gray-600 p-4 rounded-md" id="slotdiv{{$i}}{{$j}}">
                                          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                                             <div>
                                                <label for="start_time_{{$i}}_{{$j}}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
                                                <input type="time" class="schedule-time-input mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" data-day="{{$i}}" data-slot="{{$j}}" id="start_time_{{$i}}_{{$j}}" name="arr[{{$i}}][start_time][{{$j}}]" value="{{ isset($d->start_time) ? \Carbon\Carbon::parse($d->start_time)->format('H:i') : '' }}">
                                             </div>
                                             <div>
                                                <label for="end_time_{{$i}}_{{$j}}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Time</label>
                                                <input type="time" class="schedule-time-input mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" data-day="{{$i}}" data-slot="{{$j}}" id="end_time_{{$i}}_{{$j}}" name="arr[{{$i}}][end_time][{{$j}}]" value="{{ isset($d->end_time) ? \Carbon\Carbon::parse($d->end_time)->format('H:i') : '' }}">
                                             </div>
                                             <div>
                                                <label for="duration_{{$i}}_{{$j}}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration</label>
                                                <select class="schedule-time-input mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" name="arr[{{$i}}][duration][{{$j}}]" data-day="{{$i}}" data-slot="{{$j}}" id="duration_{{$i}}_{{$j}}">
                                                   {!! html_entity_decode($d->options) !!}
                                                </select>
                                             </div>
                                             <div>
                                                @if($j != 0)
                                                   <button type="button" class="remove-slot-btn inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700" data-day="{{$i}}" data-slot="{{$j}}">delete</button>
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
                                       <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                                          <div>
                                             <label for="start_time_{{$i}}_0" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
                                             <input type="time" class="schedule-time-input mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" data-day="{{$i}}" data-slot="0" id="start_time_{{$i}}_0" name="arr[{{$i}}][start_time][0]">
                                          </div>
                                          <div>
                                             <label for="end_time_{{$i}}_0" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Time</label>
                                             <input type="time" class="schedule-time-input mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" data-day="{{$i}}" data-slot="0" id="end_time_{{$i}}_0" name="arr[{{$i}}][end_time][0]">
                                          </div>
                                          <div>
                                             <label for="duration_{{$i}}_0" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration</label>
                                             <select class="schedule-time-input mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" name="arr[{{$i}}][duration][0]" data-day="{{$i}}" data-slot="0" id="duration_{{$i}}_0">
                                                <option value="">Select Duration</option>
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
                           <button type="button" onclick="disablebtn()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">Submit</button>
                        @else
                           <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">Submit</button>
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
<script>
$(document).ready(function() {

    // Use event delegation for dynamically added elements
    $(document).on('change', '.schedule-time-input', function() {
        const day = $(this).data('day');
        const slot = $(this).data('slot');
        checkAndGenerateSlots(day, slot);
    });

    $(document).on('click', '.add-slot-btn', function() {
        const day = $(this).data('day');
        const totalSlots = parseInt($('#total_slot_day_' + day).val());
        const newSlotIndex = totalSlots;

        const newSlotHtml = `
            <div class="border border-gray-200 dark:border-gray-600 p-4 rounded-md" id="slotdiv${day}${newSlotIndex}">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label for="start_time_${day}_${newSlotIndex}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Time</label>
                        <input type="time" class="schedule-time-input mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" data-day="${day}" data-slot="${newSlotIndex}" id="start_time_${day}_${newSlotIndex}" name="arr[${day}][start_time][${newSlotIndex}]">
                    </div>
                    <div>
                        <label for="end_time_${day}_${newSlotIndex}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Time</label>
                        <input type="time" class="schedule-time-input mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" data-day="${day}" data-slot="${newSlotIndex}" id="end_time_${day}_${newSlotIndex}" name="arr[${day}][end_time][${newSlotIndex}]">
                    </div>
                    <div>
                        <label for="duration_${day}_${newSlotIndex}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration</label>
                        <select class="schedule-time-input mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600" name="arr[${day}][duration][${newSlotIndex}]" data-day="${day}" data-slot="${newSlotIndex}" id="duration_${day}_${newSlotIndex}">
                            <option value="">Select Duration</option>
                        </select>
                    </div>
                    <div>
                        <button type="button" class="remove-slot-btn inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700" data-day="${day}" data-slot="${newSlotIndex}">delete</button>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2" id="slot_${day}_${newSlotIndex}"></div>
            </div>
        `;

        $('#day_' + day).append(newSlotHtml);
        $('#total_slot_day_' + day).val(newSlotIndex + 1);
    });

    $(document).on('click', '.remove-slot-btn', function() {
        const day = $(this).data('day');
        const slot = $(this).data('slot');
        $('#slotdiv' + day + slot).remove();
    });

    function checkAndGenerateSlots(day, slot) {
        const startTime = $('#start_time_' + day + '_' + slot).val();
        const endTime = $('#end_time_' + day + '_' + slot).val();
        const duration = $('#duration_' + day + '_' + slot).val();

        if (startTime && endTime) {
            if (startTime >= endTime) {
                alert("Start Time must be less than End Time.");
                $('#duration_' + day + '_' + slot).html('<option value="">Select Duration</option>');
                $('#slot_' + day + '_' + slot).html('');
                return;
            }

            // Fetch possible durations
            $.get("{{ route('admin.doctors.findtime') }}", { start_time: startTime, end_time: endTime, duration: duration }, function(data) {
                $('#duration_' + day + '_' + slot).html(data);
                // After setting durations, if a duration is already selected, generate slots
                if ($('#duration_' + day + '_' + slot).val()) {
                    generateSlotPreviews(day, slot);
                }
            });
        }

        if (startTime && endTime && duration) {
            generateSlotPreviews(day, slot);
        }
    }

    function generateSlotPreviews(day, slot) {
        const startTime = $('#start_time_' + day + '_' + slot).val();
        const endTime = $('#end_time_' + day + '_' + slot).val();
        const duration = $('#duration_' + day + '_' + slot).val();

        if (!startTime || !endTime || !duration) {
            return; // Not enough info to generate slots
        }

        $.get("{{ route('admin.doctors.generateslot') }}", { start_time: startTime, end_time: endTime, duration: duration }, function(data) {
            const slotContainer = $('#slot_' + day + '_' + slot);
            slotContainer.html(''); // Clear previous slots
            
            // Parse the HTML response from the server
            const serverHtml = $(data);
            serverHtml.each(function() { // Correctly iterate over the returned slot elements
                const slotText = $(this).text();
                slotContainer.append(`<span class="bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-primary-900 dark:text-primary-300">${slotText}</span>`);
            });
        });
    }
});

function disablebtn() {
    alert("This function is disabled in demo mode.");
}
</script>
@stop
