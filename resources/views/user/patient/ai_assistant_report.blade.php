<!DOCTYPE html>
<html>
<head>
    <title>Kunashe AI Health Assistant Consultation Report</title>
    <style>
        /* Add basic styling for your PDF. DomPDF can interpret basic CSS. */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        h1, h2 {
            color: #1a5276; /* A nice dark blue */
            margin-top: 20px;
            margin-bottom: 10px;
        }
        p {
            margin-bottom: 5px;
        }
        p strong {
            display: inline-block;
            width: 150px; /* Adjust width for label alignment if needed */
            font-weight: bold;
        }
        hr {
            border: 0;
            border-top: 1px solid #ccc;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    {{-- This line is the ONLY dynamic content needed if you're passing pre-formatted HTML --}}
    {!! $formattedPdfReport !!}
</body>
</html>