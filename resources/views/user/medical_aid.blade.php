@extends('user.layout')
@section('title')
{{ __('Accepted Medical Aid Societies & Health Insurance | Chiremba Express') }}
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url('medical-aid') }}"/>
<meta property="og:title" content="Accepted Medical Aid Societies in Zimbabwe | Chiremba Express Telehealth"/>
<meta property="og:image" content="{{ asset('image_web/').'/'.$setting->favicon }}"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="Book verified doctors and buy prescription medicine using CIMAS, PSMAS, First Mutual Health, Generation Health, Alliance Health, and other Zimbabwean medical aid schemes on Chiremba Express."/>
<meta name="description" content="Explore all approved medical aid societies accepted on Chiremba Express Zimbabwe. Book doctor consultations and order pharmacy medicines online with instant medical aid claims."/>
<meta name="keywords" content="Medical Aid Zimbabwe, Buy medicine with CIMAS, Book doctor with PSMAS, First Mutual Health pharmacy, Generation Health doctor, Alliance Health Harare, Telemedicine medical aid Zimbabwe, Medical aid co-payment, Medical shortfall Zimbabwe, Online pharmacy medical aid"/>
<link rel="canonical" href="{{ url('medical-aid') }}"/>

<!-- Schema.org JSON-LD Markup for Rich SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "MedicalWebPage",
      "@id": "{{ url('medical-aid') }}#webpage",
      "url": "{{ url('medical-aid') }}",
      "name": "Accepted Medical Aid Schemes in Zimbabwe | Chiremba Express",
      "description": "Directory of approved Zimbabwean medical aid societies for telemedicine consultations and e-pharmacy orders.",
      "about": {
        "@type": "MedicalOrganization",
        "name": "Chiremba Express Telehealth",
        "url": "{{ url('/') }}",
        "areaServed": "Zimbabwe"
      }
    },
    {
      "@type": "FAQPage",
      "@id": "{{ url('medical-aid') }}#faq",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "Can I use CIMAS or PSMAS for online doctor appointments?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Yes, Chiremba Express doctors accept major Zimbabwean medical aids including CIMAS, PSMAS, and First Mutual Health for online video and chat consultations."
          }
        },
        {
          "@type": "Question",
          "name": "How do I buy prescription medicine with medical aid on Chiremba Express?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Upload your valid doctor prescription, select your registered medical aid society (such as CIMAS or First Mutual Health) during checkout, and our partner pharmacies will process the claim directly."
          }
        },
        {
          "@type": "Question",
          "name": "What happens if there is a shortfall or co-payment?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Any shortfall not covered by your medical aid benefit limit can be settled seamlessly via EcoCash, Paynow, or Medical Wallet."
          }
        }
      ]
    }
  ]
}
</script>
@stop

@section('content')

<!-- Hero Banner -->
<section class="relative bg-gradient-to-r from-teal-800 via-teal-700 to-emerald-800 text-white py-16 lg:py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <div class="container mx-auto px-4 relative z-10 text-center max-w-4xl">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-900/60 border border-teal-500/30 text-teal-200 text-xs font-semibold mb-6 shadow-sm">
            <i class="fas fa-shield-heart text-teal-300"></i>
            <span>Verified Healthcare Coverage &bull; Zimbabwe</span>
        </div>
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight mb-6">
            Accepted <span class="text-teal-200">Medical Aid Societies</span>
        </h1>
        <p class="text-base sm:text-lg text-teal-100/90 leading-relaxed max-w-2xl mx-auto mb-8">
            Access certified doctors and order prescription medicine online. Settle consultations and pharmaceutical orders directly using your Zimbabwe medical aid plan.
        </p>

        <!-- Value Badges -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-left max-w-2xl mx-auto">
            <div class="bg-teal-900/40 border border-teal-600/40 rounded-xl p-3 flex items-center gap-3 backdrop-blur-sm">
                <div class="w-8 h-8 rounded-lg bg-teal-500/20 flex items-center justify-center text-teal-200"><i class="fas fa-check-circle"></i></div>
                <div class="text-xs font-semibold">Direct Society Claiming</div>
            </div>
            <div class="bg-teal-900/40 border border-teal-600/40 rounded-xl p-3 flex items-center gap-3 backdrop-blur-sm">
                <div class="w-8 h-8 rounded-lg bg-teal-500/20 flex items-center justify-center text-teal-200"><i class="fas fa-user-md"></i></div>
                <div class="text-xs font-semibold">Telemedicine Consults</div>
            </div>
            <div class="col-span-2 sm:col-span-1 bg-teal-900/40 border border-teal-600/40 rounded-xl p-3 flex items-center gap-3 backdrop-blur-sm">
                <div class="w-8 h-8 rounded-lg bg-teal-500/20 flex items-center justify-center text-teal-200"><i class="fas fa-pills"></i></div>
                <div class="text-xs font-semibold">E-Pharmacy Delivery</div>
            </div>
        </div>
    </div>
