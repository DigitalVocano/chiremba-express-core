@extends('admin.layout')

@section('title')
{{ __("Admin Users") }} | {{ __("Admin") }}
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
                            <span>{{ __("Admin Users") }}</span>
                        </div>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                            <i class="uil uil-user-check text-lime-400 text-3xl"></i>
                            {{ __("Administrator Accounts") }}
                        </h1>
                        <p class="text-blue-100 text-sm mt-1">
                            {{ __("Manage administrative staff accounts and assign specific security role privileges.") }}
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl border border-white/20">
                            <div class="p-2.5 bg-lime-500/20 text-lime-400 rounded-lg">
                                <i class="uil uil-users-alt text-2xl"></i>
                            </div>
                            <div>
                                <span class="block text-2xl font-bold leading-none text-white">{{ count($users) }}</span>
                                <span class="text-xs text-blue-200 font-medium">{{ __("Total Admins") }}</span>
                            </div>
                        </div>
                        @can('create-admins')
                        <a href="{{ route('admin.admins.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-lime-500 hover:bg-lime-600 text-gray-900 font-bold text-xs uppercase tracking-wider shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5">
                            <i class="uil uil-plus text-lg"></i>
                            {{ __("Create Admin") }}
                        </a>
                        @endcan
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

                <!-- Filter Bar -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
                    <div class="relative w-full sm:w-80">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <i class="uil uil-search"></i>
                        </span>
                        <input type="text" x-model="searchTerm" placeholder="{{ __('Search by name or email...') }}" class="w-full pl-9 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none transition">
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Administrator") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Email Address") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Assigned Roles") }}</th>
                                <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __("Actions") }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($users as $user)
                            <tr x-show="!searchTerm || '{{ strtolower($user->name) }}'.includes(searchTerm.toLowerCase()) || '{{ strtolower($user->email) }}'.includes(searchTerm.toLowerCase())" class="hover:bg-gray-50/80 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-[#0A2B68] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $user->name }}</span>
                                            <span class="text-xs text-gray-400">ID: #{{ $user->id }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php $userRoles = $user->getRoleNames(); @endphp
                                    @if(count($userRoles) > 0)
                                        @foreach($userRoles as $roleName)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800 capitalize mr-1">
                                            <i class="uil uil-shield mr-1"></i>
                                            {{ str_replace('-', ' ', $roleName) }}
                                        </span>
                                        @endforeach
                                    @else
                                        <span class="text-xs text-gray-400 italic">{{ __("No specific role") }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" x-data="{ confirmDelete: false }">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('edit-admins')
                                            <a href="{{ route('admin.admins.edit', $user->id) }}" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-primary-50 hover:text-primary-600 transition" title="{{ __('Edit Admin') }}">
                                                <i class="uil uil-edit text-base"></i>
                                            </a>
                                        @endcan
                                        @can('delete-admins')
                                            @if(auth()->id() !== $user->id)
                                                <button @click.prevent="confirmDelete = true" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-red-600 hover:bg-red-50 transition" title="{{ __('Delete Admin') }}">
                                                    <i class="uil uil-trash-alt text-base"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </div>

                                    <!-- Delete Confirmation Modal -->
                                    <div x-show="confirmDelete" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;" x-cloak>
                                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div @click="confirmDelete = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
                                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                                            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100 dark:border-gray-700 p-6 space-y-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                                                        <i class="uil uil-exclamation-triangle text-2xl"></i>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __("Delete Administrator") }}</h3>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                            {{ __("Are you sure you want to delete") }} <strong>{{ $user->name }}</strong>? {{ __("This action cannot be undone.") }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                                    <button @click="confirmDelete = false" type="button" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold hover:bg-gray-50 transition">
                                                        {{ __("Cancel") }}
                                                    </button>
                                                    <form action="{{ route('admin.admins.destroy', $user->id) }}" method="POST" class="inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-xs hover:bg-red-700 shadow-md transition">
                                                            {{ __("Confirm Delete") }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ __("No administrator users found.") }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards Stack -->
                <div class="md:hidden space-y-4" id="mobileCardsContainer">
                    @forelse($users as $user)
                    <div x-show="!searchTerm || '{{ strtolower($user->name) }}'.includes(searchTerm.toLowerCase()) || '{{ strtolower($user->email) }}'.includes(searchTerm.toLowerCase())" class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm space-y-3" x-data="{ confirmDelete: false }">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#0A2B68] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $user->name }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-300">
                            <span class="font-semibold text-gray-400">{{ __("Role") }}:</span>
                            <span class="capitalize font-medium">{{ $user->getRoleNames()->implode(', ') ?: __('No Role') }}</span>
                        </div>
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                            @can('edit-admins')
                            <a href="{{ route('admin.admins.edit', $user->id) }}" class="flex-1 text-center py-2 rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 font-medium text-xs">
                                <i class="uil uil-edit mr-1"></i> {{ __("Edit") }}
                            </a>
                            @endcan
                            @can('delete-admins')
                            @if(auth()->id() !== $user->id)
                            <button @click="confirmDelete = true" class="flex-1 text-center py-2 rounded-lg bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400 font-medium text-xs">
                                <i class="uil uil-trash-alt mr-1"></i> {{ __("Delete") }}
                            </button>
                            @endif
                            @endcan
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-sm text-gray-500">
                        {{ __("No administrator users found.") }}
                    </div>
                    @endforelse
                </div>

            </div>

        </div>
    </div>
</div>
@endsection