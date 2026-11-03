@extends('admin.layout')
@section('title')
View Kumbapay Application | Admin
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">View Application</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{url('admin/upload_kumbapay')}}" class="text-primary-500 hover:underline">Applications</a>
               / View Application
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-3/4">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                     <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Application Number</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $data->id ?? 'N/A' }}</dd>
                     </div>
                     <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $data->fullname ?? 'N/A' }}</dd>
                     </div>
                     <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $data->phone ?? 'N/A' }}</dd>
                     </div>
                     <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $data->address ?? 'N/A' }}</dd>
                     </div>
                     <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Profession</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $data->professionaljob ?? 'N/A' }}</dd>
                     </div>
                     <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Loan Amount</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $data->loanamount ?? 'N/A' }}</dd>
                     </div>
                     <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1 text-sm">
                           @php
                              $statusText = 'Pending';
                              $statusClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100';
                              if(isset($data->status)) {
                                 switch($data->status) {
                                       case 1:
                                          $statusText = "Accepted";
                                          $statusClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100';
                                          break;
                                       case 2:
                                       case 4:
                                          $statusText = "Rejected";
                                          $statusClass = 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100';
                                          break;
                                 }
                              }
                           @endphp
                           <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                              {{ $statusText }}
                           </span>
                        </dd>
                     </div>
                     <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Application Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $data->created_at ? $data->created_at->format('F j, Y, g:i a') : 'N/A' }}</dd>
                     </div>
                     <div class="sm:col-span-2 pt-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Attachments</dt>
                        <dd class="mt-2 space-y-2">
                           @if(isset($data->identity))
                              <a href="{{ asset('upload/identity/' . $data->identity) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-md text-sm font-medium hover:bg-blue-200 dark:hover:bg-blue-800">
                                 <i class="uil uil-file-alt mr-2"></i> View ID/Passport
                              </a>
                           @endif
                           @if(isset($data->prescription))
                              <a href="{{ asset('upload/orderprescription/' . $data->prescription) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-md text-sm font-medium hover:bg-blue-200 dark:hover:bg-blue-800">
                                 <i class="uil uil-file-medical-alt mr-2"></i> View Prescription
                              </a>
                           @endif
                        </dd>
                     </div>
                  </dl>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@stop
@section('footer')
@stop