</section>

<!-- Main Directory Stage with Alpine Filter -->
<section class="py-14 bg-slate-50 dark:bg-gray-900" x-data="{
    searchQuery: '',
    items: {{ json_encode($societies->isNotEmpty() ? $societies->map(function($s) {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'code' => $s->code,
            'description' => $s->description ?? 'Accepted across Chiremba registered doctors and partner pharmacies in Zimbabwe.',
            'doctors_count' => $s->doctors_count ?? 15,
            'pharmacies_count' => $s->pharmacies_count ?? 12,
            'logo' => $s->logo ? asset('upload/medical_aid/' . $s->logo) : null
        ];
    }) : $curatedSocieties) }},
    get filteredItems() {
        if (!this.searchQuery.trim()) return this.items;
        const q = this.searchQuery.toLowerCase();
        return this.items.filter(item => 
            item.name.toLowerCase().includes(q) || 
            (item.code && item.code.toLowerCase().includes(q)) ||
            (item.description && item.description.toLowerCase().includes(q))
        );
    }
}">
    <div class="container mx-auto px-4 max-w-7xl">
        
        <!-- Search & Filter Controls -->
        <div class="max-w-2xl mx-auto -mt-20 relative z-20 mb-12">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-slate-200 dark:border-gray-700 p-3 sm:p-4 flex items-center gap-3">
                <div class="text-teal-600 dark:text-teal-400 pl-2 text-lg">
                    <i class="fas fa-search"></i>
                </div>
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    placeholder="Search medical aid (e.g. CIMAS, PSMAS, First Mutual, Alliance)..." 
                    class="w-full bg-transparent border-0 text-slate-800 dark:text-gray-100 placeholder-slate-400 dark:placeholder-gray-400 text-sm sm:text-base focus:ring-0 focus:outline-none"
                >
                <button 
                    x-show="searchQuery.length > 0" 
                    @click="searchQuery = ''" 
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 px-2 text-sm"
                >
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
        </div>

        <!-- Section Title -->
        <div class="flex flex-col sm:flex-row items-baseline justify-between mb-8 pb-4 border-b border-slate-200 dark:border-gray-800">
            <div>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Supported Medical Aid Schemes</h2>
                <p class="text-sm text-slate-500 dark:text-gray-400 mt-1">Book doctor appointments or order medicine under your scheme</p>
            </div>
            <div class="mt-2 sm:mt-0 text-xs font-semibold text-teal-700 dark:text-teal-400">
                <span x-text="filteredItems.length"></span> Societies Available
            </div>
        </div>

        <!-- Grid of Medical Aid Schemes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="(society, index) in filteredItems" :key="index">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 p-6 flex flex-col justify-between shadow-sm hover:shadow-md hover:border-teal-400 dark:hover:border-teal-500 transition-all duration-200 group">
                    <div>
                        <!-- Header with Code & Icon -->
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-teal-50 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 flex items-center justify-center font-bold text-lg border border-teal-100 dark:border-teal-800 flex-shrink-0 group-hover:scale-105 transition-transform">
                                <template x-if="society.logo">
                                    <img :src="society.logo" :alt="society.name" class="w-10 h-10 object-contain rounded-lg">
                                </template>
                                <template x-if="!society.logo">
                                    <i class="fas fa-shield-alt"></i>
                                </template>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-100 dark:bg-teal-900/60 text-teal-800 dark:text-teal-200 uppercase tracking-wide" x-text="society.code"></span>
                        </div>

                        <!-- Society Name -->
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2 group-hover:text-teal-700 dark:group-hover:text-teal-400 transition-colors" x-text="society.name"></h3>

                        <!-- Description & SEO Context -->
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-gray-300 leading-relaxed mb-4" x-text="society.description"></p>

                        <!-- Coverage Stats -->
                        <div class="flex items-center gap-4 text-xs font-medium text-slate-500 dark:text-gray-400 mb-6 pt-3 border-t border-slate-100 dark:border-gray-700">
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-user-md text-teal-600"></i>
                                <span x-text="society.doctors_count + ' Doctors'"></span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i class="fas fa-prescription-bottle-alt text-emerald-600"></i>
                                <span x-text="society.pharmacies_count + ' Pharmacies'"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Actions (SEO Targeted Keywords) -->
                    <div class="space-y-2 pt-2">
                        <a 
                            :href="'{{ url('searchdoctor') }}?medical_aid=' + (society.code || society.name)" 
                            class="w-full py-2.5 px-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs flex items-center justify-center gap-2 transition shadow-sm"
                        >
                            <i class="fas fa-video text-[10px]"></i>
                            <span>Book Doctor with <strong x-text="society.code"></strong></span>
                        </a>
                        <a 
                            :href="'{{ url('searchpharmacy') }}?medical_aid=' + (society.code || society.name)" 
                            class="w-full py-2.5 px-3 rounded-xl bg-slate-100 dark:bg-gray-700 hover:bg-slate-200 dark:hover:bg-gray-600 text-slate-800 dark:text-gray-200 font-semibold text-xs flex items-center justify-center gap-2 transition border border-slate-200 dark:border-gray-600"
                        >
                            <i class="fas fa-pills text-teal-600 text-[10px]"></i>
                            <span>Buy Medicine with <strong x-text="society.code"></strong></span>
                        </a>
                    </div>
                </div>
            </template>
        </div>

        <!-- No Results Empty State -->
        <div x-show="filteredItems.length === 0" class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-slate-200 dark:border-gray-700 my-8">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-gray-700 text-slate-400 flex items-center justify-center mx-auto text-2xl mb-4">
                <i class="fas fa-search"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-gray-100 mb-1">No Medical Aid Society Found</h3>
            <p class="text-sm text-slate-500 dark:text-gray-400 max-w-md mx-auto mb-4">We could not find any scheme matching your search query. Please try searching by society code (e.g. CIMAS, PSMAS, FMH).</p>
            <button @click="searchQuery = ''" class="px-4 py-2 rounded-xl bg-teal-600 text-white text-xs font-semibold hover:bg-teal-700 transition">
                Reset Search
            </button>
        </div>

    </div>
