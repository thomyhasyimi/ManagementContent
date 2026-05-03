<?php
session_start();

function is_logged_in(): bool
{
    return !empty($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

function get_user_division(): string
{
    return $_SESSION['division'] ?? 'Divisi tidak diketahui';
}

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$userDivision = get_user_division();
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Menu Utama</title>
</head>
<body class="min-h-screen bg-gray-100 p-4">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Pilih Menu</h2>
                <p class="text-sm text-gray-600">Login sebagai divisi: <?= htmlspecialchars($userDivision) ?></p>
            </div>
            <a href="login.php?logout=1" class="text-sm text-blue-600 hover:underline">Logout</a>
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            <a href="input_konten.php" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Input Konten</h3>
                <p class="text-sm text-gray-600">Masukkan data konten untuk divisi Anda.</p>
            </a>
            <a href="report.php" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Laporan Divisi</h3>
                <p class="text-sm text-gray-600">Akses laporan khusus divisi dan ringkasan hasil.</p>
            </a>
            <a href="settings.php" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Pengaturan Divisi</h3>
                <p class="text-sm text-gray-600">Kelola pengaturan dan preferensi divisi Anda.</p>
            </a>
            <a href="https://drive.google.com/drive/folders/1panCqRuwM6J3JJkNlhpH-aIH0OH_N7gN" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Bahan Edit</h3>
                <p class="text-sm text-gray-600">Kelola pengaturan dan preferensi divisi Anda.</p>
            </a>
            <a href="settings.php" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Ready Upload</h3>
                <p class="text-sm text-gray-600">Kelola pengaturan dan preferensi divisi Anda.</p>
            </a>
        </div>
    </div>
</body>
</html>