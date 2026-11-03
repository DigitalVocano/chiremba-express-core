@extends('admin.layout')

@section('title')
    {{ __("Manage AI Credit Packages") }} | {{ __("Admin Dashboard") }}
@stop

@section('content')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('packageManager', () => ({
                modalOpen: false,
                isLoading: false,
                package: {},
                formAction: '{{ route("admin.ai_credits.store") }}',

                openModal(packageData = null) {
                    if (packageData && packageData.id) {
                        this.package = { ...packageData };
                        this.formAction = '{{ route("admin.ai_credits.update") }}';
                    } else {
                        this.package = { is_active: true };
                        this.formAction = '{{ route("admin.ai_credits.store") }}';
                    }
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                    this.package = {};
                },

                submitForm(event) {
                    if (this.isLoading) return;
                    this.isLoading = true;

                    const form = event.target;
                    $.ajax({
                        url: this.formAction,
                        type: 'POST',
                        context: this,
                        data: $(form).serialize(),
                        success: (response) => {
                            if (response.success) {
                                $('#packages-table').DataTable().ajax.reload(null, false);
                                this.closeModal();
                                alert(response.message);
                            } else {
                                let errorMessage = response.message || '{{ __("An error occurred.") }}';
                                if (response.errors) { errorMessage += "\n\n" + Object.values(response.errors).flat().join("\n"); }
                                alert(errorMessage);
                            }
                        },
                        error: (xhr) => { console.error(xhr.responseText); alert('{{ __("A network error occurred. Please try again.") }}'); },
                        complete: function() {
                            this.isLoading = false;
                        }
                    });
                }
            }));
        });
    </script>
    <div class="main-content" x-data="packageManager">
        <div class="page-content p-4 md:p-6">
            <div class="container-fluid mx-auto space-y-6">

                <!-- Header Hero Card -->
                <div class="admin-header-card rounded-2xl p-6 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-lime-400 text-2xl font-bold shadow-inner">
                                <i class="fa-solid fa-brain"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">{{ __("AI Credit Packages") }}</h1>
                                <p class="text-xs md:text-sm text-blue-100/80 mt-1">{{ __("Manage AI token balances and pricing packages for AI Symptom Assistant & Medical Summaries.") }}</p>
                            </div>
                        </div>
                    </div>
                    <button @click="openModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-600 hover:to-emerald-600 text-gray-900 font-bold text-sm rounded-xl shadow-lg hover:shadow-lime-500/25 transition-all">
                        <i class="uil uil-plus text-lg"></i>
                        <span>{{ __("Add New Package") }}</span>
                    </button>
                </div>

                <!-- AI Packages Card -->
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-4 md:p-6 border border-gray-100 dark:border-gray-700">

                    <!-- Search Input -->
                    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
                        <div class="relative flex-1 max-w-md">
                            <i class="uil uil-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                            <input type="text" id="aiPackageSearch" placeholder="{{('Search AI credit packages...')}}" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Desktop View -->
                    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                        <table id="packages-table" class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-700" style="width: 100% !important;">
                            <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                                <tr>
                                    <th class="px-6 py-4 w-20">{{ __("ID") }}</th>
                                    <th class="px-6 py-4">{{ __("Package Name") }}</th>
                                    <th class="px-6 py-4">{{ __("Credits Included") }}</th>
                                    <th class="px-6 py-4">{{ __("Price") }}</th>
                                    <th class="px-6 py-4">{{ __("Status") }}</th>
                                    <th class="px-6 py-4 text-right w-28">{{ __("Action") }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700/60">
                                {{-- AJAX Populated --}}
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile View -->
                    <div id="mobileCardsContainer" class="md:hidden space-y-4">
                        {{-- Mobile cards --}}
                    </div>

                </div>

            </div>
        </div>

        <!-- Modal for Add/Edit Package -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
            <div @click.away="closeModal()" class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-5 bg-gradient-to-r from-navy-900 to-blue-900 text-white flex justify-between items-center" style="background: linear-gradient(135deg, #0A2B68 0%, #1E3A8A 100%);">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-coins text-lime-400 text-lg"></i>
                        <h3 class="text-lg font-bold text-white" x-text="package.id ? '{{ __('Edit AI Credit Package') }}' : '{{ __('Add New AI Credit Package') }}'"></h3>
                    </div>
                    <button @click="closeModal()" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
                </div>
                <form @submit.prevent="submitForm($event)" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="id" x-model="package.id">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Package Name") }}</label>
                        <input type="text" id="name" name="name" x-model="package.name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" placeholder="e.g. Starter 50 AI Tokens" required>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="credits" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Credits") }}</label>
                            <input type="number" id="credits" name="credits" x-model="package.credits" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" placeholder="50" required min="1">
                        </div>
                        <div>
                            <label for="price" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Price ($)") }}</label>
                            <input type="number" step="0.01" id="price" name="price" x-model="package.price" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" placeholder="5.00" required min="0">
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Description") }}</label>
                        <textarea id="description" name="description" x-model="package.description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" placeholder="Suitable for 50 AI diagnostic queries..."></textarea>
                    </div>
                    <div class="pt-2">
                        <label for="is_active" class="inline-flex items-center cursor-pointer">
                            <input id="is_active" name="is_active" type="checkbox" value="1" x-model="package.is_active" class="rounded border-gray-300 text-lime-600 focus:ring-lime-500 h-4 w-4">
                            <span class="ml-2 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __("Active Package") }}</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closeModal()" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">{{ __("Cancel") }}</button>
                        <button type="submit" :disabled="isLoading" class="px-5 py-2.5 bg-lime-500 hover:bg-lime-600 text-gray-900 font-bold text-sm rounded-xl shadow-md transition-all flex items-center justify-center gap-2 min-w-[120px]">
                            <i x-show="isLoading" class="fas fa-spinner fa-spin text-sm" style="display: none;"></i>
                            <span x-text="isLoading ? '{{ __('Saving...') }}' : '{{ __('Save Package') }}'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('footer')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#packages-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.ai_credits.table') }}",
            columns: [
                { 
                   data: 'id', 
                   name: 'id',
                   render: function(data) {
                      return '<span class="font-mono font-bold text-gray-900 dark:text-white">#' + data + '</span>';
                   }
                },
                { 
                   data: 'name', 
                   name: 'name',
                   render: function(data) {
                      return '<div class="font-bold text-gray-900 dark:text-white">' + (data || 'Package') + '</div>';
                   }
                },
                { 
                   data: 'credits', 
                   name: 'credits',
                   render: function(data) {
                      return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"><i class="fa-solid fa-bolt mr-1 text-amber-500"></i>' + (data || 0) + ' Credits</span>';
                   }
                },
                { 
                   data: 'price', 
                   name: 'price',
                   render: function(data) {
                      return '<span class="font-extrabold text-emerald-600 dark:text-emerald-400">$' + parseFloat(data || 0).toFixed(2) + '</span>';
                   }
                },
                { 
                   data: 'is_active', 
                   name: 'is_active', 
                   orderable: false, 
                   searchable: false,
                   render: function(data) {
                      return data 
                        ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300"><i class="uil uil-check-circle mr-1"></i> Active</span>'
                        : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"><i class="uil uil-minus-circle mr-1"></i> Inactive</span>';
                   }
                },
                { 
                   data: 'action', 
                   name: 'action', 
                   orderable: false, 
                   searchable: false,
                   className: 'text-right'
                }
            ],
            dom: 'rtip',
            order: [[0, 'desc']],
            drawCallback: function(settings) {
                renderMobileCards(settings.json ? settings.json.data : []);
            }
        });

        $('#aiPackageSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        function renderMobileCards(data) {
            var container = $('#mobileCardsContainer');
            container.empty();
            if (!data || data.length === 0) {
                container.html('<div class="p-6 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-xl">No AI packages found.</div>');
                return;
            }
            $.each(data, function(i, item) {
                var card = `
                    <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/60 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900 dark:text-white text-base">${item.name}</span>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-base">$${parseFloat(item.price || 0).toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-amber-600 dark:text-amber-400"><i class="fa-solid fa-bolt mr-1"></i> ${item.credits} AI Credits</span>
                            <span class="px-2 py-0.5 rounded font-semibold ${item.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-800'}">${item.is_active ? 'Active' : 'Inactive'}</span>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }

        // Delete confirmation
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            const url = $(this).data('url');
            if (confirm('{{ __("Are you sure you want to delete this package?") }}')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            $('#packages-table').DataTable().ajax.reload(null, false);
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