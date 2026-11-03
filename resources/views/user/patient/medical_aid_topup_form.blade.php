@extends('layouts.patient')
@section('title')
{{__("Medical Aid Top-up")}} | {{__("KumbaCare")}}
@endsection
@section('meta-data')
{{-- You can add meta data here if needed, it's currently empty but correctly placed --}}
@endsection

@section('content')
<section class="py-16 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Sidebar (User Info) --}}
            <div class="lg:col-span-1">
                <div class="sticky top-28">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <div class="text-center">
                            <img class="w-32 h-32 rounded-full mx-auto border-4 border-primary-200 dark:border-primary-700 shadow-md" src="{{ asset('upload/profile/' . ($userdata->profile_pic ?? 'default.png')) }}" alt="{{ $userdata->name ?? 'User' }}">
                            <h2 class="text-2xl font-bold mt-4 text-gray-900 dark:text-white">{{ $userdata->name ?? 'User' }}</h2>
                            <p class="text-gray-600 dark:text-gray-300">{{ $userdata->email ?? '' }}</p>
                        </div>
                        <div class="mt-6 space-y-3">
                            <a href="{{ route('medical.wallet') }}" class="w-full flex items-center justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                <i class="fas fa-wallet mr-2"></i> {{__("Back to Wallet")}}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Content (Form) --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{__("Submit Medical Aid Claim")}}</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">{{__("Please provide details about your medical aid claim. Our admin will review your submission and credit your wallet upon verification.")}}</p>

                    @if(Session::has('message'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ Session::get('message') }}</span>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('user.medical.aid.topup.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="medical_aid_provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__("Medical Aid Provider")}}<span class="text-red-500">*</span></label>
                            <input type="text" name="medical_aid_provider" id="medical_aid_provider" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                        </div>

                        <div class="mb-4">
                            <label for="policy_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__("Policy Number (if applicable)")}}</label>
                            <input type="text" name="policy_number" id="policy_number" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                        </div>

                        <div class="mb-4">
                            <label for="amount_claimed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__("Amount Claimed")}} ({{ $setting->currency ?? '$' }})<span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount_claimed" id="amount_claimed" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                        </div>

                        <div class="mb-4">
                            <label for="proof_document" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__("Upload Proof Document (e.g., claim form, medical report)")}}</label>
                            <input type="file" name="proof_document" id="proof_document" accept=".pdf,image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{__("Max 5MB per document. PDF, JPEG, PNG allowed.")}}</p>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="w-full inline-flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                {{__("Submit Claim")}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('footer_scripts')
{{-- No specific JS needed for this form yet --}}
@endsection