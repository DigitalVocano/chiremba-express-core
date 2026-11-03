<!DOCTYPE html>
<html lang="en" x-data="mainLayout()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chiremba Express | Account Deletion Policy</title>

    {{-- Meta Data --}}
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ url()->current() }}"/>
    <meta property="og:title" content="Account Deletion Policy | Chiremba Express"/>
    <meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
    <meta property="og:image:width" content="250px"/>
    <meta property="og:image:height" content="250px"/>
    <meta property="og:site_name" content="{{ config('app.name') }}"/>
    <meta property="og:description" content="Understand Chiremba Express's policy and procedures for user account and data deletion."/>
    <meta property="og:keyword" content="account deletion, data privacy, Chiremba Express, telemedicine, user data, policy, remove account"/>
    <link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">

    {{-- Tailwind CSS (ensure this points to your compiled app.css) --}}
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    {{-- Google Fonts - Make sure these are linked in your main layout if not here --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome (v6 for better compatibility and newer icons) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Alpine.js (defer is crucial for performance) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Dark Mode Script (keep this in head for FOUC prevention) --}}
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    {{-- Custom styles for fonts in Tailwind - add these to your tailwind.config.js under fontFamily extend --}}
    <style>
        h1, h2, h3, h4, h5, h6 { font-family: 'Oswald', sans-serif; }
        body { font-family: 'Open Sans', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen">

    {{-- Main Content Wrapper --}}
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

        <!-- Header Section -->
        <header class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-xl shadow-lg mb-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-primary-600 mb-2">
                <b>Chiremba Express</b>
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-300 mt-2">
                {{__('Your Trusted Telemedicine Partner')}}
            </p>
        </header>

        <!-- Policy Content Section -->
        <main class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-xl shadow-lg">
            <div class="text-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white leading-tight">
                    {{__('User Data Deletion Information and Policy')}}
                </h2>
            </div>
            <hr class="border-t border-gray-200 dark:border-gray-700 my-8">

            {{-- Dynamic content from the database --}}
            <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed text-lg">
                {{--
                    The `prose` class (from @tailwindcss/typography plugin) will beautifully style
                    the HTML content coming from $data->data_deletion.
                    If you don't have the typography plugin, these styles will not apply
                    and you might need to add custom classes to your rendered HTML elements.
                --}}
                {!! $data->data_deletion !!}
            </div>

        </main>

    </div> {{-- END max-w-4xl container --}}

</body>
</html>