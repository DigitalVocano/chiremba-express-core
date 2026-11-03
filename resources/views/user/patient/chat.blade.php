@extends('layouts.patient')

@section('title')
    {{__("Chat with")}} {{ $doctor->name }}
@stop

@section('meta-data')
    {{-- Add relevant meta data here if needed, using $setting --}}
    <meta property="og:title" content="{{__("Chat with")}} {{ $doctor->name }}"/>
@stop

@section('header_styles')
{{-- This view now uses Tailwind CSS classes directly and Alpine.js for interactivity. --}}
@stop

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__("Chat with")}} {{ $doctor->name }}</h1>
        <a href="{{ url('userdashboard') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm">
            <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg" x-data="chatManager({{ $apnt_id }}, {{ $doctor->id }}, {{ auth()->id() }})" x-init="init()">
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg h-[calc(100vh-220px)] flex flex-col">
            
            {{-- Chat Header --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3 flex-shrink-0">
                <img src="{{ asset('upload/doctors/' . $doctor->image) }}" alt="Doctor Avatar" class="w-10 h-10 rounded-full object-cover">
                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $doctor->name }}</span>
            </div>

            {{-- Chat Body --}}
            <div class="flex-grow p-4 overflow-y-auto space-y-4 bg-gray-100 dark:bg-gray-900" x-ref="chatBody">
                <template x-if="isLoading && messages.length === 0">
                    <p class="text-center text-gray-500 dark:text-gray-400">Loading messages...</p>
                </template>
                
                <template x-for="message in messages" :key="message.id">
                    <div class="flex" :class="message.sender_id == authUserId ? 'justify-end' : 'justify-start'">
                        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-xl" :class="message.sender_id == authUserId ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100'">
                            <p class="text-sm" x-text="message.message"></p>
                            
                            {{-- Rich Media Display --}}
                            <template x-if="message.file_path">
                                {{-- Image Preview --}}
                                <template x-if="isImage(message.original_file_name)">
                                    <a :href="'{{ Storage::url('') }}' + message.file_path" target="_blank" class="block mt-2">
                                        <img :src="'{{ Storage::url('') }}' + message.file_path" class="rounded-lg max-w-full h-auto" alt="Chat Image">
                                    </a>
                                </template>
                                {{-- PDF/File Link --}}
                                <template x-if="!isImage(message.original_file_name)">
                                    <div class="mt-2 p-2 rounded-lg" :class="message.sender_id == authUserId ? 'bg-black/10' : 'bg-gray-300 dark:bg-gray-600'">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center min-w-0">
                                                <i class="fas fa-file-pdf text-2xl mr-2 text-red-500 flex-shrink-0"></i>
                                            <span class="text-sm font-medium" x-text="message.original_file_name || 'View Attached File'"></span>
                                            </div>
                                            <div class="flex items-center space-x-3 flex-shrink-0 ml-2">
                                                <a :href="'{{ Storage::url('') }}' + message.file_path" target="_blank" class="text-gray-600 dark:text-gray-300 hover:text-primary-500" :class="message.sender_id == authUserId ? 'text-white hover:text-gray-200' : ''" title="Preview"><i class="fas fa-eye"></i></a>
                                                <a :href="'{{ Storage::url('') }}' + message.file_path" :download="message.original_file_name" class="text-gray-600 dark:text-gray-300 hover:text-primary-500" :class="message.sender_id == authUserId ? 'text-white hover:text-gray-200' : ''" title="Download"><i class="fas fa-download"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Chat Footer --}}
            <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex items-center gap-3 flex-shrink-0">
                <input type="file" id="file-input" @change="handleFileAttachment" class="hidden" accept="image/*,.pdf">
                <button @click="triggerFileInput" class="flex-shrink-0 px-3 py-2 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none" title="Attach file">
                    <i class="fas fa-paperclip"></i>
                </button>
                <input 
                    type="text" 
                    x-model="newMessage" 
                    @keydown.enter.prevent="sendMessage" 
                    placeholder="Type your message..." 
                    class="flex-grow block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-600 focus:ring-primary-600 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 sm:text-sm"
                >
                <button 
                    @click="sendMessage" 
                    :disabled="isLoading || (newMessage.trim() === '' && !attachment)" 
                    class="px-4 py-2 bg-primary-600 text-white font-semibold rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 disabled:bg-primary-300 disabled:cursor-not-allowed"
                >
                    {{__("Send")}}
                </button>
            </div>
        </div>
    </div>
@stop

@section('footer_scripts')
<script>
    /**
     * Helper function to check if a filename represents an image.
     * @param {string} filename The name of the file.
     * @returns {boolean} True if the file is an image, false otherwise.
     */
    function isImage(filename) {
        if (!filename) return false;
        const ext = filename.split('.').pop().toLowerCase();
        return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(ext);
    }
</script>
<script>
    function chatManager(appointmentId, doctorId, authUserId) {
        return {
            appointmentId: appointmentId,
            doctorId: doctorId,
            authUserId: authUserId,
            messages: [],
            newMessage: '',
            attachment: null,
            isLoading: false,
            pollingInterval: null,

            init() {
                this.fetchMessages();
                this.pollingInterval = setInterval(() => this.fetchMessages(), 5000);
                this.$watch('messages', () => {
                    this.$nextTick(() => this.scrollToBottom());
                });
                window.addEventListener('beforeunload', () => clearInterval(this.pollingInterval));
            },

            scrollToBottom() {
                this.$refs.chatBody.scrollTop = this.$refs.chatBody.scrollHeight;
            },

            async fetchMessages() {
                if (this.messages.length === 0) this.isLoading = true;
                try {
                    const response = await fetch(`{{ url('get_user_chat_data') }}/${this.appointmentId}`);
                    if (!response.ok) throw new Error('Network response was not ok.');
                    const data = await response.json();
                    if (JSON.stringify(this.messages) !== JSON.stringify(data)) {
                        this.messages = data;
                    }
                } catch (error) {
                    console.error('Error fetching messages:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            async sendMessage() {
                if (this.isLoading || (this.newMessage.trim() === '' && !this.attachment)) return;

                this.isLoading = true;
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('apnt_id', this.appointmentId);
                formData.append('receiver_id', this.doctorId);

                let url = this.attachment ? '{{ url('send_file_user') }}' : '{{ url('send_msg_user') }}';

                if (this.attachment) {
                    formData.append('file', this.attachment, this.attachment.name);
                }
                if (this.newMessage.trim() !== '') {
                    // If there's an attachment, the filename is in newMessage. Don't send it as the main message.
                    // Send it only if there is no attachment.
                    const messageToSend = this.attachment ? '' : this.newMessage.trim();
                    formData.append('message', messageToSend);
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!response.ok) throw new Error('Failed to send message.');
                    const result = await response.json();
                    if (result.success) {
                        this.newMessage = '';
                        this.attachment = null;
                        document.getElementById('file-input').value = '';
                        await this.fetchMessages();
                    } else {
                        console.error('Server error:', result.message);
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            triggerFileInput() {
                document.getElementById('file-input').click();
            },

            handleFileAttachment(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.attachment = file;
                this.newMessage = file.name; // Show filename in input as feedback
            }
        }
    }
</script>
@stop
