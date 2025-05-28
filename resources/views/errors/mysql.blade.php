<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kesalahan Sistem</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 40px;
            background: #f9f9f9;
            text-align: center;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #c00;
        }
    </style>
</head>

<body>
    <div class="box">
        <h1>Oops!</h1>
        <p>{{ $message }}</p>
        <p><a href="{{ url()->current() }}">Muat ulang halaman</a></p>
    </div>
</body>

</html>