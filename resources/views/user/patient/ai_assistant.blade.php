@extends('layouts.patient')

@section('title')
    {{__("Kunashe AI Health Assistant")}}
@stop

@section('header_styles')
<style>
    /* Custom scrollbar for chat body */
    .chat-body::-webkit-scrollbar {
        width: 6px;
    }
    .chat-body::-webkit-scrollbar-track {
        background: transparent;
    }
    .chat-body::-webkit-scrollbar-thumb {
        background-color: #d1d5db; /* gray-400 */
        border-radius: 20px;
        border: 3px solid transparent;
    }
    .dark .chat-body::-webkit-scrollbar-thumb {
        background-color: #4b5563; /* gray-600 */
    }

    /* Ensure send icon is visible on mobile */
    .send-button-icon {
        display: block; /* Ensure the icon is always displayed */
    }

    /* Optional: Hide send text on larger screens if desired (currently not in use) */
    /* @media (min-width: 768px) {
        .send-button-text {
            display: none;
        }
    } */
</style>
@endsection

@section('content')
    <div x-data="aiChatManager(
            {{ $ai_credits }},
            {{ $activeSession ? json_encode($activeSession) : 'null' }},
            {{ isset($formattedFinalReply) ? json_encode($formattedFinalReply) : 'null' }},
            {{ isset($initialSymptom) ? json_encode($initialSymptom) : 'null' }}
        )"
        x-init="init()" class="h-full flex flex-col" id="chat-app">
        {{-- Page Header --}}
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">{{__("Kunashe AI Health Assistant")}}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __("Get preliminary health insights. This is not a substitute for professional medical advice.") }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl flex flex-col flex-grow" style="height: calc(100vh - 250px);">
            {{-- Chat Header --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0 rounded-t-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center text-white">
                        <i class="fas fa-robot text-xl"></i>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{__("Kunashe AI")}}</span>
                        <p class="text-xs text-green-500">{{__("Online")}}</p>
                    </div>
                </div>
                <div class="px-3 py-1 text-sm font-semibold rounded-full" :class="aiCredits > 0 ? 'bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100'">
                    <span x-text="aiCredits"></span> {{__("Credits Left")}}
                </div>
            </div>

            {{-- Chat Body --}}
            <div class="flex-grow p-6 overflow-y-auto space-y-6 bg-gray-100 dark:bg-gray-900 chat-body" x-ref="chatBody">
                {{-- Dynamic Messages --}}
                <template x-for="(message, index) in messages" :key="index">
                    <div class="flex items-end gap-2" :class="message.sender === 'user' ? 'justify-end' : 'justify-start'">
                        {{-- AI Avatar --}}
                        <div x-show="message.sender !== 'user'" class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white flex-shrink-0">
                            <i class="fas fa-robot text-md"></i>
                        </div>

                        {{-- Message Bubble --}}
                        <div class="max-w-lg px-4 py-3 rounded-xl shadow"
                             :class="{
                                 'bg-primary-600 text-white rounded-bl-xl rounded-tr-xl': message.sender === 'user',
                                 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-r-xl rounded-tl-xl': message.sender === 'ai',
                                 'bg-red-100 dark:bg-red-700 text-red-800 dark:text-red-100 rounded-r-xl rounded-tl-xl': message.sender === 'error'
                             }">
                            <p class="text-sm whitespace-pre-wrap" x-html="message.text.replace(/\n/g, '<br>')"></p>
                            <p class="text-xs mt-1"
                               :class="message.sender === 'user' ? 'text-primary-200 text-right' : 'text-gray-500 dark:text-gray-400 text-right'"
                               x-text="message.time">
                            </p>
                        </div>
                    </div>
                </template>

                {{-- Typing Indicator --}}
                <template x-if="isLoading">
                    <div class="flex items-end gap-2">
                        <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white flex-shrink-0">
                            <i class="fas fa-robot text-md"></i>
                        </div>
                        <div class="max-w-lg px-4 py-3 rounded-r-xl rounded-tl-xl bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow">
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-gray-500 rounded-full animate-pulse"></div>
                                <div class="w-2 h-2 bg-gray-500 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                                <div class="w-2 h-2 bg-gray-500 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Chat Input Footer --}}
            <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex-shrink-0 rounded-b-xl">
                {{-- Attachment Preview --}}
                <template x-if="attachment">
                    <div class="p-2 mb-2 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <img :src="attachmentPreview" class="w-10 h-10 rounded object-cover">
                            <span class="text-sm text-gray-600 dark:text-gray-300" x-text="attachment.name"></span>
                        </div>
                        <button @click="removeAttachment" class="text-gray-500 hover:text-red-600 dark:hover:text-red-400 p-1 rounded-full">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </template>

                {{-- Action Buttons (Download and Start New Chat) --}}
                <template x-if="chatState === 'finished'">
                    <div class="flex flex-col gap-3">
                        <a :href="'{{ route('patient.ai_assistant.download') }}?session_id='+ sessionId"
                           class="w-full bg-primary-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-primary-700 transition-colors text-center flex items-center justify-center gap-2"
                           x-show="sessionId" download>
                           <i class="fas fa-download"></i> {{__("Download Report")}}
                        </a>
                        <button @click="startNewConversation"
                                class="w-full bg-primary-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-primary-700 transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-plus"></i> {{__("Start New Consultation")}}
                        </button>
                    </div>
                </template>

                {{-- Main Input Area --}}
                <template x-if="chatState !== 'finished'">
                    <div class="flex items-center gap-3">
                        <input type="file" id="ai-file-input" @change="handleFileAttachment" class="hidden" accept="image/*">
                        <button @click="triggerFileInput" :disabled="isLoading || (aiCredits <= 0 && !isConversationStarted)" class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed" title="Attach image">
                            <i class="fas fa-paperclip"></i>
                        </button>
                        <input
                            type="text"
                            x-model="newPrompt"
                            @keydown.enter.prevent="sendPrompt"
                            placeholder="{{__('Describe symptoms or ask about an image...')}}"
                            class="flex-grow rounded-full border-gray-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 sm:text-sm px-4 py-2"
                            :disabled="isLoading || (aiCredits <= 0 && !isConversationStarted)"
                        >
                        <button
                            @click="sendPrompt"
                            :disabled="isLoading || (newPrompt.trim() === '' && !attachment) || (aiCredits <= 0 && !isConversationStarted)"
                            class="w-12 h-10 flex-shrink-0 bg-primary-600 text-white font-semibold rounded-full shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 disabled:bg-primary-300 dark:disabled:bg-gray-600 disabled:cursor-not-allowed flex items-center justify-center"
                        >
                            <i x-show="!isLoading" class="fas fa-paper-plane send-button-icon"></i>
                            <span x-show="isLoading" class="fas fa-spinner fa-spin" style="display: none;"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
