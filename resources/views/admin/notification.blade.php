@extends('admin.layout')

@section('title')
{{ __("Notifications") }} | {{ __("Admin") }}
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
                            <span>{{ __("Push Notifications") }}</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                            <i class="uil uil-bell text-lime-400 text-3xl"></i>
                            {{ __("Broadcast Push Notifications") }}
                        </h1>
                        <p class="text-blue-100 text-sm mt-1">
                            {{ __("Send targeted push notifications to all users, patients, doctors, or pharmacies.") }}
                        </p>
                    </div>
                    <div>
                        <button @click="addModal = true" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-lime-500 hover:bg-lime-600 text-gray-900 font-bold text-xs uppercase tracking-wider shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 cursor-pointer">
                            <i class="uil uil-message text-lg"></i>
                            {{ __("Create Notification") }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Overview Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-2xl font-bold">
                        <i class="uil uil-mobile-android"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $totalTokens ?? 0 }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __("Registered App Devices") }}</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl font-bold">
                        <i class="uil uil-user"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $patientTokens ?? 0 }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __("Patient Devices") }}</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl font-bold">
                        <i class="uil uil-user-md"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $doctorTokens ?? 0 }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __("Doctor Devices") }}</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-2xl font-bold">
                        <i class="uil uil-key-skeleton"></i>
                    </div>
                    <div>
                        @if($hasFcmKey ?? false)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                <i class="uil uil-check-circle mr-1"></i> {{ __("Configured") }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                <i class="uil uil-exclamation-triangle mr-1"></i> {{ __("Key Missing") }}
                            </span>
                        @endif
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">
                            <a href="{{ url('admin/notificationkey') }}" class="text-blue-600 hover:underline flex items-center gap-1">
                                {{ __("Manage FCM Key") }} <i class="uil uil-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Container & Alpine State -->
            <div x-data="notificationManager()">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl border border-gray-100 dark:border-gray-700 p-5 md:p-6">
                    @if(Session::has('message'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)" class="mb-5 rounded-xl @if(Session::get('alert-class') == 'alert-success') bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-300 @else bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-300 @endif p-4 text-sm flex justify-between items-center" role="alert">
                        <div class="flex items-center gap-2">
                            @if(Session::get('alert-class') == 'alert-success')
                                <i class="uil uil-check-circle text-lg text-emerald-600"></i>
                            @else
                                <i class="uil uil-exclamation-octagon text-lg text-rose-600"></i>
                            @endif
                            <span class="font-semibold">{{ Session::get('message') }}</span>
                        </div>
                        <button @click="show = false" class="font-bold text-xl opacity-70 hover:opacity-100">&times;</button>
                    </div>
                    @endif

                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-2">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <i class="uil uil-history text-blue-600"></i>
                            {{ __("Broadcast Logs & History") }}
                        </h2>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                        <table id="notificationtable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-16">{{ __("ID") }}</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-36">{{ __("Target Audience") }}</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Notification Body") }}</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider w-44">{{ __("Date Sent") }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                {{-- DataTables will populate --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Add Notification Modal -->
                <div x-show="addModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div @click="addModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

                        <div class="relative bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 z-10 space-y-4">
                            <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-3">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <i class="uil uil-location-arrow text-lime-500 text-xl"></i>
                                    {{ __("Create & Send Push Notification") }}
                                </h3>
                                <button @click="addModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                            </div>

                            <form action="{{ url('admin/sendnotificationtouser') }}" method="post" class="space-y-4" @submit="confirmSubmission">
                                {{ csrf_field() }}

                                <div>
                                    <label for="title" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                                        <i class="uil uil-heading text-blue-500 mr-1"></i> {{ __("Notification Title") }}
                                    </label>
                                    <input type="text" id="title" name="title" value="{{ __('Chiremba Express Notification') }}" required class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none dark:text-white" placeholder="{{ __('Enter notification title...') }}">
                                </div>

                                <div>
                                    <label for="target_role" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                                        <i class="uil uil-users-alt text-primary-500 mr-1"></i> {{ __("Target Audience / Role") }}
                                    </label>
                                    <select id="target_role" name="target_role" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none dark:text-white">
                                        <option value="all">{{ __("All Users") }} ({{ $totalTokens ?? 0 }} {{ __("registered devices") }})</option>
                                        <option value="patient">{{ __("Patients Only") }} ({{ $patientTokens ?? 0 }} {{ __("devices") }})</option>
                                        <option value="doctor">{{ __("Doctors Only") }} ({{ $doctorTokens ?? 0 }} {{ __("devices") }})</option>
                                        <option value="pharmacy">{{ __("Pharmacies Only") }} ({{ $pharmacyTokens ?? 0 }} {{ __("devices") }})</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="message" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                                        <i class="uil uil-comment-alt-text text-primary-500 mr-1"></i> {{ __("Notification Message Body") }}
                                    </label>
                                    <textarea class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none dark:text-white" rows="5" required name="message" id="message" placeholder="{{ __('Enter your push notification message...') }}"></textarea>
                                </div>

                                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <button type="button" @click="addModal = false" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold hover:bg-gray-50 transition cursor-pointer">
                                        {{ __("Cancel") }}
                                    </button>
                                    @if(Session::get("is_demo")=='1')
                                        <button type="button" onclick="disablebtn()" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider shadow-md transition cursor-pointer">
                                            {{ __("Send Broadcast") }}
                                        </button>
                                    @else
                                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider shadow-md transition cursor-pointer">
                                            {{ __("Send Broadcast") }}
                                        </button>
                                    @endif
                                </div>
                            </form>
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
    function notificationManager() {
        return {
            addModal: false,
            confirmSubmission(event) {
                if (!confirm('{{ __("Are you sure you want to broadcast this notification to the selected devices?") }}')) {
                    event.preventDefault();
                }
            }
        }
    }

    $(document).ready(function() {
        $('#notificationtable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: '{{ url("admin/notificationtable") }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'target', name: 'target', orderable: false, searchable: false },
                { data: 'message', name: 'message' },
                { data: 'created_at', name: 'created_at', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']],
            dom: '<"flex flex-col sm:flex-row justify-between items-center gap-4 mb-4"l<"ml-auto"f>>rtip'
        });
    });
</script>
@stop


