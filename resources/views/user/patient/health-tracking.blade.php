@extends('layouts.patient')

@section('title')
    Health Tracking
@stop

@section('meta-data')
    <meta property="og:type" content="website" />
    <meta property="og:url" content="KumbaCare" />
    <meta property="og:title" content="Health Tracking" />
    <meta property="og:image" content="{{ asset('image_web/').'/'.$setting->favicon }}" />
    <meta property="og:image:width" content="250px" />
    <meta property="og:image:height" content="250px" />
    <meta property="og:site_name" content="KumbaCare" />
    <meta property="og:description" content="Track your health metrics like blood pressure, weight, and more with KumbaCare." />
    <meta property="og:keyword" content="health tracking, medical records, patient dashboard, healthcare" />
    <link rel="shortcut icon" href="{{ asset('image_web/').'/'.$setting->favicon }}">
@stop

@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">Health Tracking</h1>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 sm:p-6" x-data="{ showAddModal: false }">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <h3 class="text-xl font-semibold text-tele-dark-blue dark:text-gray-200">Your Health Records</h3>
            <button type="button" @click="showAddModal = true" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                Add New Record <i class="fas fa-plus ml-2"></i>
            </button>
        </div>

        {{-- Compact Filter Form Card --}}
        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border dark:border-gray-700 mb-6">
            <form method="GET" action="{{ route('health.tracking') }}">
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="flex-grow w-full sm:w-auto">
                        <label for="date_filter" class="sr-only">Date</label>
                        <input type="date" class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm" id="date_filter" name="date_filter" value="{{ request('date_filter') }}" placeholder="Filter by date">
                    </div>
                    <div class="flex-grow w-full sm:w-auto">
                        <label for="total_filter" class="sr-only">Records per page</label>
                        <select class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:text-white sm:text-sm" id="total_filter" name="total_filter">
                            <option value="">Show All</option>
                            <option value="10" {{ request('total_filter') == '10'? 'selected': '' }}>10 per page</option>
                            <option value="25" {{ request('total_filter') == '25'? 'selected': '' }}>25 per page</option>
                            <option value="50" {{ request('total_filter') == '50'? 'selected': '' }}>50 per page</option>
                            <option value="100" {{ request('total_filter') == '100'? 'selected': '' }}>100 per page</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-secondary-500 text-white text-sm font-medium rounded-md shadow-sm hover:bg-secondary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary-500 flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- Responsive Table/Cards --}}
        <div class="overflow-x-auto">
            {{-- Desktop Table --}}
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 hidden md:table">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Blood Pressure</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Weight</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Temperature</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Glucose Level</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                             <button class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition" onclick="exportToPDF()">Export to PDF</button>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($healthRecords as $record)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{{ $record->date }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $record->blood_pressure ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $record->weight ? $record->weight . ' kg' : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $record->temperature ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $record->glucose_level ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            {{-- Add actions here if needed, e.g., Edit/Delete buttons --}}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                            No Health Records Found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Mobile Cards --}}
            <div class="grid grid-cols-1 gap-4 md:hidden">
                @forelse($healthRecords as $record)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg shadow p-4 space-y-2">
                        <p class="font-bold text-gray-900 dark:text-gray-100">{{ $record->date }}</p>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Blood Pressure</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $record->blood_pressure ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Weight</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $record->weight ? $record->weight . ' kg' : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Temperature</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $record->temperature ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Glucose Level</p>
                                <p class="text-gray-800 dark:text-gray-200">{{ $record->glucose_level ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        No Health Records Found
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $healthRecords->appends(request()->query())->links('pagination::tailwind') }}
        </div>

        {{-- Add Health Record Modal (Alpine.js) --}}
        <div x-show="showAddModal" style="display: none;" @keydown.escape.window="showAddModal = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showAddModal = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showAddModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                    <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="modal-title">Add New Health Record</h3>
                        <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                            <span class="sr-only">Close</span> <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mt-4">
                        @include('user.patient.add-health-record')
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
    {{-- JavaScript for PDF Export --}}
    <script>
      function exportToPDF() {
        // Clone the table to avoid modifying the original
        var tableClone = document.querySelector('.hidden.md\\:table').cloneNode(true);

        // Remove the actions column from the cloned table header and body
        var headerRow = tableClone.querySelector('thead tr');
        if (headerRow) {
            headerRow.querySelector('th:last-child').remove();
        }
        var bodyRows = tableClone.querySelectorAll('tbody tr');
        bodyRows.forEach(function(row) {
            var cell = row.querySelector('td:last-child');
            if (cell) {
                cell.remove();
            }
        });

        var newWindow = window.open();
        newWindow.document.write('<html><head><title>Health Records</title>');
        newWindow.document.write('<style>body{font-family:sans-serif;} table { width: 100%; border-collapse: collapse; margin-bottom: 20px; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; } h2 {text-align:center; margin-bottom:20px;} </style>');
        newWindow.document.write('</head><body>');
        newWindow.document.write('<h2>Your Health Records</h2>');
        newWindow.document.write(tableClone.outerHTML);
        newWindow.document.write('</body></html>');
        newWindow.document.close();
        newWindow.print();
    }
    </script>
@stop
