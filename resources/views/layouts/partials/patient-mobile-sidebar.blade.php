{{-- resources/views/layouts/partials/patient-mobile-sidebar.blade.php --}}
{{-- Ensure $userdata is passed to this partial or use a View Composer --}}

<!-- Backdrop -->
<div x-show="isMobileSidebarOpen"
     @click="isMobileSidebarOpen = false"
     class="fixed inset-0 z-20 bg-black bg-opacity-50 transition-opacity md:hidden"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display: none;">
</div>

<!-- Off-canvas menu -->
<aside
    x-show="isMobileSidebarOpen"
    @click.away="isMobileSidebarOpen = false"
    class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col bg-white dark:bg-gray-800 shadow-lg transform transition-transform duration-300 ease-in-out md:hidden no-scrollbar"
    :class="{ 'translate-x-0': isMobileSidebarOpen, '-translate-x-full': !isMobileSidebarOpen }"
    style="display: none;"
>
    {{-- Sidebar Header (Logo/Close) --}}
    <div class="flex items-center justify-between h-[68px] px-6 border-b dark:border-gray-700">
        <a href="{{ url('userdashboard') }}" class="text-xl font-bold text-primary-600">
            Chiremba Express
        </a>
        <button @click="isMobileSidebarOpen = false" class="text-gray-500 dark:text-gray-400 focus:outline-none">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 18L18 6M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>
    </div>

    {{-- Profile Info --}}
     <div class="flex flex-col items-center text-center p-4 border-b dark:border-gray-700">
        <figure class="profile-image w-20 h-20 rounded-full overflow-hidden mb-3 shadow-md">
            @if(isset($userdata) && $userdata->profile_pic != "")
                <img src="{{ asset('upload/profile').'/'.$userdata->profile_pic }}" alt="{{ $userdata->name ?? 'User' }}" class="object-cover w-full h-full">
            @else
                <img src="{{ asset('upload/profile/profile.png') }}" alt="Default Profile" class="object-cover w-full h-full">
            @endif
        </figure>
        <h3 class="text-md font-semibold text-tele-dark-blue dark:text-gray-200">{{ isset($userdata->name) ? $userdata->name : "" }}</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 truncate w-full">{{ isset($userdata->email) ? $userdata->email : "" }}</p>
    </div>

    {{-- Navigation Links --}}
    <nav class="flex-1 overflow-y-auto py-4 no-scrollbar">
        <ul class="space-y-1 px-4">
             @php
                // Add main website navigation links at the top of the mobile sidebar
                // The Request::is check is adjusted for root-level URLs

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
                    ['route' => 'patient.buy_credits', 'icon' => 'fas fa-coins', 'label' => __('Buy AI Credits'), 'is_route_name' => true],
                    ['route' => 'patient.ai_assistant', 'icon' => 'fas fa-robot', 'label' => __('AI Assistant'), 'is_route_name' => true],
                    ['route' => 'patient.my_subscription', 'icon' => 'fas fa-sync-alt', 'label' => __('My Subscription'), 'is_route_name' => true],
                    ['route' => 'health.tracking', 'icon' => 'fas fa-heartbeat', 'label' => __('Health Tracking'), 'is_route_name' => true],
                    ['route' => 'user.referrals', 'icon' => 'fas fa-gift', 'label' => __('Referrals'), 'is_route_name' => true],
                    // ['route' => 'show.symptom.checker', 'icon' => 'fas fa-stethoscope', 'label' => __('Symptom Checker'), 'is_route_name' => true],

                    // Main Website Navigation
                    ['route' => '/', 'icon' => 'fas fa-home', 'label' => __('Home')], // Home route is already correct
                    ['route' => 'searchdoctor', 'icon' => 'fas fa-user-md', 'label' => __('Search Doctors')],
                    ['route' => 'searchpharmacy', 'icon' => 'fas fa-prescription-bottle-alt', 'label' => __('Search Pharmacy')],
                ];

                // Reorder to place main website links at the top
                $mainWebsiteLinks = array_splice($navLinks, count($navLinks) - 3, 3);
                $navLinks = array_merge($mainWebsiteLinks, $navLinks);
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
                   title="{{ $link['label'] }}"
                >
                    <i class="{{ $link['icon'] }} w-5 h-5 mr-3"></i>
                    <span class="truncate">{{ $link['label'] }}</span>
                </a>
            </li>
            @endforeach
            <li>
                <a href="{{ route('user.logout') }}" class="flex items-center p-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-gray-100 rounded-md">
                    <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                    <span>{{ __('Logout') }}</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>