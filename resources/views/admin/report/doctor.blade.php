@extends('admin.layout')
@section('title')
{{("Doctor Report")}} | {{("Admin")}}
@stop
@section('meta-data')
@endsection
@section('content')
<div class="main-content">
    <div class="page-content p-6">
       <div class="container-fluid mx-auto">
          <!-- Hero Header Card -->
          <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0A2B68] via-[#0F3A8A] to-[#1E40AF] p-6 sm:p-8 text-white shadow-xl mb-8">
              <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
              <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                  <div>
                      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#84CC16]/20 border border-[#84CC16]/30 text-[#84CC16] text-xs font-semibold uppercase tracking-wider mb-3">
                          <i class="uil uil-chart-bar"></i> Analytics & Reports
                      </div>
                      <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Doctors Directory & Report</h1>
                      <p class="mt-2 text-blue-100 text-sm max-w-xl">
                          Filter and analyze registered medical practitioners, specialities, contact information, and registration metrics.
                      </p>
                  </div>
                  <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md px-5 py-3 rounded-xl border border-white/15">
                      <div class="w-12 h-12 rounded-lg bg-[#84CC16] text-[#0A2B68] flex items-center justify-center font-bold text-xl shadow-inner">
                          <i class="uil uil-[#0A2B68] uil-user-md"></i>
                      </div>
                      <div>
                          <div class="text-xs uppercase text-blue-200 font-semibold">Total Registered</div>
                          <div class="text-2xl font-black text-white">{{ $total ?? 0 }}</div>
                      </div>
                  </div>
              </div>
          </div>

          <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6 border border-gray-100 dark:border-gray-700">
             @if(Session::has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                    <div class="flex items-center">
                        <i class="uil uil-check mr-2"></i>
                        <span>{{ Session::get('message') }}</span>
                    </div>
                    <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                </div>
             @endif

             <div x-data="{ filter: '{{ Request::get('data_filter', '') }}' }" class="bg-gray-50 dark:bg-gray-700/50 p-5 rounded-xl border border-gray-200 dark:border-gray-600 mb-6">
                 <form action="{{url('admin/doctor_report')}}" method="get">
                     <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-center">
                         <div>
                             <label for="data_filter" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">{{("Filter by Period")}}</label>
                             <select name="data_filter" id="data_filter" x-model="filter" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 text-sm">
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
                                 <label for="start_date" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">{{("Start Date")}}</label>
                                 <input type="date" name="start_date" value="{{Request::get('start_date') ?? date('Y-m-d')}}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 text-sm">
                             </div>
                             <div>
                                 <label for="end_date" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">{{("End Date")}}</label>
                                 <input type="date" name="end_date" value="{{Request::get('end_date') ?? date('Y-m-d')}}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 text-sm">
                             </div>
                         </div>
                         <div class="pt-5">
                             <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-[#0A2B68] hover:bg-[#082255] border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest transition duration-150 shadow-md">
                                 <i class="uil uil-filter mr-2"></i> {{("Apply Filter")}}
                             </button>
                         </div>
                     </div>
                 </form>
             </div>

             <input type="hidden" name="count_data" value="{{$total}}" id="count_data">

             <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table id="myTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                   <thead class="bg-gray-100 dark:bg-gray-700/80">
                      <tr>
                         <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{("Id")}}</th>
                         <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{("Image")}}</th>
                         <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{("Doctor Name")}}</th>
                         <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{("Email")}}</th>
                         <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{("Phone")}}</th>
                         <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">{{("Service / Speciality")}}</th>
                      </tr>
                   </thead>
                   <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      @foreach ($doctors as $doc)
                      <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">#{{$doc->id}}</td>
                          <td class="px-6 py-4 whitespace-nowrap">
                              <img src="{{ $doc->image ? asset('upload/doctors/' . $doc->image) : asset('upload/doctors/default.png') }}"
                                   alt="{{ $doc->name }}"
                                   class="h-12 w-12 object-cover rounded-full border border-gray-200 dark:border-gray-600 shadow-sm">
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{$doc->name}}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{$doc->email}}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{$doc->phoneno ?? $doc->phone ?? 'N/A'}}</td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm">
                              @php
                                  $serviceName = $doc->department->name ?? $doc->services ?? null;
                              @endphp
                              @if($serviceName)
                                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                      {{ $serviceName }}
                                  </span>
                              @else
                                  <span class="text-gray-400 text-xs italic">Not assigned</span>
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
               text: '<i class="uil uil-file-download"></i> {{("Download Excel")}}',
               className: 'btn btn-sm bg-[#84CC16] text-[#0A2B68] font-bold px-3 py-1 rounded shadow-sm hover:bg-[#76b813]'
           });
       }

       $('#myTable').DataTable(dataTableOptions);
   });
</script>
@endsection
