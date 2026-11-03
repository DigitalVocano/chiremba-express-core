@extends('admin.layout')
@section('title')
{{("View Subscription")}} | {{("Admin")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-6">
      <div class="container-fluid mx-auto">
         <!-- Page Title -->
         <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("View Subscription")}}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
               <a href="{{url('admin/subscriber_doc')}}" class="text-primary-500 hover:underline">{{("Subscription")}}</a>
               / {{("View Subscription")}}
            </div>
         </div>

         <div class="flex justify-center">
            <div class="w-full lg:w-3/4">
               <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div>
                        <h6 class="font-bold text-gray-700 dark:text-gray-300">{{("Doctor Name")}}</h6>
                        <p class="text-gray-900 dark:text-gray-200">{{isset($data)?$data->doctor_id:''}}</p>
                     </div>
                     <div>
                        <h6 class="font-bold text-gray-700 dark:text-gray-300">{{("Payment Type")}}</h6>
                        <p class="text-gray-900 dark:text-gray-200">
                           @if(isset($data->payment_type) && $data->payment_type==1)
                              {{("Braintree")}}
                           @elseif(isset($data->payment_type) && $data->payment_type==2)
                              {{("Bank Deposit")}}
                           @endif
                        </p>
                     </div>
                     <div>
                        <h6 class="font-bold text-gray-700 dark:text-gray-300">{{("Amount")}}</h6>
                        <p class="text-gray-900 dark:text-gray-200">{{isset($data)?$data->amount:''}}</p>
                     </div>
                     <div>
                        <h6 class="font-bold text-gray-700 dark:text-gray-300">{{("TransactionID")}}</h6>
                        <p class="text-gray-900 dark:text-gray-200">{{isset($data)?$data->transaction_id:''}}</p>
                     </div>
                     <div>
                        <h6 class="font-bold text-gray-700 dark:text-gray-300">{{("Subscription Plan")}}</h6>
                        <p class="text-gray-900 dark:text-gray-200">
                           @if(isset($data->subscription_id))
                              {{$data->subscription_id." ".__("Month")}}
                           @endif
                        </p>
                     </div>
                     <div>
                        <h6 class="font-bold text-gray-700 dark:text-gray-300">{{("Status")}}</h6>
                        <p class="text-gray-900 dark:text-gray-200">
                           @if(isset($data->status) && $data->status==1)
                              {{("Not Active")}}
                           @elseif(isset($data->status) && $data->status==2)
                              {{("Active")}}
                           @elseif(isset($data->status) && $data->status==3)
                              {{("Expired")}}
                           @elseif(isset($data->status) && $data->status==4)
                              {{("Reject")}}
                           @endif
                        </p>
                     </div>
                     <div>
                        <h6 class="font-bold text-gray-700 dark:text-gray-300">{{("Date")}}</h6>
                        <p class="text-gray-900 dark:text-gray-200">{{isset($data)?$data->date:''}}</p>
                     </div>
                     <div>
                        <h6 class="font-bold text-gray-700 dark:text-gray-300">{{("Description")}}</h6>
                        <p class="text-gray-900 dark:text-gray-200">{{isset($data)?$data->description:''}}</p>
                     </div>
                     <div class="md:col-span-2">
                        <h6 class="font-bold text-gray-700 dark:text-gray-300">{{("Receipt File")}}</h6>
                        @if(isset($data->deposit_image))
                           @php $destinationPath = asset("upload/bank_receipt").'/'.$data->deposit_image; @endphp
                           <a href="{{ $destinationPath }}" target="_blank" class="text-primary-500 hover:underline">{{("Show Receipt")}}</a>
                        @else
                           <p class="text-gray-900 dark:text-gray-200">-</p>
                        @endif
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@stop
@section('footer')
@stop
