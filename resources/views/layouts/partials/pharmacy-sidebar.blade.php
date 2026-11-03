{{-- resources/views/layouts/partials/pharmacy-sidebar.blade.php --}}
{{-- This sidebar is for desktop. Alpine's isDesktopSidebarCollapsed is controlled from the main layout's body tag --}}
{{-- Ensure $pharmacydata is passed to this partial or use a View Composer --}}

<aside
    class="hidden md:flex flex-col bg-white dark:bg-gray-800 border-r rtl:border-r-0 rtl:border-l dark:border-gray-700 shadow-lg sidebar-transition no-scrollbar relative"
    :class="{ 'w-64': !isDesktopSidebarCollapsed, 'w-20': isDesktopSidebarCollapsed }"
>
    {{-- Sidebar Header (Logo) --}}
    <div class="flex items-center justify-center h-[68px] border-b dark:border-gray-700 shrink-0" :class="isDesktopSidebarCollapsed ? 'px-2' : 'px-6'">
        <a href="{{ url('pharmacydashboard') }}" class="text-xl font-bold text-primary-600" x-show="!isDesktopSidebarCollapsed">
            Chiremba Express
        </a>
        <a href="{{ url('pharmacydashboard') }}" class="text-2xl font-bold text-primary-600" x-show="isDesktopSidebarCollapsed" style="display: none;">
            CE
        </a>
    </div>

    {{-- Profile Info --}}
    <div class="flex flex-col items-center text-center p-4 border-b dark:border-gray-700 shrink-0" x-show="!isDesktopSidebarCollapsed">
        <figure class="profile-image w-20 h-20 rounded-full overflow-hidden mb-3 shadow-md">
            @if(isset($pharmacydata) && $pharmacydata->image != "")
                <img src="{{ asset('upload/doctors').'/'.$pharmacydata->image }}" alt="{{ $pharmacydata->name ?? 'Pharmacy Profile' }}" class="object-cover w-full h-full">
            @else
                <img src="{{ asset('upload/doctors/defaultpharmacy.png') }}" alt="Default Profile" class="object-cover w-full h-full">
            @endif
        </figure>
        <h3 class="text-md font-semibold text-tele-dark-blue dark:text-gray-200">{{ isset($pharmacydata->name) ? $pharmacydata->name : "" }}</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 truncate w-full">{{ isset($pharmacydata->email) ? $pharmacydata->email : "" }}</p>
    </div>

     {{-- Collapsed Profile Icon --}}
    <div class="flex flex-col items-center text-center py-4 border-b dark:border-gray-700 shrink-0" x-show="isDesktopSidebarCollapsed" style="display:none;">
        <figure class="profile-image w-10 h-10 rounded-full overflow-hidden shadow-md">
             @if(isset($pharmacydata) && $pharmacydata->image != "")
                <img src="{{ asset('upload/doctors').'/'.$pharmacydata->image }}" alt="{{ $pharmacydata->name ?? 'Pharmacy Profile' }}" class="object-cover w-full h-full">
            @else
                <img src="{{ asset('upload/doctors/defaultpharmacy.png') }}" alt="Default Profile" class="object-cover w-full h-full">
            @endif
        </figure>
    </div>

    {{-- Navigation Links --}}
    <nav class="flex-1 overflow-y-auto py-4 no-scrollbar">
        <ul class="space-y-1" :class="isDesktopSidebarCollapsed ? 'px-2' : 'px-4'">
            @php
                $navLinks = [
                    ['route' => 'pharmacydashboard', 'icon' => 'fas fa-columns', 'label' => __('Dashboard')],
                    ['route' => 'pharmacymedicine', 'icon' => 'fas fa-pills', 'label' => __('Medicine')],
                    ['route' => 'pharmacyreview', 'icon' => 'fas fa-star', 'label' => __('Reviews')],
                    ['route' => 'pharmacyeditprofile', 'icon' => 'fas fa-user', 'label' => __('My Profile')],
                    ['route' => 'pharmacy.pos', 'icon' => 'fas fa-cash-register', 'label' => __('POS Terminal'), 'is_route_name' => true],
                    ['route' => 'pharmacy.wallet', 'icon' => 'fas fa-wallet', 'label' => __('My Wallet'), 'is_route_name' => true],
                    ['route' => 'pharmacy.profit-loss', 'icon' => 'fas fa-chart-line', 'label' => __('Profit & Loss'), 'is_route_name' => true],
                    ['route' => 'pharmacy.my_subscription', 'icon' => 'fas fa-sync-alt', 'label' => __('My Subscription'), 'is_route_name' => true],
                    ['route' => 'pharmacychangepassword', 'icon' => 'fas fa-unlock-alt', 'label' => __('Change Password')],
                    ['route' => 'pharmacy.referrals', 'icon' => 'fas fa-gift', 'label' => __('Referrals'), 'is_route_name' => true],
                    // Add other pharmacy-specific links here
                ];
            @endphp

            @foreach ($navLinks as $link)
            <li>
                <a href="{{ isset($link['is_route_name']) && $link['is_route_name'] ? route($link['route']) : url($link['route']) }}"
                   class="flex items-center p-2 text-sm rounded-md transition-colors duration-150 ease-in-out group
                          {{ Request::is(str_replace(['.', '/'], ['*', '*'], $link['route']).'*') ? 'bg-primary-600 text-white font-semibold shadow-md' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-gray-100' }}"
                   :class="{ 'justify-center': isDesktopSidebarCollapsed }"
                   title="{{ $link['label'] }}"
                >
                    <i class="{{ $link['icon'] }} w-5 h-5" :class="{ 'mr-0': isDesktopSidebarCollapsed, 'mr-3': !isDesktopSidebarCollapsed }"></i>
                    <span x-show="!isDesktopSidebarCollapsed" class="truncate">{{ $link['label'] }}</span>
                </a>
            </li>
            @endforeach

             {{-- Logout Link --}}
            <li>
                <a href="{{ route('pharmacy.logout') }}"
                   class="flex items-center p-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-gray-100 rounded-md transition-colors duration-150 ease-in-out group"
                   :class="{ 'justify-center': isDesktopSidebarCollapsed }"
                   title="{{ __('Logout') }}"
                >
                    <i class="fas fa-sign-out-alt w-5 h-5" :class="{ 'mr-0': isDesktopSidebarCollapsed, 'mr-3': !isDesktopSidebarCollapsed }"></i>
                    <span x-show="!isDesktopSidebarCollapsed" class="truncate">{{ __('Logout') }}</span>
                </a>
            </li>
        </ul>
    </nav>

    <button @click="toggleDesktopSidebar()"
            class="hidden md:block absolute top-1/2 -translate-y-1/2 -right-3 transform p-1 bg-primary-600 text-white rounded-full shadow-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 dark:focus:ring-offset-gray-800 z-10"
            aria-label="Toggle sidebar">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path x-show="!isDesktopSidebarCollapsed" d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <path x-show="isDesktopSidebarCollapsed" d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;"></path>
        </svg>
    </button>
</aside>