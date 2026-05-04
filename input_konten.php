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
    header('Location: login.html');
    exit;
}

$success = false;
$submittedData = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulan'], $_POST['minggu'], $_POST['jenis'], $_POST['isi'])) {
    $data = [
        "bulan" => $_POST['bulan'],
        "minggu" => $_POST['minggu'],
        "jenis" => $_POST['jenis'],
        "isi" => $_POST['isi']
    ];

    $jsonData = json_encode($data);
    $url = "https://script.google.com/macros/s/AKfycbwlkE1-dFCQeQsiCTKk3ha5SUjxtu34UUogW-eIZ-3LcuGV2whOzKg0qVW0i2jSkfSt/exec";

    $options = [
        'http' => [
            'header'  => "Content-type: application/json",
            'method'  => 'POST',
            'content' => $jsonData
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    $success = true;
    $submittedData = $data;
}
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Input Konten</title>
</head>
<body class="min-h-screen bg-gray-100 p-4">


<div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="w-full max-w-2xl">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <a href="menu.php" class="mt-4 inline-block text-blue-600 hover:underline">Kembali ke Menu</a>
            <div class="flex items-center justify-between mb-6">
                
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Input Konten</h2>
                    <p class="text-sm text-gray-600">Login sebagai : <?= htmlspecialchars(get_user_division()) ?></p>
                </div>
                <a href="login.php?logout=1" class="text-sm text-blue-600 hover:underline">Logout</a>
            </div>

            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col">
                        <label class="text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                        <select name="bulan" class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-800 cursor-pointer appearance-none">
                            <option value="April" class="bg-white text-gray-800">April</option>
                            <option value="Mei" class="bg-white text-gray-800">Mei</option>
                            <option value="Juni" class="bg-white text-gray-800">Juni</option>
                            <option value="Juli" class="bg-white text-gray-800">Juli</option>
                            <option value="Agustus" class="bg-white text-gray-800">Agustus</option>
                            <option value="September" class="bg-white text-gray-800">September</option>
                            <option value="Oktober" class="bg-white text-gray-800">Oktober</option>
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label class="text-sm font-semibold text-gray-700 mb-2">Minggu</label>
                        <select name="minggu" class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-800 cursor-pointer appearance-none">
                            <option value="1" class="bg-white text-gray-800">1</option>
                            <option value="2" class="bg-white text-gray-800">2</option>
                            <option value="3" class="bg-white text-gray-800">3</option>
                            <option value="4" class="bg-white text-gray-800">4</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-700 mb-2">Jenis Konten</label>
                    <select name="jenis" class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-800 cursor-pointer appearance-none">
                        <option value="Video" class="bg-white text-gray-800">Video</option>
                        <option value="Design" class="bg-white text-gray-800">Design</option>
                        <option value="Podcast" class="bg-white text-gray-800">Podcast</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-700 mb-2">Isi Konten</label>
                    <textarea name="isi" class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-vertical min-h-32 text-gray-800"></textarea>
                </div>

                <button type="submit" class="cursor-pointer w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold py-3 px-4 rounded-lg transition duration-200">
                    Simpan
                </button>
            </form>
        </div>
    </div>
</div>

<?php if ($success && $submittedData): ?>
    <div class="mt-8 w-full mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">Data Tersimpan</h3>
            <table class="min-w-full text-sm text-left text-gray-700 border border-gray-200 rounded-lg overflow-hidden">
                <tbody>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <td class="px-4 py-3 font-semibold">Bulan</td>
                        <td class="px-4 py-3"><?= htmlspecialchars($submittedData['bulan']) ?></td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="px-4 py-3 font-semibold">Minggu</td>
                        <td class="px-4 py-3"><?= htmlspecialchars($submittedData['minggu']) ?></td>
                    </tr>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <td class="px-4 py-3 font-semibold">Jenis Konten</td>
                        <td class="px-4 py-3"><?= htmlspecialchars($submittedData['jenis']) ?></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-semibold align-top">Isi Konten</td>
                        <td class="px-4 py-3 whitespace-pre-line"><?= htmlspecialchars($submittedData['isi']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

</body>
</html>