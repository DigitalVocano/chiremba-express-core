@extends('user.layout')
@section('title', __('femtech.chw_title') . ' | Chiremba Express')

@section('content')
<div class="bg-gray-50 dark:bg-gray-900 min-h-screen py-8 text-gray-800 dark:text-gray-100">
    <div class="container mx-auto px-4">
        {{-- Top Banner --}}
        <div class="bg-primary-600 dark:bg-primary-700 text-white rounded-xl shadow-lg p-5 mb-6 flex flex-wrap items-center justify-between gap-4 border border-primary-500">
            <div>
                <span class="bg-white/20 text-white text-xs uppercase font-bold px-2.5 py-1 rounded-full">Pillar 3 • Rural & Low-Resource Edge Module</span>
                <h1 class="text-xl md:text-2xl font-bold mt-2 text-white">{{ __('femtech.chw_title') }}</h1>
                <p class="text-xs text-blue-100 mt-1">{{ __('femtech.offline_ready_badge') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div id="network-status-badge" class="bg-emerald-600 text-white text-xs font-bold px-3.5 py-2 rounded-lg flex items-center shadow-sm">
                    <span class="w-2.5 h-2.5 rounded-full bg-white mr-2"></span>
                    <span id="network-status-text">Online — Auto-Sync Active</span>
                </div>
                <button type="button" onclick="syncOfflineQueueNow()" class="bg-white text-primary-700 hover:bg-blue-50 text-xs font-bold px-4 py-2 rounded-lg shadow transition">
                    <i class="fas fa-sync-alt mr-1"></i> Sync Offline Queue (<span id="offline-queue-count">0</span>)
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Proxy Patient Intake Form (Works Online & Offline) --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    <i class="fas fa-clipboard-check text-primary-500 mr-2"></i> Proxy Patient Triage & Appointment Intake
                </h2>

                <form id="chw-intake-form" action="{{ route('chw.intake.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">CHW / Nurse Name *</label>
                            <input type="text" id="chw_name" name="chw_name" value="VHW Chipo Ndlovu" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Clinic / Rural Ward *</label>
                            <input type="text" id="chw_location_ward" name="chw_location_ward" value="Murehwa Ward 4 Rural Health Centre" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Patient Full Name *</label>
                            <input type="text" id="patient_full_name" name="patient_full_name" placeholder="e.g. Rudo Mutasa" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Patient / Next-of-Kin Phone</label>
                            <input type="text" id="patient_phone" name="patient_phone" placeholder="077..." class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Age (Years)</label>
                            <input type="number" id="patient_age" name="patient_age" placeholder="24" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-gray-50 dark:bg-gray-700/60 p-3.5 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pregnant?</label>
                            <select id="is_pregnant" name="is_pregnant" class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                                <option value="1">Yes (Antenatal)</option>
                                <option value="0">No / Postnatal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Gestational Wks</label>
                            <input type="number" id="gestational_weeks" name="gestational_weeks" value="28" class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Systolic BP</label>
                            <input type="number" id="systolic_bp" name="systolic_bp" placeholder="120" class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Diastolic BP</label>
                            <input type="number" id="diastolic_bp" name="diastolic_bp" placeholder="80" class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white p-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-2">Observed Danger Signs / Symptoms</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach(__('femtech.symptoms') as $code => $label)
                                <label class="flex items-center space-x-2 p-2.5 rounded border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/60 text-xs cursor-pointer">
                                    <input type="checkbox" name="symptoms[]" value="{{ $code }}" class="chw-symptom-checkbox text-primary-600">
                                    <span class="text-gray-800 dark:text-gray-200 font-medium">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-4 rounded-lg shadow text-sm transition">
                        <i class="fas fa-save mr-1.5"></i> Record Proxy Intake (Auto-Caches Offline if No Signal)
                    </button>
                </form>
            </div>

            {{-- Offline Sync Queue & Recent Proxy Intakes --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-2">
                        <i class="fas fa-hdd text-primary-500 mr-1.5"></i> Edge Offline Storage Queue
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                        Intakes recorded without cellular data are stored in IndexedDB/localStorage and evaluated by the embedded WHO rule engine.
                    </p>
                    <div id="offline-queue-list" class="space-y-2 text-xs text-gray-700 dark:text-gray-200"></div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-5 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">
                        <i class="fas fa-history text-primary-500 mr-1.5"></i> Recently Synced CHW Intakes
                    </h3>
                    <div class="space-y-2.5">
                        @forelse($recentIntakes as $intake)
                            <div class="p-3.5 rounded-lg border text-xs {{ in_array($intake->triage_risk_level, ['high', 'critical']) ? 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800/50 text-gray-900 dark:text-red-100' : 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-100' }}">
                                <div class="flex justify-between items-center font-bold">
                                    <span>{{ $intake->patient_full_name }}</span>
                                    <span class="uppercase px-2 py-0.5 rounded text-[10px] {{ in_array($intake->triage_risk_level, ['high', 'critical']) ? 'bg-red-600 text-white' : 'bg-green-100 text-green-800' }}">{{ $intake->triage_risk_level }}</span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mt-1">{{ $intake->chw_name }} • {{ $intake->chw_location_ward }}</p>
                                <p class="mt-1 font-medium">{{ $intake->triage_summary }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 dark:text-gray-400">No proxy intakes recorded yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const CHW_STORAGE_KEY = 'chiremba_chw_offline_queue_v1';

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw-chw-offline.js').catch(() => {});
    }

    function getOfflineQueue() {
        try {
            return JSON.parse(localStorage.getItem(CHW_STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveOfflineQueue(queue) {
        localStorage.setItem(CHW_STORAGE_KEY, JSON.stringify(queue));
        renderOfflineQueue();
    }

    function renderOfflineQueue() {
        const queue = getOfflineQueue();
        document.getElementById('offline-queue-count').innerText = queue.length;
        const container = document.getElementById('offline-queue-list');
        if (queue.length === 0) {
            container.innerHTML = '<div class="p-2.5 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-lg font-medium">All field intakes synchronized with cloud.</div>';
            return;
        }
        container.innerHTML = queue.map(item =>
            `<div class="p-2.5 bg-amber-50 dark:bg-gray-700 border border-amber-200 dark:border-amber-600 text-gray-900 dark:text-gray-100 rounded-lg">
                <strong>${item.patient_full_name}</strong> (Wk ${item.gestational_weeks || '-'}) — Queued Offline
            </div>`
        ).join('');
    }

    async function syncOfflineQueueNow() {
        const queue = getOfflineQueue();
        if (queue.length === 0 || !navigator.onLine) return;

        try {
            const response = await fetch("{{ url('/api/v1/femtech/chw/sync') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ intakes: queue })
            });
            if (response.ok) {
                saveOfflineQueue([]);
                window.location.reload();
            }
        } catch (e) {
            console.warn('Offline sync deferred until connectivity stabilizes.');
        }
    }

    document.getElementById('chw-intake-form').addEventListener('submit', function(e) {
        if (!navigator.onLine) {
            e.preventDefault();
            const checkedSymptoms = Array.from(document.querySelectorAll('.chw-symptom-checkbox:checked')).map(cb => cb.value);
            const record = {
                offline_uuid: 'chw-offline-' + Date.now() + '-' + Math.random().toString(36).substring(2, 8),
                chw_name: document.getElementById('chw_name').value,
                chw_location_ward: document.getElementById('chw_location_ward').value,
                patient_full_name: document.getElementById('patient_full_name').value,
                patient_phone: document.getElementById('patient_phone').value,
                patient_age: document.getElementById('patient_age').value,
                is_pregnant: document.getElementById('is_pregnant').value === '1',
                gestational_weeks: document.getElementById('gestational_weeks').value,
                systolic_bp: document.getElementById('systolic_bp').value,
                diastolic_bp: document.getElementById('diastolic_bp').value,
                symptoms: checkedSymptoms,
                synced_from_offline: true,
                client_recorded_at: new Date().toISOString()
            };
            const queue = getOfflineQueue();
            queue.push(record);
            saveOfflineQueue(queue);
            alert('No internet connection detected. Patient intake saved to local edge cache and will automatically sync when connectivity returns!');
            this.reset();
        }
    });

    window.addEventListener('online', () => {
        document.getElementById('network-status-badge').className = 'bg-emerald-600 text-white text-xs font-bold px-3.5 py-2 rounded-lg flex items-center shadow-sm';
        document.getElementById('network-status-text').innerText = 'Online — Auto-Sync Active';
        syncOfflineQueueNow();
    });

    window.addEventListener('offline', () => {
        document.getElementById('network-status-badge').className = 'bg-amber-600 text-white text-xs font-bold px-3.5 py-2 rounded-lg flex items-center shadow-sm';
        document.getElementById('network-status-text').innerText = 'Offline Mode — Caching Locally';
    });

    renderOfflineQueue();
</script>
@endsection
