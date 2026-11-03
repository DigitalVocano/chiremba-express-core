@extends('user.layout')
@section('title')
 @php
$fav = app\models\Setting::find(1)->title;
@endphp
{{$fav}}
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{__('Chiremba Express')}}"/>
<meta property="og:title" content="{{__('Chiremba Express')}}"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{__('Chiremba Express')}}"/>
<meta property="og:description" content="Chiremba Express is Zimbabwe's leading AI-powered telemedicine platform. Connect with top doctors, get instant AI health analysis, and order prescriptions online."/>
<meta property="og:keyword" content="Telemedicine Zimbabwe, AI Health Assistant, Online Doctor Zimbabwe, Pharmacy Delivery Harare, Medical Aid Zimbabwe, Chiremba Express, Virtual Clinic"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')

<!-- Hero Section with Alpine.js Slider -->
<div x-data="{
        activeSlide: 1,
        totalSlides: 3, // Changed to 3 for the new AI slide
        autoplayInterval: null,
        placeholderTexts: ['Search by doctor or specialty...', 'e.g., Dentist, Cardiologist...', 'Find a pharmacy near you...'],
        currentPlaceholderIndex: 0,
        placeholderText: '',
        charIndex: 0,
        isErasing: false,
        typingSpeed: 100,
        erasingSpeed: 50,
        delayBetweenTexts: 2000,
        startAutoplay() {
            // Clear any existing interval before setting a new one
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
            }
            this.autoplayInterval = setInterval(() => { this.next() }, 6000); // Slightly longer interval
        },
        stopAutoplay() { clearInterval(this.autoplayInterval); this.autoplayInterval = null; }, // Explicitly set to null
        next() { this.activeSlide = this.activeSlide === this.totalSlides ? 1 : this.activeSlide + 1; },
        prev() { this.activeSlide = this.activeSlide === 1 ? this.totalSlides : this.activeSlide - 1; },
        animatePlaceholder() {
            const currentText = this.placeholderTexts[this.currentPlaceholderIndex];
            if (this.isErasing) {
                this.placeholderText = currentText.substring(0, this.charIndex - 1);
                this.charIndex--;
                if (this.charIndex === 0) { this.isErasing = false; this.currentPlaceholderIndex = (this.currentPlaceholderIndex + 1) % this.placeholderTexts.length; setTimeout(() => this.animatePlaceholder(), this.typingSpeed); } else { setTimeout(() => this.animatePlaceholder(), this.erasingSpeed); }
            } else {
                this.placeholderText = currentText.substring(0, this.charIndex + 1);
                this.charIndex++;
                if (this.charIndex === currentText.length) { this.isErasing = true; setTimeout(() => this.animatePlaceholder(), this.delayBetweenTexts); } else { setTimeout(() => this.animatePlaceholder(), this.typingSpeed); }
            }
        }
    }"
     x-init="startAutoplay(); animatePlaceholder()"
     @mouseenter="stopAutoplay()"
     @mouseleave="startAutoplay()"
     class="relative">

    <!-- Slides Container -->
    <div class="relative w-full h-[600px] overflow-hidden">
        <!-- Slide 1 -->
        <div x-show="activeSlide === 1"
             x-transition:enter="transition ease-in-out duration-1000"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in-out duration-1000"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0">
            <img class="w-full h-full object-cover" src="{{asset('kumbacare/img/carousel-1.jpg')}}" alt="A doctor consulting with a patient">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="container mx-auto">
                    <div class="flex justify-center">
                        <div class="w-full lg:w-3/4 text-center">
                            <h1 class="text-4xl md:text-6xl font-bold text-white mb-4 animated slideInDown">{{__('Your Health, Your Way.')}}</h1>
                            <p class="text-lg text-gray-200 mb-8 animated slideInUp">Find trusted doctors, specialists, and pharmacies. Book appointments and get the care you need, anytime, anywhere.</p>
                            <div class="max-w-2xl mx-auto animated fadeIn">
                                <form action="{{url('searchdoctor')}}" method="get" class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm p-4 rounded-lg shadow-lg flex flex-col sm:flex-row gap-2" @submit.prevent="if($event.target.term.value.trim()) $event.target.submit()">
                                    <input type="text" name="term" :placeholder="placeholderText" class="form-input flex-grow text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:ring-primary-500 focus:border-primary-500">
                                    <button type="submit" class="bg-primary-500 text-white px-6 py-3 rounded-md hover:bg-primary-600 font-semibold transition-colors">Search</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div x-show="activeSlide === 2"
             x-transition:enter="transition ease-in-out duration-1000"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in-out duration-1000"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0">
            <img class="w-full h-full object-cover" src="{{asset('kumbacare/img/carousel-2.jpg')}}" alt="A pharmacy with shelves of medicine">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="container mx-auto">
                    <div class="flex justify-center">
                        <div class="w-full lg:w-3/4 text-center">
                            <h1 class="text-4xl md:text-6xl font-bold text-white mb-4 animated slideInLeft">Find Pharmacies</h1>
                            <p class="text-lg text-gray-200 mb-8 animated slideInRight">Order your prescriptions and over-the-counter medications online. We partner with trusted pharmacies to ensure timely delivery and accurate prescriptions.</p>
                           <a href="{{url('searchpharmacy')}}" class="bg-primary-500 text-white py-3 px-6 rounded-md hover:bg-primary-600 font-semibold transition-colors">Search Pharmacies</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3: AI Assistant -->
        <div x-show="activeSlide === 3"
             x-transition:enter="transition ease-in-out duration-1000"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in-out duration-1000"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0"
             x-data="{ showLoginModal: false }">
            <img class="w-full h-full object-cover" src="{{asset('kumbacare/img/carousel-2.jpg')}}" alt="Health Assistant background">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="container mx-auto">
                    <div class="flex justify-center">
                        <div class="w-full lg:w-3/4 text-center">
                            <h1 class="text-4xl md:text-6xl font-bold text-white mb-4 animated slideInDown">AI Health Assistant</h1>
                            <p class="text-lg text-gray-200 mb-8 animated slideInUp">Experience the future of healthcare in Zimbabwe. Chat with our AI to get instant health insights and generate medical reports.</p>
                            <div class="max-w-2xl mx-auto animated fadeIn">
                                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm p-4 rounded-lg shadow-lg flex flex-col sm:flex-row gap-2" x-data="{ symptom: '' }">
                                    <input type="text" name="symptom" x-model="symptom" placeholder="Enter your main symptom... (e.g., headache, cough)" class="form-input flex-grow text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 focus:ring-secondary-500 focus:border-secondary-500 rounded-md">
                                    @if(Session::has("user_id") && Session::get("role_id") == '1')
                                        <a :href="'{{ route('patient.ai_assistant') }}' + (symptom ? '?symptom=' + encodeURIComponent(symptom) : '')" class="bg-secondary-500 text-white px-6 py-3 rounded-md hover:bg-secondary-600 font-semibold transition-colors flex items-center justify-center">
                                            <i class="fas fa-robot mr-2"></i> Get Smart Analysis
                                        </a>
                                    @else
                                        <a href="#ai-assistant" class="bg-secondary-500 text-white px-6 py-3 rounded-md hover:bg-secondary-600 font-semibold transition-colors flex items-center justify-center">
                                            <i class="fas fa-robot mr-2"></i> Chat with AI
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Login/Signup Modal for this slide -->
            <div x-show="showLoginModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 flex items-center justify-center p-4" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div @click.away="showLoginModal = false" class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 text-center" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/20 mb-4">
                        <i class="fas fa-user-lock text-2xl text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Access Your Health Assistant</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Please log in or create an account to use this feature.</p>
                    <div class="space-y-4">
                        <a href="{{ url('patientlogin') }}" class="block w-full bg-primary-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-primary-600 transition-colors">Log In to Access</a>
                        <a href="{{ url('patientregister') }}" class="block w-full bg-secondary-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-secondary-600 transition-colors">Sign Up Now</a>
                    </div>
                    <button @click="showLoginModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"><i class="fas fa-times text-xl"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <button @click="prev()" class="absolute top-1/2 left-0 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white p-2 sm:p-3 rounded-full z-10 ml-2 sm:ml-4 focus:outline-none transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
    </button>
    <button @click="next()" class="absolute top-1/2 right-0 transform -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white p-2 sm:p-3 rounded-full z-10 mr-2 sm:mr-4 focus:outline-none transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
    </button>

    <!-- Indicators -->
    <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
        <template x-for="i in totalSlides" :key="i">
            <button @click="activeSlide = i" :class="{'bg-white': activeSlide === i, 'bg-white/50 hover:bg-white/75': activeSlide !== i}" class="w-3 h-3 rounded-full transition-colors"></button>
        </template>
    </div>
