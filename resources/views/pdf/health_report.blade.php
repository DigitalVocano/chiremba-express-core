<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Medical Consultation & Health Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-title {
            font-size: 22px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
        }
        .header-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 6px 10px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 25%;
        }
        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .card {
            background-color: #f1f5f9;
            border-left: 4px solid #2563eb;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .doctor-item, .pharmacy-item {
            border: 1px solid #e2e8f0;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            background-color: #ffffff;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: bold;
            color: #ffffff;
            background-color: #2563eb;
            border-radius: 10px;
        }
        .disclaimer {
            margin-top: 30px;
            padding: 10px;
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 11px;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="header-title">Chiremba Express</h1>
        <div class="header-subtitle">AI Health Assistant (VHT) Medical Assessment Report</div>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Patient Name:</td>
            <td>{{ $patient->name ?? 'Valued Patient' }}</td>
            <td class="meta-label">Report ID:</td>
            <td>#RPT-{{ $session->id ?? rand(1000,9999) }}</td>
        </tr>
        <tr>
            <td class="meta-label">Phone / Contact:</td>
            <td>{{ $patient->phone ?? $patient->phoneno ?? 'N/A' }}</td>
            <td class="meta-label">Date & Time:</td>
            <td>{{ now()->format('d M Y, h:i A') }}</td>
        </tr>
    </table>

    @if(!empty($assessmentData))
        <div class="section-title">AI Clinical Assessment Summary</div>
        <div class="card">
            @if(isset($assessmentData['assessment']))
                <p><strong>Primary Symptoms & Evaluation:</strong><br>{{ $assessmentData['assessment'] }}</p>
            @endif
            @if(isset($assessmentData['recommendation']))
                <p><strong>Clinical Recommendation:</strong><br>{{ $assessmentData['recommendation'] }}</p>
            @endif
            @if(isset($assessmentData['reasoning']))
                <p><strong>Medical Reasoning:</strong><br>{{ $assessmentData['reasoning'] }}</p>
            @endif
        </div>
    @endif

    @if(!empty($recommendedDoctors) && count($recommendedDoctors) > 0)
        <div class="section-title">Recommended Medical Specialists</div>
        @foreach($recommendedDoctors as $doctor)
            <div class="doctor-item">
                <strong>Dr. {{ $doctor['name'] ?? $doctor->name }}</strong> 
                @if(!empty($doctor['department_name'] ?? $doctor->department_name))
                    <span class="badge">{{ $doctor['department_name'] ?? $doctor->department_name }}</span>
                @endif
                <br>
                <small style="color: #64748b;">
                    Address: {{ $doctor['address'] ?? $doctor->address ?? 'Harare, Zimbabwe' }} | 
                    Rating: ⭐ {{ $doctor['rating'] ?? '4.8' }} | 
                    Consultation Fee: ${{ $doctor['consultation_fee'] ?? $doctor->fee ?? '20' }}
                </small>
            </div>
        @endforeach
    @endif

    @if(!empty($recommendedPharmacies) && count($recommendedPharmacies) > 0)
        <div class="section-title">Nearby Pharmacies & Suggested Supplies</div>
        @foreach($recommendedPharmacies as $pharmacy)
            <div class="pharmacy-item">
                <strong>{{ $pharmacy['name'] ?? $pharmacy->name }}</strong><br>
                <small style="color: #64748b;">
                    Address: {{ $pharmacy['address'] ?? $pharmacy->address ?? 'Harare' }}
                </small>
            </div>
        @endforeach
    @endif

    <div class="section-title">Consultation Transcript Log</div>
    <div style="background-color: #fafafa; border: 1px solid #f1f5f9; padding: 10px; border-radius: 4px;">
        @foreach($session->messages as $msg)
            @if(!empty($msg->content) && !$msg->is_final_report)
                <p style="margin-bottom: 8px;">
                    <strong style="color: {{ $msg->role == 'user' ? '#2563eb' : '#059669' }};">
                        {{ ucfirst($msg->role) }}:
                    </strong> 
                    {!! nl2br(e(str_replace(" (Image attached)", "", $msg->content))) !!}
                </p>
            @endif
        @endforeach
    </div>

    <div class="disclaimer">
        <strong>MEDICAL DISCLAIMER:</strong> This report is generated by Chiremba Express AI Health Assistant (VHT) for informational and preliminary triage purposes only. It does not constitute a formal diagnosis or prescription. If you are experiencing severe pain or a medical emergency, please visit the nearest hospital immediately.
    </div>

    <div class="footer">
        Generated automatically by Chiremba Express Telehealth System &bull; www.chirembaexpress.co.zw
    </div>

</body>
</html>
