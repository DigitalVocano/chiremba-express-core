@extends('admin.layout')
@section('title')
Add Asset Type | Admin
@stop
@section('meta-data')
@stop
@section('content')
<div class="main-content">
    <div class="page-content p-6">
        <div class="container-fluid mx-auto">
            <!-- Page Title -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Add Asset Type</h1>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ route('admin.wallet') }}" class="text-primary-500 hover:underline">Wallet Management</a>
                    / <a href="{{ route('admin.asset_types.index') }}" class="text-primary-500 hover:underline">Asset Types</a>
                    / Add New
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
                        <form action="{{ route('admin.asset_types.store') }}" method="POST" class="space-y-6">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name<span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required value="{{ old('name') }}">
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <textarea name="description" id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" rows="3">{{ old('description') }}</textarea>
                            </div>
                            <div>
                                <label for="estimated_min_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Minimum Value ({{ $setting->currency ?? '$' }})<span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" min="0" name="estimated_min_value" id="estimated_min_value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required value="{{ old('estimated_min_value') }}">
                            </div>
                            <div>
                                <label for="estimated_max_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Maximum Value ({{ $setting->currency ?? '$' }})<span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" min="0" name="estimated_max_value" id="estimated_max_value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" required value="{{ old('estimated_max_value') }}">
                            </div>
                            <div class="flex justify-end space-x-2 pt-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">Save</button>
                                <a href="{{ route('admin.asset_types.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">Cancel</a>
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
