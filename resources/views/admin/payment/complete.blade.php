@extends('admin.layout')
@section('title')
{{("Completed Payments")}} | {{("Admin Dashboard")}}
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
                     <i class="uil uil-check-circle"></i>
                  </div>
                  <div>
                     <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">{{("Completed Doctor Payouts")}}</h1>
                     <p class="text-xs md:text-sm text-blue-100/80 mt-1">{{("Historical audit log of disbursed doctor payments and Paynow/Bank transaction IDs.")}}</p>
                  </div>
               </div>
            </div>
         </div>

         <!-- Completed Payments Card -->
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
                  <input type="text" id="completedPaymentSearch" placeholder="{{('Search completed payouts by doctor or transaction ID...')}}" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent transition-all">
               </div>
            </div>

            <!-- Desktop View -->
            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
               <table id="completepaymenttable" class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-700" style="width: 100% !important;">
                  <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                     <tr>
                        <th scope="col" class="px-6 py-4">{{("Doctor Name")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Amount Paid")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Payout Date")}}</th>
                        <th scope="col" class="px-6 py-4 text-right">{{("Transaction / Ref ID")}}</th>
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
</div>
@stop
@section('footer')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#completepaymenttable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: '{{ url("admin/completepaymenttable") }}',
            columns: [
                { 
                   data: 'doctor_name', 
                   name: 'doctors.name',
                   render: function(data) {
                      return '<div class="font-bold text-gray-900 dark:text-white"><i class="uil uil-user-md text-lime-500 mr-1"></i> ' + (data || 'N/A') + '</div>';
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
                   data: 'date', 
                   name: 'date',
                   render: function(data) {
                      return '<span class="text-xs text-gray-600 dark:text-gray-400 font-mono"><i class="uil uil-calendar-alt mr-1"></i>' + (data || '-') + '</span>';
                   }
                },
                { 
                   data: 'transaction_id', 
                   name: 'transaction_id',
                   className: 'text-right',
                   render: function(data) {
                      return '<span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700"><i class="uil uil-receipt mr-1 text-lime-500"></i>' + (data || 'N/A') + '</span>';
                   }
                }
            ],
            dom: 'rtip',
            drawCallback: function(settings) {
                renderMobileCards(settings.json ? settings.json.data : []);
            }
        });

        $('#completedPaymentSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        function renderMobileCards(data) {
            var container = $('#mobileCardsContainer');
            container.empty();
            if (!data || data.length === 0) {
                container.html('<div class="p-6 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-xl">No completed payouts recorded.</div>');
                return;
            }
            $.each(data, function(i, item) {
                var card = `
                    <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/60 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-gray-900 dark:text-white text-base"><i class="uil uil-user-md text-lime-500 mr-1"></i> ${item.doctor_name || 'Doctor'}</span>
                            <span class="font-extrabold text-emerald-600 dark:text-emerald-400 text-base">$${parseFloat(item.amount || 0).toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <span><i class="uil uil-calendar-alt mr-1"></i> ${item.date || '-'}</span>
                            <span class="font-mono font-bold text-gray-800 dark:text-gray-200">Ref: ${item.transaction_id || 'N/A'}</span>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }
    });
</script>
@stop
