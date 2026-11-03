@extends('layouts.patient')
@section('title')
    {{ $record->document_name ?? __('Medical Image') }}
@stop
@section('meta-data')
    {{-- Add relevant meta data here if needed, using $setting --}}
    <meta property="og:title" content="{{ $record->document_name ?? __('Medical Image') }}"/>
@stop

@section('content')
    {{-- Page Title --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200 text-center">{{ $record->document_name ?? __('Medical Gallery') }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 sm:p-6">
        <div class="mb-6">
             <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                <i class="fas fa-arrow-left mr-2"></i> {{ __('Back') }}
            </a>
        </div>

        @if (in_array(pathinfo($record->document_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
            <div class="flex justify-center">
                <a href="{{ asset($record->document_path) }}" target="_blank" class="block rounded-lg overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-200 max-w-3xl">
                    <img src="{{ asset($record->document_path) }}" alt="{{ $record->document_name ?? 'Medical Image' }}" class="object-contain w-full h-auto max-h-[80vh]">
                </a>
            </div>
        @else
            <p class="text-center text-gray-600 dark:text-gray-400">{{ __('No image found or unsupported format.') }}</p>
        @endif
    </div>
@stop
