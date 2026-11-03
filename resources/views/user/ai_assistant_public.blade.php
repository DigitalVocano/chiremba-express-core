@extends('user.layout')
@section('title')
AI Health Assistant | Free Online Symptom Checker Zimbabwe
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{ url('ai-health-assistant') }}"/>
<meta property="og:title" content="AI Health Assistant | Chiremba Express"/>
<meta property="og:image" content="{{asset('kumbacare/img/ai-background.jpg')}}"/>
<meta property="og:site_name" content="{{ config('app.name') }}"/>
<meta property="og:description" content="Chat with our AI Health Assistant for instant symptom analysis and health insights. Available 24/7 in Zimbabwe. Free guest access available."/>
<meta property="og:keyword" content="AI doctor Zimbabwe, symptom checker, online health assistant, medical AI, Chiremba Express AI, free health chat"/>
@stop

@section('content')
<!-- Page Header -->
<div class="container-fluid page-header py-5 mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: url({{ asset('kumbacare/img/ai-background.jpg') }}) center center/cover no-repeat;">
    <div class="container py-5 text-center">
        <h1 class="display-3 text-white mb-3 animated slideInDown">AI Health Assistant</h1>
        <p class="text-white text-lg">Your 24/7 Virtual Health Companion</p>
    </div>
</div>

<!-- Chat Section -->
<div class="container-xxl py-5">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl overflow-hidden flex flex-col h-[600px]" x-data="{
                messages: [{role: 'assistant', content: 'Hello! I am your AI Health Assistant. How can I help you today? You can describe your symptoms or ask a health question.'}],
                userInput: '',
                isLoading: false,
                guestLimit: 5,
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
                <!-- Chat Header -->
                <div class="bg-primary-600 p-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <i class="fas fa-robot text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-bold text-lg">Health Assistant</h3>
                            <p class="text-xs opacity-80">Powered by Advanced AI</p>
                        </div>
                    </div>
                    <span class="text-xs bg-white/20 px-2 py-1 rounded">Guest Mode</span>
                </div>
                
                <!-- Chat Messages -->
                <div class="flex-grow p-6 overflow-y-auto space-y-4 bg-gray-50 dark:bg-gray-900" id="chat-messages">
                    <template x-for="(msg, index) in messages" :key="index">
                        <div :class="{'flex justify-end': msg.role === 'user', 'flex justify-start': msg.role === 'assistant'}">
                            <div :class="{'bg-primary-500 text-white': msg.role === 'user', 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600': msg.role === 'assistant'}" class="max-w-[80%] rounded-lg px-5 py-3 text-sm shadow-sm">
                                <p x-html="msg.content"></p>
                            </div>
                        </div>
                    </template>
                    <div x-show="isLoading" class="flex justify-start">
                        <div class="bg-white dark:bg-gray-700 rounded-lg px-5 py-3 text-sm border border-gray-200 dark:border-gray-600">
                            <span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>
                        </div>
                    </div>
                    <div x-show="showLoginPrompt" class="flex justify-center mt-4">
                        <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6 text-center w-full max-w-md">
                            <i class="fas fa-lock text-yellow-600 text-2xl mb-3"></i>
                            <p class="text-lg text-gray-800 dark:text-white mb-2 font-bold">Unlock Full Access</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">You've reached the guest chat limit. Login to continue chatting, save your history, and download your full <strong>Medical Report as PDF</strong>.</p>
                            <div class="flex justify-center space-x-4">
                                <a href="{{ url('patientlogin') }}" class="bg-primary-500 text-white px-6 py-2 rounded-md hover:bg-primary-600 transition-colors">Login</a>
                                <a href="{{ url('patientregister') }}" class="bg-secondary-500 text-white px-6 py-2 rounded-md hover:bg-secondary-600 transition-colors">Register</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                    <form @submit.prevent="sendMessage" class="flex gap-3">
                        <input type="text" x-model="userInput" :disabled="showLoginPrompt || isLoading" placeholder="Type your symptoms or health questions here..." class="flex-grow rounded-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-500 focus:ring-primary-500 px-4 py-3 disabled:opacity-50">
                        <button type="submit" :disabled="showLoginPrompt || isLoading" class="bg-primary-500 text-white w-12 h-12 rounded-full hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center shadow-md transition-transform transform hover:scale-105">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- SEO Content -->
            <div class="mt-12 prose dark:prose-invert max-w-none">
                <h2>Why Use Our AI Health Assistant?</h2>
                <p>Chiremba Express brings you the future of telemedicine in Zimbabwe. Our AI Health Assistant is designed to provide preliminary health insights based on your symptoms.</p>
                <ul>
                    <li><strong>Instant Analysis:</strong> Get immediate feedback on your health concerns.</li>
                    <li><strong>24/7 Availability:</strong> Access health information anytime, anywhere.</li>
                    <li><strong>Privacy First:</strong> Your guest chats are anonymous. Login for secure record keeping.</li>
                </ul>
                <p class="text-sm text-gray-500"><em>Disclaimer: The AI Health Assistant provides information for educational purposes only and does not constitute medical advice, diagnosis, or treatment. Always seek the advice of your physician or other qualified health provider with any questions you may have regarding a medical condition.</em></p>
            </div>
        </div>
    </div>
</div>
@stop

@section('footer_scripts')
<style>
    .typing-dots span {
        animation: typing 1.4s infinite ease-in-out both;
        margin: 0 1px;
        font-size: 1.5rem;
    }
    .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
    @keyframes typing {
        0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); }
    }
</style>
@stop