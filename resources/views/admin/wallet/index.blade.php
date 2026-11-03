@extends('admin.layout')

@section('title')
{{ __("Wallet Management") }} | {{ __("Admin") }}
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
                            <span>{{ __("Wallet Management") }}</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                            <i class="uil uil-wallet text-lime-400 text-3xl"></i>
                            {{ __("User & Provider Wallets") }}
                        </h1>
                        <p class="text-blue-100 text-sm mt-1">
                            {{ __("Monitor account balances, credit or debit balances, and manage provider financial deposits.") }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Container & Alpine State -->
            <div x-data="{
                    isModalOpen: false,
                    userId: '',
                    userType: '',
                    ownerName: '',
                    currentBalance: '',
                    openModal(id, type, name, balance) {
                        this.userId = id;
                        this.userType = type;
                        this.ownerName = name;
                        this.currentBalance = parseFloat(balance).toFixed(2);
                        this.isModalOpen = true;
                    }
                }" class="bg-white dark:bg-gray-800 shadow-md rounded-xl border border-gray-100 dark:border-gray-700 p-5 md:p-6">

                @if(Session::has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex justify-between items-center" role="alert">
                    <div class="flex items-center gap-2">
                        <i class="uil uil-check-circle text-lg text-emerald-600"></i>
                        <span class="font-medium">{{ Session::get('message') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-900 font-bold text-xl">&times;</button>
                </div>
                @endif

                <!-- Filter Form Card -->
                <div class="mb-6 bg-gray-50/70 dark:bg-gray-700/40 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <form id="filter-form">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div class="md:col-span-2">
                                <label for="search_term" class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">
                                    <i class="uil uil-search mr-1"></i> {{ __("Search Owner Name") }}
                                </label>
                                <input type="text" name="search_term" id="search_term" placeholder="{{ __('Type owner name...') }}" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                            </div>
                            <div>
                                <label for="owner_type_filter" class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">
                                    <i class="uil uil-user text-primary-500 mr-1"></i> {{ __("Filter Owner Type") }}
                                </label>
                                <select id="owner_type_filter" name="owner_type_filter" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                    <option value="">{{ __("All Types") }}</option>
                                    <option value="Patient">{{ __("Patient") }}</option>
                                    <option value="Doctor">{{ __("Doctor") }}</option>
                                    <option value="Pharmacy">{{ __("Pharmacy") }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="sort_by" class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">
                                    <i class="uil uil-[#0A2B68] mr-1"></i> {{ __("Sort Order") }}
                                </label>
                                <select id="sort_by" name="sort_by" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                    <option value="balance_desc">{{ __("Balance (High to Low)") }}</option>
                                    <option value="balance_asc">{{ __("Balance (Low to High)") }}</option>
                                    <option value="name_asc">{{ __("Name (A-Z)") }}</option>
                                    <option value="name_desc">{{ __("Name (Z-A)") }}</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table View -->
                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table id="wallet-list-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("ID") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Owner Type") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Owner Name") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Wallet Balance") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Actions") }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            {{-- DataTables will populate this tbody --}}
                        </tbody>
                    </table>
                </div>

                <!-- Adjust Balance Modal -->
                <div x-show="isModalOpen" @keydown.escape.window="isModalOpen = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
                    <div class="flex items-center justify-center min-h-screen p-4">
                        <div @click="isModalOpen = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

                        <div class="relative bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 z-10 space-y-4">
                            <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-3">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <i class="uil uil-coins text-lime-500 text-xl"></i>
                                    {{ __("Adjust Wallet Balance") }}
                                </h3>
                                <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                            </div>

                            <form action="{{ route('admin.wallet.adjust') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="user_id" :value="userId">
                                <input type="hidden" name="user_type" :value="userType">

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ __("Wallet Owner") }}</label>
                                    <input type="text" :value="ownerName" readonly class="w-full bg-gray-100 dark:bg-gray-700 border-0 rounded-xl px-3.5 py-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ __("Current Balance ($)") }}</label>
                                    <input type="text" :value="'$' + currentBalance" readonly class="w-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-200 dark:border-emerald-800 rounded-xl px-3.5 py-2 text-base">
                                </div>

                                <div>
                                    <label for="modal-amount" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Amount ($)") }}</label>
                                    <input type="number" step="0.01" min="0.01" id="modal-amount" name="amount" required placeholder="0.00" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                </div>

                                <div>
                                    <label for="modal-type" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Operation Type") }}</label>
                                    <select id="modal-type" name="type" required class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                        <option value="credit">{{ __("Credit (Add Funds to Wallet)") }}</option>
                                        <option value="debit">{{ __("Debit (Deduct Funds from Wallet)") }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="modal-description" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __("Reason / Description") }}</label>
                                    <textarea id="modal-description" name="description" rows="3" placeholder="{{ __('Optional reason for adjustment...') }}" class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-3.5 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none"></textarea>
                                </div>

                                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <button type="button" @click="isModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold hover:bg-gray-50 transition">
                                        {{ __("Close") }}
                                    </button>
                                    <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider shadow-md transition">
                                        {{ __("Confirm Adjustment") }}
                                    </button>
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
        var table = $('#wallet-list-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.wallet.table') }}",
                data: function (d) {
                    d.search_term = $('#search_term').val();
                    d.owner_type_filter = $('#owner_type_filter').val();
                    d.sort_by = $('#sort_by').val();
                }
            },
            columns: [
                { data: 'id', name: 'wallet_id' },
                { data: 'owner_type', name: 'owner_type' },
                { data: 'owner_name', name: 'owner_name' },
                { data: 'balance', name: 'balance' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            dom: 'lrtip',
            "drawCallback": function( settings ) {
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(this.api().table().body());
                }
            }
        });

        // Redraw table on filter change
        $('#search_term, #owner_type_filter, #sort_by').on('change keyup', function() {
            table.draw();
        });
    });
</script>
@stop

