@extends('admin.layout')

@section('title')
    {{ __("Manage Subscription Plans") }} | {{ __("Admin") }}
@stop

@section('content')
    <script>
        // Define the Alpine component before the HTML that uses it is parsed.
        // This resolves the "subscriptionManager is not defined" error.
        document.addEventListener('alpine:init', () => {
            Alpine.data('subscriptionManager', () => ({
                modalOpen: false,
                subscription: {},
                formAction: '{{ route("admin.subscriptions.store") }}',

                openModal(subscriptionData = null) {
                    if (subscriptionData && subscriptionData.id) {
                        this.subscription = { ...subscriptionData }; // Create a copy to avoid reactivity issues
                        this.formAction = '{{ route("admin.subscriptions.update") }}';
                    } else {
                        // Set sensible defaults for a new plan
                        this.subscription = { is_active: true, duration_unit: 'month' };
                        this.formAction = '{{ route("admin.subscriptions.store") }}';
                    }
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                    this.subscription = {}; // Clear data on close
                },

                submitForm(event) {
                    const form = event.target;
                    $.ajax({
                        url: this.formAction,
                        type: 'POST',
                        data: $(form).serialize(),
                        success: (response) => {
                            if (response.success) {
                                $('#subscriptions-table').DataTable().ajax.reload(null, false);
                                this.closeModal();
                                alert(response.message);
                            } else {
                                let errorMessage = response.message || '{{ __("An error occurred.") }}';
                                if (response.errors) { errorMessage += "\n\n" + Object.values(response.errors).flat().join("\n"); }
                                alert(errorMessage);
                            }
                        },
                        error: (xhr) => { console.error(xhr.responseText); alert('{{ __("A network error occurred. Please try again.") }}'); }
                    });
                }
            }));
        });
    </script>
    <div class="main-content" x-data="subscriptionManager">
        <div class="page-content p-6">
            <div class="container-fluid mx-auto">

                <!-- Page Title -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __("Subscription Plans") }}</h1>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __("Manage Subscription Plans") }}
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <!-- Actions: Create New Plan -->
                    <div class="flex justify-end mb-4">
                        <button @click="$dispatch('open-modal', { id: null })" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __("Create New Plan") }}
                        </button>
                    </div>

                     <!-- Subscription Plans Table -->
                    <div class="overflow-x-auto">
                        <table id="subscriptions-table" class="min-w-full bg-white dark:bg-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Name") }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Price") }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Duration") }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("User Type") }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Status") }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Actions") }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Data will be loaded via DataTables -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Create/Edit Modal -->
                    <div x-show="modalOpen" @open-modal.window="openModal($event.detail)" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal()"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                            <div class="inline-block align-bottom bg-white dark:bg-gray-700 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form :action="formAction" method="POST" @submit.prevent="submitForm">
                                    @csrf
                                    <template x-if="subscription.id">
                                        <input type="hidden" name="id" x-bind:value="subscription.id">
                                    </template>
                                    <div class="bg-white dark:bg-gray-700 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title" x-text="subscription.id ? '{{ __('Edit Subscription Plan') }}' : '{{ __('Create New Subscription Plan') }}'"></h3>
                                        <div class="mt-2">
                                            <div class="mb-4">
                                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Plan Name") }}</label>
                                                <input type="text" name="name" id="name" x-model="subscription.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200" required>
                                            </div>
                                            <div class="mb-4">
                                                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Price") }}</label>
                                                <input type="number" name="price" id="price" x-model="subscription.price" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200" required>
                                            </div>
                                            <div class="mb-4 flex space-x-4">
                                                <div>
                                                    <label for="duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Duration") }}</label>
                                                    <input type="number" name="duration" id="duration" x-model="subscription.duration" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200" required>
                                                </div>
                                                <div>
                                                    <label for="duration_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Duration Unit") }}</label>
                                                    <select name="duration_unit" id="duration_unit" x-model="subscription.duration_unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200" required>
                                                        <option value="day">{{ __("Day(s)") }}</option>
                                                        <option value="week">{{ __("Week(s)") }}</option>
                                                        <option value="month">{{ __("Month(s)") }}</option>
                                                        <option value="year">{{ __("Year(s)") }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <label for="user_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("User Type") }}</label>
                                                <select name="user_type" id="user_type" x-model="subscription.user_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200" required>
                                                    <option value="patient">{{ __("Patient") }}</option>
                                                    <option value="doctor">{{ __("Doctor") }}</option>
                                                    <option value="pharmacy">{{ __("Pharmacy") }}</option>
                                                    <option value="corporate">{{ __("Corporate") }}</option>
                                                </select>
                                            </div>
                                            <div class="mb-4">
                                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Description") }}</label>
                                                <textarea name="description" id="description" x-model="subscription.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"></textarea>
                                            </div>
                                            <div class="mb-4">
                                                <label for="is_active" class="inline-flex items-center">
                                                    <input type="checkbox" name="is_active" id="is_active" x-model="subscription.is_active" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __("Active") }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm" x-text="subscription.id ? '{{ __('Update') }}' : '{{ __('Create') }}'"></button>
                                        <button type="button" class="mt-3 w-full sm:mt-0 sm:ml-3 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:w-auto sm:text-sm" @click="closeModal()">{{ __("Cancel") }}</button>
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
    $(document).ready(function() {
        const table = $('#subscriptions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.subscriptions.table') }}",
            columns: [
                { data: 'name', name: 'name' },
                { data: 'price', name: 'price' },
                { data: 'duration', name: 'duration' },
                { data: 'user_type', name: 'user_type' },
                { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            drawCallback: function( settings ) {
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(this.api().table().body());
                }
            }
        });

        // Event listener for delete buttons
        $('#subscriptions-table tbody').on('click', '.delete-btn', function () {
            const url = $(this).data('url');
            if (confirm("{{ __('Are you sure you want to delete this subscription plan?') }}")) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { "_token": "{{ csrf_token() }}" },
                    success: (response) => {
                        if (response.success) {
                            table.ajax.reload(null, false);
                            alert("{{ __('Subscription plan deleted successfully.') }}");
                        } else {
                            alert(response.message || "{{ __('Error deleting subscription plan.') }}");
                        }
                    }
                });
            }
        });
    });
</script>

@stop