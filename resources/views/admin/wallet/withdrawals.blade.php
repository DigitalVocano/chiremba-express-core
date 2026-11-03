@extends('admin.layout')
@section('title')
Withdrawal Requests | Admin
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Withdrawal Requests</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ route('admin.wallet') }}" class="text-primary-500 hover:underline">Wallet Management</a>
                    / Withdrawal Requests
                </div>
            </div>

            <div x-data="{
                    isModalOpen: false,
                    requestId: '',
                    ownerName: '',
                    amount: '',
                    openModal(id, name, amount) {
                        this.requestId = id;
                        this.ownerName = name;
                        this.amount = amount;
                        this.isModalOpen = true;
                    }
                }" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                @if(Session::has('message'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                    <div class="flex items-center">
                        <i class="uil uil-check mr-2"></i>
                        <span>{{ Session::get('message') }}</span>
                    </div>
                    <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                </div>
                @endif
                <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">All Withdrawal Requests</h4>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Owner</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Withdrawal Method</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Account Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requested At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Processed By</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Processed At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Admin Notes</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($withdrawalRequests as $request)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $request->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $request->wallet->owner->name ?? 'N/A' }} ({{ class_basename($request->wallet->owner_type) }})</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $setting->currency ?? '$' }}{{ number_format($request->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ ucfirst($request->withdrawal_method) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    @php
                                        $details = json_decode($request->account_details, true);
                                    @endphp
                                    @if($details)
                                        @foreach($details as $key => $value)
                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}<br>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $badgeClass = '';
                                        switch ($request->status) {
                                            case 'pending': $badgeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100'; break;
                                            case 'approved': $badgeClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'; break;
                                            case 'rejected': $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100'; break;
                                        }
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">{{ ucfirst($request->status) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $request->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $request->processedByAdmin->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $request->processed_at ? $request->processed_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $request->admin_notes ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($request->status == 'pending')
                                    <button type="button"
                                            @click="openModal({{ $request->id }}, '{{ e($request->wallet->owner->name ?? 'N/A') }}', '{{ number_format($request->amount, 2, '.', '') }}')"
                                            class="inline-flex items-center px-3 py-1.5 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        Process
                                    </button>
                                    @else
                                    <button type="button" class="inline-flex items-center px-3 py-1.5 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed" disabled>Processed</button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">No withdrawal requests found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $withdrawalRequests->links() }}
                </div>

                {{-- Process Withdrawal Modal --}}
                <div x-show="isModalOpen" @keydown.escape.window="isModalOpen = false" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
                    <div @click.away="isModalOpen = false" class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                        <div class="flex justify-between items-center pb-3">
                            <h5 class="text-xl font-semibold text-gray-900 dark:text-white">Process Withdrawal Request</h5>
                            <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl font-bold">&times;</button>
                        </div>
                        <form action="{{ route('admin.wallet.process_withdrawal') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="request_id" :value="requestId">
                            <div>
                                <p class="text-gray-700 dark:text-gray-300">Owner: <strong x-text="ownerName"></strong></p>
                                <p class="text-gray-700 dark:text-gray-300">Amount: <strong x-text="'{{ $setting->currency ?? '$' }}' + amount"></strong></p>
                            </div>
                            <div>
                                <label for="admin_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin Notes (optional):</label>
                                <textarea id="admin_notes" name="admin_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
                            </div>
                            <div class="flex justify-end space-x-2 pt-4">
                                <button type="button" @click="isModalOpen = false" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">Cancel</button>
                                <button type="submit" name="action" value="reject" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">Reject</button>
                                <button type="submit" name="action" value="approve" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">Approve</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop
@section('footer')
@stop
