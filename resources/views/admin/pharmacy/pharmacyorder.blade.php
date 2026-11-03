@extends('admin.layout')
@section('title')
{{("Pharmacy Orders")}} | {{("Admin Dashboard")}}
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
                     <i class="fa-solid fa-cart-flatbed"></i>
                  </div>
                  <div>
                     <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">{{("Pharmacy Orders Management")}}</h1>
                     <p class="text-xs md:text-sm text-blue-100/80 mt-1">{{("Monitor digital medication orders, PointLink couriers,COD & local pickups.")}}</p>
                  </div>
               </div>
            </div>
         </div>

         <!-- Orders Card Container -->
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

            <!-- Search & Controls Header -->
            <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
               <div class="relative flex-1 max-w-md">
                  <i class="uil uil-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                  <input type="text" id="orderSearchInput" placeholder="{{('Search orders by ID, pharmacy or patient...')}}" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-lime-500 focus:border-transparent transition-all">
               </div>
            </div>

            <!-- Desktop DataTables View -->
            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
               <table id="pharmacyOrderTable" class="w-full text-left text-sm divide-y divide-gray-200 dark:divide-gray-700" style="width: 100% !important;">
                  <thead class="bg-gray-50 dark:bg-gray-900/60 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                     <tr>
                        <th scope="col" class="px-6 py-4">{{("ID")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Pharmacy")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Patient / Recipient")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Total Amount")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Contact Phone")}}</th>
                        <th scope="col" class="px-6 py-4">{{("Fulfillment")}}</th>
                        <th scope="col" class="px-6 py-4 text-right">{{("Status")}}</th>
                     </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                     {{-- AJAX Populated --}}
                  </tbody>
               </table>
            </div>

            <!-- Mobile Stacked Card Container -->
            <div id="mobileCardsContainer" class="md:hidden space-y-4">
               {{-- Mobile cards generated dynamically --}}
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
        var table = $('#pharmacyOrderTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("pharmacyorder.table") }}',
            columns: [
                { 
                   data: 'id', 
                   name: 'id',
                   render: function(data) {
                      return '<span class="font-mono font-bold text-gray-900 dark:text-white">#' + data + '</span>';
                   }
                },
                { 
                   data: 'pharmacy_name', 
                   name: 'pharmacy_name',
                   render: function(data) {
                      return '<div class="font-bold text-gray-900 dark:text-white">' + (data || 'N/A') + '</div>';
                   }
                },
                { 
                   data: 'user_name', 
                   name: 'user_name',
                   render: function(data) {
                      return '<div class="text-gray-800 dark:text-gray-200">' + (data || 'Guest Patient') + '</div>';
                   }
                },
                { 
                   data: 'total', 
                   name: 'total',
                   render: function(data) {
                      return '<span class="font-bold text-emerald-600 dark:text-emerald-400">$' + parseFloat(data || 0).toFixed(2) + '</span>';
                   }
                },
                { 
                   data: 'phone', 
                   name: 'phone',
                   render: function(data) {
                      return '<span class="text-xs font-mono text-gray-600 dark:text-gray-400"><i class="uil uil-phone mr-1"></i>' + (data || '-') + '</span>';
                   }
                },
                { 
                   data: 'order_type', 
                   name: 'order_type',
                   render: function(data) {
                      var isDelivery = (data || '').toLowerCase().includes('delivery');
                      return isDelivery 
                        ? '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300"><i class="uil uil-truck mr-1"></i> PointLink Delivery</span>'
                        : '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300"><i class="uil uil-store mr-1"></i> Self Pickup</span>';
                   }
                },
                { 
                   data: 'status', 
                   name: 'status',
                   className: 'text-right',
                   render: function(data) {
                      var status = (data || '').toLowerCase();
                      if (status === 'completed' || status === 'delivered') {
                         return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300"><i class="uil uil-check-circle mr-1"></i> Completed</span>';
                      } else if (status === 'cancelled' || status === 'rejected') {
                         return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300"><i class="uil uil-times-circle mr-1"></i> Cancelled</span>';
                      }
                      return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"><i class="uil uil-clock mr-1"></i> Processing</span>';
                   }
                },
            ],
            dom: 'rtip',
            pageLength: 10,
            language: {
                zeroRecords: '<div class="p-8 text-center text-gray-500 dark:text-gray-400">No pharmacy orders found matching your criteria.</div>'
            },
            drawCallback: function(settings) {
                renderMobileCards(settings.json ? settings.json.data : []);
            }
        });

        $('#orderSearchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        function renderMobileCards(data) {
            var container = $('#mobileCardsContainer');
            container.empty();
            if (!data || data.length === 0) {
                container.html('<div class="p-6 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/50 rounded-xl">No pharmacy orders found.</div>');
                return;
            }
            $.each(data, function(i, item) {
                var card = `
                    <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700/60 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-200 dark:border-gray-700">
                            <span class="font-mono font-bold text-gray-900 dark:text-white">#${item.id}</span>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">$${parseFloat(item.total || 0).toFixed(2)}</span>
                        </div>
                        <div class="space-y-1 text-xs">
                            <p class="font-bold text-gray-900 dark:text-white text-sm"><i class="uil uil-store text-lime-500 mr-1"></i> ${item.pharmacy_name || 'N/A'}</p>
                            <p class="text-gray-600 dark:text-gray-400"><i class="uil uil-user mr-1"></i> ${item.user_name || 'Guest Patient'}</p>
                            <p class="text-gray-600 dark:text-gray-400"><i class="uil uil-phone mr-1"></i> ${item.phone || '-'}</p>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700 text-xs">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">${item.order_type || 'Order'}</span>
                            <span class="px-2 py-0.5 rounded-md font-bold ${item.status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800'}">${item.status || 'Pending'}</span>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        }
    });
</script>
@stop
