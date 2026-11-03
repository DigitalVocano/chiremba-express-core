@extends('admin.layout')
@section('title')
{{("Pending Payments")}} | {{("Admin Dashboard")}}
@stop
@section('meta-data')
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
                     <i class="uil uil-clock-two"></i>
                  </div>
                  <div>
                     <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">{{("Pending Doctor Payouts")}}</h1>
                     <p class="text-xs md:text-sm text-blue-100/80 mt-1">{{("Review accumulated earnings and disburse payouts to healthcare providers.")}}</p>
                  </div>
               </div>
            </div>
         </div>

         <script>
            function paymentManager() {
                return {
                    paymentModalOpen: false,
                    paymentDetails: { doctor_id: null, name: '', amount: 0 },
                    formAction: '{{ url("admin/updatepayment") }}',
                    openPaymentModal(detail) {
                        this.paymentDetails = detail;
                        this.paymentModalOpen = true;
                    },
                    closePaymentModal() {
                        this.paymentModalOpen = false;
                        this.paymentDetails = { doctor_id: null, name: '', amount: 0 };
                    }
                }
            }
         </script>
         <div x-data="paymentManager()" @open-payment-modal.window="openPaymentModal($event.detail)">

            <!-- Pending Payout Card -->
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-4 md:p-6 border border-gray-100 dark:border-gray-700">
               @if(Session::has('message'))
               <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex justify-between items-center" role="alert">
                  <div class="flex items-center gap-2">
                      <i class="uil uil-check-circle text-lg"></i>
                      <span class="font-medium">{{ Session::get('message') }}</span>
                  </div>
                  <button @click="show = false" class="text-emerald-900 dark:text-emerald-300 font-bold">&times;</button>
               </div>
               @endif

               <!-- Search Header -->
               <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
                  <div class="relative flex-1 max-w-md">
                     <i class="uil uil-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                     <input type="text" id="pendingPaymentSearch" placeholder="{{('Search doctor payouts by name...')}}" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent transition-all">
                  </div>
               </div>

               <!-- Desktop View -->
               <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                  <table id="pendingpaymenttable" class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-700" style="width: 100% !important;">
                     <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                        <tr>
                           <th scope="col" class="px-6 py-4">{{("Latest Date")}}</th>
                           <th scope="col" class="px-6 py-4">{{("Doctor")}}</th>
                           <th scope="col" class="px-6 py-4">{{("Pending Amount")}}</th>
                           <th scope="col" class="px-6 py-4">{{("Completed Consults")}}</th>
                           <th scope="col" class="px-6 py-4 text-right w-28">{{("Disburse")}}</th>
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

            <!-- Pay Payout Modal -->
            <div x-show="paymentModalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" x-cloak>
               <div class="relative w-full max-w-md shadow-2xl rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden" @click.away="closePaymentModal()">
                  <div class="p-5 bg-gradient-to-r from-navy-900 to-blue-900 text-white flex justify-between items-center" style="background: linear-gradient(135deg, #0A2B68 0%, #1E3A8A 100%);">
                     <div class="flex items-center gap-2">
                        <i class="uil uil-money-bill text-lime-400 text-xl"></i>
                        <h5 class="text-lg font-bold text-white">{{("Disburse Payout")}}</h5>
                     </div>
                     <button @click="closePaymentModal()" class="text-white/80 hover:text-white text-2xl font-bold">&times;</button>
                  </div>
                  <form :action="formAction" method="post" class="p-6 space-y-4">
                     <input type="hidden" name="doctor_id" :value="paymentDetails.doctor_id"/>
                     <input type="hidden" name="amount" :value="paymentDetails.amount"/>
                     {{csrf_field()}}
                     
                     <div class="p-4 bg-gray-50 dark:bg-gray-900/60 rounded-xl space-y-2 border border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-center text-sm">
                           <span class="text-gray-500 dark:text-gray-400">{{("Doctor Name")}}:</span>
                           <span class="font-bold text-gray-900 dark:text-white" x-text="paymentDetails.name"></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                           <span class="text-gray-500 dark:text-gray-400">{{("Payout Amount")}}:</span>
                           <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-base" x-text="'$' + parseFloat(paymentDetails.amount || 0).toFixed(2)"></span>
                        </div>
                     </div>

                     <div>
                        <label for="translation_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{("Transaction / Reference ID")}}</label>
                        <input type="text" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent" id="translation_id" name="translation_id" placeholder='{{("Enter Paynow/Bank Ref ID...")}}' required>
                     </div>

                     <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closePaymentModal()" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">{{('Cancel')}}</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition-all flex items-center gap-1">
                           <i class="uil uil-check-circle"></i>
                           <span>{{("Confirm Payout")}}</span>
                        </button>
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
        var table = $('#pendingpaymenttable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: '{{ url("admin/pendingpaymenttable") }}',
            columns: [
                { 
                   data: 'date', 
                   name: 'latest_date',
                   render: function(data) {
                      return '<span class="text-xs text-gray-600 dark:text-gray-400 font-mono">' + (data || '-') + '</span>';
                   }
                },
                { 
                   data: 'doctor_name', 
                   name: 'doctors.name',
                   render: function(data) {
                      return '<div class="font-bold text-gray-900 dark:text-white">' + (data || 'N/A') + '</div>';
                   }
                },
                { 
                   data: 'amount', 
                   name: 'amount',
                   render: function(data) {
                      return '<span class="font-extrabold text-emerald-600 dark:text-emerald-400">$' + parseFloat(data || 0).toFixed(2) + '</span>';
                   }
                },
                { 
                   data: 'total_booking', 
                   name: 'total_booking',
                   render: function(data) {
                      return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">' + (data || '0') + ' Appointments</span>';
                   }
                },
                { 
                    data: 'action', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-right',
                    render: function(data, type, row) {
                        return `<button type="button" @click="$dispatch('open-payment-modal', { doctor_id: ${row.doctor_id}, name: '${row.doctor_name}', amount: ${row.amount} })" class="inline-flex items-center gap-1 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl font-bold text-xs shadow-md transition-all"><i class="uil uil-money-withdraw text-sm"></i> {{("Pay Now")}}</button>`;
                    }
                }
            ],
            dom: 'rtip',
            drawCallback: function( settings ) {
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    window.Alpine.initTree(this.api().table().body());
                }
                renderMobileCards(settings.json ? settings.json.data : []);
            }
        });

        $('#pendingPaymentSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        function renderMobileCards(data) {
            var container = $('#mobileCardsContainer');
            container.empty();
            if (!data || data.length === 0) {
                container.html('<div class="p-6 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-xl">No pending payouts.</div>');
                return;
            }
            $.each(data, function(i, item) {
                var card = `
                    <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/60 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900 dark:text-white text-base"><i class="uil uil-user-md text-lime-500 mr-1"></i> ${item.doctor_name || 'Doctor'}</span>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-base">$${parseFloat(item.amount || 0).toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400">
                            <span><i class="uil uil-calendar-alt mr-1"></i> ${item.date || '-'}</span>
                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 font-semibold">${item.total_booking || 0} consults</span>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-payment-modal', { detail: { doctor_id: ${item.doctor_id}, name: '${item.doctor_name}', amount: ${item.amount} } }))" class="w-full py-2 bg-emerald-600 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1">
                                <i class="uil uil-money-withdraw"></i> Disburse Payout
                            </button>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }
    });
</script>
@stop