</section>

<!-- How It Works Section -->
<section class="py-16 bg-white dark:bg-gray-800 border-t border-slate-200 dark:border-gray-700">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">Step by Step Guide</span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 dark:text-white mt-2">How to Use Medical Aid on Chiremba Express</h2>
            <p class="text-sm sm:text-base text-slate-600 dark:text-gray-300 mt-3">Simple 3-step digital healthcare process from consultation to medication delivery.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 rounded-2xl bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 relative">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white font-bold flex items-center justify-center mb-4 text-base">1</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Select Doctor or Pharmacy</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-gray-300">Choose your preferred doctor specialty or upload your doctor’s prescription to our licensed pharmacy network.</p>
            </div>
            <div class="p-6 rounded-2xl bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 relative">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white font-bold flex items-center justify-center mb-4 text-base">2</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Provide Membership Details</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-gray-300">Enter your medical aid society name, member number, and suffix. Our system checks provider acceptance instantly.</p>
            </div>
            <div class="p-6 rounded-2xl bg-slate-50 dark:bg-gray-700/50 border border-slate-200 dark:border-gray-600 relative">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white font-bold flex items-center justify-center mb-4 text-base">3</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Instant Claim & Delivery</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-gray-300">The consultation or medicine is claimed directly. Any shortfall or co-payment can be settled seamlessly via EcoCash or Medical Wallet.</p>
            </div>
        </div>
    </div>
