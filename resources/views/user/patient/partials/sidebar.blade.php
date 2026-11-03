{{-- resources/views/user/patient/partials/sidebar.blade.php --}}
{{-- This sidebar is for desktop. Alpine's isDesktopSidebarCollapsed is controlled from the main layout's body tag --}}
{{-- Ensure $userdata is passed to this partial or use a View Composer --}}

<aside
    class="hidden md:flex flex-col bg-white dark:bg-gray-800 border-r rtl:border-r-0 rtl:border-l dark:border-gray-700 shadow-lg sidebar-transition no-scrollbar relative"
    :class="{ 'w-64': !isDesktopSidebarCollapsed, 'w-20': isDesktopSidebarCollapsed }"
>
    {{-- Sidebar Header (Logo) --}}
    <div class="flex items-center justify-center h-[68px] border-b dark:border-gray-700 shrink-0" :class="isDesktopSidebarCollapsed ? 'px-2' : 'px-6'">
        <a href="{{ url('userdashboard') }}" class="text-xl font-bold text-primary-600" x-show="!isDesktopSidebarCollapsed">
            KumbaCare
        </a>
        <a href="{{ url('userdashboard') }}" class="text-2xl font-bold text-primary-600" x-show="isDesktopSidebarCollapsed" style="display: none;">
            KC
        </a>
    </div>

    {{-- Profile Info --}}
    <div class="flex flex-col items-center text-center p-4 border-b dark:border-gray-700 shrink-0" x-show="!isDesktopSidebarCollapsed">
        <figure class="profile-image w-20 h-20 rounded-full overflow-hidden mb-3 shadow-md">
            @if(isset($userdata) && $userdata->profile_pic != "")
                <img src="{{ asset('upload/profile').'/'.$userdata->profile_pic }}" alt="{{ $userdata->name ?? 'User Profile' }}" class="object-cover w-full h-full">
            @else
                <img src="{{ asset('upload/profile/profile.png') }}" alt="Default Profile" class="object-cover w-full h-full">
            @endif
        </figure>
        <h3 class="text-md font-semibold text-tele-dark-blue dark:text-gray-200">{{ isset($userdata->name) ? $userdata->name : "" }}</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 truncate w-full">{{ isset($userdata->email) ? $userdata->email : "" }}</p>
    </div>

     {{-- Collapsed Profile Icon --}}
    <div class="flex flex-col items-center text-center py-4 border-b dark:border-gray-700 shrink-0" x-show="isDesktopSidebarCollapsed" style="display:none;">
        <figure class="profile-image w-10 h-10 rounded-full overflow-hidden shadow-md">
             @if(isset($userdata) && $userdata->profile_pic != "")
                <img src="{{ asset('upload/profile').'/'.$userdata->profile_pic }}" alt="{{ $userdata->name ?? 'User Profile' }}" class="object-cover w-full h-full">
            @else
                <img src="{{ asset('upload/profile/profile.png') }}" alt="Default Profile" class="object-cover w-full h-full">
            @endif
        </figure>
    </div>

    {{-- Navigation Links --}}
    <nav class="flex-1 overflow-y-auto py-4 no-scrollbar">
        <ul class="space-y-1" :class="isDesktopSidebarCollapsed ? 'px-2' : 'px-4'">
            @php
                $navLinks = [
                    ['route' => 'userdashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => __('Dashboard')],
                    ['route' => 'usermedicineorder', 'icon' => 'fas fa-pills', 'label' => __('Medicine Order')],
                    ['route' => 'favouriteuser', 'icon' => 'fas fa-heart', 'label' => __('Favourite Doctors')],
                    // ['route' => 'viewschedule', 'icon' => 'fas fa-clock', 'label' => __('Schedule Timing')],
                    ['route' => 'userreview', 'icon' => 'fas fa-star', 'label' => __('Review')],
                    ['route' => 'usereditprofile', 'icon' => 'fas fa-user-edit', 'label' => __('My Profile')],
                    ['route' => 'changepassword', 'icon' => 'fas fa-key', 'label' => __('Change Password')],
                    ['route' => 'medical.records.wallet', 'icon' => 'fas fa-file-medical', 'label' => __('Medical Records'), 'is_route_name' => true], // New link for medical records
                    ['route' => 'medical.wallet', 'icon' => 'fas fa-wallet', 'label' => __('Medical Wallet'), 'is_route_name' => true],
                    ['route' => 'patient.my_subscription', 'icon' => 'fas fa-sync-alt', 'label' => __('My Subscription'), 'is_route_name' => true],
                    ['route' => 'patient.buy_credits', 'icon' => 'fas fa-coins', 'label' => __('Buy AI Credits'), 'is_route_name' => true],
                    ['route' => 'patient.ai_assistant', 'icon' => 'fas fa-robot', 'label' => __('AI Assistant'), 'is_route_name' => true],
                    ['route' => 'health.tracking', 'icon' => 'fas fa-heartbeat', 'label' => __('Health Tracking'), 'is_route_name' => true],
                    ['route' => 'femtech.hub', 'icon' => 'fas fa-baby-carriage', 'label' => __('Maternal & SRHR Hub'), 'is_route_name' => true],
                    ['route' => 'chw.portal', 'icon' => 'fas fa-user-nurse', 'label' => __('CHW Proxy Mode'), 'is_route_name' => true],
                    ['route' => 'user.referrals', 'icon' => 'fas fa-gift', 'label' => __('Referrals'), 'is_route_name' => true],
                    // ['route' => 'show.symptom.checker', 'icon' => 'fas fa-stethoscope', 'label' => __('Symptom Checker'), 'is_route_name' => true],
                    ['route' => 'user.logout', 'icon' => 'fas fa-sign-out-alt', 'label' => __('Logout'), 'is_route_name' => true],
                ];
            @endphp

           @foreach ($navLinks as $link)
                @php
                    $isActive = false;
                    if (isset($link['is_route_name']) && $link['is_route_name']) {
                        // For named routes, use routeIs() for exact matching. Add wildcard for parent routes.
                        $isActive = request()->routeIs($link['route'] . '*');
                    } else {
                        // For URL paths, use Request::is().
                        $isActive = ($link['route'] === '/') ? Request::is('/') : Request::is(ltrim($link['route'], '/') . '*');
                    }
                @endphp
                <li>
                    <a href="{{ isset($link['is_route_name']) && $link['is_route_name'] ? route($link['route']) : url($link['route']) }}"
                       class="flex items-center p-2 text-sm rounded-md transition-colors duration-150 ease-in-out group
                              {{ $isActive ? 'bg-primary-600 text-white font-semibold shadow-md' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-gray-100' }}"
                       :class="{ 'justify-center': isDesktopSidebarCollapsed }"
                       title="{{ $link['label'] }}"
                    >
                        <i class="{{ $link['icon'] }} w-5 h-5" :class="{ 'mr-0': isDesktopSidebarCollapsed, 'mr-3': !isDesktopSidebarCollapsed }"></i>
                        <span x-show="!isDesktopSidebarCollapsed" class="truncate">{{ $link['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    {{-- Desktop sidebar toggle button (positioned absolutely within the sidebar) --}}
    {{-- This button is controlled by the `toggleDesktopSidebar` method in the main layout's body x-data --}}
    <button @click="toggleDesktopSidebar()"
            class="hidden md:block absolute top-1/2 -translate-y-1/2 -right-3 transform p-1 bg-primary-600 text-white rounded-full shadow-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 dark:focus:ring-offset-gray-800 z-10"
            aria-label="Toggle sidebar"
    >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path x-show="!isDesktopSidebarCollapsed" d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <path x-show="isDesktopSidebarCollapsed" d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;"></path>
        </svg>
    </button>
</aside>
