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
                <p class="text-sm text-gray-600">Login sebagai : <?= htmlspecialchars($userDivision) ?></p>
            </div>
            <a href="login.php?logout=1" class="text-sm text-blue-600 hover:underline">Logout</a>
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            <a href="input_konten.php" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Input Konten</h3>
                <p class="text-sm text-gray-600">Masukkan data konten untuk keperluan Anda.</p>
            </a>
            <a href="bahan_edit.php" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Bahan Edit</h3>
                <p class="text-sm text-gray-600">Ambil data bahan dan tandai statusnya di sini.</p>
            </a>
            <a href="upload_media.php" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Upload Media</h3>
                <p class="text-sm text-gray-600">Unggah foto atau video dan arahkan ke folder Google Drive per bulan/minggu.</p>
            </a>

            <a href="https://drive.google.com/drive/folders/1cpKfNeKTVE-l6EcKVc7i3KTTMS0w6NDq?usp=drive_link" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition">
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Siap Diposting</h3>
                <p class="text-sm text-gray-600">Akses file yang siap diposting untuk keperluan Anda.</p>
            </a>
        </div>
    </div>
</body>
</html>