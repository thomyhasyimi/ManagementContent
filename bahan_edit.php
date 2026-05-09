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

$jsonFile = 'data.json';
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

$data = json_decode(file_get_contents($jsonFile), true) ?? [];
$userDivision = get_user_division();

$filteredData = [];
foreach ($data as $index => $row) {
    if ($userDivision === 'Admin' || (!empty($row['divisi']) && $row['divisi'] === $userDivision)) {
        $filteredData[] = ['index' => $index, 'row' => $row];
    }
}

$successMessage = '';
$errorMessage = '';

$scriptUrl = 'https://script.google.com/macros/s/AKfycbxwqvHb1WvQU2JFtx4UuqjaexnrNqvnTMaJ59oqe9Ym580rcRFQlxBwqGJ1kpDrcBzT/exec';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['row_index'], $_POST['status'])) {
    $rowIndex = (int) $_POST['row_index'];
    $newStatus = trim($_POST['status']);

    if (isset($data[$rowIndex])) {
        $data[$rowIndex]['status'] = $newStatus;
        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

        $payload = [
            'action' => 'updateStatus',
            'bulan' => $data[$rowIndex]['bulan'] ?? '',
            'minggu' => $data[$rowIndex]['minggu'] ?? '',
            'jenis' => $data[$rowIndex]['jenis'] ?? '',
            'status' => $newStatus
        ];

        $contextOptions = [
            'http' => [
                'header'  => "Content-type: application/json",
                'method'  => 'POST',
                'content' => json_encode($payload),
                'timeout' => 30
            ]
        ];

        $context = stream_context_create($contextOptions);
        $response = @file_get_contents($scriptUrl, false, $context);

        if ($response === false) {
            $errorMessage = 'Status berhasil diubah lokal, tetapi update ke spreadsheet gagal.';
        } else {
            $responseData = json_decode($response, true);
            if (!empty($responseData['status']) && $responseData['status'] === 'success') {
                $successMessage = 'Status bahan edit berhasil diperbarui dan spreadsheet disinkronkan.';
            } else {
                $errorMessage = 'Perubahan lokal berhasil, tetapi spreadsheet merespons error: ' . ($responseData['message'] ?? 'tidak diketahui');
            }
        }

        $filteredData = [];
        foreach ($data as $index => $row) {
            if ($userDivision === 'Admin' || (!empty($row['divisi']) && $row['divisi'] === $userDivision)) {
                $filteredData[] = ['index' => $index, 'row' => $row];
            }
        }
    } else {
        $errorMessage = 'Data bahan edit tidak ditemukan.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Bahan Edit</title>
</head>
<body class="min-h-screen bg-gray-100 p-4">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Bahan Edit</h1>
                <p class="text-sm text-gray-600">Login sebagai: <?= htmlspecialchars(get_user_division()) ?></p>
                <p class="text-sm text-gray-500">Data bahan edit diambil dari spreadsheet yang disinkronkan saat input konten.</p>
            </div>
            <div class="space-x-4">
                <a href="menu.php" class="text-blue-600 hover:underline">Kembali ke Menu</a>
                <a href="login.php?logout=1" class="text-blue-600 hover:underline">Logout</a>
            </div>
        </div>

        <?php if ($successMessage): ?>
            <div class="mb-4 rounded-xl bg-green-50 border border-green-200 p-4 text-green-800"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="mb-4 rounded-xl bg-red-50 border border-red-200 p-4 text-red-800"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <?php if (empty($filteredData)): ?>
            <div class="rounded-xl bg-white shadow-lg p-8 text-gray-700">
                <p>Tidak ada data bahan edit yang tersedia untuk divisi Anda saat ini.</p>
                <p class="mt-2 text-sm text-gray-500">Silakan tambahkan konten melalui menu Input Konten terlebih dahulu atau pastikan divisi Anda sudah terdaftar pada data.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto bg-white rounded-xl shadow-lg p-4">
                <table class="min-w-full text-left text-gray-700 border-collapse border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-3 border-b">No</th>
                            <th class="px-4 py-3 border-b">Bulan</th>
                            <th class="px-4 py-3 border-b">Minggu</th>
                            <th class="px-4 py-3 border-b">Jenis</th>
                            <th class="px-4 py-3 border-b">Isi</th>
                            <th class="px-4 py-3 border-b">Status</th>
                            <th class="px-4 py-3 border-b">Divisi</th>
                            <th class="px-4 py-3 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filteredData as $index => $item): ?>
                            <?php $rowIndex = $item['index']; $row = $item['row']; ?>
                            <tr class="border-b border-gray-200">
                                <td class="px-4 py-3"><?= $index + 1 ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($row['bulan'] ?? '-') ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($row['minggu'] ?? '-') ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($row['jenis'] ?? '-') ?></td>
                                <td class="px-4 py-3 whitespace-pre-line"><?= htmlspecialchars($row['isi'] ?? '-') ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($row['status'] ?? 'Belum Progres') ?></td>
                                <td class="px-4 py-3"><?= htmlspecialchars($row['divisi'] ?? '-') ?></td>
                                <td class="px-4 py-3">
                                    <form method="POST" class="flex gap-2 items-center">
                                        <input type="hidden" name="row_index" value="<?= $rowIndex ?>">
                                        <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white text-gray-800">
                                            <option value="Belum Progres" <?= (isset($row['status']) && $row['status'] === 'Belum Progres') ? 'selected' : '' ?>>Belum Progres</option>
                                            <option value="On Progres" <?= (isset($row['status']) && $row['status'] === 'On Progres') ? 'selected' : '' ?>>On Progres</option>
                                            <option value="Ready to Upload" <?= (isset($row['status']) && $row['status'] === 'Ready to Upload') ? 'selected' : '' ?>>Ready to Upload</option>
                                        </select>
                                        <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