<script>
    function aiChatManager(initialCredits, activeSession, formattedFinalReply, initialSymptom) {
        return {
            // --- STATE ---
            messages: [],
            historyForApi: [],
            newPrompt: '',
            isLoading: false,
            sessionId: null,
            aiCredits: initialCredits,
            isConversationStarted: false,
            chatState: 'idle', // 'idle', 'gathering', 'finished',
            attachment: null,
            attachmentPreview: null,

            // --- METHODS ---
            init() {
                // Scroll to bottom when new messages are added
                this.$watch('messages', () => {
                    this.$nextTick(() => this.scrollToBottom());
                });

                if (activeSession) {
                    // Load existing session data
                    this.sessionId = activeSession.id;
                    this.isConversationStarted = true;
                    this.chatState = activeSession.status === 'completed' ? 'finished' : 'gathering';

                    activeSession.messages.forEach((msg, index) => {
                        // Reconstruct history for the API
                        this.historyForApi.push({ role: msg.role, content: msg.content });

                        // For the last message in a completed chat, use the pre-formatted HTML for display.
                        let displayText = (index === activeSession.messages.length - 1 && formattedFinalReply)
                            ? formattedFinalReply
                            : msg.content;

                        // Reconstruct messages for display
                        this.messages.push({
                            text: displayText,
                            sender: msg.role === 'user' ? 'user' : 'ai',
                            time: new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                        });
                    });
                } else {
                    // Add initial welcome message for a new session
                    this.messages.push({
                        text: "{{ __('Hello! I am Kunashe, your AI Health Assistant. To start a consultation, please describe your primary symptom or reason for your visit. Please note, 1 AI credit will be used for the entire consultation.') }}",
                        sender: 'ai',
                        time: this.getCurrentTime()
                    });

                    if (initialSymptom && initialSymptom.trim() !== '') {
                        this.newPrompt = initialSymptom;
                        this.sendPrompt();
                    }
                }
            },

            scrollToBottom() {
                this.$refs.chatBody.scrollTop = this.$refs.chatBody.scrollHeight;
            },

            getCurrentTime() {
                return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },

            startNewConversation() {
                this.messages = [];
                this.historyForApi = [];
                this.newPrompt = '';
                this.sessionId = null;
                this.isConversationStarted = false;
                this.chatState = 'idle';
                this.messages.push({ text: "{{ __('Hello! I am Kunashe, your AI Health Assistant. To start a consultation, please describe your primary symptom or reason for your visit. Please note, 1 AI credit will be used for the entire consultation.') }}", sender: 'ai',time: this.getCurrentTime()});
            },

            triggerFileInput() {
                document.getElementById('ai-file-input').click();
            },

            handleFileAttachment(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    this.messages.push({ text: 'Only image files (JPEG, PNG, GIF, WEBP) are allowed.', sender: 'error', time: this.getCurrentTime() });
                    return;
                }

                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    this.messages.push({ text: 'File size cannot exceed 5MB.', sender: 'error', time: this.getCurrentTime() });
                    return;
                }

                this.attachment = file;
                const reader = new FileReader();
                reader.onload = (e) => { this.attachmentPreview = e.target.result; };
                reader.readAsDataURL(file);
            },

            removeAttachment() {
                this.attachment = null;
                this.attachmentPreview = null;
                document.getElementById('ai-file-input').value = '';
            },

            async sendPrompt() {
                const promptText = this.newPrompt.trim();
                if (this.isLoading || (!promptText && !this.attachment)) return;

                if (!this.isConversationStarted && this.aiCredits <= 0) {
                    this.messages.push({
                        text: "{{ __('You have no AI credits left. Please') }} <a href='{{ route('patient.buy_credits') }}' class='underline font-bold'>{{ __('purchase more') }}</a> {{ __('to use this feature.') }}",
                        sender: 'error',
                        time: this.getCurrentTime()
                    });
                    return;
                }

                this.isLoading = true;
                
                let userMessageForDisplay = {
                    text: promptText, 
                    sender: 'user',
                    time: this.getCurrentTime()
                };
                if (this.attachment) {
                    userMessageForDisplay.text += `<br><div class="mt-2"><img src="${this.attachmentPreview}" alt="attachment" class="rounded-lg max-w-xs h-auto" /></div>`;
                }
                this.messages.push(userMessageForDisplay);

                // Add user message to API history
                const userMessageForApi = { role: 'user', content: promptText };
                this.historyForApi.push(userMessageForApi);

                // Prepare FormData for the backend.
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('action', this.isConversationStarted ? 'continue' : 'start');
                if (this.sessionId) {
                    formData.append('session_id', this.sessionId);
                }
                formData.append('history', JSON.stringify(this.historyForApi));
                if (this.attachment) {
                    formData.append('image', this.attachment);
                }

                // Clear inputs
                this.newPrompt = '';
                this.removeAttachment();

                try {
                    const response = await fetch('{{ route("patient.ask_ai") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'An unknown error occurred.');
                    }

                    const aiMessage = { role: 'assistant', content: data.raw_reply };
                    this.messages.push({ text: data.reply, sender: 'ai', time: this.getCurrentTime() });
                    this.historyForApi.push(aiMessage);

                    this.chatState = data.state;
                    this.aiCredits = data.credits_left;
                    this.sessionId = data.session_id; // Update session ID from backend
                    
                    if (!this.isConversationStarted) {
                        this.isConversationStarted = true;
                    }

                } catch (error) {
                    this.messages.push({ text: `Error: ${error.message}`, sender: 'error', time: this.getCurrentTime() });
                    this.historyForApi.pop(); // Remove the failed user prompt from API history
                } finally {
                    this.isLoading = false;
                }
            }
        }
    }
</script>
@endsection