<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kesalahan Order</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #fff5f5;
            color: #742a2a;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            padding: 2rem;
            border: 1px solid #f5c6cb;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .icon {
            font-size: 5rem;
            color: #e3342f;
            margin-bottom: 1rem;
            animation: pulse 1.5s infinite;
        }

        .title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .message {
            font-size: 1.25rem;
            margin-bottom: 2rem;
        }

        .link {
            text-decoration: none;
            color: #e3342f;
            font-weight: 500;
        }

        .link:hover {
            text-decoration: underline;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚠️</div>
        <div class="title">Kesalahan Order</div>
        <div class="message">{{ $message }}</div>
        <a href="/" class="link">Kembali ke Beranda</a>
    </div>
</body>
</html>

