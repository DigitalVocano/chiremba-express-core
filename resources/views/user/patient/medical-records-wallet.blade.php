@extends('layouts.patient')

@section('title')
    Medical Records
@stop

@section('meta-data')
    <meta property="og:type" content="website" />
    <meta property="og:url" content="KumbaCare" />
    <meta property="og:title" content="Medical Wallet" />
    <meta property="og:image" content="{{ asset('image_web/'). '/'. $setting->favicon }}" />
    <meta property="og:image:width" content="250px" />
    <meta property="og:image:height" content="250px" />
    <meta property="og:site_name" content="KumbaCare" />
    <meta property="og:description" content="Manage your medical records, prescriptions, and images securely in your KumbaCare wallet." />
    <meta property="og:keyword" content="medical records, e-prescription, patient wallet, healthcare" />
    <link rel="shortcut icon" href="{{ asset('image_web/'). '/'. $setting->favicon }}">
@stop

@section('content')
    {{-- The outer layout structure is handled by layouts.patient --}}

    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">Medical Records</h1>
    </div>

    {{-- Alert Messages --}}
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

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6" x-data="medicalWalletManager()">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex-grow">
                <h3 class="text-xl font-semibold text-tele-dark-blue dark:text-gray-200">Your Medical Records</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 max-w-prose">
                    Upload and manage your prescriptions, lab reports, and images. You can control which records are visible to your doctor.
                </p>
            </div>
            <button type="button" @click="openModal()" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 flex-shrink-0">
                Create New Medical Record
            </button>
        </div>

        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                @php
                    $tabs = [
                        'e-prescription' => ['label' => 'E-Prescriptions', 'icon' => 'fa-file-prescription'],
                        'image' => ['label' => 'Images', 'icon' => 'fa-file-image'],
                        'pdf' => ['label' => 'PDFs', 'icon' => 'fa-file-pdf'],
                        'appointment' => ['label' => 'Appointment Files', 'icon' => 'fa-file-invoice'],
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                    <button @click="activeTab = '{{ $key }}'"
                            :class="{
                                'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === '{{ $key }}',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-200 dark:hover:border-gray-600': activeTab !== '{{ $key }}'
                            }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none flex items-center">
                        <i class="fas {{ $tab['icon'] }} mr-2"></i> {{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="mt-4">
            @foreach($tabs as $key => $tab)
                <div x-show="activeTab === '{{ $key }}'" x-cloak>
                    @include('user.patient.partials.medical-records-section', [
                        'title' => $tab['label'],
                        'records' => $medicalRecords->where('record_type', $key)
                    ])
                </div>
            @endforeach
        </div>

        {{-- Create New Record Modal --}}
        <div x-show="showCreateModal" style="display: none;" @keydown.escape.window="closeModal()" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal()" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showCreateModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                    <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="modal-title">Create New Medical Record</h3>
                        <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                            <span class="sr-only">Close</span> <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('upload.medical.record') }}" method="POST" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="record_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Record Type</label>
                                <select name="record_type" id="record_type" x-model="recordType" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm" required>
                                    <option value="">Select Record Type</option>
                                    <option value="e-prescription">E-Prescription</option>
                                    <option value="image">Image</option>
                                    <option value="pdf">PDF</option>
                                    <option value="appointment">Appointment File</option>
                                </select>
                            </div>

                            <template x-if="recordType === 'e-prescription'">
                                {{-- E-Prescription fields --}}
                                <div class="col-span-full grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="appointment_id_eprescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Appointment</label>
                                        <select name="appointment_id" id="appointment_id_eprescription" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                            <option value="">-- Select Appointment --</option>
                                            @foreach ($bookdata as $appointment)
                                                <option value="{{ $appointment->id }}">{{ $appointment->doctorls->name }} - {{ $appointment->date }} {{ $appointment->slot_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="document_name_eprescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document Name</label>
                                        <input type="text" name="document_name" id="document_name_eprescription" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm" placeholder="e.g., Prescription for Flu" required>
                                    </div>
                                </div>
                            </template>

                            <template x-if="recordType === 'appointment'">
                                {{-- Appointment File fields --}}
                                <div class="col-span-full">
                                    <label for="appointment_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Appointment</label>
                                    <select name="appointment_id" id="appointment_id_appointment" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                        <option value="">-- Select Appointment --</option>
                                        @foreach ($bookdata as $appointment)
                                            <option value="{{ $appointment->id }}">{{ $appointment->doctorls->name }} - {{ $appointment->date }} {{ $appointment->slot_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </template>

                            <template x-if="recordType === 'image' || recordType === 'pdf'">
                                {{-- Image/PDF fields --}}
                                <div class="col-span-full grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="sm:col-span-1">
                                        <label for="document_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document Name:</label>
                                        <input type="text" name="document_name" id="document_name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm" placeholder="e.g., Lab Report" required>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <label for="document" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Document:</label>
                                        <input type="file" name="document" id="document" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-600 file:text-white hover:file:bg-primary-700 dark:file:bg-primary-500 dark:file:text-white dark:hover:file:bg-primary-600" required>
                                    </div>
                                </div>
                            </template>

                            <template x-if="recordType">
                                {{-- Visibility field --}}
                                <div class="col-span-full">
                                <label for="visibility" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Visibility:</label>
                                <select name="visibility" id="visibility" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                                    <option value="visible">Visible to Doctor</option>
                                    <option value="hidden">Hidden from Doctor</option>
                                </select>
                                </div>
                            </template>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3 col-span-full">
                            <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 focus:outline-none">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
<script>
    function medicalWalletManager() {
        return {
            showCreateModal: false,
            activeTab: 'e-prescription',
            recordType: '',
            openModal() {
                this.recordType = '';
                this.showCreateModal = true;
            },
            closeModal() {
                this.showCreateModal = false;
            }
        };
    }
</script>
@stop
