<?php
session_start();

function is_logged_in(): bool
{
    return !empty($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// Jika sudah login, redirect ke input_konten.php
header('Location: input_konten.php');
exit;
