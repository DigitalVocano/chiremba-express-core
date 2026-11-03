@extends('admin.layout')

@section('title')
    {{ __("Manage Subscription Plans") }} | {{ __("Admin Dashboard") }}
@stop

@section('content')
    <div class="main-content">
        <div class="page-content p-4 md:p-6">
            <div class="container-fluid mx-auto space-y-6">

                <!-- Header Hero Card -->
                <div class="admin-header-card rounded-2xl p-6 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-lime-400 text-2xl font-bold shadow-inner">
                                <i class="uil uil-bill"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">{{ __("Subscription Plans") }}</h1>
                                <p class="text-xs md:text-sm text-blue-100/80 mt-1">{{ __("Configure membership tiers, pricing, and recurring billing for patients, doctors & pharmacies.") }}</p>
                            </div>
                        </div>
                    </div>
                    <button @click="$dispatch('open-modal', { id: null, is_active: 1 })" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-lime-500 to-emerald-500 hover:from-lime-600 hover:to-emerald-600 text-gray-900 font-bold text-sm rounded-xl shadow-lg hover:shadow-lime-500/25 transition-all">
                        <i class="uil uil-plus text-lg"></i>
                        <span>{{ __("Create New Plan") }}</span>
                    </button>
                </div>

                <!-- Subscription Plans Container -->
                <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-4 md:p-6 border border-gray-100 dark:border-gray-700"
                     x-data="{ modalOpen: false, subscription: {} }"
                     @open-modal.window="modalOpen = true; subscription = $event.detail || {}"
                     @modal-closed.window="modalOpen = false; subscription = {}">

                    <!-- Display Messages -->
                    @if(Session::has('message'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex justify-between items-center" role="alert">
                            <div class="flex items-center gap-2">
                               <i class="uil uil-check-circle text-lg"></i>
                               <span class="font-medium">{{ Session::get('message') }}</span>
                            </div>
                            <button @click="show = false" class="text-emerald-900 dark:text-emerald-300 font-bold">&times;</button>
                        </div>
                    @endif

                    <!-- Search Input -->
                    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
                        <div class="relative flex-1 max-w-md">
                            <i class="uil uil-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                            <input type="text" id="subscriptionSearch" placeholder="{{('Search subscription plans by name or user type...')}}" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Desktop DataTables View -->
                    <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                        <table id="subscriptions-table" class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-700" style="width: 100% !important;">
                            <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                                <tr>
                                    <th class="px-6 py-4">{{ __("Plan Name") }}</th>
                                    <th class="px-6 py-4">{{ __("Price") }}</th>
                                    <th class="px-6 py-4">{{ __("Duration") }}</th>
                                    <th class="px-6 py-4">{{ __("Target Role") }}</th>
                                    <th class="px-6 py-4">{{ __("Status") }}</th>
                                    <th class="px-6 py-4 text-right w-36">{{ __("Actions") }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700/60">
                                <!-- AJAX Populated -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Stacked Card View -->
                    <div id="mobileCardsContainer" class="md:hidden space-y-4">
                        {{-- Mobile cards --}}
                    </div>

                    <!-- Create/Edit Modal -->
                    <div x-show="modalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" x-cloak>
                        <div class="relative w-full max-w-lg shadow-2xl rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden" @click.away="modalOpen = false; $dispatch('modal-closed')">
                            <div class="p-5 bg-gradient-to-r from-navy-900 to-blue-900 text-white flex justify-between items-center" style="background: linear-gradient(135deg, #0A2B68 0%, #1E3A8A 100%);">
                                <div class="flex items-center gap-2">
                                    <i class="uil uil-tag-alt text-lime-400 text-xl"></i>
                                    <h3 class="text-lg font-bold text-white" x-text="subscription.id ? '{{ __('Edit Subscription Plan') }}' : '{{ __('Create New Subscription Plan') }}'"></h3>
                                </div>
                                <button @click="modalOpen = false; $dispatch('modal-closed')" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
                            </div>
                            <form x-bind:action="subscription.id ? '{{ route('admin.subscriptions.update') }}' : '{{ route('admin.subscriptions.store') }}'" method="POST" @submit.prevent="submitForm($event, subscription.id)" class="p-6 space-y-4">
                                @csrf
                                <template x-if="subscription.id">
                                    <input type="hidden" name="id" x-bind:value="subscription.id">
                                </template>
                                
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Plan Name") }}</label>
                                    <input type="text" name="name" id="name" x-model="subscription.name" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" placeholder="e.g., Premium Doctor Monthly" required>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="price" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Price ($)") }}</label>
                                        <input type="number" name="price" id="price" x-model="subscription.price" min="0" step="0.01" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" placeholder="29.99" required>
                                    </div>
                                    <div>
                                        <label for="user_type" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Target Role") }}</label>
                                        <select name="user_type" id="user_type" x-model="subscription.user_type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" required>
                                            <option value="patient">{{ __("Patient") }}</option>
                                            <option value="doctor">{{ __("Doctor") }}</option>
                                            <option value="pharmacy">{{ __("Pharmacy") }}</option>
                                            <option value="corporate">{{ __("Corporate") }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="duration" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Duration") }}</label>
                                        <input type="number" name="duration" id="duration" x-model="subscription.duration" min="1" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" required>
                                    </div>
                                    <div>
                                        <label for="duration_unit" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Unit") }}</label>
                                        <select name="duration_unit" id="duration_unit" x-model="subscription.duration_unit" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" required>
                                            <option value="day">{{ __("Day(s)") }}</option>
                                            <option value="week">{{ __("Week(s)") }}</option>
                                            <option value="month">{{ __("Month(s)") }}</option>
                                            <option value="year">{{ __("Year(s)") }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Description") }}</label>
                                    <textarea name="description" id="description" x-model="subscription.description" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" placeholder="Plan highlights & benefits..."></textarea>
                                </div>

                                <div class="pt-2">
                                    <label for="is_active" class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_active" id="is_active" x-model="subscription.is_active" value="1" class="rounded border-gray-300 text-lime-600 focus:ring-lime-500 h-4 w-4">
                                        <span class="ml-2 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ __("Active Plan") }}</span>
                                    </label>
                                </div>

                                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <button type="button" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700" @click="modalOpen = false; $dispatch('modal-closed')">{{ __("Cancel") }}</button>
                                    <button type="submit" class="px-5 py-2.5 bg-lime-500 hover:bg-lime-600 text-gray-900 font-bold text-sm rounded-xl shadow-md transition-all" x-text="subscription.id ? '{{ __('Update Plan') }}' : '{{ __('Create Plan') }}'"></button>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#subscriptions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.subscriptions.table') }}",
                columns: [
                    { 
                       data: 'name', 
                       name: 'name',
                       render: function(data) {
                          return '<div class="font-bold text-gray-900 dark:text-white">' + (data || 'Unnamed') + '</div>';
                       }
                    },
                    { 
                       data: 'price', 
                       name: 'price',
                       render: function(data) {
                          return '<span class="font-bold text-emerald-600 dark:text-emerald-400">$' + parseFloat(data || 0).toFixed(2) + '</span>';
                       }
                    },
                    { 
                       data: 'duration', 
                       name: 'duration',
                       render: function(data, type, row) {
                          return '<span class="text-xs font-semibold text-gray-700 dark:text-gray-300">' + (data || 1) + ' ' + (row.duration_unit || 'month') + '(s)</span>';
                       }
                    },
                    { 
                       data: 'user_type', 
                       name: 'user_type',
                       render: function(data) {
                          return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 uppercase tracking-wider">' + (data || 'all') + '</span>';
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
                    },
                ],
                dom: 'rtip',
                drawCallback: function(settings) {
                    renderMobileCards(settings.json ? settings.json.data : []);
                }
            });

            $('#subscriptionSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            function renderMobileCards(data) {
                var container = $('#mobileCardsContainer');
                container.empty();
                if (!data || data.length === 0) {
                    container.html('<div class="p-6 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-xl">No subscription plans found.</div>');
                    return;
                }
                $.each(data, function(i, item) {
                    var card = `
                        <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/60 rounded-xl p-4 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-gray-900 dark:text-white text-base">${item.name}</span>
                                <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-base">$${parseFloat(item.price || 0).toFixed(2)}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400">
                                <span><i class="uil uil-clock mr-1"></i> ${item.duration} ${item.duration_unit || 'month'}(s)</span>
                                <span class="uppercase font-semibold text-blue-600 dark:text-blue-400">${item.user_type}</span>
                            </div>
                        </div>
                    `;
                    container.append(card);
                });
            }
        });

        function delete_record(url) {
            if (confirm("{{ __('Are you sure you want to delete this subscription plan?') }}")) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#subscriptions-table').DataTable().ajax.reload();
                            alert(response.message || "{{ __('Subscription plan deleted successfully.') }}");
                        } else {
                            alert(response.message || "{{ __('Error deleting subscription plan.') }}");
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("{{ __('An error occurred while deleting the subscription plan.') }}");
                    }
                });
            }
        }

        function submitForm(event, id) {
            let form = $(event.target);
            let url = form.attr('action');

            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#subscriptions-table').DataTable().ajax.reload();
                        document.dispatchEvent(new CustomEvent('modal-closed'));
                        alert(response.message || (id ? "{{ __('Subscription plan updated successfully.') }}" : "{{ __('Subscription plan created successfully.') }}"));
                    } else {
                        let errorMessage = response.message || "{{ __('An error occurred while saving the subscription plan.') }}";
                        if (response.errors) {
                            errorMessage += "\n" + Object.values(response.errors).map(messages => messages.join("\n")).join("\n");
                        }
                        alert(errorMessage);
                    }
                },
                error: function(xhr, status, error) {
                    alert("{{ __('An error occurred while communicating with the server.') }}");
                }
            });
        }
    </script>
@stop