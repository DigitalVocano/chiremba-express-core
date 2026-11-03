@extends('admin.layout')
@section('title')
{{("User Report")}} | {{("Admin")}}
@stop
@section('meta-data')
@endsection
@section('content')
<div class="main-content">
    <div class="page-content p-6">
       <div class="container-fluid mx-auto">
          <!-- Page Title -->
          <div class="flex justify-between items-center mb-6">
             <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("User Report")}}</h1>
             <div class="text-sm text-gray-500 dark:text-gray-400">
                {{("User Report")}}
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

             <div x-data="{ filter: '{{ Request::get('data_filter', '') }}' }" class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg mb-6">
                 <form action="{{url('admin/user_report')}}" method="get">
                     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-center">
                         <div>
                             <label for="data_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{("Filter by Period")}}</label>
                             <select name="data_filter" id="data_filter" x-model="filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
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
                         <div x-show="filter === '1'" x-transition class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4" style="display: none;">
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
                         {{("User not found")}}
                     @else
                         {{("Total user register are")}} {{$total}}
                     @endif
                 @else
                     {{("User List")}}
                 @endif
             </h4>

             <input type="hidden" name="count_data" value="{{$total}}" id="count_data">

             <div class="overflow-x-auto">
                <table id="myTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                   <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Id")}}</th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Name")}}</th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Email")}}</th>
                         <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Phone")}}</th>
                      </tr>
                   </thead>
                   <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      @foreach ($user as $doc)
                      <tr>
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{$doc->id}}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{$doc->name}}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{$doc->email}}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{$doc->phone}}</td>
                      </tr>
                      @endforeach
                   </tbody>
                </table>
             </div>
          </div>
       </div>
    </div>
</div>
@endsection

@section('footer')
<script>
   $(document).ready(function() {
       const hasData = $("#count_data").val() > 0;
       const dataTableOptions = {
           order: [[0, 'desc']],
           dom: 'Bfrtip',
           buttons: []
       };

       if (hasData) {
           dataTableOptions.buttons.push({
               extend: 'excel',
               text: '{{("Download excel")}}'
           });
       }

       $('#myTable').DataTable(dataTableOptions);
   });
</script>
@endsection
