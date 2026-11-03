@extends('admin.layout')
@section('title')
{{("Dashboard")}} | {{("Admin")}} {{("Dashboard")}}
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("Dashboard")}}</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{("Dashboard")}}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-1" data-plugin="counterup">{{$totalappointment}}</h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">{{("New Appointment")}}</p>
                        </div>
                        <div class="text-primary-500 text-4xl">
                            <i class="uil-shutter-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-1" data-plugin="counterup">{{$totaldoctor}}</h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">{{("Total Doctors")}}</p>
                        </div>
                        <div class="text-primary-500 text-4xl">
                            <i class="uil-flask"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-1" data-plugin="counterup">{{$totalpatient}}</h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">{{("Total Patients")}}</p>
                        </div>
                        <div class="text-primary-500 text-4xl">
                            <i class="uil-file-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-1" data-plugin="counterup">{{$totalreview}}</h4>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">{{("Total Review")}}</p>
                        </div>
                        <div class="text-primary-500 text-4xl">
                            <i class="uil-star"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">{{("Today Appointment")}}</h4>

                <div class="overflow-x-auto">
                    <table id="latsrappointmenttable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Id")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Doctor Name")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Patient Name")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("DateTime")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Phone")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("User Description")}}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{("Status")}}</th>
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
        $('#latsrappointmenttable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.appointment.latest") }}',
                error: function (xhr, error, thrown) {
                    // Log the full error response to the console
                    console.error("DataTables Ajax error: ", xhr.responseText);
                    alert("An error occurred while loading data. Please check the browser's developer console for more information.");
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'doctor_name', name: 'doctors.name' },
                { data: 'patient_name', name: 'users.name' },
                { data: 'date', name: 'appointments.date' },
                { data: 'phone', name: 'appointments.phone' },
                { data: 'user_description', name: 'appointments.user_description' },
                { data: 'status', name: 'status', orderable: false, searchable: false }
            ],
            dom: '<"flex justify-between items-center mb-4"l<"ml-auto"f>>rtip',
        });
    });
</script>
@stop
