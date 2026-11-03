<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Prescription</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif; /* Better for special characters */
            color: #333;
            font-size: 10px;
            margin: 0;
        }
        .container {
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        .header-table {
            width: 100%;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
            width: 50%; /* Give columns a width to help with layout */
        }
        .doctor-details {
            text-align: left;
        }
        .doctor-name {
            font-size: 20px; /* Reduced from h1 default */
            font-weight: 900;
            color: #000;
            margin: 0 0 4px 0;
        }
        .doctor-specialty, .doctor-address {
            margin: 2px 0;
            font-size: 11px;
            color: #555;
        }
        .patient-details {
            text-align: right;
        }
        .patient-details p {
            margin: 2px 0;
            font-size: 11px;
        }
        .patient-details .label {
            font-weight: bold;
        }
        .prescription-section-header {
            margin-top: 25px;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
        }
        .rx-symbol {
            font-size: 28px; /* Reduced from 48px */
            font-weight: bold;
            vertical-align: middle;
            padding-right: 10px; /* Consistent unit */
        }
        .prescription-title {
            font-size: 18px; /* Reduced from 20px */
            font-weight: bold;
            vertical-align: middle;
        }
        .medicines-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .medicines-table th, .medicines-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }
        .medicines-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 10px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="doctor-details">
                    <div class="doctor-name">{{ $appointment->doctorls->name ?? 'Doctor Name' }}</div>
                    <div class="doctor-specialty">{{ $appointment->doctorls->department->name ?? 'Specialty' }}</div>
                    <div class="doctor-address">{{ $appointment->doctorls->address ?? 'Clinic Address' }}</div>
                </td>
                <td class="patient-details">
                    <p><span class="label">Patient:</span> {{ $appointment->patientls->name ?? 'Patient Name' }}</p>
                    <p><span class="label">Date:</span> {{ \Carbon\Carbon::parse($appointment->date)->format('F d, Y') }}</p>
                </td>
            </tr>
        </table>

        <div class="prescription-section-header">
            <span class="rx-symbol">R<sub>x</sub></span>
            <span class="prescription-title">Prescription</span>
        </div>

        <table class="medicines-table">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Type</th>
                    <th>Dosage</th>
                    <th>Duration</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($medicines) && count($medicines) > 0)
                    @foreach($medicines as $medicine)
                        <tr>
                            <td>{{ $medicine->medicine_name ?? '' }}</td>
                            <td>{{ $medicine->type ?? '' }}</td>
                            <td>{{ $medicine->dosage ?? '' }}</td>
                            <td>{{ $medicine->repeat_days ?? 'N/A' }} Days</td>
                            <td>                                
                                @if(isset($medicine->time) && is_array($medicine->time))
                                    <ul>
                                    @foreach($medicine->time as $time)
                                        <li style="font-size: 10px;">{{ is_object($time) ? ($time->t_time ?? '') : $time }}</li>
                                    @endforeach
                                    </ul>
                                @else
                                    N/A
                                @endif
                            </td>
                         </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="text-align: center;">No medicines prescribed for this appointment.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="footer">
            <p>This is a digitally generated prescription and does not require a signature.</p>
        </div>
    </div>
</body>
</html>