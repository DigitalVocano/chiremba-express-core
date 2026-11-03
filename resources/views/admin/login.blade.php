<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8"/>
    @php
        $setting = app\models\Setting::find(1);
        $title = $setting->title ?? 'Chiremba Express';
        $favicon = (isset($setting->favicon) && $setting->favicon) ? asset('image_web/'.$setting->favicon) : asset('upload/profile/profile.png');
        $logoFile = (isset($setting->logo) && $setting->logo) ? $setting->logo : '';
        $logoUrl = $logoFile ? asset('image_web/'.$logoFile) : '';
    @endphp

    <title>{{ __("Log In") }} | {{ $title }} Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta content='{{ __("Chiremba Express Admin Portal") }}' name="description"/>
    <link rel="shortcut icon" href="{{ $favicon }}"/>

    <!-- Tailwind CSS CDN Failsafe -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandDark: '#0A2B68',
                        brandGreen: '#10B981',
                        brandLime: '#84CC16'
                    }
                }
            }
        }
    </script>
    <!-- Compiled CSS & FontAwesome -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            background: radial-gradient(circle at 10% 20%, #061838 0%, #0A2B68 50%, #031027 100%);
            font-family: 'Poppins', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .lime-glow {
            box-shadow: 0 0 25px rgba(132, 204, 22, 0.35);
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 sm:p-6 lg:p-8">

    <!-- Background Glow -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-lime-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md my-auto">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center p-4 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 shadow-2xl lime-glow mb-4">
                @if($logoUrl)
                    <img class="h-14 w-auto object-contain max-h-16" src="{{ $logoUrl }}" alt="Logo" onerror="this.style.display='none'; document.getElementById('fallback-logo').style.display='flex';">
                    <div id="fallback-logo" class="hidden items-center space-x-2 text-white font-extrabold text-2xl tracking-wide">
                        <i class="fa-solid fa-heart-pulse text-lime-400 text-3xl"></i>
                        <span>Chiremba<span class="text-lime-400">Express</span></span>
                    </div>
                @else
                    <div class="flex items-center space-x-2 text-white font-extrabold text-2xl tracking-wide">
                        <i class="fa-solid fa-heart-pulse text-lime-400 text-3xl"></i>
                        <span>Chiremba<span class="text-lime-400">Express</span></span>
                    </div>
                @endif
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight drop-shadow-md">
                Admin Portal
            </h1>
            <p class="mt-2 text-sm text-blue-200/80">
                Sign in with your administrator credentials
            </p>
        </div>

        <!-- Glass Card -->
        <div class="glass-card shadow-2xl rounded-3xl p-8 sm:p-10 border border-white/40">
            @if(Session::has('message'))
                <div class="rounded-2xl bg-red-50 border border-red-200 p-4 mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                            <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                        </div>
                        <div class="text-sm font-medium text-red-800">
                            {{ Session::get('message') }}
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ url('admin/postlogin') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">
                        <i class="fa-regular fa-envelope text-blue-900 mr-1.5"></i>{{ __("Email Address") }}
                    </label>
                    <div class="relative rounded-2xl shadow-sm">
                        <input type="email" name="email" id="email" required 
                               class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-transparent transition duration-200 text-sm font-medium"
                               placeholder="admin@chirembaexpress.co.zw"
                               value="{{ isset($_COOKIE['email']) ? $_COOKIE['email'] : '' }}">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                    </div>
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">
                        <i class="fa-solid fa-lock text-blue-900 mr-1.5"></i>{{ __("Password") }}
                    </label>
                    <div class="relative rounded-2xl shadow-sm">
                        <input type="password" name="password" id="password" required 
                               class="block w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-transparent transition duration-200 text-sm font-medium"
                               placeholder="••••••••••••">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i id="passwordToggleIcon" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & 2FA Info -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-2.5 cursor-pointer">
                            <input id="remember_me" name="remember_me" type="checkbox" 
                                   class="w-4 h-4 text-lime-600 border-gray-300 rounded focus:ring-lime-500 transition duration-150" 
                                   value="1" {{ (isset($_COOKIE['remember_me']) && $_COOKIE['remember_me'] == 1) ? 'checked' : '' }}>
                            <span class="text-xs font-semibold text-gray-700">{{ __('Keep me logged in on this device') }}</span>
                        </label>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-tight">
                        <i class="fa-solid fa-circle-info text-blue-800 mr-1"></i>If unchecked, you will be logged out automatically when closing your browser.
                    </p>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="w-full py-4 px-6 rounded-2xl text-white font-bold text-base shadow-xl bg-gradient-to-r from-blue-900 via-indigo-900 to-lime-600 hover:from-blue-950 hover:to-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center space-x-2">
                        <span>{{ __('Sign In to Admin Dashboard') }}</span>
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-xs text-blue-200/60">
            &copy; {{ date('Y') }} Chiremba Express. Secure Admin Portal.
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>