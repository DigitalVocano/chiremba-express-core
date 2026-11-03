@extends('admin.layout')
@section('title')
{{("Appointments")}} | {{("Admin Dashboard")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
   <div class="page-content p-4 sm:p-6">
      <div class="container-fluid mx-auto space-y-6">

         <!-- Admin Header Hero Card -->
         <div class="admin-header-card rounded-3xl p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
            <div class="flex items-center space-x-4">
               <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center border border-white/20 text-lime-400 text-2xl shadow-inner">
                  <i class="fa-solid fa-calendar-check"></i>
               </div>
               <div>
                  <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{("Appointments Management")}}</h1>
                  <p class="text-xs sm:text-sm text-blue-100/80 mt-1">Track patient-doctor bookings, consultation schedules, and statuses</p>
               </div>
            </div>
            <div class="flex items-center space-x-2 text-xs bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-xl border border-white/20 self-start sm:self-auto">
               <a href="{{ url('admin/dashboard') }}" class="hover:text-lime-300 transition-colors">Home</a>
               <span class="text-white/40">/</span>
               <span class="font-semibold text-lime-400">Appointments</span>
            </div>
         </div>

         <!-- Main Content Card -->
         <div class="bg-white dark:bg-gray-800 shadow-xl rounded-3xl p-6 border border-gray-100 dark:border-gray-700">
            @if(Session::has('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-6 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/50 p-4 text-sm text-emerald-800 dark:text-emerald-200 flex justify-between items-center" role="alert">
               <div class="flex items-center space-x-2">
                   <i class="fa-solid fa-circle-check text-emerald-500"></i>
                   <span>{{ Session::get('message') }}</span>
               </div>
               <button @click="show = false" class="text-emerald-900 dark:text-emerald-300 font-bold">&times;</button>
            </div>
            @endif

            <!-- Desktop Full-Width Table View (hidden on mobile < md) -->
            <div class="hidden md:block w-full overflow-x-auto rounded-2xl border border-gray-100 dark:border-gray-700">
               <table id="appointmenttable" class="w-full divide-y divide-gray-200 dark:divide-gray-700" style="width: 100% !important;">
                  <thead class="bg-gray-50 dark:bg-gray-700/60">
                     <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Id")}}</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Doctor")}}</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Patient")}}</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Date & Time")}}</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Phone")}}</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Description")}}</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Status")}}</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider pr-6">{{("Action")}}</th>
                     </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                     {{-- DataTables populates tbody --}}
                  </tbody>
               </table>
            </div>

            <!-- Mobile Card View Container (visible on mobile < md) -->
            <div id="mobileCardsContainer" class="block md:hidden space-y-4">
               <!-- Mobile cards populated via JS on table redraw -->
            </div>

         </div>
      </div>
   </div>
</div>
@stop
@section('footer')
@parent
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#appointmenttable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            width: "100%",
            ajax: '{{ route("admin.appointment.table") }}',
            columns: [
                { data: 'id', name: 'id', width: '8%', className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-semibold' },
                { data: 'doctor_name', name: 'doctor_name', width: '18%', className: 'px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white' },
                { data: 'patient_name', name: 'patient_name', width: '18%', className: 'px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200' },
                { data: 'date', name: 'date', width: '14%', className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300' },
                { data: 'phone', name: 'phone', width: '12%', className: 'px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300' },
                { data: 'user_description', name: 'appointments.user_description', width: '14%', className: 'px-6 py-4 text-sm text-gray-600 dark:text-gray-300 truncate max-w-xs' },
                { data: 'status', name: 'status', width: '8%', className: 'px-6 py-4 whitespace-nowrap text-sm' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '8%', className: 'px-6 py-4 whitespace-nowrap text-right text-sm font-medium pr-6' }
            ],
            dom: 'lrtip',
            drawCallback: function(settings) {
                // Populate mobile cards view on redraw
                var api = this.api();
                var data = api.rows({ page: 'current' }).data();
                var container = $('#mobileCardsContainer');
                container.empty();

                if (data.length === 0) {
                    container.append('<div class="p-6 text-center text-gray-500 bg-gray-50 rounded-2xl">No appointment records found.</div>');
                    return;
                }

                data.each(function(row) {
                    var docName = row.doctor_name || 'Doctor';
                    var patientName = row.patient_name || 'Patient';

                    var cardHtml = `
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-md space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-gray-900 dark:text-white text-base">${docName}</div>
                                    <div class="text-xs text-gray-500">Patient: ${patientName}</div>
                                </div>
                                <div>${row.status || ''}</div>
                            </div>
                            <div class="text-xs space-y-1 pt-2 border-t border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-300">
                                <div><i class="fa-regular fa-clock text-lime-600 mr-2"></i><strong>Schedule:</strong> ${row.date || 'N/A'}</div>
                                <div><i class="fa-solid fa-phone text-blue-900 mr-2"></i><strong>Phone:</strong> ${row.phone || 'N/A'}</div>
                                <div><i class="fa-solid fa-notes-medical text-lime-600 mr-2"></i><strong>Notes:</strong> ${row.user_description || 'N/A'}</div>
                            </div>
                            <div class="pt-3 flex justify-end items-center border-t border-gray-100 dark:border-gray-700">
                                ${row.action}
                            </div>
                        </div>
                    `;
                    container.append(cardHtml);
                });
            }
        });
    });
</script>
@stop

