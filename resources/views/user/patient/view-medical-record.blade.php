@extends('layouts.patient')
@section('title')
    {{ $record->document_name ?? __('Medical Record') }}
@stop
@section('meta-data')
    {{-- Add relevant meta data here if needed, using $setting --}}
    <meta property="og:title" content="{{ $record->document_name ?? __('Medical Record') }}"/>
@stop

@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200 text-center">{{ $record->document_name }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 sm:p-8 text-center">
        <div class="mb-6">
             <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                <i class="fas fa-arrow-left mr-2"></i> {{ __('Back') }}
            </a>
        </div>

        <div class="my-8">
            <i class="fas fa-file-alt text-6xl text-gray-400 dark:text-gray-500 mb-4"></i>
            <p class="text-lg text-gray-700 dark:text-gray-300 mb-6">{{ __('This file type is not supported for direct viewing in the browser.') }}</p>
        </div>


        <a href="{{ asset($record->document_path) }}" download
           class="inline-flex items-center px-6 py-3 bg-primary-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 dark:focus:ring-offset-gray-800">
            <i class="fas fa-download mr-2"></i> {{ __('Download Document') }}
        </a>
    </div>
@stop