</div>

<!-- Key Services Section -->
<div class="py-16 bg-gray-50 dark:bg-gray-800">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="p-6">
                <div class="text-primary-500 dark:text-primary-400 mb-4"><i class="fas fa-user-md fa-3x"></i></div>
                <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Expert Doctors</h3>
                <p class="text-gray-600 dark:text-gray-400">Connect with certified and experienced healthcare professionals across various specialties.</p>
            </div>
            <div class="p-6">
                <div class="text-primary-500 dark:text-primary-400 mb-4"><i class="fas fa-pills fa-3x"></i></div>
                <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Online Pharmacy</h3>
                <p class="text-gray-600 dark:text-gray-400">Order your medications and health products from trusted local pharmacies for delivery.</p>
            </div>
            <div class="p-6">
                <div class="text-primary-500 dark:text-primary-400 mb-4"><i class="fas fa-shield-alt fa-3x"></i></div>
                <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Affordable Insurance</h3>
                <p class="text-gray-600 dark:text-gray-400">Access comprehensive health coverage with our OneHealth plan, starting from just $1/month.</p>
            </div>
        </div>
    </div>
</div>
<!-- Featured Providers Section -->
@if(isset($featured_providers) && $featured_providers->count() > 0)
<div class="py-16 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Featured Providers</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Discover our trusted and highly-rated healthcare partners.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($featured_providers as $provider)
                @if($provider->profile_type == 1)
                    @include('user.partials.doctor-card', ['doctor' => $provider])
                @elseif($provider->profile_type == 2)
                    @include('user.partials.pharmacy-card', ['pharmacy' => $provider])
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif
<!-- How It Works Section -->
<div class="py-16 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Get Care in 3 Easy Steps</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Your journey to better health is just a few clicks away.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <!-- Step 1 -->
            <div class="relative p-6">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gray-100 dark:text-gray-700 font-bold text-9xl z-0">1</div>
                <div class="relative z-10">
                    <div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mx-auto mb-4">
                        <i class="fas fa-search fa-2x"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Find Your Doctor</h3>
                    <p class="text-gray-600 dark:text-gray-400">Search for a specialist or a general practitioner that fits your needs.</p>
                </div>
            </div>
            <!-- Step 2 -->
            <div class="relative p-6">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gray-100 dark:text-gray-700 font-bold text-9xl z-0">2</div>
                <div class="relative z-10">
                    <div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mx-auto mb-4">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Book an Appointment</h3>
                    <p class="text-gray-600 dark:text-gray-400">Choose a convenient time slot and book your virtual or in-person consultation.</p>
                </div>
            </div>
            <!-- Step 3 -->
            <div class="relative p-6">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gray-100 dark:text-gray-700 font-bold text-9xl z-0">3</div>
                <div class="relative z-10">
                    <div class="flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/20 text-primary-500 dark:text-primary-600 mx-auto mb-4">
                        <i class="fas fa-laptop-medical fa-2x"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-2 text-gray-800 dark:text-white">Get Consultation</h3>
                    <p class="text-gray-600 dark:text-gray-400">Receive expert medical advice, prescriptions, and a personalized care plan.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Symptom Checker Section -->
