@extends('layouts.doctor')
@section('title')
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
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="prescriptionPageManager({{ $am->id }})">
    <div class="mb-4">
        <a href="{{ url('doctorappointment') }}" class="text-primary-600 hover:underline dark:text-primary-400 dark:hover:text-primary-300">
            <i class="fas fa-arrow-left mr-2"></i>{{__('Back to appointment list')}}
        </a>
    </div>

    @if(Session::has('message'))
        <div class="mb-4 p-4 text-sm {{ Session::get('alert-class', 'text-blue-700 bg-blue-100 dark:bg-blue-200 dark:text-blue-800') }} rounded-lg" role="alert">
            {{ Session::get('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column --}}
        <div class="lg:col-span-1 space-y-8">
            {{-- Patient Details Card --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <div class="flex flex-col items-center">
                    <figure class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-200 dark:border-gray-700 shadow-lg">
                        @if($am->patientls && $am->patientls->profile_pic)
                            <img src="{{ asset('upload/profile').'/'.$am->patientls->profile_pic }}" alt="{{ $am->patientls->name }}" class="object-cover w-full h-full">
                        @else
                            <img src="{{ asset('upload/profile/profile.png') }}" alt="Default Patient" class="object-cover w-full h-full">
                        @endif
                    </figure>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mt-4">{{ $am->patientls->name ?? __('Unknown Patient') }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $am->patientls->email ?? '' }}</p>
                </div>

                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">{{__("Appointment Info")}}</h4>
                    <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start">
                            <i class="fas fa-calendar-alt w-5 text-center text-primary-600 dark:text-primary-400 mt-1"></i>
                            <span class="ml-3">
                                <span class="font-semibold">{{__("Date")}}:</span> {{ date("F d, Y", strtotime($am->date)) }}
                            </span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-clock w-5 text-center text-primary-600 dark:text-primary-400 mt-1"></i>
                            <span class="ml-3">
                                <span class="font-semibold">{{__("Time")}}:</span> {{ $am->slot_name }}
                            </span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-phone w-5 text-center text-primary-600 dark:text-primary-400 mt-1"></i>
                            <span class="ml-3">
                                <span class="font-semibold">{{__("Phone")}}:</span> {{ $am->phone }}
                            </span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-sticky-note w-5 text-center text-primary-600 dark:text-primary-400 mt-1"></i>
                            <span class="ml-3">
                                <span class="font-semibold">{{__("Notes")}}:</span> {{ $am->user_description ?: __('No notes provided.') }}
                            </span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-info-circle w-5 text-center text-primary-600 dark:text-primary-400 mt-1"></i>
                            <span class="ml-3 flex items-center">
                                <span class="font-semibold mr-2">{{__("Status")}}:</span>
                                @php
                                    $statusText = __('Absent');
                                    $statusClass = 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300';
                                    if($am->status=='1'){ $statusText = __("Received"); $statusClass = 'bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100'; }
                                    else if($am->status=='2'){ $statusText = __("Approved"); $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'; }
                                    else if($am->status=='3'){ $statusText = __("In Process"); $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100'; }
                                    else if($am->status=='4'){ $statusText = __("Completed"); $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'; }
                                    else if($am->status=='5'){ $statusText = __("Rejected"); $statusClass = 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100'; }
                                @endphp
                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{$statusClass}}">
                                    {{$statusText}}
                                </span>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="lg:col-span-2 space-y-8">
            {{-- Actions Card --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4">{{__("Actions")}}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <button @click="isCompleteModalOpen = true" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                        <i class="fas fa-check-circle mr-2"></i> {{__("Complete Appointment")}}
                    </button>
                    <button @click="isPrescriptionModalOpen = true" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800">
                        <i class="fas fa-pills mr-2"></i> {{__("Add Prescription")}}
                    </button>
                    <button @click="isReportModalOpen = true" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        <i class="fas fa-file-upload mr-2"></i> {{__("Upload Report")}}
                    </button>
                </div>
            </div>

            {{-- Prescription Details Card --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4">{{__("Prescription Details")}}</h3>
                <div class="space-y-4">
                    @if(count($app_medicine) > 0)
                        @foreach($app_medicine as $a_m)
                            @php
                                $m = json_decode($a_m->medicines);
                                $medicine_list = $m->medicine ?? [];
                            @endphp
                            @foreach($medicine_list as $medicine_item)
                                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-md shadow-sm relative">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-bold text-gray-800 dark:text-gray-100">{{ $medicine_item->medicine_name ?? '' }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $medicine_item->type ?? '' }} - {{ $medicine_item->dosage ?? '' }}</p>
                                        </div>
                                        <div class="flex space-x-3">
                                            <a href="{{ url('delete_prescription', $a_m->id) }}" onclick="return confirm('{{__('Are you sure you want to delete this prescription?')}}')" class="text-red-500 hover:text-red-700 dark:hover:text-red-400" title="{{__('delete')}}"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </div>
                                    <div class="mt-3 border-t border-gray-200 dark:border-gray-600 pt-3">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <strong>{{__("Duration")}}:</strong> {{ $medicine_item->repeat_days ?? 'N/A' }} {{__("Days")}}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            <strong>{{__("Time")}}:</strong>
                                            @if(isset($medicine_item->time) && is_array($medicine_item->time))
                                                @foreach($medicine_item->time as $time)
                                                    @php
                                                        $time_value = is_object($time) ? ($time->t_time ?? '') : $time;
                                                    @endphp
                                                    <span class="inline-block bg-gray-200 dark:bg-gray-600 rounded-full px-2 py-0.5 text-xs font-semibold text-gray-700 dark:text-gray-200 mr-1 mb-1">{{ $time_value }}</span>
                                                @endforeach
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    @else
                        <div class="text-center text-gray-500 dark:text-gray-400 py-6">
                            <i class="fas fa-pills text-3xl text-gray-400"></i>
                            <p class="mt-2">{{__("No prescription details found.")}}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Reports Card --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-4">{{__("Uploaded Reports & Images")}}</h3>
                @if(count($img) > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        @foreach($img as $image)
                            @php
                                $fileExtension = strtolower(pathinfo($image->image, PATHINFO_EXTENSION));
                                $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
                            <div class="group relative cursor-pointer" @if($image->is_verified) @click="openReportDetail({{ $image->id }})" @else @click.prevent @endif>
                                <a href="{{ $image->is_verified ? '#' : asset('upload/ap_imd_up').'/'.$image->image }}" @if(!$image->is_verified && $isImage) data-fancybox="gallery" @elseif(!$image->is_verified) target="_blank" @endif data-caption="{{ $image->name }}">
                                    @if($isImage)
                                        <img src="{{ asset('upload/ap_imd_up').'/'.$image->image }}" alt="{{ $image->name }}" class="w-full h-32 object-cover rounded-lg shadow-sm transition-transform duration-300 group-hover:scale-105">
                                    @else
                                        <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center transition-transform duration-300 group-hover:scale-105">
                                            <i class="fas fa-file-alt text-4xl text-gray-500"></i>
                                        </div>
                                    @endif
                                    @if($image->is_verified)
                                        <div class="absolute top-2 left-2 bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg flex items-center pointer-events-none">
                                            <i class="fas fa-microscope mr-1"></i> AI Verified
                                        </div>
                                    @endif
                                </a>
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-2 rounded-b-lg truncate">{{ $image->name }}</div>
                                <a href="{{ url('delete_report', $image->id) }}" onclick="return confirm('{{__('Are you sure you want to delete this report?')}}')" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" title="{{__('delete')}}">
                                    <i class="fas fa-trash text-xs"></i>
                                </a>
                                @if($image->is_verified)
                                    <div class="absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-lg pointer-events-none">
                                        <span class="text-white font-bold">{{__('View Analysis')}}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500 dark:text-gray-400 py-6">
                        <i class="fas fa-file-image text-3xl text-gray-400"></i>
                        <p class="mt-2">{{__("No reports or images have been uploaded.")}}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Include Modals --}}
    @include('user.doctor.partials.prescription-modals')

    <!-- View Report Analysis Modal -->
    <div x-show="isReportDetailModalOpen" style="display: none;" @keydown.escape.window="isReportDetailModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isReportDetailModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isReportDetailModalOpen = false" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="isReportDetailModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                <div class="flex items-start justify-between pb-3 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="selectedReport ? selectedReport.name : 'Report Details'"></h3>
                    <button type="button" @click="isReportDetailModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mt-4 max-h-[70vh] overflow-y-auto" x-show="selectedReportId">
                    <div class="space-y-4">
                        <a :href="selectedReport ? '{{ asset('upload/ap_imd_up') }}/' + selectedReport.image : '#'" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-external-link-alt mr-2"></i> {{__('View Original File')}}
                        </a>
                        
                        {{-- Discussion Area --}}
                        <div class="space-y-4">
                            <template x-for="message in discussionHistory" :key="message.id">
                                <div class="p-4 rounded-lg" :class="message.role === 'ai' ? 'bg-gray-100 dark:bg-gray-700' : 'bg-blue-50 dark:bg-blue-900/50'">
                                    <p class="font-semibold text-sm" :class="message.role === 'ai' ? 'text-gray-700 dark:text-gray-200' : 'text-blue-700 dark:text-blue-300'" x-text="message.role === 'ai' ? 'AI Analysis' : 'Your Feedback'"></p>
                                    <div class="prose prose-sm dark:prose-invert max-w-none" x-html="formatDiscussionContent(message.content)"></div>
                                </div>
                            </template>
                        </div>

                        {{-- Doctor's Feedback/Argument Area --}}
                        <div class="border-t dark:border-gray-600 pt-4">
                            <label for="doctorFeedback" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__("Argue or Refine Diagnosis")}}</label>
                            <textarea id="doctorFeedback" x-model="doctorFeedback" rows="3" class="mt-1 block w-full form-textarea" placeholder="{{__('e.g., The patient has no fever, please reconsider...')}}"></textarea>
                            <button @click="refineAnalysis" :disabled="isRefining" class="mt-2 w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 disabled:bg-primary-400">
                                <template x-if="isRefining">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </template>
                                <i class="fas fa-lightbulb mr-2" x-show="!isRefining"></i>
                                <span x-text="isRefining ? '{{__('Refining...')}}' : '{{__('Send Feedback & Refine')}}'"></span>
                            </button>
                            <template x-if="refinementError">
                                <p class="text-red-500 text-xs mt-2" x-text="refinementError"></p>
                            </template>
                        </div>

                        {{-- Finalize Report Area --}}
                        <div class="border-t dark:border-gray-600 pt-4">
                            <label for="finalDoctorNotes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__("Final Confirmation & Notes")}}</label>
                            <textarea id="finalDoctorNotes" x-model="finalDoctorNotes" rows="4" class="mt-1 block w-full form-textarea" placeholder="{{__('Add your final professional conclusion here.')}}"></textarea>
                            <button @click="finalizeReport" class="mt-2 w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-check-circle mr-2"></i>
                                {{__("Confirm & Finalize Report")}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer_scripts')
<script>
function prescriptionPageManager(appointmentId) {
    return {
        // Modal states
        isCompleteModalOpen: false,
        isReportDetailModalOpen: false,
        isReportModalOpen: false,
        isPrescriptionModalOpen: false,

        // Prescription form state
        medicineSearch: '',
        medicineType: '', // For the form field
        dosage: '', // For the form field
        medicineSuggestions: [],
        showMedicineSuggestions: false,

        // AI Assistant state
        appointmentId: appointmentId,
        aiIsLoading: false,
        aiSuggestions: null,
        aiError: '',
        aiRemainingCredits: null,

        // Report analysis state
        reportName: '',
        reportFile: null,
        isAnalyzingReport: false,
        reportAnalysis: null,
        reportError: '',
        doctorNotes: '',
        selectedReportId: null,
        discussionHistory: [],
        doctorFeedback: '',
        isRefining: false,
        refinementError: '',
        finalDoctorNotes: '',
        parsedAnalysis: null,

        // --- Methods for Prescription Form ---
        fetchSuggestions() {
            if (this.medicineSearch.length < 2) {
                this.medicineSuggestions = [];
                this.showMedicineSuggestions = false;
                return;
            }
            fetch(`{{ url('/getmedicines') }}?inputText=${this.medicineSearch}`)
                .then(response => response.json())
                .then(data => {
                    this.medicineSuggestions = data.suggestions;
                    this.showMedicineSuggestions = true;
                })
                .catch(error => console.error('Error fetching medicines:', error));
        },
        selectSuggestion(suggestion) {
            this.medicineSearch = suggestion.name;
            this.medicineType = suggestion.medicine_type;
            this.dosage = suggestion.dosage;
            this.showMedicineSuggestions = false;
        },

        // --- Methods for AI Assistant ---
        getAiSuggestion() {
            this.aiIsLoading = true;
            this.aiSuggestions = null;
            this.aiError = '';

            fetch('{{ route('doctor.ai.getSuggestion') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    appointment_id: this.appointmentId
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.error || 'An unknown error occurred.') });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.aiSuggestions = data.suggestions;
                    this.aiRemainingCredits = data.remaining_credits;
                } else {
                    this.aiError = data.error || 'Failed to get suggestions.';
                }
            })
            .catch(error => {
                this.aiError = error.message;
            })
            .finally(() => {
                this.aiIsLoading = false;
            });
        },

        saveReport() {
            // This will now submit the form programmatically.
            this.$refs.reportForm.submit();
        },

        openReportDetail(reportId) {
            this.selectedReportId = reportId;
            this.discussionHistory = [];
            this.refinementError = '';
            this.doctorFeedback = '';
            this.finalDoctorNotes = '';
            // Fetch the full report details including the discussion history
            fetch(`/doctor/ai/get-report-details/${reportId}`)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        this.selectedReport = data.report;
                        this.discussionHistory = data.report.discussions;
                        this.finalDoctorNotes = data.report.doctor_notes; // Pre-fill with existing notes
                    } else {
                        // handle error
                    }
                });
            this.isReportDetailModalOpen = true;
        },

        formatDiscussionContent(content) {
            try {
                const parsed = JSON.parse(content);
                let html = `<strong>Diagnosis:</strong> ${parsed.diagnosis}<br>`;
                html += `<strong>Summary:</strong> ${parsed.summary}<br><br>`;
                html += `<strong>Findings:</strong><ul>`;
                parsed.findings.forEach(finding => {
                    html += `<li><strong>${finding.finding}</strong>: ${finding.reasoning}</li>`;
                });
                html += `</ul>`;
                return html;
            } catch (e) {
                // If it's not JSON, it's a plain text message from the doctor
                return `<p>${content}</p>`;
            }
        },

        analyzeReport() {
            if (!this.reportFile) {
                this.reportError = 'Please select a file to analyze.';
                return;
            }
            this.isAnalyzingReport = true;
            this.reportAnalysis = null;
            this.reportError = '';
            const formData = new FormData();
            formData.append('report_file', this.reportFile);

            fetch('{{ route('doctor.ai.analyzeReport') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.error || 'Analysis failed.') });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.reportAnalysis = data.analysis;
                    // You can also update a shared credit counter if you have one
                } else {
                    this.reportError = data.error || 'Failed to get analysis.';
                }
            })
            .catch(error => {
                this.reportError = error.message;
            })
            .finally(() => {
                this.isAnalyzingReport = false;
            });
        },

        refineAnalysis() {
            if (!this.doctorFeedback.trim()) {
                this.refinementError = 'Please enter your feedback or question.';
                return;
            }
            this.isRefining = true;
            this.refinementError = '';

            // Add doctor's feedback to the UI immediately for a responsive feel
            this.discussionHistory.push({ id: 'temp_doctor_' + Date.now(), role: 'doctor', content: this.doctorFeedback });

            fetch('{{ route('doctor.ai.refineReport') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ report_id: this.selectedReportId, feedback: this.doctorFeedback })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.discussionHistory.push(data.new_message); // Add AI's new response
                    this.doctorFeedback = ''; // Clear the input
                } else {
                    this.refinementError = data.error || 'Failed to refine analysis.';
                    // Remove the temporary doctor message on error
                    this.discussionHistory.pop();
                }
            })
            .catch(err => {
                this.refinementError = 'An unexpected error occurred.';
                this.discussionHistory.pop();
            })
            .finally(() => this.isRefining = false);
        },

        finalizeReport() {
            if (!this.finalDoctorNotes.trim()) {
                alert('Please enter your final notes before finalizing.');
                return;
            }
            fetch('{{ route('doctor.ai.finalizeReport') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ report_id: this.selectedReportId, final_notes: this.finalDoctorNotes })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    this.isReportDetailModalOpen = false;
                    alert('Report has been finalized successfully.'); // Or use a nicer notification
                    window.location.reload(); // Reload to show updated state
                } else {
                    alert('Error: ' + data.error);
                }
            });
        }
    }
}
</script>
@endsection