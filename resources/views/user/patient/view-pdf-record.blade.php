@extends('layouts.patient')
@section('title')
    {{ $record->document_name ?? __('PDF Document') }}
@stop
@section('meta-data')
    {{-- Add relevant meta data here if needed, using $setting --}}
    <meta property="og:title" content="{{ $record->document_name ?? __('PDF Document') }}"/>
@stop

@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200 text-center">{{ $record->document_name }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 sm:p-6">
        <div class="mb-6">
             <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                <i class="fas fa-arrow-left mr-2"></i> {{ __('Back') }}
            </a>
        </div>

        {{-- PDF Viewer --}}
        <div class="w-full h-[80vh] sm:h-[calc(100vh-220px)]">
            <iframe src="{{ asset($record->document_path)}}" width="100%" height="100%" class="border-none rounded-md shadow-inner"></iframe>
        </div>
    </div>
@stop
