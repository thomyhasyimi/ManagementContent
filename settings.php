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

$division = get_user_division();
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Pengaturan Divisi</title>
</head>
<body class="min-h-screen bg-gray-100 p-4">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold">Pengaturan Divisi</h1>
                <p class="text-sm text-gray-600">Divisi: <?= htmlspecialchars($division) ?></p>
            </div>
            <a href="login.php?logout=1" class="text-blue-600 hover:underline">Logout</a>
        </div>
        <p class="text-gray-700">Halaman ini berisi pengaturan yang bisa Anda ubah untuk divisi Anda.</p>
        <a href="menu.php" class="mt-4 inline-block text-blue-600 hover:underline">Kembali ke Menu</a>
    </div>
</body>
</html>