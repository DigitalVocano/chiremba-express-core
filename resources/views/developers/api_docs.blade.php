<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chiremba Developer API | Telehealth, E-Pharmacy & Healthcare REST APIs</title>
    <meta name="description" content="Build modern healthcare apps and WhatsApp chatbots with Chiremba Developer APIs. Access certified doctor directories, e-pharmacy networks, and telemedicine consultation workflows. Compatible with Zimflow low-code chatbot builder.">
    <meta name="keywords" content="Chiremba API, Telehealth API, Healthcare API Zimbabwe, Doctors API, Pharmacy API, Zimflow WhatsApp Chatbot, Medical REST API, Low Code WhatsApp AI">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/developers/api') }}">

    <!-- Open Graph / SEO -->
    <meta property="og:title" content="Chiremba Developer Platform | Telehealth & E-Pharmacy APIs">
    <meta property="og:description" content="Integrate verified doctors, online pharmacies, and telemedicine consultations into your web apps or Zimflow WhatsApp bots.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/developers/api') }}">
    <meta property="og:site_name" content="Chiremba Telehealth">

    <!-- Schema.org Structured Data for SEO Indexing -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "TechArticle",
      "headline": "Chiremba Telehealth & Pharmacy REST API Documentation",
      "description": "Developer documentation for Chiremba healthcare APIs including certified doctors directory, pharmacy network, and medical specialties.",
      "author": {
        "@type": "Organization",
        "name": "Chiremba Telehealth"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Chiremba Telehealth",
        "url": "{{ url('/') }}"
      },
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ url('/developers/api') }}"
      }
    }
    </script>

    <!-- Tailwind CSS CDN & FontAwesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, pre { font-family: 'JetBrains Mono', monospace; }
        .gradient-brand { background: linear-gradient(135deg, #0284C7 0%, #0D9488 50%, #10B981 100%); }
        .gradient-zimflow { background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); }
        .glow-card { box-shadow: 0 10px 40px -10px rgba(2, 132, 199, 0.15); }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen antialiased selection:bg-teal-500 selection:text-white">

    <!-- Top Navigation -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ url('/') }}" class="flex items-center space-x-3">
                    @if(isset($setting) && $setting->logo)
                        <img src="{{ asset('image_web/' . $setting->logo) }}" alt="Chiremba Express Logo" class="h-9 w-auto">
                    @else
                        <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center shadow-lg shadow-teal-500/20">
                            <i class="fa-solid fa-notes-medical text-white text-lg"></i>
                        </div>
                    @endif
                    <div>
                        <span class="text-xl font-bold tracking-tight text-white">Chiremba <span class="text-teal-400">Express</span></span>
                        <span class="ml-2 px-2 py-0.5 text-[10px] font-semibold bg-teal-500/10 text-teal-300 rounded-full border border-teal-500/20">Developer Portal</span>
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-4">
                <a href="#endpoints" class="hidden sm:inline-block text-sm font-medium text-slate-300 hover:text-white transition">APIs</a>
                <a href="#zimflow-showcase" class="text-sm font-medium text-emerald-400 hover:text-emerald-300 transition flex items-center gap-1.5">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>Zimflow AI</span>
                </a>
                <a href="#quickstart" class="hidden sm:inline-block text-sm font-medium text-slate-300 hover:text-white transition">Quickstart</a>
                <button onclick="openModal()" class="px-4 py-2 rounded-lg bg-teal-500 hover:bg-teal-400 text-slate-950 font-semibold text-sm transition shadow-lg shadow-teal-500/25 flex items-center space-x-2">
                    <i class="fa-solid fa-key text-xs"></i>
                    <span>Get API Key</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-16 pb-20 lg:pt-24 lg:pb-28">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(13,148,136,0.15),transparent_50%)]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-slate-800 border border-slate-700 mb-6">
                    <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                    <span class="text-xs font-medium text-slate-300">Public Healthcare REST API &middot; v1.0</span>
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-tight">
                    Healthcare APIs for <span class="text-transparent bg-clip-text gradient-brand">Builders & Innovators</span>
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-slate-400 leading-relaxed">
                    Integrate verified doctor directories, licensed pharmacies, real-time availability slots, and telemedicine consultations into your mobile apps, web systems, or WhatsApp AI bots.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <button onclick="openModal()" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-base transition shadow-xl shadow-teal-500/20 flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Generate API Key</span>
                    </button>
                    <a href="#zimflow-showcase" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-400 font-semibold text-base border border-emerald-500/30 transition flex items-center justify-center space-x-2">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        <span>Build WhatsApp Chatbot</span>
                    </a>
                </div>
            </div>

            <!-- Highlights Grid -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl bg-slate-800/50 border border-slate-700/80 glow-card">
                    <div class="w-12 h-12 rounded-xl bg-teal-500/10 border border-teal-500/20 text-teal-400 flex items-center justify-center mb-4 text-xl">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Doctors Directory API</h3>
                    <p class="mt-2 text-sm text-slate-400">Search qualified medical practitioners by specialty, clinic address, city, consultation fees, and real-time availability.</p>
                </div>
                <div class="p-6 rounded-2xl bg-slate-800/50 border border-slate-700/80 glow-card">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center mb-4 text-xl">
                        <i class="fa-solid fa-prescription-bottle-medical"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Pharmacies Network API</h3>
                    <p class="mt-2 text-sm text-slate-400">Find nearby licensed pharmacies, delivery modes (PointLink network & self-delivery), and coordinates.</p>
                </div>
                <div class="p-6 rounded-2xl bg-slate-800/50 border border-slate-700/80 glow-card">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mb-4 text-xl">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Zimflow Low-Code Integration</h3>
                    <p class="mt-2 text-sm text-slate-400">Connect our REST endpoints directly to <a href="https://zimflow.co.zw" target="_blank" class="text-emerald-400 underline font-semibold">Zimflow.co.zw</a> to deploy WhatsApp healthcare chatbots in minutes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Build Cases & Zimflow Showcase -->
    <section id="zimflow-showcase" class="py-16 bg-slate-950/80 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950/40 border border-emerald-500/30 shadow-2xl relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
                
                <div class="max-w-3xl">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold mb-4">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                        <span>Featured Integration &middot; Low-Code Automation</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white leading-tight">
                        Build Telemedicine WhatsApp AI Chatbots in Minutes with <a href="https://zimflow.co.zw" target="_blank" class="text-emerald-400 hover:text-emerald-300 underline decoration-emerald-500 decoration-2">Zimflow</a>
                    </h2>
                    <p class="mt-4 text-base text-slate-300 leading-relaxed">
                        <a href="https://zimflow.co.zw" target="_blank" class="text-emerald-300 font-semibold underline">Zimflow</a> is a visual, low-code WhatsApp chatbot and conversational AI workflow builder powered by visual node flows and generative AI (OpenAI & Gemini). With Zimflow and Chiremba APIs, you can build production-ready digital healthcare assistants without writing complex server code.
                    </p>
                </div>

                <!-- 3 WhatsApp Use Cases Grid -->
                <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-700/60">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-4 text-lg">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <h3 class="text-base font-bold text-white">1. WhatsApp Doctor Booking Bot</h3>
                        <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                            Use Zimflow's HTTP Node to call <code class="text-teal-400">/api/v1/doctors</code>. Patients on WhatsApp can view available doctors by specialty, see consultation fees, and schedule appointments instantly.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-700/60">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center mb-4 text-lg">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <h3 class="text-base font-bold text-white">2. WhatsApp Pharmacy Finder Bot</h3>
                        <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                            Patients share their WhatsApp GPS location. Zimflow queries <code class="text-teal-400">/api/v1/pharmacies</code> and returns nearby pharmacies with home delivery and phone contacts.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-slate-900/80 border border-slate-700/60">
                        <div class="w-10 h-10 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center mb-4 text-lg">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <h3 class="text-base font-bold text-white">3. AI Symptom Triage Assistant</h3>
                        <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                            Zimflow AI Assistant nodes understand natural language in Shona, Ndebele, or English. It queries Chiremba's clinical departments (<code class="text-teal-400">/api/v1/departments</code>) and directs patients to the right medical specialist.
                        </p>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="https://zimflow.co.zw" target="_blank" class="px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-sm transition shadow-lg shadow-emerald-500/20 flex items-center space-x-2">
                        <span>Get Started on Zimflow.co.zw</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    </a>
                    <span class="text-xs text-slate-400">No complex backend required &middot; Ready in minutes with visual flows</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Quickstart & Authentication Section -->
    <section id="quickstart" class="py-16 bg-slate-900 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <h2 class="text-3xl font-extrabold text-white">Authentication & Quickstart</h2>
                <p class="mt-3 text-slate-400">Authenticate all HTTP requests by sending your unique API Key via the <code class="text-teal-400 bg-slate-800 px-2 py-0.5 rounded">X-API-KEY</code> header or Bearer Token.</p>
            </div>

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                <div class="space-y-4">
                    <div class="p-6 rounded-xl bg-slate-950/60 border border-slate-800">
                        <div class="flex items-center space-x-3 text-teal-400 font-bold mb-2">
                            <span class="w-6 h-6 rounded-full bg-teal-500/20 flex items-center justify-center text-xs">1</span>
                            <span>Generate API Key</span>
                        </div>
                        <p class="text-sm text-slate-400">Click <strong>Get API Key</strong> above to register your application and immediately receive your <code class="text-slate-300">ch_live_...</code> key.</p>
                    </div>

                    <div class="p-6 rounded-xl bg-slate-950/60 border border-slate-800">
                        <div class="flex items-center space-x-3 text-teal-400 font-bold mb-2">
                            <span class="w-6 h-6 rounded-full bg-teal-500/20 flex items-center justify-center text-xs">2</span>
                            <span>Include X-API-KEY Header</span>
                        </div>
                        <p class="text-sm text-slate-400">Pass <code class="text-teal-400">X-API-KEY: ch_live_your_key</code> in headers on all calls to <code class="text-slate-300">/api/v1/*</code>.</p>
                    </div>

                    <div class="p-6 rounded-xl bg-slate-950/60 border border-slate-800">
                        <div class="flex items-center space-x-3 text-teal-400 font-bold mb-2">
                            <span class="w-6 h-6 rounded-full bg-teal-500/20 flex items-center justify-center text-xs">3</span>
                            <span>Standard JSON Responses</span>
                        </div>
                        <p class="text-sm text-slate-400">All responses return clean UTF-8 JSON with pagination metadata and standard HTTP error codes.</p>
                    </div>
                </div>

                <!-- Code Example Card -->
                <div class="rounded-2xl bg-slate-950 border border-slate-800 overflow-hidden shadow-2xl">
                    <div class="px-4 py-3 bg-slate-900 border-b border-slate-800 flex items-center justify-between">
                        <div class="flex space-x-2">
                            <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/70"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/70"></div>
                        </div>
                        <span class="text-xs text-slate-400 font-mono">cURL Example</span>
                    </div>
                    <pre class="p-6 text-sm text-teal-300 overflow-x-auto leading-relaxed"><code>curl -X GET "{{ url('/api/v1/doctors') }}?city=Harare" \
  -H "X-API-KEY: ch_live_your_api_key_here" \
  -H "Accept: application/json"</code></pre>
                </div>
            </div>
        </div>
    </section>

    <!-- Endpoints Reference -->
    <section id="endpoints" class="py-16 bg-slate-950/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-white">API Reference (v1)</h2>
            <p class="mt-2 text-slate-400">Explore the complete schema for certified doctors, pharmacies, and clinical departments.</p>

            <div class="mt-10 space-y-8">
                <!-- Endpoint 1: Doctors -->
                <div class="p-6 sm:p-8 rounded-2xl bg-slate-800/40 border border-slate-700/80">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-3 py-1 rounded-md bg-emerald-500/20 text-emerald-400 font-bold text-xs uppercase">GET</span>
                        <code class="text-base sm:text-lg font-bold text-white">/api/v1/doctors</code>
                        <span class="text-xs text-slate-400 ml-auto">Certified Medical Practitioners</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-300">Returns list of verified, active doctors with fees, locations, and specialties.</p>
                    
                    <div class="mt-4 text-sm text-slate-300">
                        <span class="font-semibold text-slate-200">Query Parameters:</span>
                        <ul class="list-disc list-inside mt-1 text-slate-400 space-y-1">
                            <li><code class="text-teal-400">department_id</code> (integer, optional): Filter by medical specialty ID</li>
                            <li><code class="text-teal-400">city</code> (string, optional): Filter by doctor's clinic city</li>
                            <li><code class="text-teal-400">page</code> (integer, optional): Pagination page number (default 1)</li>
                        </ul>
                    </div>

                    <div class="mt-4 rounded-xl bg-slate-950 p-4 border border-slate-800 font-mono text-xs text-slate-300 overflow-x-auto">
                        <span class="text-slate-500 block mb-2">// 200 OK Response Sample</span>
{
  "success": true,
  "data": [
    {
      "id": 12,
      "name": "Dr. Sarah Dube",
      "specialty": "Pediatrics",
      "consultation_fee": 35.00,
      "city": "Harare",
      "address": "Baines Medical Centre",
      "latitude": -17.8216,
      "longitude": 31.0492
    }
  ],
  "pagination": { "total": 48, "current_page": 1, "per_page": 20 }
}
                    </div>
                </div>

                <!-- Endpoint 2: Pharmacies -->
                <div class="p-6 sm:p-8 rounded-2xl bg-slate-800/40 border border-slate-700/80">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-3 py-1 rounded-md bg-emerald-500/20 text-emerald-400 font-bold text-xs uppercase">GET</span>
                        <code class="text-base sm:text-lg font-bold text-white">/api/v1/pharmacies</code>
                        <span class="text-xs text-slate-400 ml-auto">Licensed Pharmacies & Dispensaries</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-300">Returns active pharmacies, fulfillment modes, and delivery logistics details.</p>

                    <div class="mt-4 text-sm text-slate-300">
                        <span class="font-semibold text-slate-200">Query Parameters:</span>
                        <ul class="list-disc list-inside mt-1 text-slate-400 space-y-1">
                            <li><code class="text-teal-400">city</code> (string, optional): Filter by pharmacy city</li>
                            <li><code class="text-teal-400">page</code> (integer, optional): Pagination page number</li>
                        </ul>
                    </div>

                    <div class="mt-4 rounded-xl bg-slate-950 p-4 border border-slate-800 font-mono text-xs text-slate-300 overflow-x-auto">
                        <span class="text-slate-500 block mb-2">// 200 OK Response Sample</span>
{
  "success": true,
  "data": [
    {
      "id": 5,
      "pharmacy_name": "MedHelp Central Pharmacy",
      "phone": "+263771234567",
      "address": "45 Samora Machel Ave, Harare",
      "delivery_mode": "pointlink",
      "latitude": -17.8286,
      "longitude": 31.0534
    }
  ],
  "pagination": { "total": 22, "current_page": 1, "per_page": 20 }
}
                    </div>
                </div>

                <!-- Endpoint 3: Departments -->
                <div class="p-6 sm:p-8 rounded-2xl bg-slate-800/40 border border-slate-700/80">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-3 py-1 rounded-md bg-emerald-500/20 text-emerald-400 font-bold text-xs uppercase">GET</span>
                        <code class="text-base sm:text-lg font-bold text-white">/api/v1/departments</code>
                        <span class="text-xs text-slate-400 ml-auto">Medical Specialties Directory</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-300">Returns medical departments and clinical categories available on Chiremba.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-12 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="text-sm text-slate-400">
                &copy; {{ date('Y') }} Chiremba Telehealth &amp; PointLink Network. All rights reserved.
            </div>
            <div class="flex items-center space-x-6 text-sm text-slate-400">
                <a href="{{ url('/') }}" class="hover:text-white transition">Home</a>
                <a href="https://zimflow.co.zw" target="_blank" class="text-emerald-400 hover:text-emerald-300 transition">Powered by Zimflow</a>
                <a href="{{ url('aboutus') }}" class="hover:text-white transition">About</a>
                <a href="{{ url('Privacy_Policy') }}" class="hover:text-white transition">Privacy</a>
            </div>
        </div>
    </footer>

    <!-- Developer Registration Modal -->
    <div id="keyModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 sm:p-8 relative shadow-2xl">
            <button onclick="closeModal()" class="absolute top-5 right-5 text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>

            @if(!$isLoggedIn)
            <div id="authRequiredSection">
                <div class="text-center mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-teal-500/10 border border-teal-500/20 text-teal-400 flex items-center justify-center mx-auto text-2xl mb-4">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white">Account Authentication Required</h3>
                    <p class="text-sm text-slate-400 mt-2">
                        To protect API reliability and prevent abuse, developer API credentials are restricted to registered accounts. Please sign in or register to get your API keys.
                    </p>
                </div>

                <div class="space-y-3">
                    <a href="{{ url('patientlogin') }}" class="w-full py-3 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-sm transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-user"></i>
                        <span>Sign In as Patient / User</span>
                    </a>
                    <a href="{{ url('profilelogin') }}" class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm transition border border-slate-700 flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-user-doctor text-teal-400"></i>
                        <span>Sign In as Provider (Doctor / Pharmacy)</span>
                    </a>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-800 text-center">
                    <p class="text-xs text-slate-400 mb-3">Don't have an account yet?</p>
                    <div class="flex items-center justify-center gap-4 text-xs font-semibold">
                        <a href="{{ url('patientregister') }}" class="text-teal-400 hover:underline">Register Account</a>
                        <span class="text-slate-600">&bull;</span>
                        <a href="{{ url('doctorregister') }}" class="text-teal-400 hover:underline">Join as Healthcare Provider</a>
                    </div>
                </div>
            </div>
            @else
            <div id="formSection">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Developer API Key</h3>
                        <p class="text-xs text-slate-400">Instant access to Chiremba public API endpoints</p>
                    </div>
                </div>

                <form id="regForm" onsubmit="submitDeveloperForm(event)" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Application Name</label>
                        <input type="text" id="app_name" name="app_name" required placeholder="e.g. Zimflow WhatsApp Bot / Clinic Portal" class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-teal-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Developer / Organization Name</label>
                        <input type="text" id="developer_name" name="developer_name" required placeholder="e.g. Tendai Chikore" class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-teal-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Developer Email</label>
                        <input type="email" id="developer_email" name="developer_email" required placeholder="developer@example.com" class="w-full px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-teal-500 text-sm">
                    </div>

                    <div id="errorBox" class="hidden text-xs text-red-400 bg-red-950/40 p-3 rounded-lg border border-red-900/50"></div>

                    <button type="submit" id="btnSubmit" class="w-full py-3 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-sm transition shadow-lg shadow-teal-500/20 flex items-center justify-center space-x-2">
                        <span>Generate Key Now</span>
                    </button>
                </form>
            </div>
            @endif

            <!-- Credentials Result Display -->
            <div id="resultSection" class="hidden space-y-4">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto text-xl mb-3">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Your Credentials Are Ready!</h3>
                    <p class="text-xs text-slate-400">Save your API key and secret key now. The secret key is only displayed once.</p>
                </div>

                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3 font-mono text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase">API Key (X-API-KEY)</span>
                        <div class="flex items-center justify-between mt-1 text-teal-300">
                            <span id="resApiKey" class="break-all font-semibold"></span>
                            <button onclick="copyToClipboard('resApiKey')" class="ml-2 px-2 py-1 bg-slate-800 rounded text-slate-300 hover:text-white">Copy</button>
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase">Secret Key</span>
                        <div class="flex items-center justify-between mt-1 text-amber-300">
                            <span id="resSecretKey" class="break-all font-semibold"></span>
                            <button onclick="copyToClipboard('resSecretKey')" class="ml-2 px-2 py-1 bg-slate-800 rounded text-slate-300 hover:text-white">Copy</button>
                        </div>
                    </div>
                </div>

                <button onclick="closeModal()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm transition">
                    Done
                </button>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('keyModal').classList.remove('hidden');
            const formSec = document.getElementById('formSection');
            if (formSec) formSec.classList.remove('hidden');
            const resSec = document.getElementById('resultSection');
            if (resSec) resSec.classList.add('hidden');
        }

        function closeModal() {
            document.getElementById('keyModal').classList.add('hidden');
        }

        async function submitDeveloperForm(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            const err = document.getElementById('errorBox');
            err.classList.add('hidden');
            btn.disabled = true;
            btn.innerText = 'Generating...';

            const payload = {
                app_name: document.getElementById('app_name').value,
                developer_name: document.getElementById('developer_name').value,
                developer_email: document.getElementById('developer_email').value,
                _token: document.querySelector('input[name="_token"]').value
            };

            try {
                const response = await fetch("{{ url('/developers/register') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (data.success) {
                    document.getElementById('resApiKey').innerText = data.credentials.api_key;
                    document.getElementById('resSecretKey').innerText = data.credentials.secret_key;
                    document.getElementById('formSection').classList.add('hidden');
                    document.getElementById('resultSection').classList.remove('hidden');
                } else {
                    err.innerText = data.message || 'Registration failed.';
                    err.classList.remove('hidden');
                }
            } catch (errCatch) {
                err.innerText = 'Network error: ' + errCatch.message;
                err.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Generate Key Now';
            }
        }

        function copyToClipboard(id) {
            const text = document.getElementById(id).innerText;
            navigator.clipboard.writeText(text);
            alert('Copied to clipboard!');
        }
    </script>
</body>
</html>
