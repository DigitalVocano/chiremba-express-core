@extends('admin.layout')
@section('title')
{{("Developer Console & Diagnostics")}} | {{("Admin Dashboard")}}
@stop
@section('content')
@php
   $resolvedTab = $activeTab ?? 'crashlytics';
   if ($resolvedTab === 'overview') $resolvedTab = 'crashlytics';
   if ($resolvedTab === 'keys') $resolvedTab = 'api_keys';
   $isFirebaseReady = $firebaseConfigured ?? !empty($fcmClientEmail ?? null);
   $resolvedFirebaseEmail = $firebaseEmail ?? $fcmClientEmail ?? 'Configured';
@endphp

<style>
   /* Self-contained Developer Console styles (independent of Tailwind purge) */
   .dev-console-wrap {
      padding: 1.25rem;
      max-width: 1600px;
      margin: 0 auto;
   }
   .dev-hero {
      background: linear-gradient(135deg, #0A2B68 0%, #1E3A8A 55%, #065F46 100%);
      border-radius: 1rem;
      padding: 1.5rem;
      color: #ffffff;
      box-shadow: 0 10px 25px -5px rgba(10, 43, 104, 0.25);
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
   }
   .dev-kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
   }
   .dev-kpi-card {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 0.85rem;
      padding: 1rem 1.25rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04);
   }
   .dark .dev-kpi-card {
      background: #1f2937;
      border-color: #374151;
   }
   .dev-kpi-label {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: #6b7280;
   }
   .dark .dev-kpi-label {
      color: #9ca3af;
   }
   .dev-kpi-value {
      font-size: 1.65rem;
      font-weight: 800;
      margin-top: 0.25rem;
      line-height: 1.2;
      color: #111827;
   }
   .dark .dev-kpi-value {
      color: #f9fafb;
   }
   .dev-kpi-sub {
      font-size: 0.75rem;
      color: #6b7280;
      margin-top: 0.25rem;
   }
   .dev-tabs-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      border-bottom: 2px solid #e5e7eb;
      padding-bottom: 0.75rem;
      margin-bottom: 1.25rem;
   }
   .dark .dev-tabs-bar {
      border-color: #374151;
   }
   .dev-tab-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.6rem 1.1rem;
      border-radius: 0.75rem;
      font-size: 0.85rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.15s ease;
      border: 1px solid #d1d5db;
      background: #ffffff;
      color: #374151;
   }
   .dark .dev-tab-btn {
      background: #1f2937;
      border-color: #374151;
      color: #e5e7eb;
   }
   .dev-tab-btn:hover {
      background: #f3f4f6;
      border-color: #9ca3af;
   }
   .dark .dev-tab-btn:hover {
      background: #374151;
   }
   .dev-tab-btn.active {
      background: #0A2B68 !important;
      color: #ffffff !important;
      border-color: #0A2B68 !important;
      box-shadow: 0 4px 12px rgba(10, 43, 104, 0.25);
   }
   .dev-card {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 0.85rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      margin-bottom: 1.25rem;
      overflow: hidden;
   }
   .dark .dev-card {
      background: #1f2937;
      border-color: #374151;
   }
   .dev-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.4rem;
      padding: 0.5rem 1rem;
      border-radius: 0.6rem;
      font-size: 0.8rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.15s ease;
      text-decoration: none;
      border: 1px solid transparent;
      white-space: nowrap;
   }
   .dev-btn-primary {
      background: #0A2B68 !important;
      color: #ffffff !important;
      border-color: #0A2B68 !important;
   }
   .dev-btn-primary:hover {
      background: #1E3A8A !important;
   }
   .dev-btn-emerald {
      background: #059669 !important;
      color: #ffffff !important;
      border-color: #059669 !important;
   }
   .dev-btn-emerald:hover {
      background: #047857 !important;
   }
   .dev-btn-danger {
      background: #dc2626 !important;
      color: #ffffff !important;
      border-color: #dc2626 !important;
   }
   .dev-btn-danger:hover {
      background: #b91c1c !important;
   }
   .dev-btn-warning {
      background: #d97706 !important;
      color: #ffffff !important;
      border-color: #d97706 !important;
   }
   .dev-btn-outline {
      background: #f3f4f6 !important;
      color: #1f2937 !important;
      border: 1px solid #d1d5db !important;
   }
   .dark .dev-btn-outline {
      background: #374151 !important;
      color: #f3f4f6 !important;
      border-color: #4b5563 !important;
   }
   .dev-btn-outline:hover {
      background: #e5e7eb !important;
   }
   .dev-input, .dev-select {
      padding: 0.5rem 0.75rem;
      border-radius: 0.6rem;
      border: 1px solid #d1d5db;
      background: #f9fafb;
      color: #111827;
      font-size: 0.82rem;
   }
   .dark .dev-input, .dark .dev-select {
      background: #111827;
      border-color: #4b5563;
      color: #f9fafb;
   }
   .dev-input:focus, .dev-select:focus {
      outline: none;
      border-color: #0A2B68;
      box-shadow: 0 0 0 2px rgba(10, 43, 104, 0.15);
   }
   .dev-push-form-grid {
      display: grid;
      grid-template-columns: 2fr 1.2fr 1.5fr auto;
      gap: 0.85rem;
      align-items: end;
      margin-top: 1rem;
   }
   @media (max-width: 1024px) {
      .dev-push-form-grid {
         grid-template-columns: 1fr;
      }
   }
   .dev-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.82rem;
      text-align: left;
   }
   .dev-table th {
      background: #f9fafb;
      color: #4b5563;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 0.72rem;
      letter-spacing: 0.03em;
      padding: 0.85rem 1rem;
      border-bottom: 1px solid #e5e7eb;
   }
   .dark .dev-table th {
      background: #111827;
      color: #9ca3af;
      border-color: #374151;
   }
   .dev-table td {
      padding: 0.85rem 1rem;
      border-bottom: 1px solid #f3f4f6;
      vertical-align: top;
      color: #1f2937;
   }
   .dark .dev-table td {
      border-color: #374151;
      color: #e5e7eb;
   }
   .dev-table tr:hover td {
      background: rgba(10, 43, 104, 0.02);
   }
   .dev-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.2rem 0.6rem;
      border-radius: 9999px;
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
   }
   .dev-badge-red { background: #fee2e2; color: #991b1b; }
   .dev-badge-amber { background: #fef3c7; color: #92400e; }
   .dev-badge-green { background: #d1fae5; color: #065f46; }
   .dev-badge-blue { background: #dbeafe; color: #1e40af; }
   .dev-badge-purple { background: #ede9fe; color: #5b21b6; }
   .dev-badge-gray { background: #f3f4f6; color: #374151; }
   .dev-modal-backdrop {
      position: fixed;
      inset: 0;
      z-index: 9999;
      background: rgba(15, 23, 42, 0.72);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
   }
   .dev-modal-box {
      background: #ffffff;
      border-radius: 1rem;
      max-width: 900px;
      width: 100%;
      max-height: 85vh;
      display: flex;
      flex-direction: column;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
      overflow: hidden;
      border: 1px solid #e5e7eb;
   }
   .dark .dev-modal-box {
      background: #1f2937;
      border-color: #374151;
   }
   .dev-trace-pre {
      margin: 0;
      padding: 1.25rem;
      overflow-y: auto;
      background: #0f172a;
      color: #6ee7b7;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 0.78rem;
      line-height: 1.55;
      white-space: pre-wrap;
      word-break: break-word;
   }
</style>

<div class="dev-console-wrap" x-data="{
    activeTab: '{{ $resolvedTab }}',
    selectedTrace: null,
    selectedTraceTitle: '',
    testPushToken: '',
    openTrace(title, trace) {
        this.selectedTraceTitle = title;
        this.selectedTrace = trace;
    },
    selectTokenForTest(token) {
        this.testPushToken = token;
        this.activeTab = 'push';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}">

   <!-- Header Hero Card -->
   <div class="dev-hero">
      <div style="display: flex; align-items: center; gap: 1rem;">
         <div style="width: 3.25rem; height: 3.25rem; border-radius: 0.85rem; background: rgba(255,255,255,0.14); display: flex; align-items: center; justify-content: center; font-size: 1.65rem; color: #a3e635;">
            <i class="uil uil-bug"></i>
         </div>
         <div>
            <h1 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: #ffffff;">{{("Developer Console & Diagnostics")}}</h1>
            <p style="font-size: 0.82rem; color: rgba(255,255,255,0.85); margin: 0.25rem 0 0 0;">
               {{("Real-time mobile crash analytics, iOS/Android FCM & APNs push telemetry, user bug reports, Laravel server logs, and XAPI keys.")}}
            </p>
         </div>
      </div>
      <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.65rem;">
         @if($isFirebaseReady)
            <span style="padding: 0.45rem 0.85rem; border-radius: 0.65rem; background: rgba(16, 185, 129, 0.25); border: 1px solid rgba(110, 231, 183, 0.45); color: #ecfdf5; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem;">
               <span style="width: 8px; height: 8px; border-radius: 50%; background: #34d399; display: inline-block;"></span>
               Firebase Linked ({{ $resolvedFirebaseEmail }})
            </span>
         @else
            <span style="padding: 0.45rem 0.85rem; border-radius: 0.65rem; background: rgba(239, 68, 68, 0.3); border: 1px solid rgba(252, 165, 165, 0.5); color: #fef2f2; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 0.45rem;">
               <span style="width: 8px; height: 8px; border-radius: 50%; background: #f87171; display: inline-block;"></span>
               Firebase Credentials Missing
            </span>
         @endif
         <a href="{{ url('/developers/api') }}" target="_blank" class="dev-btn" style="background: rgba(255,255,255,0.16); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
            <i class="uil uil-external-link-alt"></i>
            <span>{{("Public API Docs")}}</span>
         </a>
      </div>
   </div>

   <!-- Flash Alerts -->
   @if(session('success'))
      <div style="padding: 0.9rem 1.15rem; margin-bottom: 1.25rem; border-radius: 0.75rem; background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; font-size: 0.85rem; font-weight: 600;">
         <i class="uil uil-check-circle"></i> {{ session('success') }}
      </div>
   @endif
   @if(session('error'))
      <div style="padding: 0.9rem 1.15rem; margin-bottom: 1.25rem; border-radius: 0.75rem; background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; font-size: 0.85rem; font-weight: 600;">
         <i class="uil uil-exclamation-triangle"></i> {{ session('error') }}
      </div>
   @endif

   <!-- KPI Summary Strip -->
   <div class="dev-kpi-grid">
      <div class="dev-kpi-card">
         <div class="dev-kpi-label">Total App Logs</div>
         <div class="dev-kpi-value">{{ number_format($stats['total_logs'] ?? (method_exists($diagLogs ?? null, 'total') ? $diagLogs->total() : count($diagLogs ?? []))) }}</div>
         <div class="dev-kpi-sub">Mobile Telemetry & Events</div>
      </div>
      <div class="dev-kpi-card">
         <div class="dev-kpi-label" style="color: #dc2626;">Unresolved Crashes</div>
         <div class="dev-kpi-value" style="color: #dc2626;">{{ number_format($stats['crashes_24h'] ?? $crashCount ?? 0) }}</div>
         <div class="dev-kpi-sub">Uncaught & Fatal Exceptions</div>
      </div>
      <div class="dev-kpi-card">
         <div class="dev-kpi-label" style="color: #d97706;">Push Errors</div>
         <div class="dev-kpi-value" style="color: #d97706;">{{ number_format($stats['push_errors_24h'] ?? $fcmErrorCount ?? 0) }}</div>
         <div class="dev-kpi-sub">FCM / APNs Warnings & Errors</div>
      </div>
      <div class="dev-kpi-card">
         <div class="dev-kpi-label" style="color: #4f46e5;">iOS Telemetry</div>
         <div class="dev-kpi-value" style="color: #4f46e5;">{{ number_format($stats['ios_logs'] ?? $iosEventCount ?? 0) }}</div>
         <div class="dev-kpi-sub">{{ $stats['ios_tokens'] ?? 0 }} Registered iOS Devices</div>
      </div>
      <div class="dev-kpi-card">
         <div class="dev-kpi-label" style="color: #059669;">Android Tokens</div>
         <div class="dev-kpi-value" style="color: #059669;">{{ number_format($stats['android_tokens'] ?? count($tokens ?? [])) }}</div>
         <div class="dev-kpi-sub">Active FCM Devices</div>
      </div>
      <div class="dev-kpi-card">
         <div class="dev-kpi-label" style="color: #7c3aed;">User Bug Reports</div>
         <div class="dev-kpi-value" style="color: #7c3aed;">{{ number_format($stats['bug_reports'] ?? count($bugReports ?? [])) }}</div>
         <div class="dev-kpi-sub">In-App Submissions</div>
      </div>
   </div>

   <!-- Navigation Tabs -->
   <div class="dev-tabs-bar">
      <button type="button" @click="activeTab = 'crashlytics'" :class="activeTab === 'crashlytics' ? 'dev-tab-btn active' : 'dev-tab-btn'">
         <i class="uil uil-bolt-alt"></i>
         <span>Crashlytics & Mobile Logs ({{ method_exists($diagLogs ?? null, 'total') ? $diagLogs->total() : count($diagLogs ?? []) }})</span>
      </button>
      <button type="button" @click="activeTab = 'push'" :class="activeTab === 'push' ? 'dev-tab-btn active' : 'dev-tab-btn'">
         <i class="uil uil-bell"></i>
         <span>iOS & Android Push Tester ({{ count($tokens ?? []) }})</span>
      </button>
      <button type="button" @click="activeTab = 'bugs'" :class="activeTab === 'bugs' ? 'dev-tab-btn active' : 'dev-tab-btn'">
         <i class="uil uil-comment-exclamation"></i>
         <span>User Bug Reports ({{ count($bugReports ?? []) }})</span>
      </button>
      <button type="button" @click="activeTab = 'server_logs'" :class="activeTab === 'server_logs' ? 'dev-tab-btn active' : 'dev-tab-btn'">
         <i class="uil uil-server"></i>
         <span>Laravel Server Logs ({{ count($serverLogs ?? []) }})</span>
      </button>
      <button type="button" @click="activeTab = 'api_keys'" :class="activeTab === 'api_keys' ? 'dev-tab-btn active' : 'dev-tab-btn'">
         <i class="uil uil-key-skeleton"></i>
         <span>Developer API Keys (XAPI)</span>
      </button>
   </div>

   <!-- TAB 1: CRASHLYTICS & MOBILE LOGS -->
   <div x-show="activeTab === 'crashlytics'">
      <div class="dev-card" style="padding: 1rem;">
         <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem;">
            <form action="{{ url('/admin/developers') }}" method="GET" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
               <input type="hidden" name="tab" value="crashlytics">
               <select name="category" class="dev-select">
                  <option value="">All Categories</option>
                  <option value="crash" {{ request('category') === 'crash' ? 'selected' : '' }}>Crash / Exception</option>
                  <option value="fcm" {{ request('category') === 'fcm' ? 'selected' : '' }}>FCM / APNs Push</option>
                  <option value="api_error" {{ request('category') === 'api_error' ? 'selected' : '' }}>API / Network Error</option>
                  <option value="auth" {{ request('category') === 'auth' ? 'selected' : '' }}>Auth / Login</option>
                  <option value="bug_report" {{ request('category') === 'bug_report' ? 'selected' : '' }}>User Bug Report</option>
               </select>
               <select name="platform" class="dev-select">
                  <option value="">All Platforms</option>
                  <option value="ios" {{ request('platform') === 'ios' ? 'selected' : '' }}>iOS (iPhone/iPad)</option>
                  <option value="android" {{ request('platform') === 'android' ? 'selected' : '' }}>Android</option>
                  <option value="admin_test" {{ request('platform') === 'admin_test' ? 'selected' : '' }}>Admin Test</option>
               </select>
               <select name="level" class="dev-select">
                  <option value="">All Severities</option>
                  <option value="critical" {{ request('level') === 'critical' ? 'selected' : '' }}>Critical / Fatal</option>
                  <option value="error" {{ request('level') === 'error' ? 'selected' : '' }}>Error</option>
                  <option value="warning" {{ request('level') === 'warning' ? 'selected' : '' }}>Warning</option>
                  <option value="info" {{ request('level') === 'info' ? 'selected' : '' }}>Info</option>
               </select>
               <input type="text" name="log_search" value="{{ request('log_search') }}" placeholder="Search message, device, user..." class="dev-input" style="min-width: 220px;">
               <button type="submit" class="dev-btn dev-btn-primary">
                  <i class="uil uil-filter"></i> Filter Logs
               </button>
               <a href="{{ url('/admin/developers?tab=crashlytics') }}" class="dev-btn dev-btn-outline">Reset</a>
            </form>

            <form action="{{ url('/admin/developers/clear-logs') }}" method="POST" onsubmit="return confirm('Clear mobile diagnostic logs?')">
               @csrf
               <button type="submit" class="dev-btn dev-btn-danger">
                  <i class="uil uil-trash-alt"></i> Clear Telemetry Logs
               </button>
            </form>
         </div>
      </div>

      <div class="dev-card">
         <div style="overflow-x: auto;">
            <table class="dev-table">
               <thead>
                  <tr>
                     <th>Time</th>
                     <th>Level / Category</th>
                     <th>Platform & Device</th>
                     <th>User</th>
                     <th>Event & Diagnostic Details</th>
                     <th style="text-align: right;">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($diagLogs as $log)
                     <tr>
                        <td style="white-space: nowrap;">
                           <div style="font-weight: 700;">{{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '' }}</div>
                           <div style="font-size: 0.72rem; color: #6b7280;">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</div>
                        </td>
                        <td style="white-space: nowrap;">
                           @php
                              $lvlBadge = match(strtolower($log->level ?? 'info')) {
                                 'fatal', 'critical', 'error' => 'dev-badge-red',
                                 'warning' => 'dev-badge-amber',
                                 default => 'dev-badge-green',
                              };
                           @endphp
                           <span class="dev-badge {{ $lvlBadge }}">{{ $log->level }}</span>
                           <div style="margin-top: 0.3rem; font-family: monospace; font-size: 0.75rem; color: #6b7280;">{{ $log->category }}</div>
                        </td>
                        <td>
                           <div style="font-weight: 700; text-transform: uppercase;">
                              @if(strtolower($log->platform ?? '') === 'ios')
                                 <span style="color: #4f46e5;"><i class="uil uil-apple"></i> iOS</span>
                              @elseif(strtolower($log->platform ?? '') === 'android')
                                 <span style="color: #059669;"><i class="uil uil-android"></i> Android</span>
                              @else
                                 <span><i class="uil uil-server"></i> {{ $log->platform ?: 'Server' }}</span>
                              @endif
                              @if($log->app_version)
                                 <span style="font-size: 0.72rem; font-weight: 400; color: #6b7280;">v{{ $log->app_version }}</span>
                              @endif
                           </div>
                           <div style="font-size: 0.75rem; color: #6b7280;">{{ $log->device_model ?: $log->os_version ?: '—' }}</div>
                           @if($log->permission_status)
                              <div style="font-size: 0.72rem; color: #4f46e5; font-weight: 600;">Perm: {{ $log->permission_status }}</div>
                           @endif
                        </td>
                        <td>
                           @if($log->user_id || $log->user_name)
                              <div style="font-weight: 700;">{{ $log->user_name ?: 'User #'.$log->user_id }}</div>
                              <div style="font-size: 0.72rem; color: #6b7280;">{{ ucfirst($log->user_role ?: 'user') }} (ID: {{ $log->user_id ?: 'N/A' }})</div>
                           @else
                              <span style="color: #9ca3af;">Anonymous / System</span>
                           @endif
                        </td>
                        <td style="max-width: 480px;">
                           <div style="font-weight: 700;">{{ $log->title }}</div>
                           <div style="font-size: 0.8rem; color: #4b5563; margin-top: 0.2rem; word-break: break-word;">{{ \Illuminate\Support\Str::limit($log->message, 260) }}</div>
                           @if($log->fcm_token || $log->apns_token)
                              <div style="margin-top: 0.4rem; display: flex; flex-wrap: wrap; gap: 0.4rem; font-family: monospace; font-size: 0.7rem;">
                                 @if($log->fcm_token)
                                    <span class="dev-badge dev-badge-gray">FCM: {{ substr($log->fcm_token, 0, 20) }}...</span>
                                 @endif
                                 @if($log->apns_token)
                                    <span class="dev-badge dev-badge-purple">APNs: {{ substr($log->apns_token, 0, 20) }}...</span>
                                 @endif
                              </div>
                           @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                           <div style="display: inline-flex; gap: 0.4rem;">
                              @if($log->stack_trace || $log->metadata)
                                 <button type="button"
                                    @click="openTrace(@js($log->title ?: 'Diagnostic Details'), @js(($log->stack_trace ? $log->stack_trace . "\n\n" : '') . ($log->metadata ? 'Metadata: ' . (is_string($log->metadata) ? $log->metadata : json_encode($log->metadata, JSON_PRETTY_PRINT)) : '')))"
                                    class="dev-btn dev-btn-outline">
                                    <i class="uil uil-file-search-alt"></i> Inspect Trace
                                 </button>
                              @endif
                              @if($log->fcm_token)
                                 <button type="button"
                                    @click="selectTokenForTest(@js($log->fcm_token))"
                                    class="dev-btn dev-btn-emerald">
                                    <i class="uil uil-message"></i> Test Push
                                 </button>
                              @endif
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="6" style="padding: 2.5rem; text-align: center; color: #6b7280;">
                           No mobile diagnostic or crash logs matching the current filter.
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
         @if(method_exists($diagLogs, 'hasPages') && $diagLogs->hasPages())
            <div style="padding: 1rem; border-top: 1px solid #e5e7eb;">
               {{ $diagLogs->links() }}
            </div>
         @endif
      </div>
   </div>

   <!-- TAB 2: iOS & ANDROID PUSH DIAGNOSTICS -->
   <div x-show="activeTab === 'push'" style="display: none;">
      <div class="dev-card" style="padding: 1.25rem;">
         <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 1rem; font-weight: 800;">
            <i class="uil uil-rocket" style="color: #059669; font-size: 1.25rem;"></i>
            <span>Live FCM / APNs Push Notification Tester</span>
         </div>
         <p style="font-size: 0.8rem; color: #6b7280; margin: 0.25rem 0 0 0;">
            Send a high-priority FCM HTTP v1 push notification (<code>apns-priority: 10</code>, <code>mutable-content: 1</code>, <code>content-available: 1</code>) directly to any device token and inspect the exact Google/Apple APNs response.
         </p>

         <form action="{{ url('/admin/developers/test-push') }}" method="POST" class="dev-push-form-grid">
            @csrf
            <div>
               <label style="display: block; font-size: 0.76rem; font-weight: 700; margin-bottom: 0.3rem;">Target FCM Registration Token</label>
               <input type="text" name="token" x-model="testPushToken" required placeholder="Paste FCM token or click 'Test Push' on any device row below..." class="dev-input" style="width: 100%; font-family: monospace;">
            </div>
            <div>
               <label style="display: block; font-size: 0.76rem; font-weight: 700; margin-bottom: 0.3rem;">Notification Title</label>
               <input type="text" name="title" value="Chiremba Push Diagnostic" class="dev-input" style="width: 100%;">
            </div>
            <div>
               <label style="display: block; font-size: 0.76rem; font-weight: 700; margin-bottom: 0.3rem;">Notification Body</label>
               <input type="text" name="body" value="Your iOS/Android push notifications are working!" class="dev-input" style="width: 100%;">
            </div>
            <div>
               <button type="submit" class="dev-btn dev-btn-emerald" style="padding: 0.6rem 1.25rem;">
                  <i class="uil uil-message"></i> Send Test Push
               </button>
            </div>
         </form>
      </div>

      <div class="dev-card">
         <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem;">
            <div>
               <h3 style="font-size: 0.95rem; font-weight: 800; margin: 0;">Registered Device Tokens (<code>savetoken</code> &amp; Firebase RTDB)</h3>
               <p style="font-size: 0.78rem; color: #6b7280; margin: 0.2rem 0 0 0;">Displays linked Patients, Doctors &amp; Pharmacies first, followed by unauthenticated guest installs.</p>
            </div>
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
               <form action="{{ url('/admin/developers') }}" method="GET" style="display: flex; align-items: center; gap: 0.4rem;">
                  <input type="hidden" name="tab" value="push">
                  <input type="text" name="token_search" value="{{ request('token_search') }}" placeholder="Search user name, phone, email, token..." class="dev-input" style="min-width: 240px;">
                  <button type="submit" class="dev-btn dev-btn-primary">
                     <i class="uil uil-search"></i> Search
                  </button>
                  @if(request('token_search'))
                     <a href="{{ url('/admin/developers?tab=push') }}" class="dev-btn dev-btn-outline">Reset</a>
                  @endif
               </form>
               <a href="{{ url('/admin/developers?tab=push&sync_tokens=1') }}" class="dev-btn dev-btn-emerald" title="Pull and link all user tokens from Firebase Realtime Database">
                  <i class="uil uil-sync"></i> Sync Users from Firebase
               </a>
            </div>
         </div>
         <div style="overflow-x: auto;">
            <table class="dev-table">
               <thead>
                  <tr>
                     <th>User / Owner</th>
                     <th>Role</th>
                     <th>Platform &amp; Device</th>
                     <th>Permission</th>
                     <th>APNs Token (iOS)</th>
                     <th>FCM Token</th>
                     <th>Last Synced</th>
                     <th style="text-align: right;">Action</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($tokens as $t)
                     <tr>
                        <td>
                           <div style="font-weight: 700;">{{ $t->owner_name ?? ('User #' . ($t->user_id ?: 'Guest')) }}</div>
                           <div style="font-size: 0.72rem; color: #6b7280;">
                              @if(!empty($t->doctor_id))
                                 Doctor ID: #{{ $t->doctor_id }}
                              @elseif(!empty($t->user_id))
                                 Patient ID: #{{ $t->user_id }}
                              @else
                                 Record #{{ $t->id }} (Pre-login Guest)
                              @endif
                              @if(!empty($t->owner_contact)) &bull; {{ $t->owner_contact }} @endif
                           </div>
                        </td>
                        <td>
                           @php
                              $roleName = $t->owner_role ?? (!empty($t->doctor_id) ? 'Doctor' : (!empty($t->user_id) ? 'Patient' : 'Guest'));
                              $roleBadge = match($roleName) {
                                 'Doctor' => 'dev-badge-blue',
                                 'Pharmacy' => 'dev-badge-purple',
                                 'Patient' => 'dev-badge-green',
                                 default => 'dev-badge-gray',
                              };
                           @endphp
                           <span class="dev-badge {{ $roleBadge }}">{{ $roleName }}</span>
                        </td>
                        <td>
                           <div style="font-weight: 700; text-transform: uppercase;">
                              @if(strtolower($t->platform ?? '') === 'ios')
                                 <span style="color: #4f46e5;"><i class="uil uil-apple"></i> iOS</span>
                              @elseif(strtolower($t->platform ?? '') === 'android')
                                 <span style="color: #059669;"><i class="uil uil-android"></i> Android</span>
                              @else
                                 <span style="color: #6b7280;">{{ $t->platform ?: 'Android / Legacy' }}</span>
                              @endif
                              @if($t->app_version)
                                 <span style="font-size: 0.72rem; font-weight: 400; color: #6b7280;">v{{ $t->app_version }}</span>
                              @endif
                           </div>
                           <div style="font-size: 0.75rem; color: #6b7280;">{{ $t->device_model ?: '—' }}</div>
                        </td>
                        <td>
                           @if($t->permission_status)
                              <span class="dev-badge {{ $t->permission_status === 'authorized' ? 'dev-badge-green' : 'dev-badge-amber' }}">
                                 {{ $t->permission_status }}
                              </span>
                           @else
                              <span style="color: #9ca3af;">—</span>
                           @endif
                        </td>
                        <td style="font-family: monospace; font-size: 0.75rem;">
                           @if($t->apns_token)
                              <span style="color: #4f46e5; font-weight: 600;" title="{{ $t->apns_token }}">{{ substr($t->apns_token, 0, 20) }}...</span>
                           @elseif(strtolower($t->platform ?? '') === 'ios')
                              <span class="dev-badge dev-badge-red">Missing APNs Token</span>
                           @else
                              <span style="color: #9ca3af;">N/A (Android)</span>
                           @endif
                        </td>
                        <td style="font-family: monospace; font-size: 0.75rem; color: #059669;" title="{{ $t->token }}">
                           {{ $t->token ? substr($t->token, 0, 28) . '...' : 'Empty' }}
                        </td>
                        <td style="color: #6b7280; font-size: 0.78rem;">
                           {{ $t->updated_at ? $t->updated_at->diffForHumans() : '—' }}
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                           @if($t->token)
                              <button type="button" @click="selectTokenForTest(@js($t->token))" class="dev-btn dev-btn-primary">
                                 <i class="uil uil-message"></i> Test Push
                              </button>
                           @endif
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="8" style="padding: 2rem; text-align: center; color: #6b7280;">No tokens found.</td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- TAB 3: USER BUG REPORTS -->
   <div x-show="activeTab === 'bugs'" style="display: none;">
      <div class="dev-card">
         <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb;">
            <h3 style="font-size: 0.95rem; font-weight: 800; margin: 0;">In-App User Bug & Issue Reports (<code>reportspam</code>)</h3>
            <p style="font-size: 0.78rem; color: #6b7280; margin: 0.2rem 0 0 0;">Reports submitted directly by patients, doctors, and pharmacies from the mobile app.</p>
         </div>
         <div style="overflow-x: auto;">
            <table class="dev-table">
               <thead>
                  <tr>
                     <th>Report ID</th>
                     <th>User</th>
                     <th>Subject / Title</th>
                     <th>Description</th>
                     <th>Submitted At</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($bugReports as $bug)
                     <tr>
                        <td style="font-weight: 700;">#{{ $bug->id }}</td>
                        <td>
                           <div style="font-weight: 700;">{{ $bug->user_name ?? ('User #' . ($bug->user_id ?: 'Guest')) }}</div>
                           @if(!empty($bug->user_contact))
                              <div style="font-size: 0.72rem; color: #6b7280;">{{ $bug->user_contact }}</div>
                           @endif
                        </td>
                        <td style="font-weight: 700;">{{ $bug->title }}</td>
                        <td style="max-width: 480px; word-break: break-word;">{{ $bug->description }}</td>
                        <td style="color: #6b7280;">{{ $bug->created_at ? $bug->created_at->format('Y-m-d H:i') : '—' }}</td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="5" style="padding: 2.5rem; text-align: center; color: #6b7280;">
                           No user bug reports submitted yet.
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- TAB 4: LARAVEL SERVER LOGS -->
   <div x-show="activeTab === 'server_logs'" style="display: none;">
      <div class="dev-card" style="padding: 1rem 1.25rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
         <div>
            <h3 style="font-size: 0.95rem; font-weight: 800; margin: 0;">Laravel Application Log (<code>storage/logs/laravel.log</code>)</h3>
            <p style="font-size: 0.78rem; color: #6b7280; margin: 0.2rem 0 0 0;">Showing the latest {{ count($serverLogs ?? []) }} entries from the backend server log.</p>
         </div>
         <form action="{{ url('/admin/developers/clear-server-logs') }}" method="POST" onsubmit="return confirm('Truncate storage/logs/laravel.log and prune Telescope bloat?')">
            @csrf
            <button type="submit" class="dev-btn dev-btn-danger">
               <i class="uil uil-trash-alt"></i> Clear Server Log & Telescope Bloat
            </button>
         </form>
      </div>

      <div class="dev-card">
         <div style="overflow-x: auto;">
            <table class="dev-table">
               <thead>
                  <tr>
                     <th>Timestamp</th>
                     <th>Level</th>
                     <th>Message</th>
                     <th style="text-align: right;">Stack Trace</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($serverLogs as $sLog)
                     <tr>
                        <td style="white-space: nowrap; font-family: monospace; font-size: 0.78rem; color: #6b7280;">{{ $sLog['timestamp'] }}</td>
                        <td style="white-space: nowrap;">
                           @php
                              $lvl = strtoupper($sLog['level'] ?? 'INFO');
                              $sBadge = in_array($lvl, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])
                                 ? 'dev-badge-red'
                                 : ($lvl === 'WARNING' ? 'dev-badge-amber' : 'dev-badge-blue');
                           @endphp
                           <span class="dev-badge {{ $sBadge }}">{{ $lvl }}</span>
                        </td>
                        <td style="font-family: monospace; font-size: 0.76rem; word-break: break-all; max-width: 680px;">
                           {{ \Illuminate\Support\Str::limit($sLog['message'], 320) }}
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                           @php $sTrace = $sLog['trace'] ?? $sLog['context'] ?? ''; @endphp
                           @if(!empty($sTrace))
                              <button type="button" @click="openTrace(@js($sLog['timestamp'] . ' [' . $sLog['level'] . ']'), @js($sLog['message'] . "\n\n" . $sTrace))" class="dev-btn dev-btn-outline">
                                 <i class="uil uil-file-search-alt"></i> View Trace
                              </button>
                           @endif
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="4" style="padding: 2.5rem; text-align: center; color: #6b7280;">Server log file is currently empty.</td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- TAB 5: DEVELOPER API KEYS (XAPI) -->
   <div x-show="activeTab === 'api_keys'" style="display: none;">
      <div class="dev-card" style="padding: 1rem;">
         <form action="{{ url('/admin/developers') }}" method="GET" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
            <input type="hidden" name="tab" value="api_keys">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by app, developer, or API key..." class="dev-input" style="min-width: 280px;">
            <button type="submit" class="dev-btn dev-btn-primary">
               <i class="uil uil-search"></i> Search
            </button>
         </form>
      </div>

      <div class="dev-card">
         <div style="overflow-x: auto;">
            <table class="dev-table">
               <thead>
                  <tr>
                     <th>App & Developer</th>
                     <th>API Key</th>
                     <th>Rate Limit</th>
                     <th>Status</th>
                     <th>Last Activity</th>
                     <th style="text-align: right;">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  @forelse($keys as $key)
                     <tr>
                        <td>
                           <div style="font-weight: 700;">{{ $key->app_name }}</div>
                           <div style="font-size: 0.75rem; color: #6b7280;">{{ $key->developer_name }} &bull; {{ $key->developer_email }}</div>
                        </td>
                        <td style="font-family: monospace; font-size: 0.78rem; color: #059669;">
                           {{ substr($key->api_key, 0, 18) }}...
                        </td>
                        <td>{{ $key->rate_limit_per_minute }} req/min</td>
                        <td>
                           @if($key->is_active)
                              <span class="dev-badge dev-badge-green">Active</span>
                           @else
                              <span class="dev-badge dev-badge-red">Suspended</span>
                           @endif
                        </td>
                        <td style="color: #6b7280;">
                           {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never used' }}
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                           <div style="display: inline-flex; align-items: center; gap: 0.4rem;">
                              <form action="{{ url('/admin/developers/' . $key->id . '/toggle') }}" method="POST" style="display: inline;">
                                 @csrf
                                 <button type="submit" class="dev-btn {{ $key->is_active ? 'dev-btn-warning' : 'dev-btn-emerald' }}">
                                    {{ $key->is_active ? 'Suspend' : 'Activate' }}
                                 </button>
                              </form>
                              <form action="{{ url('/admin/developers/' . $key->id . '/regenerate') }}" method="POST" style="display: inline;" onsubmit="return confirm('Regenerate secret key for this app?')">
                                 @csrf
                                 <button type="submit" title="Regenerate Secret Key" class="dev-btn dev-btn-outline">
                                    <i class="uil uil-sync"></i> Regenerate
                                 </button>
                              </form>
                              <form action="{{ url('/admin/developers/' . $key->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Revoke and delete this API key permanently?')">
                                 @csrf
                                 @method('DELETE')
                                 <button type="submit" title="Revoke Key" class="dev-btn dev-btn-danger">
                                    <i class="uil uil-trash-alt"></i>
                                 </button>
                              </form>
                           </div>
                        </td>
                     </tr>
                  @empty
                     <tr>
                        <td colspan="6" style="padding: 2.5rem; text-align: center; color: #6b7280;">
                           No developer API keys found.
                        </td>
                     </tr>
                  @endforelse
               </tbody>
            </table>
         </div>
      </div>
   </div>

   <!-- Stack Trace & Metadata Inspector Modal -->
   <div x-show="selectedTrace !== null" style="display: none;" class="dev-modal-backdrop" @keydown.escape.window="selectedTrace = null">
      <div class="dev-modal-box" @click.outside="selectedTrace = null">
         <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 0.9rem; font-weight: 800; margin: 0; font-family: monospace;" x-text="selectedTraceTitle"></h3>
            <button type="button" @click="selectedTrace = null" class="dev-btn dev-btn-outline" style="padding: 0.3rem 0.6rem;">
               <i class="uil uil-times"></i> Close
            </button>
         </div>
         <pre class="dev-trace-pre" x-text="selectedTrace"></pre>
      </div>
   </div>
</div>
@endsection