<div class="py-16 bg-gray-100 dark:bg-gray-800" id="ai-assistant" x-data="{
    messages: [{role: 'assistant', content: 'Hello! I am your AI Health Assistant. How can I help you today? You can describe your symptoms or ask a health question.'}],
    userInput: '',
    isLoading: false,
    guestLimit: 3,
    interactionCount: 0,
    showLoginPrompt: false,
    sendMessage() {
        if (this.userInput.trim() === '' || this.isLoading) return;
        
        if (this.interactionCount >= this.guestLimit) {
            this.showLoginPrompt = true;
            return;
        }

        const userMsg = this.userInput;
        this.messages.push({role: 'user', content: userMsg});
        this.userInput = '';
        this.isLoading = true;
        this.interactionCount++;

        // Prepare history for context
        const history = this.messages.map(m => ({role: m.role, content: m.content}));

        fetch('{{ url('guest/ask-ai') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: userMsg, history: history })
        })
        .then(response => response.json())
        .then(data => {
            this.isLoading = false;
            if (data.error) {
                this.messages.push({role: 'assistant', content: data.error});
                if(data.error.includes('limit')) this.showLoginPrompt = true;
            } else {
                this.messages.push({role: 'assistant', content: data.reply});
                if (this.interactionCount >= this.guestLimit) {
                    setTimeout(() => { this.showLoginPrompt = true; }, 1000);
                }
            }
        })
        .catch(error => {
            this.isLoading = false;
            this.messages.push({role: 'assistant', content: 'Sorry, I encountered an error. Please try again.'});
        });
    }
}">
    <div class="container mx-auto px-4">
        <div class="relative bg-primary-600 dark:bg-primary-700 rounded-lg shadow-xl overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('{{ asset('kumbacare/img/ai-background.jpg') }}');"></div>
            <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8 items-center p-8 md:p-12">
                <!-- Left side: Animation -->
                <div class="hidden md:flex flex-col justify-center items-center">
                    {{-- Lottie Animation for AI Assistant --}}
                    <lottie-player src="{{ asset('kumbacare/json/AI assistant - Animation.json') }}" background="transparent" speed="1" style="width: 300px; height: 300px;" loop autoplay></lottie-player>
                    <div class="text-white text-center mt-4">
                        <h3 class="text-2xl font-bold">AI Health Assistant</h3>
                        <p class="opacity-90">Your 24/7 Virtual Health Companion</p>
                    </div>
                </div>
                <!-- Right side: Chat Interface -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl overflow-hidden flex flex-col h-[500px]">
                    <div class="bg-gray-100 dark:bg-gray-900 p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <span class="font-semibold text-gray-700 dark:text-gray-200"><i class="fas fa-robot text-primary-500 mr-2"></i> Chat Session</span>
                        <span class="text-xs text-gray-500">Guest Mode</span>
                    </div>
                    
                    <div class="flex-grow p-4 overflow-y-auto space-y-4" id="chat-messages">
                        <template x-for="(msg, index) in messages" :key="index">
                            <div :class="{'flex justify-end': msg.role === 'user', 'flex justify-start': msg.role === 'assistant'}">
                                <div :class="{'bg-primary-500 text-white': msg.role === 'user', 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200': msg.role === 'assistant'}" class="max-w-[80%] rounded-lg px-4 py-2 text-sm shadow-sm">
                                    <p x-html="msg.content"></p>
                                </div>
                            </div>
                        </template>
                        <div x-show="isLoading" class="flex justify-start">
                            <div class="bg-gray-200 dark:bg-gray-700 rounded-lg px-4 py-2 text-sm">
                                <span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>
                            </div>
                        </div>
                        <div x-show="showLoginPrompt" class="flex justify-center mt-4">
                            <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 text-center w-full">
                                <p class="text-sm text-yellow-800 dark:text-yellow-200 mb-3 font-semibold">You've reached the guest chat limit.</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">Login to continue chatting, save your history, and download your full <strong>Medical Report as PDF</strong>.</p>
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ url('patientlogin') }}" class="bg-primary-500 text-white px-4 py-2 rounded text-sm hover:bg-primary-600">Login</a>
                                    <a href="{{ url('patientregister') }}" class="bg-secondary-500 text-white px-4 py-2 rounded text-sm hover:bg-secondary-600">Register</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                        <form @submit.prevent="sendMessage" class="flex gap-2">
                            <input type="text" x-model="userInput" :disabled="showLoginPrompt || isLoading" placeholder="Type your symptoms here..." class="flex-grow rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50">
                            <button type="submit" :disabled="showLoginPrompt || isLoading" class="bg-primary-500 text-white px-4 py-2 rounded-md hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Doctor AI Section -->
