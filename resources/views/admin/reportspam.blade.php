@extends('admin.layout')

@section('title')
{{ __("Complaints") }} | {{ __("Admin") }}
@stop

@section('content')
<div class="main-content">
    <div class="page-content p-4 md:p-6">
        <div class="container-fluid mx-auto space-y-6">

            <!-- Hero Header Card -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0A2B68] via-[#0F3A8A] to-[#1E40AF] p-6 md:p-8 text-white shadow-xl">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-blue-200 mb-2">
                            <a href="{{ url('admin/dashboard') }}" class="hover:underline flex items-center gap-1">
                                <i class="uil uil-home"></i> {{ __("Dashboard") }}
                            </a>
                            <span>/</span>
                            <span>{{ __("Complaints & Spam Reports") }}</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                            <i class="uil uil-exclamation-octagon text-lime-400 text-3xl"></i>
                            {{ __("Complaints & Support Tickets") }}
                        </h1>
                        <p class="text-blue-100 text-sm mt-1">
                            {{ __("Review patient and provider complaints, reported issues, and respond directly via email.") }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Container & Alpine State -->
            <div x-data="complaintsPage()" class="bg-white dark:bg-gray-800 shadow-md rounded-xl border border-gray-100 dark:border-gray-700 p-5 md:p-6">

                @if(Session::has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex justify-between items-center" role="alert">
                    <div class="flex items-center gap-2">
                        <i class="uil uil-check-circle text-lg text-emerald-600"></i>
                        <span class="font-medium">{{ Session::get('message') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-900 font-bold text-xl">&times;</button>
                </div>
                @endif

                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table id="compaintable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("ID") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("User Name") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Complaint Subject") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Description") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Actions") }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            {{-- DataTables will populate this tbody --}}
                        </tbody>
                    </table>
                </div>

                <!-- Complaint Details Modal -->
                <div x-show="isModalOpen" @keydown.escape.window="isModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div @click="isModalOpen = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

                        <div class="relative bg-white dark:bg-gray-800 rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 z-10 space-y-4">
                            <div class="flex justify-between items-start border-b border-gray-100 dark:border-gray-700 pb-3">
                                <div>
                                    <span class="text-xs font-bold text-primary-600 uppercase tracking-wider">{{ __("Complaint Review") }}</span>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="selectedComplaint.title"></h3>
                                </div>
                                <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                            </div>

                            <div class="space-y-3">
                                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
                                    <span>{{ __("Filed By") }}: <strong class="text-gray-900 dark:text-white" x-text="selectedComplaint.patient_name || selectedComplaint.username"></strong></span>
                                    <span class="text-gray-400" x-text="selectedComplaint.patient_email"></span>
                                </div>

                                <div class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-xl text-sm text-gray-700 dark:text-gray-300 leading-relaxed border border-gray-100 dark:border-gray-700" x-text="selectedComplaint.description"></div>
                            </div>

                            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <button @click="isModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold hover:bg-gray-50 transition">
                                    {{ __("Close") }}
                                </button>
                                <a :href="'mailto:' + selectedComplaint.patient_email + '?subject=Re: Complaint - ' + selectedComplaint.title" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider shadow-md transition inline-flex items-center gap-2">
                                    <i class="uil uil-envelope-alt text-sm"></i>
                                    {{ __("Reply via Email") }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@stop

@section('footer')
<script>
    function complaintsPage() {
        return {
            isModalOpen: false,
            selectedComplaint: {},
            openModal(complaint) {
                this.selectedComplaint = complaint;
                this.isModalOpen = true;
            }
        }
    }

    $(document).ready(function() {
        $('#compaintable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: '{{ url("admin/compaintable") }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'username', name: 'username' },
                { data: 'title', name: 'title' },
                { data: 'description', name: 'description' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 mb-4"l<"ml-auto"f>>rtip'
        });
    });
</script>
@stop

