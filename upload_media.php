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

$driveFolderMap = [
    'April' => [
        '1' => '',
        '2' => '',
        '3' => '',
        '4' => ''
    ],
    'Mei' => [
        '1' => '',
        '2' => '',
        '3' => '',
        '4' => ''
    ],
    'Juni' => [
        '1' => 'https://drive.google.com/drive/folders/1-BTxLPhqoop0N59XpQOR4iTS5_gkqnQC?usp=drive_link',
        '2' => 'https://drive.google.com/drive/folders/1nAq7bSR6ixDlvKrncheOznDrV8nFZ-v6?usp=drive_link',
        '3' => 'https://drive.google.com/drive/folders/1GLThYAm9c1lH2oJy1Jn627FBAAB4cYxf?usp=drive_link',
        '4' => 'https://drive.google.com/drive/folders/1R2gpPoNSX-sh05mZfihoYXqqTwZs0qF1?usp=drive_link'
    ],
    'Juli' => [
        '1' => '',
        '2' => '',
        '3' => '',
        '4' => ''
    ],
    'Agustus' => [
        '1' => '',
        '2' => '',
        '3' => '',
        '4' => ''
    ],
    'September' => [
        '1' => '',
        '2' => '',
        '3' => '',
        '4' => ''
    ],
    'Oktober' => [
        '1' => '',
        '2' => '',
        '3' => '',
        '4' => ''
    ]
];

