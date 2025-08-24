<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteName }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, {{ $themeColor ?? '#6366f1' }}, #8b5cf6);
            color: white;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .info {
            margin: 1rem 0;
            padding: 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            font-size: 1.1rem;
        }
        .badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin: 0.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 {{ $siteName }}</h1>
        <div class="info">
            <div class="badge">Domain: {{ $domain }}</div>
            <div class="badge">App: {{ $app }}</div>
            <div class="badge">Theme: {{ $themeColor }}</div>
        </div>
        <p>Powered by <strong>Larabus Framework</strong></p>
        <p><a href="/about" style="color: white; text-decoration: underline;">About Page</a></p>
    </div>
</body>
</html>