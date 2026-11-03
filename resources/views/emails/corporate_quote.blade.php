<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Corporate Quote Request</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Corporate Quote Request from Chiremba Express Website</h2>
        <p>You have received a new quote request with the following details:</p>
        <ul>
            <li><strong>Name:</strong> {{ $data['name'] }}</li>
            <li><strong>Email:</strong> {{ $data['email'] }}</li>
            <li><strong>Company Name:</strong> {{ $data['company_name'] }}</li>
            <li><strong>Phone Number:</strong> {{ $data['phone'] }}</li>
            <li><strong>Message:</strong> {{ $data['message'] ?? 'N/A' }}</li>
        </ul>
        <p>Please respond to them as soon as possible.</p>
        <p>Thank you,<br>Chiremba Express Team</p>
    </div>
</body>
</html>