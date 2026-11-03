{{-- resources/views/layouts/partials/pharmacy-header.blade.php --}}
{{-- Ensure $pharmacydata is passed to this partial or use a View Composer --}}
<header class="flex items-center justify-between px-4 sm:px-6 py-3 bg-white dark:bg-gray-800 border-b-2 border-primary-600 shadow-sm">
    <div class="flex items-center">
        {{-- Mobile menu button --}}
        <button @click="isMobileSidebarOpen = true" class="text-gray-500 dark:text-gray-400 focus:outline-none md:hidden">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 6H20M4 12H20M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>

        {{-- Desktop sidebar toggle button --}}
        <button @click="toggleDesktopSidebar()" class="text-gray-500 dark:text-gray-400 focus:outline-none hidden md:block ml-3">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 6H20M4 12H14M4 18H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>

        {{-- Optional: Page Title or Breadcrumbs can go here --}}
        {{-- <h1 class="text-xl font-semibold text-gray-700 dark:text-gray-200 ml-4">@yield('title')</h1> --}}
    </div>

    <div class="flex items-center space-x-3 sm:space-x-4">
        {{-- Main Website Navigation (Desktop Only) - Styled as buttons --}}
        <div class="hidden md:flex items-center space-x-2">
            <a href="{{ url('/') }}" class="bg-primary-500 text-white px-3 py-1 rounded-md hover:bg-primary-600 text-sm">{{ __('Home') }}</a>
            <a href="{{ url('searchdoctor') }}" class="bg-primary-500 text-white px-3 py-1 rounded-md hover:bg-primary-600 text-sm">{{ __('Search Doctors') }}</a>
            <a href="{{ url('searchpharmacy') }}" class="bg-primary-500 text-white px-3 py-1 rounded-md hover:bg-primary-600 text-sm">{{ __('Search Pharmacy') }}</a>
        </div>

        {{-- Dark Mode Toggle --}}
        <button @click="toggleDarkMode()" class="text-gray-500 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-600 focus:outline-none">
            <template x-if="isDarkMode">
                <i class="fas fa-sun w-5 h-5 sm:w-6 sm:h-6"></i>
            </template>
            <template x-if="!isDarkMode">
                 <i class="fas fa-moon w-5 h-5 sm:w-6 sm:h-6"></i>
            </template>
        </button>

        {{-- User Dropdown --}}
        <div x-data="{ dropdownOpen: false }" class="relative">
            <button @click="dropdownOpen = !dropdownOpen" class="relative block w-8 h-8 sm:w-10 sm:h-10 overflow-hidden rounded-full shadow-lg focus:outline-none border-2 border-transparent hover:border-primary-600">
                 @if(isset($pharmacydata) && $pharmacydata->image != "")
                    <img src="{{ asset('upload/doctors').'/'.$pharmacydata->image }}" alt="{{ $pharmacydata->name ?? 'User' }}" class="object-cover w-full h-full">
                @else
                    <img src="{{ asset('upload/doctors/defaultpharmacy.png') }}" alt="Default Profile" class="object-cover w-full h-full">
                @endif
            </button>

            <div x-show="dropdownOpen" @click="dropdownOpen = false" class="fixed inset-0 z-10 w-full h-full" style="display: none;"></div>

            <div x-show="dropdownOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 transform scale-90"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-90"
                 class="absolute right-0 z-20 w-48 mt-2 overflow-hidden bg-white dark:bg-gray-700 rounded-md shadow-xl" style="display: none;">
                <a href="{{ url('pharmacyeditprofile') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-primary-600 hover:text-white dark:hover:bg-primary-700">{{ __('My Profile') }}</a>
                <a href="{{ url('pharmacychangepassword') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-primary-600 hover:text-white dark:hover:bg-primary-700">{{ __('Change Password') }}</a>
                <a href="{{ route('pharmacy.logout') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-primary-600 hover:text-white dark:hover:bg-primary-700">{{ __('Logout') }}</a>
            </div>
        </div>
    </div>
</header>