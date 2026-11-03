<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Chiremba Express')) - Pharmacy</title>

    <!-- Favicon -->
    @php
        // Consider moving this logic to a View Composer or Service Provider for cleaner layouts
        // For now, assuming $setting is available or fallback
        $fav = $setting->favicon ?? (\App\Models\Setting::find(1)->favicon ?? 'favicon.ico');
    @endphp
    <link rel="icon" href="{{ asset('image_web/' . $fav) }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet"> {{-- Ensure Tailwind CSS is compiled --}}
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    {{-- DataTables CSS --}}
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">

    @yield('meta-data')
    @yield('header_styles')

    <script>
        // On page load, this script runs before the page is rendered to prevent FOUC (Flash of Unstyled Content).
        // It checks for a saved preference in localStorage, or the system's color scheme preference.
        if (localStorage.darkMode === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        /* Basic transition for sidebar width */
        .sidebar-transition {
            transition: width 0.3s ease-in-out;
        }
        /* Hide scrollbar for webkit-based browsers */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-700"
      x-data="{
          isMobileSidebarOpen: false,
          isDesktopSidebarCollapsed: localStorage.getItem('desktopSidebarCollapsed') === 'true',
          isDarkMode: localStorage.getItem('darkMode') === 'true',
          toggleDesktopSidebar() {
              this.isDesktopSidebarCollapsed = !this.isDesktopSidebarCollapsed;
              localStorage.setItem('desktopSidebarCollapsed', this.isDesktopSidebarCollapsed);
          },
          toggleDarkMode() {
            this.isDarkMode = !this.isDarkMode;
            // This now directly controls the class on the <html> tag and saves the preference.
            if (this.isDarkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('darkMode', this.isDarkMode);
          }, 
          showTopUpModal: false, 
          showWithdrawModal: false
      }"
      {{-- The :class on body is no longer needed as  handled on <html>.
           x-init syncs Alpine's state with the DOM state set by the script in <head>. --}}
      x-init="isDarkMode = document.documentElement.classList.contains('dark')">
    <div class="flex h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.partials.pharmacy-mobile-sidebar')
        @include('layouts.partials.pharmacy-sidebar')
        
        <div class="flex-1 flex flex-col overflow-hidden">
            @include('layouts.partials.pharmacy-header')
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-800 no-scrollbar">
                <div class="px-4 sm:px-6 lg:px-8 py-8 w-full"> {{-- Pass $setting to views using this layout --}}
                    @yield('content')
                </div>
            </main>
            @include('layouts.partials.pharmacy-footer')
        </div>
    </div>

    {{-- Load jQuery and DataTables before other scripts to ensure availability --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script src="{{ mix('js/app.js') }}" defer></script> {{-- Alpine.js and other JS --}}
    @yield('footer_scripts')
</body>
</html>
