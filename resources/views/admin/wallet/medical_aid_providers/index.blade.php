@extends('admin.layout')

@section('title')
    {{ __("Medical Aid Providers") }} | {{ __("Admin") }}
@stop

@section('content')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('providerManager', () => ({
                modalOpen: false,
                isLoading: false,
                provider: {},
                formAction: '{{ route("admin.medical_aid_providers.store") }}',
                imagePreview: null,

                openModal(providerData = null) {
                    this.imagePreview = null;
                    if (providerData && providerData.id) {
                        this.provider = { ...providerData };
                        this.formAction = '{{ route("admin.medical_aid_providers.update") }}';
                        if (this.provider.logo) {
                            this.imagePreview = '{{ asset("upload/medical_aids") }}/' + this.provider.logo;
                        }
                    } else {
                        this.provider = {};
                        this.formAction = '{{ route("admin.medical_aid_providers.store") }}';
                    }
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                    this.provider = {};
                    this.imagePreview = null;
                },

                handleFileChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.imagePreview = URL.createObjectURL(file);
                    }
                },

                submitForm(event) {
                    if (this.isLoading) return;
                    this.isLoading = true;

                    const form = event.target;
                    const formData = new FormData(form);

                    $.ajax({
                        url: this.formAction,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: (response) => {
                            if (response.success) {
                                $('#providers-table').DataTable().ajax.reload(null, false);
                                this.closeModal();
                                alert(response.message);
                            } else {
                                let errorMessage = response.message || '{{ __("An error occurred.") }}';
                                if (response.errors) { errorMessage += "\n\n" + Object.values(response.errors).flat().join("\n"); }
                                alert(errorMessage);
                            }
                        },
                        error: (xhr) => { console.error(xhr.responseText); alert('{{ __("A network error occurred. Please try again.") }}'); },
                        complete: () => {
                            this.isLoading = false;
                        }
                    });
                }
            }));
        });
    </script>

    <div class="main-content" x-data="providerManager" @open-modal.window="openModal($event.detail)">
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
                                <a href="{{ route('admin.wallet.index') }}" class="hover:underline">{{ __("Wallet Management") }}</a>
                                <span>/</span>
                                <span>{{ __("Medical Aid Providers") }}</span>
                            </div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                                <i class="uil uil-medical-square text-lime-400 text-3xl"></i>
                                {{ __("Medical Aid Partners") }}
                            </h1>
                            <p class="text-blue-100 text-sm mt-1">
                                {{ __("Manage supported health insurance & medical aid scheme providers for wallet claim processing.") }}
                            </p>
                        </div>
                        <div>
                            <button @click="openModal()" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-lime-500 hover:bg-lime-600 text-gray-900 font-bold text-xs uppercase tracking-wider shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
                                <i class="uil uil-plus text-lg"></i>
                                {{ __("Add New Provider") }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content Card -->
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl border border-gray-100 dark:border-gray-700 p-5 md:p-6">
                    <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                        <table id="providers-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Logo") }}</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Provider Name") }}</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Description") }}</th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Actions") }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                {{-- DataTables will populate --}}
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Add/Edit Provider Modal -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div @click="closeModal()" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 z-10 space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2" x-text="provider.id ? '{{ __('Edit Provider') }}' : '{{ __('Add New Provider') }}'"></h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                    </div>

                    <form @submit.prevent="submitForm($event)" class="space-y-4">
                        @csrf
                        <input type="hidden" name="id" x-model="provider.id">

                        <div>
                            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">{{ __("Provider Name") }}</label>
                            <input type="text" id="name" name="name" x-model="provider.name" placeholder="{{ __('e.g., Cimas Health Group') }}" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none" required>
                        </div>

                        <div>
                            <label for="logo" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">{{ __("Provider Logo") }}</label>
                            <div class="flex items-center space-x-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-600">
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" class="h-14 w-14 object-cover rounded-xl border shadow-sm flex-shrink-0">
                                </template>
                                <template x-if="!imagePreview">
                                    <div class="h-14 w-14 rounded-xl bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-400">
                                        <i class="uil uil-image text-2xl"></i>
                                    </div>
                                </template>
                                <input type="file" id="logo" name="logo" @change="handleFileChange" accept="image/*" class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                            </div>
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">{{ __("Description") }}</label>
                            <textarea id="description" name="description" x-model="provider.description" rows="3" placeholder="{{ __('Provide additional details or accepted package levels...') }}" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="closeModal()" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold hover:bg-gray-50 transition">
                                {{ __("Cancel") }}
                            </button>
                            <button type="submit" :disabled="isLoading" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider shadow-md transition flex items-center gap-2 disabled:opacity-50">
                                <i x-show="isLoading" class="fas fa-spinner fa-spin text-sm" style="display: none;"></i>
                                <span x-text="isLoading ? '{{ __('Saving...') }}' : '{{ __('Save Provider') }}'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
<script>
    $(document).ready(function() {
        $('#providers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.medical_aid_providers.table') }}",
            columns: [
                { data: 'logo', name: 'logo', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'description', name: 'description' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            const url = $(this).data('url');
            if (confirm('{{ __("Are you sure you want to delete this provider?") }}')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            $('#providers-table').DataTable().ajax.reload(null, false);
                            alert(response.message);
                        } else {
                            alert(response.message || '{{ __("An error occurred.") }}');
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('{{ __("A network error occurred. Please try again.") }}');
                    }
                });
            }
        });
    });
</script>
@stop