@extends('admin.layout')

@section('title')
{{ __("Role Management") }} | {{ __("Admin") }}
@stop

@section('content')
<div class="main-content">
    <div class="page-content p-4 md:p-6">
        <div class="container-fluid mx-auto space-y-6">

            <!-- Hero Header Card -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0A2B68] via-[#0F3A8A] to-[#1E40AF] p-6 md:p-8 text-white shadow-xl">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-wider text-blue-200 mb-2">
                            <a href="{{ url('admin/dashboard') }}" class="hover:underline flex items-center gap-1">
                                <i class="uil uil-home"></i> {{ __("Dashboard") }}
                            </a>
                            <span>/</span>
                            <span>{{ __("Roles & Permissions") }}</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                            <i class="uil uil-shield-check text-lime-400 text-3xl"></i>
                            {{ __("Role & Permission Management") }}
                        </h1>
                        <p class="text-blue-100 text-sm mt-1">
                            {{ __("Manage system roles and configure detailed access control permissions.") }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl border border-white/20">
                        <div class="p-2.5 bg-lime-500/20 text-lime-400 rounded-lg">
                            <i class="uil uil-key-skeleton text-2xl"></i>
                        </div>
                        <div>
                            <span class="block text-2xl font-bold leading-none text-white">{{ count($roles) }}</span>
                            <span class="text-xs text-blue-200 font-medium">{{ __("Active System Roles") }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl border border-gray-100 dark:border-gray-700 p-5 md:p-6" x-data="{ searchTerm: '' }">
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800 p-4 text-sm text-emerald-800 dark:text-emerald-300 flex justify-between items-center" role="alert">
                    <div class="flex items-center gap-2">
                        <i class="uil uil-check-circle text-lg text-emerald-600"></i>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-900 font-bold text-xl">&times;</button>
                </div>
                @endif

                <!-- Search & Filters -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                    <div class="relative w-full sm:w-80">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <i class="uil uil-search"></i>
                        </span>
                        <input type="text" x-model="searchTerm" placeholder="{{ __('Search roles...') }}" class="w-full pl-9 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none transition">
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Role Name") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Status") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Actions") }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($roles as $role)
                            <tr x-show="!searchTerm || '{{ strtolower($role->name) }}'.includes(searchTerm.toLowerCase())" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-[#0A2B68] dark:text-blue-300 flex items-center justify-center font-bold text-sm">
                                            <i class="uil uil-user-lock text-lg"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-semibold text-gray-900 dark:text-white capitalize">{{ str_replace('-', ' ', $role->name) }}</span>
                                            <span class="text-xs text-gray-400">ID: #{{ $role->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        {{ __("Active System Role") }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.roles.edit', $role->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-primary-50 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300 hover:bg-primary-100 font-medium text-xs transition">
                                        <i class="uil uil-edit"></i>
                                        {{ __("Edit Permissions") }}
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Stack View -->
                <div class="md:hidden space-y-4" id="mobileCardsContainer">
                    @foreach($roles as $role)
                    <div x-show="!searchTerm || '{{ strtolower($role->name) }}'.includes(searchTerm.toLowerCase())" class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-[#0A2B68] dark:text-blue-300 flex items-center justify-center font-bold text-sm">
                                    <i class="uil uil-user-lock text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white capitalize">{{ str_replace('-', ' ', $role->name) }}</h4>
                                    <span class="text-xs text-gray-400">ID: #{{ $role->id }}</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                {{ __("Active") }}
                            </span>
                        </div>
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="w-full text-center py-2 rounded-lg bg-primary-600 text-white font-medium text-xs shadow hover:bg-primary-700 transition">
                                <i class="uil uil-edit mr-1"></i> {{ __("Edit Permissions") }}
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>

        </div>
    </div>
</div>
@endsection