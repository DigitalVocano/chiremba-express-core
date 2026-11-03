@extends('layouts.patient')
@section('title')
Patient Dashboard
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{__('KumbaCare')}}"/>
<meta property="og:title" content="{{__('Patient Dashboard')}}"/>
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
    @php
        // Recalculate appointment counts directly in the view to ensure accuracy.
        $userId = \Session::get("user_id");
        $totalappointment = \App\Models\BookAppointment::where("user_id", $userId)->count();
        $completeappointment = \App\Models\BookAppointment::where("user_id", $userId)->where('status', '4')->count();
        // Pending includes Received, Approved, and In Process statuses.
        $pendingappointment = \App\Models\BookAppointment::where("user_id", $userId)->whereIn('status', ['1', '2', '3'])->count();
    @endphp

    {{-- The outer layout structure is handled by layouts.patient --}}

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-semibold text-tele-dark-blue dark:text-gray-200">Patient Dashboard</h1>
        </div>

        {{-- The sidebar and main content flex layout are handled by layouts.patient --}}
        {{-- <div class="flex flex-col md:flex-row gap-4"> --}}
            {{-- <div class="md:w-1/4">
                @include('user.patient.partials.sidebar', ['userdata' => $userdata])
            </div> --}}

            {{-- Main Content Area --}}
            <div class="space-y-6"> {{-- Adjusted spacing --}}

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Appointment Summary Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @include('user.patient.partials.appointment-summary-card', ['title' => 'Total Appointments', 'count' => $totalappointment, 'icon' => 'fas fa-calendar-check'])
                    @include('user.patient.partials.appointment-summary-card', ['title' => 'Completed Appointments', 'count' => $completeappointment, 'icon' => 'fas fa-check-circle'])
                    @include('user.patient.partials.appointment-summary-card', ['title' => 'Pending Appointments', 'count' => $pendingappointment, 'icon' => 'fas fa-exclamation-triangle'])
                    {{-- Wallet Balance Card --}}
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex items-center justify-between">
                        <div class="flex-shrink-0">
                            <div class="text-purple-500 dark:text-purple-400 text-3xl">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                        <div class="flex-grow ml-4 text-right">
                            <div class="text-2xl font-bold text-tele-dark-blue dark:text-gray-200">
                                {{ $setting->currency }}{{ number_format($userdata->wallet->balance ?? 0, 2) }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{__('Wallet Balance')}}</div>
                        </div>
                    </div>
                </div>

                {{-- Doctor's Appointment List --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 sm:p-6">
                    @include('user.patient.partials.doctors-appointment-table', ['bookdata' => $bookdata, 'type' => $type])

                    {{-- Mobile Card View for Appointments --}}
                    @include('user.patient.partials.doctors-appointment-cards', ['bookdata' => $bookdata, 'type' => $type])
                </div>

            </div>
        {{-- </div> --}}
    {{-- </div> --}}

    {{-- Doctor Details Modal (Alpine.js and Tailwind CSS) --}}
    <div
        x-data="{
            showDoctorModal: false,
            doctorName: '',
            doctorPhone: '',
            doctorImage: '{{ asset('upload/profile/profile.png') }}',
            appointmentDate: '',
            appointmentSlot: '',
            appointmentStatus: '',
            appointmentStatusClass: '',
            prescriptionData: [],
            appointmentId: null
        }"
        x-show="showDoctorModal"
        @open-doctor-modal.window="
            showDoctorModal = true;
            doctorName = $event.detail.name;
            doctorPhone = $event.detail.phone;
            doctorImage = $event.detail.image || '{{ asset('upload/profile/profile.png') }}';
            appointmentSlot = $event.detail.slot_name;
            appointmentStatus = $event.detail.statusText;
            appointmentStatusClass = $event.detail.statusClass;
            prescriptionData = $event.detail.medicines;
            appointmentId = $event.detail.appointmentId;
        "
        @keydown.escape.window="showDoctorModal = false"
        style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background overlay --}}
            <div x-show="showDoctorModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="showDoctorModal = false" aria-hidden="true"></div>

            {{-- Modal panel --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                x-show="showDoctorModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-tele-dark-blue dark:text-gray-200" id="modal-title">
                        Doctor Details
                    </h3>
                    <button type="button" @click="showDoctorModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="mt-4 space-y-6">
                   {{-- Doctor Info Section --}}
                     <div class="flex flex-col sm:flex-row items-center sm:items-start mb-6 border-b dark:border-gray-700 pb-4 gap-4">
                        <div class="w-24 h-24 sm:w-1/4 sm:h-auto flex-shrink-0">
                           <img :src="doctorImage" alt="Doctor Image" class="object-cover w-full h-full rounded-lg shadow-md">
                        </div>
                        <div class="w-full sm:w-1/2 sm:ml-4 text-center sm:text-left">
                           <div class="text-xl font-semibold text-tele-dark-blue dark:text-gray-200" x-text="doctorName"></div>
                           <div class="text-base text-gray-600 dark:text-gray-400" x-text="doctorPhone"></div>
                           <div class="text-sm mt-2"><span class="px-3 py-1 rounded-full text-xs font-semibold" :class="appointmentStatusClass" x-text="appointmentStatus"></span></div>
                        </div>
                        <div class="w-full sm:w-1/4 text-center sm:text-right mt-4 sm:mt-0">
                           <div class="flex justify-center sm:justify-end items-center mb-1 text-gray-500 dark:text-gray-400">
                              <i class="fas fa-calendar mr-2"></i>
                              <span class="text-sm" x-text="appointmentSlot"></span>
                           </div>
                           <a :href="'{{ url('userchat') }}/' + appointmentId" class="mt-2 inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                               <i class="fas fa-comments mr-2"></i> Chat
                           </a>
                        </div>
                     </div>
                     {{-- Prescription Section --}}
                     <div class="mt-4">
                         <h4 class="text-lg font-semibold text-tele-dark-blue dark:text-gray-200 mb-3">
                            Prescription
                         </h4>
                          <div class="space-y-3 text-sm">
                            <template x-if="prescriptionData && prescriptionData.length > 0">
                                <template x-for="medicine in prescriptionData" :key="medicine.medicine_name">
                                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-700">
                                        <div class="font-semibold text-tele-dark-blue dark:text-gray-200 text-md" x-text="medicine.medicine_name"></div>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-1 mt-2 text-xs text-gray-700 dark:text-gray-400">
                                            <div><span class="font-medium text-gray-600 dark:text-gray-300">Type:</span> <span x-text="medicine.type"></span></div>
                                            <div><span class="font-medium text-gray-600 dark:text-gray-300">Dosage:</span> <span x-text="medicine.dosage"></span></div>
                                            <div><span class="font-medium text-gray-600 dark:text-gray-300">Time:</span> <span x-text="medicine.time.map(t => t.t_time).join(', ')"></span></div>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            Consume it for <span x-text="medicine.repeat_days"></span> Days
                                        </div>
                                    </div>
                                </template>
                            </template>
                            <template x-if="!prescriptionData || prescriptionData.length === 0">
                                <div class="text-gray-500 dark:text-gray-400 italic py-4 text-center">Prescription not found</div>
                            </template>
                          </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="path_admin" value="{{ url('/') }}">
@endsection

@section('footer_scripts') {{-- This section is correctly defined in layouts.patient --}}
    <script>
        function get_desc(id){
            $.ajax({
                url: $("#path_admin").val() + "/get-user-appointment/" + id,
                data: { },
                success: function(data)
                {
                    console.log(data);
                    let doctorImage = data.doctorls && data.doctorls.image ? "{{ asset('upload/doctors') }}/" + data.doctorls.image : "{{ asset('upload/profile/profile.png') }}";

                    var statusText = "";
                    var statusClass = "";
                    switch(data.status.toString()) {
                        case '1': statusText = "{{ __('Received') }}"; statusClass = 'bg-blue-100 text-blue-700 dark:bg-blue-700 dark:text-blue-100'; break;
                        case '2': statusText = "{{ __('Approved') }}"; statusClass = 'bg-green-100 text-green-700 dark:bg-green-700 dark:text-green-100'; break;
                        case '3': statusText = "{{ __('In Process') }}"; statusClass = 'bg-yellow-100 text-yellow-700 dark:bg-yellow-600 dark:text-yellow-100'; break;
                        case '4': statusText = "{{ __('Completed') }}"; statusClass = 'bg-green-100 text-green-700 dark:bg-green-700 dark:text-green-100'; break;
                        case '5': statusText = "{{ __('Rejected') }}"; statusClass = 'bg-red-100 text-red-700 dark:bg-red-700 dark:text-red-100'; break;
                        default: statusText = "{{ __('Absent') }}"; statusClass = 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200'; break;
                    }

                    let medicines = [];
                    if(data.medicine && data.medicine.medicine && Array.isArray(data.medicine.medicine)){
                        medicines = data.medicine.medicine.map(med => {
                            let tTime = "";
                            if(med.time && med.time.length > 0) {
                                tTime = med.time.map(t => t.t_time).join(', ');
                            }
                            return {
                                medicine_name: med.medicine_name,
                                type: med.type,
                                dosage: med.dosage,
                                time: med.time, // Keep original time array for Alpine template
                                repeat_days: med.repeat_days
                            };
                        });
                    }

                    // Dispatch custom event for Alpine.js to pick up
                    window.dispatchEvent(new CustomEvent('open-doctor-modal', {
                        detail: {
                            name: data.doctorls ? data.doctorls.name : 'N/A',
                            phone: data.phone || 'N/A',
                            image: doctorImage,
                            slot_name: data.slot_name || 'N/A',
                            statusText: statusText,
                            statusClass: statusClass,
                            medicines: medicines,
                            appointmentId: id
                        }
                    }));
                }
            });
        }
    </script>
@endsection
