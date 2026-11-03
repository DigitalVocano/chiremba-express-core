@extends('admin.layout')

@section('title')
    {{ __('Languages_Translation') }} - {{ __('Edit') }}
@stop

@section('meta-data')
    {{-- Add relevant meta tags here if needed for SEO or social sharing --}}
@stop

@section('content')
    <div class="min-h-screen bg-gray-100">
        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- Page Title and Breadcrumb --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-2xl font-bold text-gray-800">{{ __('Languages_Translation') }}</h4>
                        <nav class="hidden sm:block" aria-label="breadcrumb">
                            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                                <li><a href="{{ url('admin/dashboard') }}" class="hover:text-gray-700">{{ __('Dashboard') }}</a></li>
                                <li><span class="text-gray-400">/</span></li>
                                <li><a href="{{ url('admin/languages/'. $lang) }}" class="hover:text-gray-700">{{ __('Languages_Translation') }}</a></li>
                                <li><span class="text-gray-400">/</span></li>
                                <li class="font-medium text-gray-700">{{ __('Edit') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="flex justify-center">
                    <div class="w-full lg:w-2/3 xl:w-1/2"> {{-- Adjust width as needed --}}
                        <div class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
                            <h1 class="text-3xl font-semibold mb-6 text-gray-900">{{ __('Edit') }} ({{ strtoupper($lang) }})</h1>

                            <form action="{{ route('languages.update', ['lang' => $lang, 'key' => $key]) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <label for="key" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Key') }}</label>
                                    <input type="text" name="key" id="key"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-600 cursor-not-allowed focus:outline-none"
                                           value="{{ $key }}" required readonly>
                                </div>

                                <div class="mb-6">
                                    <label for="value" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Value') }}</label>
                                    <input type="text" name="value" id="value"
                                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ $value }}" required>
                                    @error('value')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end">
                                    @if (Session::get('is_demo') == '0')
                                        {{-- In a real application, consider a disabled state for the button or a tooltip explaining why it's disabled in demo mode. --}}
                                        <button type="button" onclick="alert('{{ __('This feature is disabled in demo mode.') }}')"
                                                class="px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-400 cursor-not-allowed opacity-75">
                                            {{ __('Submit') }}
                                        </button>
                                    @else
                                        <button type="submit"
                                                class="px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            {{ __('Submit') }}
                                        </button>
                                    @endif
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
    {{-- Any specific JavaScript for this page, like validation or other dynamic behavior, would go here. --}}
    @if (Session::get('is_demo') == '0')
        <script>
            // This function is still useful if you have an actual JS 'disablebtn()' function.
            // Otherwise, for pure demo mode display, the button styling is enough.
            function disablebtn() {
                // You might want a more user-friendly message or action here,
                // like a modal or toast notification.
                alert('{{ __('This action is disabled in demo mode.') }}');
            }
        </script>
    @endif
@stop