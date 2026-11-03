@extends('admin.layout')
@section('title')
{{("Doctor Subscription Report")}} | {{("Admin")}}
@stop
@section('footer')

@stop
@section('meta-data')
@stop
@section('content')

<div class="main-content">
    <div class="page-content p-6">
       <div class="container-fluid mx-auto">
          <!-- Page Title -->
          <div class="flex justify-between items-center mb-6">
             <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Doctor Subscription Report")}}</h1>
             <div class="text-sm text-gray-500 dark:text-gray-400">
                {{("Doctor Subscription Report")}}
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

             <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg mb-6">
                 <form action="{{url('admin/do_sub_report')}}" method="get">
                     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-center">
                         <div>
                             <label for="data_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Filter by Period")}}</label>
                             <select name="data_filter" id="data_filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" onchange="showDiv('hidden_div', this)">
                                 <option value="">{{("All data")}}</option>
                                 <option value="1" {{Request::get('data_filter') == '1' ? 'selected':''}}>{{("Custom Range")}}</option>
                                 <option value="today" {{Request::get('data_filter') == 'today' ? 'selected':''}}>{{("Today")}}</option>
                                 <option value="last_week" {{Request::get('data_filter') == 'last_week' ? 'selected':''}}>{{("Last week")}}</option>
                                 <option value="this_month" {{Request::get('data_filter') == 'this_month' ? 'selected':''}}>{{("This month")}}</option>
                                 <option value="last_month" {{Request::get('data_filter') == 'last_month' ? 'selected':''}}>{{("Last month")}}</option>
                                 <option value="this_year" {{Request::get('data_filter') == 'this_year' ? 'selected':''}}>{{("This year")}}</option>
                                 <option value="last_year" {{Request::get('data_filter') == 'last_year' ? 'selected':''}}>{{("Last year")}}</option>
                             </select>
                         </div>
                         <div id="hidden_div" class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4" style="display: {{Request::get('data_filter') == '1' ? 'grid' : 'none'}};">
                             <div>
                                 <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Start Date")}}</label>
                                 <input type="date" name="start_date" value="{{Request::get('start_date') ?? date('Y-m-d')}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                             </div>
                             <div>
                                 <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("End Date")}}</label>
                                 <input type="date" name="end_date" value="{{Request::get('end_date') ?? date('Y-m-d')}}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                             </div>
                         </div>
                         <div class="pt-5">
                             <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Apply Filter")}}</button>
                         </div>
                     </div>
                 </form>
             </div>

             <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                 @if(Request::get('data_filter'))
                     @if ($total == null)
                         {{("doctor subscribe not found")}}
                     @else
                         {{("Total doctor subscribe are")}} {{$total}}
                     @endif
                 @else
                     {{("Doctor Subscribe List")}}
                 @endif
             </h4>

             <input type="hidden" name="count_data" value="{{$total}}" id="count_data">

             <div class="overflow-x-auto">
                <table id="myTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                   <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Id")}}</th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Doctor Name")}}</th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Payment Type")}}</th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Amount")}}</th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Status")}}</th>
                      </tr>
                   </thead>
                   <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      @foreach ($doctorsubscription as $doc)
                      <tr>
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{$doc->id}}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                              @if ($doc->doctors)
                                  {{$doc->doctors->name}} ({{ $doc->doctors->profile_type == 2 ? 'Pharmacy' : 'Doctor' }})
                              @elseif ($doc->provider)
                                  {{$doc->provider->name}} ({{ $doc->provider->profile_type == 2 ? 'Pharmacy' : 'Doctor' }})
                              @else
                                  Subscriber #{{ $doc->subscriber_id ?: $doc->doctor_id }}
                              @endif
                              @if ($doc->subscription)
                                  <span class="block text-xs text-teal-600">{{ $doc->subscription->name }}</span>
                              @endif
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                              @if($doc->payment_type == 0)
                              Wallet
                              @elseif($doc->payment_type == 1)
                              Braintree
                              @elseif ($doc->payment_type == 2)
                              Bank Deposit
                              @elseif ($doc->payment_type == 3)
                              Razorpay
                              @elseif ($doc->payment_type == 4)
                              Paystack
                              @endif
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">${{$doc->amount}}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                              @if($doc->status == 1)
                              not active
                              @elseif ($doc->status == 2)
                              active
                              @elseif ($doc->status == 3)
                              expiry
                              @elseif ($doc->status == 4)
                              reject
                              @endif
                          </td>
                      </tr>
                      @endforeach
                   </tbody>
                </table>
             </div>
          </div>
       </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
   function showDiv(divId, element) {
      document.getElementById(divId).style.display = element.value == 1 ? 'grid' : 'none';
   }
</script>
<script>
   $(document).ready(function() {
       var test = $("#count_data").val();
       if(test > 0){
           $('#myTable').DataTable( {
               dom: 'Bfrtip',
               order: [[0, 'desc']],
               buttons: [
                   {
                       extend: 'excel',
                       text: '{{("Download excel")}}'
                   }
               ]
           });
       } else {
           let table = new DataTable('#myTable', {
               order: [[0, 'desc']]
           });
       }
   });
</script>

@stop
@section('footer')

@stop
