<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Unauthorized Access | WMIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f0fdf4; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen text-slate-800">
    <div class="max-w-lg w-full text-center p-8 bg-white rounded-3xl shadow-2xl border border-emerald-100">
        <div class="text-7xl mb-4">⛔</div>
        <h1 class="text-5xl font-black text-rose-600 mb-2">403</h1>
        <h2 class="text-2xl font-bold mb-4">Access Denied</h2>
        <p class="text-slate-500 mb-8">You do not have the required permissions to view this municipal resource.</p>
        <a href="{{ url('/') }}" class="inline-block px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-full transition-all shadow-md">Return Home</a>
    </div>
</body>
</html>
