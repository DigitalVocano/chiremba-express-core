<!-- Complete Appointment Modal -->
<div x-show="isCompleteModalOpen" style="display: none;" @keydown.escape.window="isCompleteModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="isCompleteModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isCompleteModalOpen = false" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="isCompleteModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
            <div class="flex items-start justify-between pb-3 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="modal-title">{{__("Complete Appointment")}}</h3>
                <button type="button" @click="isCompleteModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{__("Are you sure you want to mark this appointment as completed? This action cannot be undone.")}}
                </p>
                <form action="{{ route('complete-doctor-appointment') }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="id" value="{{ $am->id }}">
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="isCompleteModalOpen = false" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-500">{{__("Cancel")}}</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md shadow-sm text-sm font-medium hover:bg-blue-600 focus:outline-none">{{__("Complete")}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Report Modal -->
<div x-show="isReportModalOpen" style="display: none;" @keydown.escape.window="isReportModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="isReportModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isReportModalOpen = false" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="isReportModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
            <div class="flex items-start justify-between pb-3 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{__("Upload Report/Image")}}</h3>
                <button type="button" @click="isReportModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ url('save_prescription') }}" method="POST" enctype="multipart/form-data" class="mt-4" x-ref="reportForm">
                @csrf
                <input type="hidden" name="id" value="{{ $am->id }}">
                <div class="space-y-4">
                    <div>
                        <label for="report_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Report Name')}}</label>
                        <input type="text" name="name" id="report_name" x-model="reportName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required>
                    </div>
                    <div>
                        <label for="report_img" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Report File (Image or PDF)')}}</label>
                        <input type="file" name="report_img" id="report_img" @change="reportFile = $event.target.files[0]" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" required>
                    </div>
                </div>

                {{-- AI Analysis Section --}}
                <div class="mt-6" x-show="reportFile">
                    <button type="button" @click="analyzeReport" :disabled="isAnalyzingReport" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400">
                        <template x-if="isAnalyzingReport">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                        <i class="fas fa-microscope mr-2" x-show="!isAnalyzingReport"></i>
                        <span x-text="isAnalyzingReport ? '{{__('Analyzing Report...')}}' : '{{__('Analyze with AI')}}'"></span>
                    </button>
                </div>

                <template x-if="reportError">
                    <div class="mt-4 p-3 text-sm text-red-700 bg-red-100 dark:bg-red-200 dark:text-red-800 rounded-lg" x-text="reportError"></div>
                </template>

                <template x-if="reportAnalysis">
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4 space-y-4">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{__('AI Analysis Result')}}</h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('AI Suggested Diagnosis')}}</label>
                            <input type="text" x-model="reportAnalysis.diagnosis" class="mt-1 block w-full form-input bg-gray-100 dark:bg-gray-700" readonly>
                        </div>

                        <h5 class="font-semibold text-gray-700 dark:text-gray-200">{{__('Key Findings & Reasoning')}}</h5>
                        <div class="space-y-3">
                            <template x-for="(item, index) in reportAnalysis.findings" :key="index">
                                <div class="p-3 border rounded-md dark:border-gray-600">
                                    <p class="font-medium text-gray-800 dark:text-gray-200" x-text="item.finding"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="item.reasoning"></p>
                                </div>
                            </template>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('AI Summary')}}</label>
                            <textarea x-model="reportAnalysis.summary" rows="3" class="mt-1 block w-full form-textarea bg-gray-100 dark:bg-gray-700" readonly></textarea>
                        </div>

                        <div class="border-t border-dashed dark:border-gray-600 pt-4">
                            <label for="doctor_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Your Confirmation & Notes (Required)')}}</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{__("Please review the AI's analysis. Add your professional conclusion, corrections, or confirmation below. This will be saved with the report.")}}</p>
                            <textarea name="doctor_notes" id="doctor_notes" x-model="doctorNotes" rows="4" class="mt-1 block w-full form-textarea" required></textarea>
                        </div>

                        {{-- Hidden input to submit the AI analysis --}}
                        <input type="hidden" name="ai_analysis" :value="JSON.stringify(reportAnalysis)">
                    </div>
                </template>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="isReportModalOpen = false" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-500">{{__("Cancel")}}</button>
                    <button type="submit" :disabled="isAnalyzingReport" class="px-4 py-2 bg-primary-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-primary-700 focus:outline-none disabled:bg-primary-400">
                        <span x-show="!reportAnalysis">{{__("Upload Report")}}</span>
                        <span x-show="reportAnalysis">{{__("Confirm & Save Report")}}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Prescription Modal -->
