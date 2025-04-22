<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Not Found</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1a202c;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            text-align: center;
        }

        .icon {
            font-size: 5rem;
            color: #ff6b6b;
            margin-bottom: 1rem;
            animation: shake 1.5s infinite;
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
            color: #3490dc;
            font-weight: 500;
        }

        .link:hover {
            text-decoration: underline;
        }

        @keyframes shake {

            0%,
            100% {
                transform: rotate(0);
            }

            25% {
                transform: rotate(-10deg);
            }

            50% {
                transform: rotate(10deg);
            }

            75% {
                transform: rotate(-5deg);
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <div class="title">404 - Halaman Tidak Ditemukan</div>
        <div class="message">Maaf, halaman yang Anda cari tidak tersedia.</div>
        <a href="/" class="link">Kembali ke Beranda</a>
    </div>
</body>
</html>