<div class="py-16 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="relative bg-gray-800 dark:bg-gray-700 rounded-lg shadow-xl overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center opacity-10" style="background-image: url('{{ asset('kumbacare/img/doctor-ai-background.jpg') }}');"></div>
            <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-8 items-center p-8 md:p-12">
                <!-- Left side: Content -->
                <div class="text-center md:text-left text-white">
                    <h2 class="text-3xl md:text-4xl font-bold mb-3">{{__('AI-Powered Tools for Doctors')}}</h2>
                    <p class="max-w-2xl mx-auto md:mx-0 mb-8">{{__('Enhance your diagnostic capabilities with our advanced AI. Get instant prescription suggestions and detailed analysis of medical reports to save time and improve patient outcomes.')}}</p>
                    <div class="flex justify-center md:justify-start">
                        <a href="{{ url('doctorregister') }}" class="bg-white text-gray-800 px-6 py-3 rounded-md hover:bg-gray-100 font-semibold transition-colors flex items-center justify-center sm:inline-flex">
                            <i class="fas fa-user-md mr-2"></i> {{__('Join as a Provider')}}
                        </a>
                    </div>
                </div>
                <!-- Right side: Image -->
                <div class="hidden md:flex justify-center items-center h-64">
                    {{-- You can replace this with a relevant image or another Lottie animation --}}
                    <img src="{{ asset('kumbacare/img/doctor-ai.jpg') }}" alt="AI for Doctors" class="rounded-lg shadow-lg object-cover h-full w-auto">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Browse by Specialist Section -->
