@extends('admin.layout')
@section('title')
Medical Aid Submission Details | Admin
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Medical Aid Submission Details</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ route('admin.wallet') }}" class="text-primary-500 hover:underline">Wallet Management</a>
                    / <a href="{{ route('admin.medical_aid_submissions.index') }}" class="text-primary-500 hover:underline">Medical Aid Submissions</a>
                    / Details
                </div>
            </div>

            <div class="flex justify-center">
                <div class="w-full lg:w-3/4">
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
                        @if ($errors->any())
                            <div class="mb-4 rounded-lg bg-red-100 dark:bg-red-200 p-4 text-sm text-red-700 dark:text-red-800" role="alert">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <h4 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Submission Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div><strong>Submission ID:</strong> {{ $submission->id }}</div>
                            <div><strong>Owner:</strong> {{ $submission->owner->name ?? 'N/A' }} ({{ class_basename($submission->owner_type) }})</div>
                            <div><strong>Medical Aid Provider:</strong> {{ $submission->medical_aid_provider }}</div>
                            <div><strong>Policy Number:</strong> {{ $submission->policy_number ?? 'N/A' }}</div>
                            <div><strong>Amount Claimed:</strong> {{ ($setting->currency ?? '$') . number_format($submission->amount_claimed, 2) }}</div>
                            <div>
                                <strong>Current Status:</strong>
                                @php
                                    $badgeClass = '';
                                    switch ($submission->status) {
                                        case 'pending': $badgeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100'; break;
                                        case 'approved': $badgeClass = 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100'; break;
                                        case 'rejected': $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100'; break;
                                    }
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">{{ ucfirst($submission->status) }}</span>
                            </div>
                            <div><strong>Submitted At:</strong> {{ $submission->submitted_at->format('Y-m-d H:i:s') }}</div>
                            @if($submission->status != 'pending')
                            <div><strong>Processed At:</strong> {{ $submission->processed_at ? $submission->processed_at->format('Y-m-d H:i:s') : 'N/A' }}</div>
                            <div><strong>Approved Amount:</strong> {{ $submission->approved_amount ? ($setting->currency ?? '$') . number_format($submission->approved_amount, 2) : 'N/A' }}</div>
                            <div class="col-span-full"><strong>Admin Notes:</strong> {{ $submission->admin_notes ?? 'N/A' }}</div>
                            @endif
                        </div>

                        <h4 class="text-xl font-semibold text-gray-800 dark:text-white mt-5 mb-4">Proof Document</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($submission->proof_document_path)
                            <div>
                                <a href="{{ asset($submission->proof_document_path) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i class="fas fa-file-download mr-2"></i> View Document
                                </a>
                            </div>
                            @else
                            <div class="col-span-full"><p class="text-gray-600 dark:text-gray-400">No proof document uploaded for this submission.</p></div>
                            @endif
                        </div>

                        @if($submission->status == 'pending')
                        <h4 class="text-xl font-semibold text-gray-800 dark:text-white mt-5 mb-4">Process Submission</h4>
                        <form x-data="{ approvedAmount: {{ old('approved_amount', $submission->amount_claimed) ?? 0 }} }" action="{{ route('admin.medical_aid_submissions.process') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="submission_id" value="{{ $submission->id }}">
                            <div>
                                <label for="approved_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Approved Amount ({{ $setting->currency ?? '$' }})</label>
                                <input type="number" step="0.01" min="0" name="approved_amount" id="approved_amount" x-model.number="approvedAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                            </div>
                            <div>
                                <label for="admin_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin Notes</label>
                                <textarea name="admin_notes" id="admin_notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" rows="3">{{ old('admin_notes') }}</textarea>
                            </div>
                            <div class="flex justify-end space-x-2 pt-4">
                                <button type="submit" name="action" value="approve" :disabled="!approvedAmount || approvedAmount <= 0" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">Approve & Credit Wallet</button>
                                <button type="submit" name="action" value="reject" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">Reject Submission</button>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
