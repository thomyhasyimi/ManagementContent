<?php
session_start();

$VALID_TOKENS = [
    'adm-456' => 'Admin',
    'us-123' => 'User',
];

function login_with_token(string $token): bool
{
    global $VALID_TOKENS;
    $token = trim($token);

    if (isset($VALID_TOKENS[$token])) {
        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        $_SESSION['division'] = $VALID_TOKENS[$token];
        return true;
    }

    return false;
}

$loginError = '';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    if (login_with_token($_POST['token'])) {
        if ($_SESSION['division'] === 'Admin') {
            header('Location: https://docs.google.com/spreadsheets/d/1Zdkh5O7gGKawPoQG2azLaJxjzssnUuMslGa96IqQxJM/edit?hl=id&gid=0#gid=0');
        } else {
            header('Location: menu.php');
        }
        exit;
    }

    $loginError = 'Token tidak valid. Silakan coba lagi.';
}
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>Login Token</title>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-lg">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Login dengan Token</h1>
        <?php if ($loginError): ?>
            <div class="mb-4 px-4 py-3 text-red-800 bg-red-100 rounded-lg">
                <?= htmlspecialchars($loginError) ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="login.php" class="space-y-4">
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Token</label>
                <input type="password" name="token" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Masukkan token Anda">
            </div>
            <button type="submit" class="w-full py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Masuk</button>
        </form>
        <p class="mt-4 text-sm text-gray-500">Masukkan token yang benar untuk melanjutkan ke halaman input konten.</p>
    </div>
</body>
</html>