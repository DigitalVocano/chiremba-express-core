@extends('layouts.doctor')
@section('title')
{{__('Schedule Timing')}}
@stop
@section('meta-data')
{{-- Meta data is handled by the main layout --}}
@stop
@section('content')
<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__('Schedule Timing')}}</h1>
</div>

@php
    // Prepare data for Alpine.js
    $days = [__("Sunday"), __("Monday"), __("Tuesday"), __("Wednesday"), __("Thursday"), __("Friday"), __("Saturday")];
    $formattedData = [];
    for ($i = 0; $i < 7; $i++) {
        $day_slots = collect($data)->where('day_id', $i)->values();
        $slots = [];
        if ($day_slots->isNotEmpty()) {
            foreach ($day_slots as $slot) {
                $slots[] = [
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'duration' => $slot->duration,
                ];
            }
        } else {
            // Add a default empty slot for days with no schedule
            $slots[] = ['start_time' => '', 'end_time' => '', 'duration' => ''];
        }
        $formattedData[$i] = $slots;
    }
@endphp

<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6" x-data="scheduleManager(JSON.parse('{{ json_encode($formattedData, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}'))">
    <form action="{{url('updatedoctortiming')}}" method="post">
        @csrf
        <input type="hidden" name="doctor_id" value="{{Session::get('user_id')}}"/>

        <!-- Tabs for Days of the Week -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-4 overflow-x-auto" aria-label="Tabs">
                @foreach($days as $index => $day)
                    <button type="button"
                            @click="activeTab = {{ $index }}"
                            :class="{ 'border-primary-600 text-primary-600': activeTab === {{ $index }}, 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600': activeTab !== {{ $index }} }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none">
                        {{ $day }}
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Tab Panels -->
        <div class="mt-6">
            @for($i=0; $i<7; $i++)
            <div x-show="activeTab === {{ $i }}" x-cloak>
                <input type="hidden" name="arr[]" value="{{$i}}">
                <div class="space-y-4">
                    <template x-for="(slot, index) in schedules[{{$i}}]" :key="index">
                        <div class="p-4 border dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-700/50 flex flex-wrap items-end gap-4">
                            <!-- Start Time -->
                            <div class="flex-1 min-w-[150px]">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Start Time')}}</label>
                                <input type="time" :name="'arr[{{$i}}][start_time]['+index+']'" x-model="slot.start_time" class="mt-1 block w-full form-input">
                            </div>
                            <!-- End Time -->
                            <div class="flex-1 min-w-[150px]">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('End Time')}}</label>
                                <input type="time" :name="'arr[{{$i}}][end_time]['+index+']'" x-model="slot.end_time" class="mt-1 block w-full form-input">
                            </div>
                            <!-- Duration -->
                            <div class="flex-1 min-w-[150px]">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Duration')}}</label>
                                <select :name="'arr[{{$i}}][duration]['+index+']'" x-model="slot.duration" class="mt-1 block w-full form-select">
                                    <option value="">{{__("Select Duration")}}</option>
                                    <option value="15">15 {{__("Minutes")}}</option>
                                    <option value="30">30 {{__("Minutes")}}</option>
                                    <option value="45">45 {{__("Minutes")}}</option>
                                    <option value="60">1 {{__("Hour")}}</option>
                                </select>
                            </div>
                            <!-- Remove Button -->
                            <div class="flex-shrink-0">
                                <button type="button" @click="removeSlot({{ $i }}, index)" x-show="schedules[{{$i}}].length > 1" class="px-3 py-2 text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400 focus:outline-none">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Add Slot Button -->
                <div class="mt-4">
                    <button type="button" @click="addSlot({{ $i }})" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                        <i class="fas fa-plus mr-2"></i>{{__("Add Time")}}
                    </button>
                </div>
            </div>
            @endfor
        </div>

        <div class="mt-8 flex justify-end">
            <button class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600" type="submit">
                {{__("Submit")}}
                <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </form>
</div>
@endsection
@section('footer_scripts')
<script>
function scheduleManager(initialData) {
    return {
        activeTab: new Date().getDay(), // 0 for Sunday, 1 for Monday, etc.
        schedules: initialData,
        addSlot(dayIndex) {
            this.schedules[dayIndex].push({
                start_time: '',
                end_time: '',
                duration: ''
            });
        },
        removeSlot(dayIndex, slotIndex) {
            if (this.schedules[dayIndex].length > 1) {
                this.schedules[dayIndex].splice(slotIndex, 1);
            } else {
                // If it's the last slot, just clear it instead of removing it
                this.schedules[dayIndex][slotIndex] = { start_time: '', end_time: '', duration: '' };
            }
        }
    };
}
</script>
@endsection
