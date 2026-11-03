@extends('admin.layout')
@section('title')
{{("Subscription List")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Subscription List")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               {{("Subscription List")}}
            </div>
         </div>

         <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            @if(Session::has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
               <div class="flex items-center">
                   <i class="uil uil-check mr-2"></i>
                   <span>{{ Session::get('message') }}</span>
               </div>
               <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
            </div>
            @endif

            <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">{{("Subscription List")}}</h4>

            <div class="overflow-x-auto">
               <table id="subscriber_doc" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                     <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Doctor Name")}}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Payment Type")}}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Amount")}}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Status")}}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Receipt File")}}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Action")}}</th>
                     </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                     {{-- DataTables will populate this tbody --}}
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
@stop
@section('footer')
<script>
    $(document).ready(function() {
        $('#subscriber_doc').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: '{{ url("admin/subscribetable") }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'doctor_id', name: 'doctor_id' },
                { data: 'payment_type', name: 'payment_type' },
                { data: 'amount', name: 'amount' },
                { data: 'status', name: 'status' },
                { data: 'deposit_image', name: 'deposit_image', orderable: false, searchable: false, render: function(data) {
                    return data ? `<a href="${data}" target="_blank" class="text-primary-500 hover:underline">View Receipt</a>` : 'N/A';
                }},
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            dom: '<"flex justify-between items-center mb-4"l<"ml-auto"f>>rtip'
        });
    });

      function active_order(record_id,status)
      {
        var x = confirm('{{("Are you sure active this subscription")}}');
        if(x){
             $.ajax({
              url: '{{route("active-order")}}',
              method: "POST",
              data: {
                "_token": "{{ csrf_token() }}",
                "record_id": record_id,
                "status":status
              },
              success: function (data)
              {
                window.location.reload();
              }
          });
        }
      }


  function disable_order(record_id,status)
  {
    var x = confirm('{{("Are you sure disable this subscription")}}');
    if(x){
      $.ajax({
        url: '{{route("disable-order")}}',
        method: "POST",
        data: {
          "_token": "{{ csrf_token() }}",
          "record_id": record_id,
          "status":status
        },
        success: function (data)
        {
          window.location.reload();
        }
    });
    }
  }
</script>
@stop
