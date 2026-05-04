<?php
session_start();
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: login.html');
    exit;
}
$division = $_SESSION['division'] ?? 'Divisi tidak diketahui';
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Perencanaan Konten</title>
</head>
<body class="min-h-screen bg-gray-100 p-4">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">Perencanaan Konten</h1>
                <p class="text-sm text-gray-600">Divisi: <?= htmlspecialchars($division) ?></p>
            </div>
            <a href="index.php" class="text-blue-600 hover:underline">Kembali ke Menu</a>
        </div>
        <p class="text-gray-700">Gunakan halaman ini untuk membuat perencanaan konten sesuai divisi Anda.</p>
    </div>
</body>
</html>
