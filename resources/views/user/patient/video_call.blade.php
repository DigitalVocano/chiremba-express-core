@extends('layouts.patient')

@section('title')
    {{ __('Video Consultation') }}
@stop

@section('content')
<div class="h-screen flex flex-col bg-gray-900">
    {{-- Header --}}
    <div class="bg-gray-800 px-6 py-4 flex justify-between items-center shadow-md z-10">
        <div class="text-white">
            <h1 class="text-lg font-semibold">{{ __('Consultation with') }} {{ $appointment->doctorls->name }}</h1>
            <p class="text-sm text-gray-400">{{ __('Appointment ID:') }} #{{ $appointment->id }}</p>
        </div>
        <div class="flex items-center space-x-4">
            <div id="call-timer" class="text-white font-mono text-xl bg-gray-700 px-3 py-1 rounded">00:00:00</div>
        </div>
    </div>

    {{-- Video Container --}}
    <div class="flex-grow relative overflow-hidden flex justify-center items-center bg-black">
        {{-- Remote Video (Doctor) --}}
        <div id="remote-player" class="w-full h-full absolute inset-0"></div>
        
        {{-- Waiting Message --}}
        <div id="waiting-message" class="absolute inset-0 flex items-center justify-center text-white z-0">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-4"></div>
                <p class="text-xl">{{ __('Waiting for doctor to join...') }}</p>
            </div>
        </div>

        {{-- Local Video (Patient) --}}
        <div id="local-player" class="absolute bottom-4 right-4 w-48 h-36 bg-gray-800 border-2 border-gray-600 rounded-lg shadow-lg overflow-hidden z-20"></div>
    </div>

    {{-- Controls --}}
    <div class="bg-gray-800 px-6 py-4 flex justify-center items-center space-x-6 z-10">
        <button id="mic-btn" class="p-4 rounded-full bg-gray-700 text-white hover:bg-gray-600 focus:outline-none transition-colors">
            <i class="fas fa-microphone"></i>
        </button>
        <button id="video-btn" class="p-4 rounded-full bg-gray-700 text-white hover:bg-gray-600 focus:outline-none transition-colors">
            <i class="fas fa-video"></i>
        </button>
        <button id="leave-btn" class="p-4 rounded-full bg-red-600 text-white hover:bg-red-700 focus:outline-none transition-colors">
            <i class="fas fa-phone-slash"></i>
        </button>
    </div>
</div>
@stop

@section('footer_scripts')
<script src="https://cdn.jsdelivr.net/npm/livekit-client/dist/livekit-client.umd.min.js"></script>
<script>
    const LIVEKIT_URL = "wss://linkit-6u57ihxm.livekit.cloud";
    const CHANNEL = "appointment_{{ $appointment->id }}";
    const TOKEN_URL = "{{ route('agora.token') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    let room;

    async function join() {
        // Fetch Token
        const response = await fetch(TOKEN_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ channel_name: CHANNEL })
        });
        const data = await response.json();
        
        if (data.error || !data.token) {
            alert("Failed to get token: " + (data.error || "No token returned"));
            return;
        }

        // Initialize LiveKit Room
        room = new LiveKitClient.Room();

        // Remote Participant Connected
        room.on(LiveKitClient.RoomEvent.TrackSubscribed, (track, publication, participant) => {
            document.getElementById('waiting-message').style.display = 'none';
            if (track.kind === 'video') {
                const remotePlayerContainer = document.getElementById("remote-player");
                remotePlayerContainer.innerHTML = ''; // Clear previous
                const element = track.attach();
                element.className = "w-full h-full object-cover";
                remotePlayerContainer.appendChild(element);
            } else if (track.kind === 'audio') {
                const element = track.attach();
                document.body.appendChild(element);
            }
        });

        // Remote Participant Disconnected
        room.on(LiveKitClient.RoomEvent.TrackUnsubscribed, (track, publication, participant) => {
            if (track.kind === 'video') {
                track.detach();
                document.getElementById("remote-player").innerHTML = '';
            }
        });

        room.on(LiveKitClient.RoomEvent.ParticipantDisconnected, (participant) => {
            document.getElementById('waiting-message').style.display = 'flex';
        });

        // Connect to Room
        await room.connect(data.url || LIVEKIT_URL, data.token);

        // Publish Local Tracks (Camera & Mic)
        await room.localParticipant.enableCameraAndMicrophone();

        // Play Local Video Preview
        const localVideoPublication = room.localParticipant.videoTrackPublications.values().next().value;
        if (localVideoPublication && localVideoPublication.track) {
            const localElement = localVideoPublication.track.attach();
            localElement.className = "w-full h-full object-cover";
            document.getElementById("local-player").appendChild(localElement);
        }

        startTimer();
    }

    // Controls
    let isMicOn = true;
    document.getElementById('mic-btn').onclick = async () => {
        isMicOn = !isMicOn;
        await room.localParticipant.setMicrophoneEnabled(isMicOn);
        document.getElementById('mic-btn').classList.toggle('bg-red-600', !isMicOn);
        document.getElementById('mic-btn').classList.toggle('bg-gray-700', isMicOn);
        document.getElementById('mic-btn').innerHTML = isMicOn ? '<i class="fas fa-microphone"></i>' : '<i class="fas fa-microphone-slash"></i>';
    };

    let isVideoOn = true;
    document.getElementById('video-btn').onclick = async () => {
        isVideoOn = !isVideoOn;
        await room.localParticipant.setCameraEnabled(isVideoOn);
        document.getElementById('video-btn').classList.toggle('bg-red-600', !isVideoOn);
        document.getElementById('video-btn').classList.toggle('bg-gray-700', isVideoOn);
        document.getElementById('video-btn').innerHTML = isVideoOn ? '<i class="fas fa-video"></i>' : '<i class="fas fa-video-slash"></i>';
    };

    document.getElementById('leave-btn').onclick = async () => {
        if (room) {
            await room.disconnect();
        }
        window.location.href = "{{ route('userdashboard') }}";
    };

    // Timer
    let seconds = 0;
    function startTimer() {
        setInterval(() => {
            seconds++;
            const date = new Date(0);
            date.setSeconds(seconds);
            document.getElementById('call-timer').innerText = date.toISOString().substr(11, 8);
        }, 1000);
    }

    join();
</script>
@stop