<div class="py-16 bg-gray-50 dark:bg-gray-800">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Browse by Specialist</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Find the right healthcare provider for your specific needs.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($department as $d)
            <a href="{{url('searchdoctor?type=').$d->id}}" class="block p-6 bg-white dark:bg-gray-700 rounded-lg shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center mb-4">
                    <img src="{{asset('upload/services').'/'.$d->icon}}" class="h-12 w-12 mr-4" alt="{{ $d->name }} Icon">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white">{{$d->name}}</h4>
                        <p class="text-sm text-primary-500 dark:text-primary-400">{{$d->doctors_count}} Doctors</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($d->description, 80) }}</p>
            </a>
            @endforeach
        </div>
        <div class="text-center mt-12">
            <a href="{{url('viewspecialist')}}" class="inline-block bg-transparent border-2 border-primary-500 text-primary-500 font-semibold py-2 px-6 rounded-md hover:bg-primary-500 hover:text-white transition-colors">
                {{__('All Specialist')}} <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="py-16 bg-white dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white">What Our Patients Say</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Real stories from people we've helped.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <img class="w-12 h-12 rounded-full mr-4 object-cover" src="{{ asset('kumbacare/img/testimonial-1.jpg') }}" alt="Patient photo">
                    <div>
                        <h4 class="font-semibold text-gray-800 dark:text-white">Tendai M.</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Harare</p>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-300 italic">"Chiremba Express made it so easy to find a pediatrician for my son. The online booking was a lifesaver, and the doctor was fantastic. Highly recommended!"</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <img class="w-12 h-12 rounded-full mr-4 object-cover" src="{{ asset('kumbacare/img/testimonial-2.jpg') }}" alt="Patient photo">
                    <div>
                        <h4 class="font-semibold text-gray-800 dark:text-white">John D.</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Bulawayo</p>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-300 italic">"The OneHealth insurance is incredibly affordable and gives me peace of mind. Knowing I have cover for consultations and medicine for just $1 is amazing."</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <img class="w-12 h-12 rounded-full mr-4 object-cover" src="{{ asset('kumbacare/img/testimonial-3.jpg') }}" alt="Patient photo">
                    <div>
                        <h4 class="font-semibold text-gray-800 dark:text-white">Anesu C.</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Mutare</p>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-300 italic">"I used KumbaPay for a specialist visit, and it was a seamless process. Being able to pay in installments removed a huge financial burden."</p>
            </div>
        </div>
    </div>
