@extends('admin.layout')
@section('title')
Reviews | Admin
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Reviews</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{url('admin/dashboard')}}">Home</a> /
               <span class="text-gray-900 dark:text-white">Reviews</span>
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

            <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Reviews List</h4>
            <div class="overflow-x-auto">
               <table id="reviewtable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-700">
                     <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Id</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Doctor Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Patient Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rating</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Review</th>
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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#reviewtable').DataTable({
            destroy: true, // Add this to prevent re-initialization error
            processing: true,
            serverSide: true,
            ajax: '{{ route("admin.reviews.table") }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'doctor_name', name: 'doctorls.name' },
                { data: 'username', name: 'patientls.name' },
                { data: 'ratting', name: 'rating' },
                { data: 'comment', name: 'description' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            dom: 'lrtip',
            buttons: []
        });
    });

    function delete_record(url) {
        if (confirm('Are you sure you want to delete this record?')) {
            window.location.href = url;
        }
    }
</script>
@stop
