<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Server</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #edf2f7;
            color: #2d3748;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            padding: 2rem;
            border: 1px solid #cbd5e0;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .icon {
            font-size: 5rem;
            color: #4299e1;
            margin-bottom: 1rem;
            animation: bounce 1.5s infinite;
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
            color: #4299e1;
            font-weight: 500;
        }

        .link:hover {
            text-decoration: underline;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="icon">💥</div>
        <div class="title">500 - Kesalahan Server</div>
        <div class="message">Terjadi kesalahan pada server. Silakan coba lagi nanti.</div>
        <a href="/" class="link">Kembali ke Beranda</a>
    </div>
</body>
</html>

