@extends('admin.layout')

@section('title')
{{ __("Edit Permissions") }} - {{ ucfirst(str_replace('-', ' ', $role->name)) }} | {{ __("Admin") }}
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
                            <a href="{{ route('admin.roles.index') }}" class="hover:underline">{{ __("Roles") }}</a>
                            <span>/</span>
                            <span>{{ __("Edit Permissions") }}</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                            <i class="uil uil-keyhole-circle text-lime-400 text-3xl"></i>
                            {{ __("Edit Permissions for") }}: <span class="text-lime-300 capitalize">{{ str_replace('-', ' ', $role->name) }}</span>
                        </h1>
                        <p class="text-blue-100 text-sm mt-1">
                            {{ __("Configure module level access rights and administrative privileges for this role.") }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl border border-gray-100 dark:border-gray-700 p-5 md:p-8">
                <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-8">
                        @foreach($permissions as $group => $permissionList)
                        <div class="bg-gray-50/50 dark:bg-gray-700/30 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-600 pb-3 mb-4">
                                <i class="uil uil-folder text-primary-500 text-xl"></i>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                    {{ str_replace('-', ' ', $group) }}
                                </h4>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 ml-auto">
                                    {{ count($permissionList) }} {{ __("Permissions") }}
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                @foreach($permissionList as $permission)
                                <label class="flex items-center space-x-3 p-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-primary-400 transition cursor-pointer shadow-sm">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                           class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-gray-900 dark:border-gray-600"
                                           {{ $role->hasPermissionTo($permission) ? 'checked' : '' }}>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 capitalize">
                                        {{ str_replace('-', ' ', $permission->name) }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end space-x-3 pt-6 mt-8 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('admin.roles.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold text-xs uppercase tracking-wider hover:bg-gray-100 transition">
                            {{ __("Cancel") }}
                        </a>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider shadow-md hover:shadow-lg transition flex items-center gap-2">
                            <i class="uil uil-check-circle text-base"></i>
                            {{ __("Save Permissions") }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection