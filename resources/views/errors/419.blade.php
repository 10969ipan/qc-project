<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url={{ route('login') }}">
    <title>Sesi Berakhir - Redirecting...</title>
    <script>
        window.location.href = "{{ route('login') }}";
    </script>
</head>
<body style="background-color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; color: #475569;">
    <div style="text-align: center;">
        <p>Sesi Anda telah berakhir. Mengalihkan ke halaman login...</p>
        <a href="{{ route('login') }}" style="color: #2563eb; text-decoration: underline;">Klik di sini jika Anda tidak dialihkan secara otomatis.</a>
    </div>
</body>
</html>
