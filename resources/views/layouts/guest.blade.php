<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Chiremba Express')</title>

    <!-- Favicon -->
    @php
        // This logic is fine here for a simple app, but could be moved to a View Composer for larger apps.
        $setting = \App\Models\Setting::first();
        $fav = $setting->favicon ?? 'favicon.ico';
    @endphp
    <link rel="icon" href="{{ asset('image_web/' . $fav) }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles (Tailwind CSS) -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    @yield('meta-data')

    <script>
        // On page load, this script runs before the page is rendered to prevent FOUC (Flash of Unstyled Content).
        if (localStorage.darkMode === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="font-sans antialiased">
    <div class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        @yield('content')
    </div>

    <!-- Scripts (Alpine.js, etc.) -->
    <script src="{{ mix('js/app.js') }}" defer></script>
    @yield('footer_scripts')
</body>
</html>