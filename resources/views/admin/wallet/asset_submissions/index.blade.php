@extends('admin.layout')
@section('title')
Asset Submissions | Admin
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Asset Submissions</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ route('admin.wallet') }}" class="text-primary-500 hover:underline">Wallet Management</a>
                    / Asset Submissions
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
                <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">All Asset Submissions</h4>

                <div class="overflow-x-auto">
                    <table id="asset-submissions-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Owner</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Asset Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Serial Number</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Submitted At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
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
        $('#asset-submissions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.asset_submissions.table') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'owner', name: 'owner', orderable: false, searchable: false },
                { data: 'asset_type', name: 'asset_type', orderable: false, searchable: false },
                { data: 'serial_number', name: 'serial_number' },
                { data: 'description', name: 'description' },
                { data: 'status', name: 'status' },
                { data: 'submitted_at', name: 'submitted_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ]
        });
    });
</script>
@stop
