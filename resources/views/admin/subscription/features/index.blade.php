@extends('admin.layout')

@section('title')
    {{ __("Manage Features for") }} {{ $subscription->name }} | {{ __("Admin") }}
@stop

@section('content')
    <script>
        // Define the Alpine component before the HTML that uses it is parsed.
        function featureManager() {
            return {
                modalOpen: false,
                modalTitle: '',
                formAction: '',
                feature: {},
                predefinedFeatures: [],
                featureType: 'predefined', // 'predefined' or 'custom'
                selectedPredefined: '',

                init() {
                    // Safely parse the data from the data attribute when the component initializes
                    if (this.$el.dataset.predefinedFeatures) {
                        this.predefinedFeatures = JSON.parse(this.$el.dataset.predefinedFeatures);
                    }
                },

                openModal(feature = {}) {
                    this.feature = { ...feature }; // Use a copy
                    const isPredefined = this.predefinedFeatures.some(pf => pf.code === feature.code);

                    if (feature.id) {
                        this.modalTitle = '{{ __("Edit Feature") }}';
                        this.formAction = '{{ route("admin.subscriptions.features.update", [$subscription->id, "FEATURE_ID"]) }}'.replace('FEATURE_ID', feature.id);
                        this.featureType = isPredefined ? 'predefined' : 'custom';
                        this.selectedPredefined = isPredefined ? feature.code : '';
                    } else {
                        this.modalTitle = '{{ __("Add New Feature") }}';
                        this.formAction = '{{ route("admin.subscriptions.features.store", $subscription->id) }}';
                        this.featureType = 'predefined';
                        this.selectedPredefined = '';
                        this.feature = {}; // Clear for new feature
                    }
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                    this.selectedPredefined = '';
                },

                updateFeatureFromPredefined() {
                    if (this.selectedPredefined) {
                        const selected = this.predefinedFeatures.find(pf => pf.code === this.selectedPredefined);
                        if (selected) {
                            this.feature.name = selected.name;
                            this.feature.code = selected.code;
                            this.feature.description = selected.description;
                        }
                    } else {
                        this.feature.name = '';
                        this.feature.code = '';
                        this.feature.description = '';
                    }
                },

                submitForm(event) {
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('input[name="_token"]').value);
                    if (this.feature.id) { formData.append('_method', 'POST'); }
                    
                    formData.append('name', this.feature.name || '');
                    formData.append('code', this.feature.code || '');
                    formData.append('value', this.feature.value || '');
                    formData.append('description', this.feature.description || '');

                    if (!formData.get('code')) { alert('Please select or enter a feature code.'); return; }

                    fetch(this.formAction, { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } })
                        .then(response => response.json().then(data => ({ ok: response.ok, data })))
                        .then(({ ok, data }) => {
                            if (ok && data.success) { window.location.reload(); } 
                            else {
                                let errorMessage = data.message || 'An unknown error occurred.';
                                if (data.errors) { errorMessage += '\n\n' + Object.values(data.errors).flat().join('\n'); }
                                alert(errorMessage);
                            }
                        })
                        .catch(error => alert('A network error occurred. Please try again.'));
                }
            };
        }
    </script>
    <div class="main-content" 
        x-data="featureManager()" 
        data-predefined-features='@json($predefinedFeatures)'
        x-init="init()"
    >
        <div class="page-content p-6">
            <div class="container-fluid mx-auto">

                <!-- Page Title and Breadcrumbs -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ __("Manage Features for") }} {{ $subscription->name }}
                        </h1>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <a href="{{ route('admin.subscriptions.index') }}" class="hover:text-primary-500">{{ __("Subscription Plans") }}</a>
                            / {{ __("Manage Features") }}
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __("Back to Plans") }}
                        </a>
                    </div>
                </div>

                <!-- Display Success/Error Messages (if needed) -->
                @if(session('message'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                        <span>{{ session('message') }}</span>
                        <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                    </div>
                @endif

                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <!-- Feature Management Table -->
                    <div class="overflow-x-auto">
                        <table id="features-table" class="min-w-full bg-white dark:bg-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Name") }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Code") }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Value") }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Description") }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Actions") }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <!-- Data will be loaded via DataTables -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Add New Feature Button (will be implemented later) -->
                    <div class="mt-6">
                        <button @click="openModal({})" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __("Add New Feature") }}
                        </button>
                    </div>
                </div>

                <!-- Create/Edit Feature Modal -->
                <div @open-feature-modal.window="openModal($event.detail.feature)"
                     x-show="modalOpen" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal()"></div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div class="inline-block align-bottom bg-white dark:bg-gray-700 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <form :action="formAction" method="POST" @submit.prevent="submitForm">
                                @csrf
                                <template x-if="feature.id">
                                    <input type="hidden" name="_method" value="POST"> <!-- Method spoofing for update -->
                                </template>
                                {{-- The form now submits data from the Alpine component, not directly from inputs --}}
                                <div class="bg-white dark:bg-gray-700 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title" x-text="modalTitle"></h3>
                                    <div class="mt-4 space-y-4">
                                        <!-- Feature Type Selector -->
                                        <div>
                                            <label for="feature_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Feature Type") }}</label>
                                            <select id="feature_type" x-model="featureType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200">
                                                <option value="predefined">{{ __("Predefined Limit") }}</option>
                                                <option value="custom">{{ __("Custom Feature") }}</option>
                                            </select>
                                        </div>

                                        <!-- Predefined Feature Dropdown -->
                                        <div x-show="featureType === 'predefined'">
                                            <label for="predefined_feature_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Predefined Feature") }}</label>
                                            <select id="predefined_feature_code" x-model="selectedPredefined" @change="updateFeatureFromPredefined()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200">
                                                <option value="">-- {{ __("Select a predefined feature") }} --</option>
                                                <template x-for="pf in predefinedFeatures" :key="pf.code">
                                                    <option :value="pf.code" x-text="pf.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- Custom Feature Inputs -->
                                        <div x-show="featureType === 'custom'" class="space-y-4">
                                            <div>
                                                <label for="feature_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Name") }}</label>
                                                <input type="text" id="feature_name" x-model="feature.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200" :required="featureType === 'custom'">
                                            </div>
                                            <div>
                                                <label for="feature_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Code") }}</label>
                                                <input type="text" id="feature_code" x-model="feature.code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200" :required="featureType === 'custom'">
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __("A unique code for this feature (e.g., 'ai_credits', 'support_level'). No spaces.") }}</p>
                                            </div>
                                        </div>

                                        <!-- Value and Description (always visible) -->
                                        <div>
                                            <label for="feature_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Value") }}</label>
                                            <input type="text" name="value" id="feature_value" x-model="feature.value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __("The value of the feature (e.g., '100', 'basic', 'true').") }}</p>
                                        </div>
                                        <div>
                                            <label for="feature_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Description") }}</label>
                                            <textarea name="description" id="feature_description" x-model="feature.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm" x-text="feature.id ? '{{ __('Update') }}' : '{{ __('Add') }}'"></button>
                                    <button type="button" class="mt-3 w-full sm:mt-0 sm:ml-3 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:w-auto sm:text-sm" @click="closeModal()">{{ __("Cancel") }}</button>
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
        $('#features-table').DataTable({
            destroy: true, // Useful for re-initialization if needed
            processing: false, // Set to false for client-side data
            data: @json($features),
            columns: [
                { data: 'name', name: 'name' },
                { data: 'code', name: 'code' },
                { data: 'value', name: 'value' },
                { data: 'description', name: 'description' },
                { data: 'id', name: 'action', orderable: false, searchable: false },
            ],
            columnDefs: [
                {
                    targets: 4, // The 'action' column
                    render: function(data, type, row) {
                        const editButton = `<button type="button" class="p-1 text-blue-600 hover:text-blue-900" title="Edit" data-feature='${JSON.stringify(row)}'><i class="uil uil-pen"></i></button>`;
                        const deleteButton = `<button type="button" class="p-1 text-red-600 hover:text-red-900" title="Delete" data-id="${row.id}"><i class="uil uil-trash-alt"></i></button>`;
                        return `<div class="flex justify-end items-center space-x-2">${editButton}${deleteButton}</div>`;
                    }
                }
            ]
        });

        // Event listener for Edit button
        $('#features-table tbody').on('click', 'button[title="Edit"]', function () {
            const featureData = JSON.parse($(this).attr('data-feature'));
            window.dispatchEvent(new CustomEvent('open-feature-modal', { detail: { feature: featureData } }));
        });

        // Event listener for Delete button
        $('#features-table tbody').on('click', 'button[title="Delete"]', function () {
            const featureId = $(this).data('id');
            if (confirm('{{ __("Are you sure you want to delete this feature?") }}')) {
                deleteFeature(featureId);
            }
        });
    });

    function deleteFeature(featureId) {
        const url = '{{ route("admin.subscriptions.features.destroy", [$subscription->id, "FEATURE_ID"]) }}'.replace('FEATURE_ID', featureId);
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            }
        })
        .then(response => response.json().then(data => ({ ok: response.ok, data })))
        .then(({ ok, data }) => ok ? window.location.reload() : alert(data.message || 'An error occurred.'))
        .catch(error => alert('A network error occurred. Please try again.'));
    }
</script>
@endsection