</section>

<!-- Frequently Asked Questions Accordion -->
<section class="py-16 bg-slate-50 dark:bg-gray-900 border-t border-slate-200 dark:border-gray-800" x-data="{ activeAccordion: null }">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Medical Aid & Insurance FAQs</h2>
            <p class="text-sm text-slate-500 dark:text-gray-400 mt-2">Answers to common questions about claiming, co-payments, and provider networks.</p>
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <button @click="activeAccordion = (activeAccordion === 1 ? null : 1)" class="w-full p-5 text-left font-bold text-slate-800 dark:text-gray-100 flex items-center justify-between">
                    <span>Which medical aid societies are accepted?</span>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': activeAccordion === 1 }"></i>
                </button>
                <div x-show="activeAccordion === 1" x-collapse class="px-5 pb-5 text-xs sm:text-sm text-slate-600 dark:text-gray-300 border-t border-slate-100 dark:border-gray-700 pt-3">
                    We accept major registered societies in Zimbabwe including CIMAS, PSMAS, First Mutual Health, Generation Health, Alliance Health, Fidelity Life (FLMAS), BonVie, and Masca.
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <button @click="activeAccordion = (activeAccordion === 2 ? null : 2)" class="w-full p-5 text-left font-bold text-slate-800 dark:text-gray-100 flex items-center justify-between">
                    <span>How do I pay co-payments or shortfalls?</span>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': activeAccordion === 2 }"></i>
                </button>
                <div x-show="activeAccordion === 2" x-collapse class="px-5 pb-5 text-xs sm:text-sm text-slate-600 dark:text-gray-300 border-t border-slate-100 dark:border-gray-700 pt-3">
                    If your medical aid package has a tariff shortfall or co-payment, you can instantly settle the remaining balance using your Chiremba Medical Wallet, EcoCash, or debit card via Paynow.
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-slate-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <button @click="activeAccordion = (activeAccordion === 3 ? null : 3)" class="w-full p-5 text-left font-bold text-slate-800 dark:text-gray-100 flex items-center justify-between">
                    <span>Can I order chronic medication for delivery with medical aid?</span>
                    <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': activeAccordion === 3 }"></i>
                </button>
                <div x-show="activeAccordion === 3" x-collapse class="px-5 pb-5 text-xs sm:text-sm text-slate-600 dark:text-gray-300 border-t border-slate-100 dark:border-gray-700 pt-3">
                    Yes! Upload your valid prescription on our pharmacy section, select your medical aid society, and partner pharmacies in Harare, Bulawayo, Mutare, Gweru, and other towns will dispense and dispatch directly to your doorstep.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-12 bg-teal-700 text-white">
    <div class="container mx-auto px-4 max-w-5xl flex flex-col sm:flex-row items-center justify-between gap-6 text-center sm:text-left">
        <div>
            <h3 class="text-xl sm:text-2xl font-bold">Are you a healthcare provider or medical aid representative?</h3>
            <p class="text-xs sm:text-sm text-teal-100 mt-1">Join our certified network of digital telemedicine practitioners and pharmacy partners.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ url('doctorregister') }}" class="px-5 py-2.5 rounded-xl bg-white text-teal-800 hover:bg-teal-50 font-bold text-xs transition shadow-sm">
                Join as Provider
            </a>
            <a href="{{ url('contactus') }}" class="px-5 py-2.5 rounded-xl bg-teal-800 hover:bg-teal-900 text-white font-semibold text-xs border border-teal-600 transition">
                Contact Us
            </a>
        </div>
    </div>
</section>

@endsection
