@extends('admin.layout')

@section('title')
    {{ __('Languages_Translation') }}
@stop

@section('meta-data')
    {{-- Consider adding meta descriptions or keywords here if relevant --}}
@stop

@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                {{-- Page Title and Breadcrumb --}}
                <div class="row">
                    <div class="col-12">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xl font-semibold text-gray-800 dark:text-white">{{ __('Languages_Translation') }}</h4>
                            <nav class="hidden sm:block" aria-label="breadcrumb">
                                <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                                    <li><a href="{{ url('admin/dashboard') }}" class="hover:text-primary-500">{{ __('Dashboard') }}</a></li>
                                    <li><i class="uil uil-angle-right-b"></i></li>
                                    <li class="font-medium text-gray-700 dark:text-gray-200">{{ __('Languages_Translation') }}</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                            @php $availableLanguages = config('languages', ['en' => 'English']); @endphp

                            {{-- Language Tabs --}}
                            <div x-data="{ 
                                    openTab: '{{ $lang }}', 
                                    showAddLangModal: false, 
                                    searchTerm: '',                                    
                                    allMessages: {{ json_encode(collect($messages)->map(function($msgs) {
                                        return collect($msgs)->sortKeys()->map(function($value, $key) {
                                            return ['key' => $key, 'value' => (string)$value];
                                        })->values();
                                    })) }},
                                    get currentMessages() {
                                        const messages = this.allMessages[this.openTab] || [];
                                        if (this.searchTerm.trim() === '') {
                                            return messages;
                                        }
                                        const term = this.searchTerm.toLowerCase();
                                        return messages.filter(item => 
                                            item.key.toLowerCase().includes(term) || 
                                            item.value.toLowerCase().includes(term)
                                        );
                                    },
                                    async updateTranslation(details) { try { const response = await fetch(details.url, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': details.token, 'Accept': 'application/json' }, body: JSON.stringify({ key: details.key, value: details.value }) }); if (!response.ok) { throw new Error('Network response was not ok'); } const result = await response.json(); return result.success; } catch (error) { console.error('Update failed:', error); return false; } } }" class="mb-4">
                                <ul class="flex flex-wrap border-b border-gray-200 dark:border-gray-700" id="myTab" role="tablist">
                                    @foreach ($availableLanguages as $langCode => $langName)
                                        <li class="mr-2">
                                            <a @click.prevent="openTab = '{{ $langCode }}'; window.history.pushState({}, '', '{{ url('admin/languages/' . $langCode) }}');"
                                               href="#" :class="{ 'border-primary-500 text-primary-600': openTab === '{{ $langCode }}', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': openTab !== '{{ $langCode }}' }"
                                               class="inline-block py-3 px-4 text-sm font-medium text-center rounded-t-lg border-b-2">
                                                {{ $langName }} ({{ strtoupper($langCode) }})
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                {{-- Action Buttons --}}
                                <div class="mt-4 flex justify-end space-x-2">
                                    {{-- Always add new keys based on English for consistency --}}
                                    <a href="{{ route('languages.create', ['lang' => 'en']) }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="uil uil-plus-circle mr-2"></i> {{ __('Add New Key') }}
                                    </a>
                                    <button @click="showAddLangModal = true"
                                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                        <i class="uil uil-plus mr-2"></i> {{ __('Add Language') }}
                                    </button>
                                </div>

                                {{-- Tab Content --}}
                                <div class="mt-4" id="myTabContent">
                                    @foreach ($availableLanguages as $langCode => $langName)
                                        <div x-show="openTab === '{{ $langCode }}'" x-cloak
                                             class="p-4 bg-gray-50 rounded-lg dark:bg-gray-800/50"
                                             id="{{ $langCode }}_tab_content">

                                            {{-- Include the common table structure --}}
                                            @include('admin.partials._language_table', ['lang' => $langCode, 'messages' => $messages[$langCode] ?? []])

                                        </div>
                                    @endforeach
                                </div>

                                <!-- Add New Language Modal -->
                                <div x-show="showAddLangModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-cloak>
                                   <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
                                      <div class="flex justify-between items-center pb-3">
                                         <h5 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('Add New Language') }}</h5>
                                         <button @click="showAddLangModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl font-bold leading-none">&times;</button>
                                      </div>
                                      <form action="{{ route('languages.store-language') }}" method="post" class="space-y-4">
                                         @csrf
                                         <div>
                                            <label for="lang_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Language Name') }}</label>
                                            <input type="text" name="name" id="lang_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="e.g., German" required>
                                         </div>
                                         <div>
                                            <label for="lang_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Language Code (2 Letters)') }}</label>
                                            <input type="text" name="code" id="lang_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" placeholder="e.g., de" required maxlength="2" minlength="2">
                                         </div>
                                         <div class="flex justify-end pt-2">
                                            @if(Session::get("is_demo")=='0')
                                               <button type="button" onclick="disablebtn()" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{('Submit')}}</button>
                                            @else
                                               <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-800 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150">{{("Add Language")}}</button>
                                            @endif
                                         </div>
                                      </form>
                                   </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    {{-- Any specific scripts or footer content for this page --}}
@stop