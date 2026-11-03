<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('code', '404') - @yield('title', 'Error') | Chiremba Express</title>
    
    @php
        $setting = null;
        try {
            $setting = \App\Models\Setting::find(1);
        } catch (\Throwable $e) {}
        $logo = ($setting && !empty($setting->logo)) ? asset('image_web/'.$setting->logo) : asset('image_web/299186.png');
        $fav = ($setting && !empty($setting->favicon)) ? asset('image_web/'.$setting->favicon) : asset('image_web/767239.ico');
    @endphp

    <link rel="icon" href="{{ $fav }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', Inter, sans-serif;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(13, 148, 136, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(20, 184, 166, 0.06) 0px, transparent 50%);
            background-attachment: fixed;
        }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between text-slate-800 antialiased selection:bg-teal-600 selection:text-white bg-slate-50">

    <!-- Top Navigation Header -->
    <header class="w-full border-b border-slate-200 bg-white/90 backdrop-blur-md sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group transition">
                <img src="{{ $logo }}" alt="Chiremba Express" class="h-10 sm:h-12 w-auto object-contain transition group-hover:opacity-90" onerror="this.onerror=null; this.src='{{ asset('image_web/299186.png') }}';">
                <span class="sr-only">Chiremba Express</span>
            </a>

            <div class="flex items-center gap-2 sm:gap-4">
                <a href="{{ url('/searchdoctor') }}" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition">
                    <i class="fas fa-user-md text-teal-600"></i>
                    <span>Find Doctors</span>
                </a>
                <a href="{{ url('/developers/api') }}" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200 transition">
                    <i class="fas fa-code text-teal-600"></i>
                    <span>Developers</span>
                </a>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-teal-600 hover:bg-teal-700 text-white transition shadow-sm">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Stage -->
    <main class="flex-grow flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full text-center">
            
            <!-- Status Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 shadow-sm mb-6">
                <span class="w-2 h-2 rounded-full @yield('badge-dot-color', 'bg-rose-500') animate-pulse"></span>
                <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider">@yield('status-badge', 'Error Encountered')</span>
            </div>

            <!-- Error Code -->
            <div class="relative my-2 select-none">
                <h1 class="text-7xl sm:text-9xl font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-b from-teal-700 via-teal-600 to-slate-400">
                    @yield('code', '404')
                </h1>
            </div>

            <!-- Heading Title -->
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-4 mb-3">
                @yield('title', 'Page Not Found')
            </h2>

            <!-- Descriptive Message -->
            <p class="text-sm sm:text-base text-slate-600 max-w-lg mx-auto leading-relaxed mb-8">
                @yield('message', 'We could not find the page or resource you requested. It may have been moved, renamed, or is temporarily unavailable.')
            </p>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center justify-center gap-3 mb-10">
                <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm transition shadow-sm">
                    <i class="fas fa-home"></i>
                    <span>Back to Home</span>
                </a>

                <button onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ url('/') }}'" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-semibold text-sm border border-slate-300 transition shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                    <span>Go Back</span>
                </button>

                <a href="{{ url('/developers/api') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-800 font-semibold text-sm border border-teal-200 transition">
                    <i class="fas fa-code"></i>
                    <span>Developer Portal</span>
                </a>
            </div>

            <!-- Helpful Resource Links Card -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200 text-left shadow-sm">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fas fa-compass text-teal-600"></i>
                    <span>Popular Telehealth Destinations</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <a href="{{ url('/searchdoctor') }}" class="p-3.5 rounded-xl bg-slate-50 hover:bg-teal-50/60 border border-slate-200 hover:border-teal-300 transition flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center group-hover:scale-105 transition">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900 group-hover:text-teal-700 transition text-xs">Find Doctors</div>
                            <div class="text-[11px] text-slate-500">Book consultations</div>
                        </div>
                    </a>

                    <a href="{{ url('/searchpharmacy') }}" class="p-3.5 rounded-xl bg-slate-50 hover:bg-teal-50/60 border border-slate-200 hover:border-teal-300 transition flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center group-hover:scale-105 transition">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900 group-hover:text-emerald-700 transition text-xs">Pharmacies</div>
                            <div class="text-[11px] text-slate-500">Order medicines</div>
                        </div>
                    </a>

                    <a href="{{ url('/medical-aid') }}" class="p-3.5 rounded-xl bg-slate-50 hover:bg-teal-50/60 border border-slate-200 hover:border-teal-300 transition flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center group-hover:scale-105 transition">
                            <i class="fas fa-shield-heart"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900 group-hover:text-blue-700 transition text-xs">Medical Aid</div>
                            <div class="text-[11px] text-slate-500">Accepted schemes</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Need Help Support Footer -->
            <p class="mt-8 text-xs text-slate-500">
                Experiencing technical difficulties? 
                <a href="{{ url('/contactus') }}" class="text-teal-600 hover:underline font-medium">Contact Chiremba Support</a> 
                or reach out to our team.
            </p>
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-200 py-6 text-center text-xs text-slate-500 bg-white">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>&copy; {{ date('Y') }} Chiremba Express. All rights reserved. Healthcare & Telemedicine Platform.</div>
            <div class="flex items-center gap-4 text-slate-500">
                <a href="{{ url('/Privacy_Policy') }}" class="hover:text-slate-900 transition">Privacy Policy</a>
                <span>&bull;</span>
                <a href="{{ url('/terms') }}" class="hover:text-slate-900 transition">Terms of Service</a>
                <span>&bull;</span>
                <a href="{{ url('/developers/api') }}" class="hover:text-teal-600 transition">Developers</a>
            </div>
        </div>
    </footer>

</body>
</html>
