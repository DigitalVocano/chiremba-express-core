<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8"/>
    @php
        $setting = app\models\Setting::find(1);
        $title = $setting->title ?? 'Chiremba Express';
        $favicon = (isset($setting->favicon) && $setting->favicon) ? asset('image_web/'.$setting->favicon) : asset('upload/profile/profile.png');
        $logo = (isset($setting->logo) && $setting->logo) ? asset('image_web/'.$setting->logo) : asset('upload/profile/profile.png');
    @endphp

    <title>{{ __("2FA Verification") }} | {{ $title }} Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
    <!-- FontAwesome & Google Fonts -->
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

    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-lime-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md my-auto">
        <!-- Header / Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center p-4 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 shadow-2xl lime-glow mb-4">
                <div class="flex items-center space-x-2 text-white font-extrabold text-2xl tracking-wide">
                    <i class="fa-solid fa-shield-halved text-lime-400 text-3xl"></i>
                    <span>Two-Factor<span class="text-lime-400"> Authentication</span></span>
                </div>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight drop-shadow-md">
                Admin Security Check
            </h1>
            <p class="mt-2 text-xs text-blue-200/80">
                Enter the 6-digit code from your Authenticator App
            </p>
        </div>

        <!-- Glassmorphism Card -->
        <div class="glass-card shadow-2xl rounded-3xl p-8 sm:p-10">
            @if(Session::has('message'))
                <div class="rounded-2xl bg-red-50 border border-red-200 p-4 mb-6">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                        <div class="text-sm font-medium text-red-800">
                            {{ Session::get('message') }}
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.2fa.post_verify') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="code" class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-3 text-center">
                        <i class="fa-solid fa-key text-blue-900 mr-1.5"></i>{{ __("6-Digit Verification Code") }}
                    </label>
                    <div class="relative rounded-2xl shadow-sm">
                        <input type="text" name="code" id="code" required maxlength="6" autofocus
                               class="block w-full text-center tracking-[0.5em] text-2xl font-mono py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 placeholder-gray-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-transparent transition duration-200"
                               placeholder="000000" autocomplete="off">
                    </div>
                    <p class="mt-2 text-xs text-gray-500 text-center">
                        Open Google Authenticator, Authy, or your 2FA app to view your 6-digit code.
                    </p>
                </div>

                <div>
                    <button type="submit" 
                            class="w-full py-4 px-6 rounded-2xl text-white font-bold text-base shadow-xl bg-gradient-to-r from-blue-900 via-indigo-900 to-lime-600 hover:from-blue-950 hover:to-lime-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500 transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-lock-open text-sm"></i>
                        <span>{{ __('Verify & Continue') }}</span>
                    </button>
                </div>

                <div class="text-center pt-2">
                    <a href="{{ route('admin.login') }}" class="text-xs font-semibold text-blue-900 hover:underline">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>

        <div class="text-center mt-6 text-xs text-blue-200/60">
            &copy; {{ date('Y') }} Chiremba Express. Protected by TOTP 2FA.
        </div>
    </div>

</body>
</html>