$success = false;
$errorMessage = '';
$successMessage = '';
$savedPath = '';
$driveFolderUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bulan = $_POST['bulan'] ?? '';
    $minggu = $_POST['minggu'] ?? '';
    $jenis = $_POST['jenis'] ?? '';

    $driveFolderUrl = $driveFolderMap[$bulan][$minggu] ?? '';

    if (empty($_FILES['media']['name'])) {
        $errorMessage = 'Pilih file foto atau video terlebih dahulu.';
    } else {
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'video/mp4',
            'video/mov',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-matroska'
        ];

        $uploadDir = __DIR__ . '/uploads/' . $bulan . '/Minggu-' . $minggu;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $savedFiles = [];
        $errors = [];
        $files = $_FILES['media'];
        $count = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $type = is_array($files['type']) ? $files['type'][$i] : $files['type'];
            $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];

            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = "File {$name} gagal diupload (error code: {$error}).";
                continue;
            }

            if (!in_array($type, $allowedTypes)) {
                $errors[] = "Tipe file {$name} tidak didukung.";
                continue;
            }

            $safeName = preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($name));
            $destination = $uploadDir . '/' . $safeName;

            if (move_uploaded_file($tmpName, $destination)) {
                $savedFiles[] = $destination;
            } else {
                $errors[] = "Gagal menyimpan file {$name} ke server lokal.";
            }
        }

        if (!empty($savedFiles)) {
            $success = true;
            $savedPath = implode(', ', $savedFiles);

            // Jika ada folder Drive, kirim file ke Apps Script
            if ($driveFolderUrl) {
                $driveScriptUrl = 'https://script.google.com/macros/s/YOUR_DEPLOYED_SCRIPT_ID/exec'; // Ganti dengan URL Apps Script Drive yang sudah dideploy

                $filesPayload = [];
                foreach ($savedFiles as $filePath) {
                    $fileName = basename($filePath);
                    $fileContent = file_get_contents($filePath);
                    $base64 = base64_encode($fileContent);
                    $mimeType = mime_content_type($filePath);

                    $filesPayload[] = [
                        'name' => $fileName,
                        'base64' => $base64,
                        'mimeType' => $mimeType
                    ];
                }

                $payload = [
                    'action' => 'upload',
                    'folderUrl' => $driveFolderUrl,
                    'files' => $filesPayload
                ];

                error_log('Payload to Apps Script: ' . json_encode($payload));

                // Gunakan cURL untuk request yang lebih reliable
                $ch = curl_init($driveScriptUrl);
                if ($ch === false) {
                    $errorMessage = 'Upload lokal berhasil, tetapi cURL tidak tersedia untuk mengirim ke Google Drive.';
                } else {
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'User-Agent: PHP-Upload-Script'
                    ]);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 2 menit timeout
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Skip SSL verification untuk testing
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                    $response = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $curlError = curl_error($ch);
                    $curlErrno = curl_errno($ch);

                    curl_close($ch);

                    error_log('cURL Response: ' . $response);
                    error_log('HTTP Code: ' . $httpCode);
                    error_log('cURL Error: ' . $curlError);
                    error_log('cURL Errno: ' . $curlErrno);

                    if ($response === false) {
                        $errorMessage = 'Upload lokal berhasil, tetapi gagal mengirim ke Google Drive. Error: ' . $curlError . ' (Code: ' . $curlErrno . ')';
                    } else {
                        $responseData = json_decode($response, true);
                        if (!empty($responseData['status']) && $responseData['status'] === 'success') {
                            $successMessage = 'File berhasil diunggah ke server lokal dan Google Drive.';
                            if (!empty($responseData['uploaded'])) {
                                $successMessage .= ' (' . count($responseData['uploaded']) . ' file berhasil)';
                            }
                        } else {
                            $errorMessage = 'Upload lokal berhasil, tetapi ke Drive gagal: ' . ($responseData['message'] ?? 'tidak diketahui');
                            if (!empty($responseData['debug'])) {
                                $errorMessage .= ' | Debug: ' . implode(' | ', $responseData['debug']);
                            }
                            $errorMessage .= ' | HTTP Code: ' . $httpCode;
                        }
                    }
                }
            } else {
                $errorMessage = 'Upload lokal berhasil, tetapi folder Google Drive belum dikonfigurasi untuk bulan/minggu ini.';
            }

            if (!empty($errors)) {
                $errorMessage .= ' Beberapa file gagal: ' . implode(' ', $errors);
            }
        } else {
            $errorMessage = implode(' ', $errors);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Upload Media</title>
</head>
<body class="min-h-screen bg-gray-100 p-4">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Upload Foto / Video</h1>
                <p class="text-sm text-gray-600">Login sebagai: <?= htmlspecialchars(get_user_division()) ?></p>
            </div>
            <div class="space-x-4">
                <a href="menu.php" class="text-blue-600 hover:underline">Kembali ke Menu</a>
                <a href="login.php?logout=1" class="text-blue-600 hover:underline">Logout</a>
            </div>
        </div>

        <?php if ($errorMessage): ?>
            <div class="mb-4 rounded-xl bg-red-50 border border-red-200 p-4 text-red-800"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <?php if ($successMessage): ?>
            <div class="mb-4 rounded-xl bg-green-50 border border-green-200 p-4 text-green-800"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        <?php if ($success && !$successMessage && !$errorMessage): ?>
            <div class="mb-4 rounded-xl bg-green-50 border border-green-200 p-4 text-green-800">
                File berhasil diunggah ke server lokal.
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col">
                        <label class="text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                        <select name="bulan" required class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-800 cursor-pointer appearance-none">
                            <option value="April">April</option>
                            <option value="Mei">Mei</option>
                            <option value="Juni">Juni</option>
                            <option value="Juli">Juli</option>
                            <option value="Agustus">Agustus</option>
                            <option value="September">September</option>
                            <option value="Oktober">Oktober</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <label class="text-sm font-semibold text-gray-700 mb-2">Minggu</label>
                        <select name="minggu" required class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-800 cursor-pointer appearance-none">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-700 mb-2">Jenis Media</label>
                    <select name="jenis" required class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-gray-800 cursor-pointer appearance-none">
                        <option value="Foto">Foto</option>
                        <option value="Video">Video</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-700 mb-2">Pilih File</label>
                    <input type="file" name="media[]" accept="image/*,video/*" multiple required class="border border-gray-300 rounded-lg px-4 py-3 text-gray-800" />
                    <p class="text-sm text-gray-500 mt-2">Pilih lebih dari satu file untuk mengunggah sekaligus.</p>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition">Upload dan Buka Folder Drive</button>
            </form>
        </div>

        <?php if ($success): ?>
            <div class="mt-6 bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Lokasi Upload</h2>
                <p class="text-gray-700 mb-2">File tersimpan lokal di server:</p>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 break-words"><?= htmlspecialchars($savedPath) ?></div>
                <?php if ($driveFolderUrl): ?>
                    <p class="text-gray-700 mt-4">Buka folder Google Drive untuk bulan dan minggu yang dipilih:</p>
                    <a href="<?= htmlspecialchars($driveFolderUrl) ?>" target="_blank" class="inline-block mt-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Buka Folder Drive</a>
                <?php else: ?>
                    <p class="text-gray-700 mt-4">Belum ada folder Google Drive terkonfigurasi untuk bulan/minggu ini. Silakan atur link folder di kode.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
