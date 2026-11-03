@extends('admin.layout')

@section('title')
{{("News")}} | {{("Admin")}}
@stop

@section('meta-data')
@stop

@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{("News")}}</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ url('admin/dashboard') }}" class="text-primary-500 hover:underline">{{("Dashboard")}}</a>
                    / {{("News")}}
                </div>
            </div>

            <div class="flex justify-center">
                <div class="w-full lg:w-2/3">
                    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                        @if(Session::has('message'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-4 rounded-lg bg-green-100 dark:bg-green-200 p-4 text-sm text-green-700 dark:text-green-800 flex justify-between items-center" role="alert">
                            <span>{{ Session::get('message') }}</span>
                            <button @click="show = false" class="text-green-900 dark:text-green-800 font-bold">&times;</button>
                        </div>
                        @endif

                        <form action="{{ url('admin/sendnews') }}" method="post" class="space-y-4" x-data="{ target: 'doctors' }">
                            {{ csrf_field() }}

                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("News Content") }}</label>
                                <textarea id="content" name="content" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Target Audience") }}</label>
                                <div class="mt-2 space-y-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="target_audience" id="target_doctors" value="doctors" x-model="target" class="form-radio text-primary-600">
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __("Doctors by Category") }}</span>
                                    </label>
                                    <label class="inline-flex items-center ml-4">
                                        <input type="radio" name="target_audience" id="target_pharmacies" value="pharmacies" x-model="target" class="form-radio text-primary-600">
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __("All Pharmacies") }}</span>
                                    </label>
                                    <label class="inline-flex items-center ml-4">
                                        <input type="radio" name="target_audience" id="target_patients" value="patients" x-model="target" class="form-radio text-primary-600">
                                        <span class="ml-2 text-gray-700 dark:text-gray-300">{{ __("All Patients") }}</span>
                                    </label>
                                </div>
                            </div>

                            <div x-show="target === 'doctors'" x-transition>
                                <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Departments") }}</label>
                                <select id="department" name="department[]" class="select2-multiple mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" multiple>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="target === 'doctors' || target === 'patients'" x-transition>
                                <div>
                                    <label for="subscription" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __("Subscription Types") }}</label>
                                    <select id="subscription" name="subscription[]" class="select2-multiple mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" multiple>
                                        @foreach ($doctorSubscriptions as $subscription)
                                            <option value="{{ $subscription->id }}">{{ $subscription->name }} (Provider)</option>
                                        @endforeach
                                        @foreach ($patientSubscriptions as $subscription)
                                            <option value="{{ $subscription->id }}">{{ $subscription->name }} (Patient)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 dark:text-gray-400"><span class="text-red-500">*</span> {{ __("You can send email to news users who have subscribed news on website") }}</p>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{ __('Submit') }}</button>
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
<script src="{{asset('ckeditor/ckeditor.js')}}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        CKEDITOR.replace('content');

        // Initialize Select2 - it's a jQuery plugin, so we use jQuery here.
        // This is safe because app.js (which includes jQuery) is loaded before this script.
        $('.select2-multiple').select2({
            placeholder: "{{ __('Select options') }}",
            allowClear: true
        });
    });
</script>
@stop
