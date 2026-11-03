@extends('admin.layout')
@section('title')
Featured Management | Admin
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Featured Management</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Featured Management
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
                <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Doctors & Pharmacies</h4>

                <div class="overflow-x-auto">
                    <table id="featured-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Featured Status</th>
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

<!-- Feature Modal -->
<div x-data="{ open: false, profileId: '' }" @open-feature-modal.window="open = true; profileId = $event.detail.id" x-cloak>
    <div x-show="open" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center pb-3">
                <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Feature Profile</h5>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl font-bold">&times;</button>
            </div>
            <form action="{{ route('admin.featured.feature') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="id" x-model="profileId">
                <div>
                    <label for="days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Number of Days to Feature:</label>
                    <input type="number" name="days" id="days" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                </div>
                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button" @click="open = false" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">Feature</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
@section('footer')
<script>
    function openFeatureModal(id) {
        window.dispatchEvent(new CustomEvent('open-feature-modal', { detail: { id: id } }));
    }

    function unfeatureProfile(id) {
        if (confirm('Are you sure you want to unfeature this profile?')) {
            $.ajax({
                url: '{{ route('admin.featured.unfeature') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: id
                },
                success: function(response) {
                    if(response.success) {
                        $('#featured-table').DataTable().ajax.reload(); // Redraw table without full page reload
                    }
                },
                error: function(xhr) {
                    alert('Error unfeaturing profile.');
                    console.error(xhr.responseText);
                }
            });
        }
    }

    $(document).ready(function() {
        $('#featured-table').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.featured.table') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'profile_type', name: 'profile_type' },
                { data: 'featured_status', name: 'featured_status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            dom: 'lrtip'
        });
    });
</script>
@stop
