<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Chiremba Express')) - Patient</title>

    <!-- Favicon -->
    @php
        // Consider moving this logic to a View Composer or Service Provider for cleaner layouts
        $fav = \App\Models\Setting::find(1)->favicon ?? 'favicon.ico';
    @endphp
    <link rel="icon" href="{{ asset('image_web/' . $fav) }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet"> {{-- Ensure Tailwind CSS is compiled --}}
    {{-- You might still need some of the old global styles during transition --}}
    {{-- <link href="{{asset('front_pro/assets/css/font-awesome-all.css')}}" rel="stylesheet"> --}}
    {{-- <link href="{{asset('front_pro/assets/css/flaticon.css')}}" rel="stylesheet"> --}}
    {{-- <link href="{{asset('front_pro/assets/css/style.css?v=2324')}}" rel="stylesheet"> --}}
    {{-- Font Awesome for icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
    {{-- Include jQuery and Bootstrap CSS for modals and other components --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- Font Awesome for icons (if not already included globally) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900"
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
          }
      }"
      x-init="isDarkMode = document.documentElement.classList.contains('dark')">
    <div class="flex h-screen">
        {{-- Mobile Sidebar (Off-canvas) --}}
        @include('layouts.partials.patient-mobile-sidebar')

        {{-- Desktop Sidebar --}}
        @include('user.patient.partials.sidebar') {{-- Pass $userdata from controller to views using this layout --}}

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Header --}}
            @include('layouts.partials.patient-header') {{-- Pass $setting to views using this layout --}}

            {{-- Page Content Wrapper --}}
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 dark:bg-gray-800 no-scrollbar relative">
                @yield('full_width_content_top')
                {{-- The main content area with padding --}}
                {{-- Added z-10 and relative to ensure it stacks correctly above any full-width content --}}
                <div class="px-4 sm:px-6 lg:px-8 py-8 z-10 relative">
                    @yield('content')
                </div>
                @yield('full_width_content_bottom')
            </main>

            {{-- Footer --}}
            @include('layouts.partials.patient-footer')
        </div>
    </div>

    {{-- jQuery and Bootstrap JS (required for Bootstrap modals) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ mix('js/app.js') }}" defer></script> {{-- Alpine.js and other JS --}}
    {{-- Ensure Alpine.js is included if not part of mix('js/app.js') --}}
    @yield('footer_scripts')
</body>
</html>