@extends('layouts.patient')
@section('title')
{{__("Asset-Backed Top-up")}} | {{__("KumbaCare")}}
@stop
@section('meta-data')
@stop


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
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{__("Submit Asset for Top-up")}}</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">{{__("Please provide details about your asset. Our admin will review your submission and credit your wallet upon verification.")}}</p>

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

                    <form action="{{ route('user.asset.topup.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="asset_type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__("Asset Type")}}<span class="text-red-500">*</span></label>
                            <select name="asset_type_id" id="asset_type_id" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                                <option value="">{{__("Select an Asset Type")}}</option>
                                @foreach($assetTypes as $assetType)
                                    <option value="{{ $assetType->id }}" data-min="{{ $assetType->estimated_min_value }}" data-max="{{ $assetType->estimated_max_value }}">{{ $assetType->name }}</option>
                                @endforeach
                            </select>
                            <p id="estimated-value-range" class="text-sm text-gray-500 dark:text-gray-400 mt-1"></p>
                        </div>

                        <div class="mb-4">
                            <label for="serial_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__("Serial Number (if applicable)")}}</label>
                            <input type="text" name="serial_number" id="serial_number" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__("Description of Asset")}}<span class="text-red-500">*</span></label>
                            <textarea name="description" id="description" rows="4" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200"></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="images" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{__("Upload Images (e.g., proof of ownership, asset condition)")}}</label>
                            <input type="file" name="images[]" id="images" multiple accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-gray-700 dark:file:text-gray-200 dark:hover:file:bg-gray-600">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{__("Max 2MB per image. JPEG, PNG, GIF, SVG allowed.")}}</p>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="w-full inline-flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                {{__("Submit Request")}}
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
<script>
    document.getElementById('asset_type_id').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var minVal = selectedOption.dataset.min;
        var maxVal = selectedOption.dataset.max;
        var rangeDisplay = document.getElementById('estimated-value-range');
        var currencySymbol = "{{ $setting->currency ?? '$' }}"; // Assuming $setting is available in the Blade view

        if (minVal && maxVal) {
            rangeDisplay.textContent = "{{__('Estimated Value Range')}}: " + currencySymbol + parseFloat(minVal).toFixed(2) + " - " + currencySymbol + parseFloat(maxVal).toFixed(2);
        } else {
            rangeDisplay.textContent = "";
        }
    });
</script>
@endsection