<div x-show="isPrescriptionModalOpen" style="display: none;" @keydown.escape.window="isPrescriptionModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="isPrescriptionModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="isPrescriptionModalOpen = false" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="isPrescriptionModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
            <div class="flex items-start justify-between pb-3 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{__("Add New Prescription")}}</h3>
                <button type="button" @click="isPrescriptionModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div>
            {{-- AI Assistant Section within Modal --}}
            <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg mb-6 border border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center mb-2">
                    <h4 class="text-md font-semibold text-gray-700 dark:text-gray-200">{{__("AI Prescription Assistant")}}</h4>
                    <template x-if="aiRemainingCredits !== null">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{__('Credits:')}} <span x-text="aiRemainingCredits" class="font-bold text-primary-600"></span></span>
                    </template>
                </div>
                <button @click="getAiSuggestion()" :disabled="aiIsLoading" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800 disabled:bg-primary-400 disabled:cursor-not-allowed">
                    <template x-if="aiIsLoading">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <i class="fas fa-robot mr-2" x-show="!aiIsLoading"></i>
                    <span x-text="aiIsLoading ? '{{__('Analyzing...')}}' : '{{__('Get AI Suggestion')}}'"></span>
                </button>

                {{-- Error Message --}}
                <template x-if="aiError">
                    <div class="mt-3 p-3 text-xs text-red-700 bg-red-100 dark:bg-red-200 dark:text-red-800 rounded-lg" role="alert" x-text="aiError"></div>
                </template>

                {{-- Suggestions Display --}}
                <template x-if="aiSuggestions">
                    <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-3 space-y-3 text-sm">
                        <div>
                            <h5 class="font-semibold text-gray-700 dark:text-gray-200">{{__('Potential Diagnoses:')}}</h5>
                            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 mt-1">
                                <template x-for="diagnosis in aiSuggestions.diagnoses" :key="diagnosis">
                                    <li x-text="diagnosis"></li>
                                </template>
                            </ul>
                        </div>
                        <div>
                            <h5 class="font-semibold text-gray-700 dark:text-gray-200">{{__('Suggested Prescriptions:')}}</h5>
                            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 mt-1">
                                <template x-for="prescription in aiSuggestions.prescriptions" :key="prescription">
                                    <li x-text="prescription"></li>
                                </template>
                            </ul>
                        </div>
                        <div>
                            <h5 class="font-semibold text-gray-700 dark:text-gray-200">{{__('Additional Notes:')}}</h5>
                            <p class="text-gray-600 dark:text-gray-400 mt-1" x-text="aiSuggestions.notes"></p>
                        </div>
                    </div>
                </template>
            </div>

            <form action="{{ url('save_prescription') }}" method="POST" class="mt-4">
                @csrf
                <input type="hidden" name="id" value="{{ $am->id }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="relative">
                        <label for="medicine_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Medicine Name')}}</label>
                        <input type="text" name="medicine[0][medicine_name]" id="medicine_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required x-model="medicineSearch"
                               @input.debounce.300ms="fetchSuggestions()"
                               @focus="showMedicineSuggestions = true"
                               autocomplete="off">
                        <div x-show="showMedicineSuggestions && medicineSuggestions.length > 0" @click.away="showMedicineSuggestions = false" class="absolute z-20 w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md mt-1 shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="suggestion in medicineSuggestions" :key="suggestion.id">
                                <div @click="selectSuggestion(suggestion)" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700" x-text="suggestion.name"></div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label for="medicine_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Medicine Type')}}</label>
                        <input type="text" name="medicine[0][type]" id="medicine_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required x-model="medicineType">
                    </div>
                    <div>
                        <label for="dosage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Dosage')}}</label>
                        <input type="text" name="medicine[0][dosage]" id="dosage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required x-model="dosage">
                    </div>
                    <div>
                        <label for="repeat_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Days')}}</label>
                        <input type="number" name="medicine[0][repeat_days]" id="repeat_days" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Time')}}</label>
                    <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <label class="flex items-center"><input type="checkbox" name="medicine[0][time][]" value="Morning" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-primary-500"> <span class="ml-2 text-gray-700 dark:text-gray-300">{{__('Morning')}}</span></label>
                        <label class="flex items-center"><input type="checkbox" name="medicine[0][time][]" value="Afternoon" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-primary-500"> <span class="ml-2 text-gray-700 dark:text-gray-300">{{__('Afternoon')}}</span></label>
                        <label class="flex items-center"><input type="checkbox" name="medicine[0][time][]" value="Evening" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-primary-500"> <span class="ml-2 text-gray-700 dark:text-gray-300">{{__('Evening')}}</span></label>
                        <label class="flex items-center"><input type="checkbox" name="medicine[0][time][]" value="Night" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:checked:bg-primary-500"> <span class="ml-2 text-gray-700 dark:text-gray-300">{{__('Night')}}</span></label>
                    </div>
                </div>
                <div class="mt-4">
                    <label for="comment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Comment')}}</label>
                    <textarea name="medicine[0][comment]" id="comment" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="isPrescriptionModalOpen = false" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-500">{{__("Cancel")}}</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-primary-700 focus:outline-none">{{__("Save Prescription")}}</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>