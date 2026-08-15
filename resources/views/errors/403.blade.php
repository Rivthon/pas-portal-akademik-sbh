<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: Arial, sans-serif;
            color: #334155;
            background: #f5f7fb;
        }
        .card {
            width: min(520px, 100%);
            padding: 38px 30px;
            text-align: center;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 14px 40px rgba(15, 23, 42, .1);
        }
        .code { margin: 0; color: #696cff; font-size: 72px; line-height: 1; }
        h1 { margin: 14px 0 8px; color: #1e293b; font-size: 25px; }
        p { margin: 0 0 24px; color: #64748b; line-height: 1.6; }
        a {
            display: inline-block;
            padding: 11px 20px;
            color: #fff;
            text-decoration: none;
            background: #696cff;
            border-radius: 9px;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="code">403</div>
        <h1>Akses Ditolak</h1>
        <p>Akun Anda tidak memiliki hak akses untuk membuka halaman ini.</p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}">Kembali</a>
    </main>
</body>
</html>
