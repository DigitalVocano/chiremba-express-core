@extends('layouts.doctor')

@section('title')
    {{__("Chat with")}} {{ $patient->name }}
@stop

@section('meta-data')
    {{-- Cleaned up meta section. Add specific meta tags if needed. --}}
    <meta property="og:title" content="{{__("Chat with")}} {{ $patient->name }}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
@stop

@section('header_styles')
@stop
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200">{{__("Chat with")}} {{ $patient->name }}</h1>
        {{-- Added a back button for better navigation --}}
        <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm">
            <i class="fas fa-arrow-left mr-2"></i>{{ __('Back') }}
        </a>
    </div>

    {{-- 
      The entire chat interface is now a single Alpine.js component.
      - `x-data`: Initializes the component with the chatManager function. We pass in server-side data directly.
      - `x-init`: Calls the `init()` method when the component is ready.
    --}}
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg" x-data="chatManager({{ $apnt_id }}, {{ $patient->id }}, {{ auth()->id() }})" x-init="init()">
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg h-[calc(100vh-220px)] flex flex-col">
            
            {{-- Chat Header --}}
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3 flex-shrink-0">
                <img src="{{ asset($patient->profile_pic) }}" alt="User Avatar" class="w-10 h-10 rounded-full object-cover">
                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $patient->name }}</span>
            </div>

            {{-- Chat Body --}}
            <div class="flex-grow p-4 overflow-y-auto space-y-4 bg-gray-100 dark:bg-gray-900" x-ref="chatBody">
                <template x-if="isLoading && messages.length === 0">
                    <p class="text-center text-gray-500 dark:text-gray-400">Loading messages...</p>
                </template>
                
                {{-- Messages are now rendered declaratively from the `messages` array in our Alpine component --}}
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
                    class="px-4 py-2 bg-primary-600 text-white font-semibold rounded-md shadow-sm hover:bg-lime-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 disabled:bg-primary-300 disabled:cursor-not-allowed"
                >
                    {{__("Send")}}
                </button>
            </div>
        </div>
    </div>
@endsection

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
    // This is the Alpine.js component logic that powers the chat.
    function chatManager(appointmentId, patientId, authUserId) {
        return {
            // --- STATE ---
            appointmentId: appointmentId,
            patientId: patientId,
            authUserId: authUserId,
            messages: [],
            newMessage: '',
            attachment: null,
            isLoading: false,
            pollingInterval: null,

            // --- METHODS ---
            init() {
                this.fetchMessages();
                // Poll for new messages every 5 seconds for a real-time feel.
                this.pollingInterval = setInterval(() => this.fetchMessages(), 5000);

                // Automatically scroll to the bottom when new messages are added.
                this.$watch('messages', () => {
                    this.$nextTick(() => this.scrollToBottom());
                });

                // Clean up the interval when the user navigates away to prevent memory leaks.
                window.addEventListener('beforeunload', () => {
                    clearInterval(this.pollingInterval);
                });
            },

            scrollToBottom() {
                this.$refs.chatBody.scrollTop = this.$refs.chatBody.scrollHeight;
            },

            async fetchMessages() {
                if (this.messages.length === 0) this.isLoading = true;
                
                try {
                    const response = await fetch(`{{ url('get_doctor_chat_data') }}/${this.appointmentId}`);
                    if (!response.ok) throw new Error('Network response was not ok.');
                    const data = await response.json();
                    
                    // Only update the array if the content has actually changed.
                    // This prevents unnecessary screen redraws by Alpine.
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
                if (this.isLoading || (this.newMessage.trim() === '' && !this.attachment)) {
                    return;
                }

                this.isLoading = true;
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('apnt_id', this.appointmentId);
                formData.append('receiver_id', this.patientId);

                let url = this.attachment ? '{{ url('send_file_doctor') }}' : '{{ url('send_msg_doctor') }}';

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
                        document.getElementById('file-input').value = ''; // Reset file input
                        await this.fetchMessages(); // Refresh messages immediately
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
                
                // You could add validation here for file type or size if needed
                this.attachment = file;
                // Provide feedback to the user that a file is attached
                this.newMessage = file.name;
            }
        }
    }
</script>
@endsection