</div>



<!-- App Download Section -->
<div class="py-16 bg-gray-50 dark:bg-gray-800">
    <div class="container mx-auto px-4">
        <div class="bg-secondary-500 rounded-lg shadow-xl overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 items-center">
                <div class="p-8 md:p-12 text-white">
                    <h2 class="text-3xl font-bold mb-3">Mobile Health, Maximum Convenience</h2>
                    <p class="mb-6 opacity-90">With the Chiremba Express App, you can seamlessly find and connect with doctors, apply for medical advances, and lease medical equipment. Your health, managed from your pocket.</p>
                    <div class="flex items-center space-x-4">
                        <a href="{{$setting->app_url}}" target="_blank" class="app-download-btn"> {{-- h-8 is now set by CSS --}}
                            <img src="{{asset('kumbacare/img/App_Store_26_icon.webp')}}" alt="App Store">
                            <div class="text-container">
                                <span class="text-xs">Download on the</span>
                                <span class="text-base">App Store</span>
                            </div>
                        </a>
                        <a href="{{$setting->playstore_url}}" target="_blank" class="app-download-btn"> {{-- h-8 is now set by CSS --}}
                            <img src="{{asset('kumbacare/img/1664285914google-play-logo-png.webp')}}" alt="Google Play">
                            <div class="text-container">
                                <span class="text-xs">Get it on</span>
                                <span class="text-base">Google Play</span>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="hidden md:block h-full">
                    <img src="{{asset('image_web/').'/'.$setting->app_banner}}" alt="Chiremba Express App" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Developer Platform & Zimflow Section -->
<div class="py-16 bg-slate-900 text-white relative overflow-hidden">
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 text-xs font-bold mb-4">
                <i class="fas fa-code"></i>
                <span>Public Healthcare REST API &middot; XAPI v1.0</span>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-4">
                Build Healthcare Solutions &amp; WhatsApp AI Chatbots
            </h2>
            <p class="text-slate-300 text-base md:text-lg mb-8 max-w-2xl mx-auto leading-relaxed">
                Connect your apps directly to certified doctor directories and pharmacy networks via Chiremba APIs. Build automated WhatsApp booking and triage chatbots in minutes using <a href="https://zimflow.co.zw" target="_blank" class="text-emerald-400 font-bold underline hover:text-emerald-300">Zimflow.co.zw</a> low-code visual AI flows.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ url('/developers/api') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-sm transition shadow-lg shadow-teal-500/20 flex items-center justify-center gap-2">
                    <i class="fas fa-terminal"></i>
                    <span>Open Developer Portal</span>
                </a>
                <a href="https://zimflow.co.zw" target="_blank" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-400 font-semibold text-sm border border-emerald-500/30 transition flex items-center justify-center gap-2">
                    <i class="fab fa-whatsapp text-lg"></i>
                    <span>Build Chatbot on Zimflow.co.zw</span>
                </a>
            </div>
        </div>
    </div>
</div>
@stop
@section('footer_scripts')
{{-- Lottie Player for JSON-based animations --}}
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<style>
    .typing-dots span {
        animation: typing 1.4s infinite ease-in-out both;
        margin: 0 1px;
    }
    .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
    @keyframes typing {
        0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); }
    }
</style>
@stop
