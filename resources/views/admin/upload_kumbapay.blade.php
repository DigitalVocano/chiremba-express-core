@extends('admin.layout')
@section('title')
Kumbapay Applications | Admin Dashboard
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Kumbapay Applications</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{ url('admin/dashboard') }}" class="text-primary-500 hover:underline">Dashboard</a> / Kumbapay Applications
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

            <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Applications List</h4>

            <div class="overflow-x-auto">
               <table id="kumbapayApplicationsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                     <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">id</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">fullname</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">phone</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">address</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Professional Job</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Loan Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Identity</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Prescription</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Action</th>
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
        $('#kumbapayApplicationsTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ url("admin/kumbapaytable") }}',
                error: function (xhr, error, thrown) {
                    console.error("DataTables Ajax error: ", xhr.responseText);
                    alert("An error occurred while loading data. Please check the browser's developer console for more information.");
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'fullname', name: 'fullname' },
                { data: 'phone', name: 'phone' },
                { data: 'address', name: 'address' },
                { data: 'professionaljob', name: 'professionaljob' },
                { data: 'loanamount', name: 'loanamount' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'identity_doc', name: 'identity_doc', orderable: false, searchable: false },
                { data: 'prescription', name: 'prescription', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            dom: '<"flex justify-between items-center mb-4"l<"ml-auto"f>>rtip'
        });
    });

    function active_application(record_id, status) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to accept this application.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, accept it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("active_application") }}',
                    method: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "record_id": record_id,
                        "status": status
                    },
                    success: function (data) {
                        Swal.fire(
                            'Accepted!',
                            'The application has been accepted.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'There was an error accepting the application.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    function disable_application(record_id, status) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to reject this application.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, reject it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("disable_application") }}',
                    method: "POST",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "record_id": record_id,
                        "status": status
                    },
                    success: function (data) {
                        Swal.fire(
                            'Rejected!',
                            'The application has been rejected.',
                            'success'
                        ).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'There was an error rejecting the application.',
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>
@stop
