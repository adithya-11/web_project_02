<?php
declare(strict_types=1);

if (!isset($_SESSION['user'])) {
    $_SESSION['notice'] = 'Please log in to enter the vault.';
    redirect('index.php');
}
if (time() - ($_SESSION['last_active'] ?? 0) >= 1800) {
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['notice'] = 'Your session timed out. Please log in again.';
    redirect('index.php');
}
$_SESSION['last_active'] = time();
