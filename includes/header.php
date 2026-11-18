<?php require_once __DIR__ . '/csrf.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <?php if (!empty($refreshAfter)): ?><meta http-equiv="refresh" content="<?= e((int) $refreshAfter) ?>"><?php endif; ?>
    <title><?= e($title) ?> | Vault Sentinel</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='14' fill='%2305070a' stroke='%23f1c97c' stroke-width='2'/%3E%3Ccircle cx='16' cy='16' r='5' fill='%23f1c97c'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/rooms.css">
</head>
<body data-theme="<?= e(vault_theme()) ?>" class="page-<?= e(basename($_SERVER['SCRIPT_NAME'] ?? 'index.php', '.php')) ?>">
<div class="scene" aria-hidden="true">
    <div class="scene__glow"></div>
    <div class="scene__beam"></div>
    <div class="scene__dust scene__dust--near"></div>
    <div class="scene__dust scene__dust--far"></div>
    <div class="scene__floor"></div>
    <div class="scene__grain"></div>
</div>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <a class="brand" href="<?= isset($_SESSION['user']) ? 'lobby.php' : 'index.php' ?>">
        <span class="brand__dial" aria-hidden="true"></span>
        <span class="brand__name">Vault <em>Sentinel</em></span>
    </a>
    <nav class="site-nav" aria-label="Main navigation">
        <a href="team.php"<?= nav_current('team.php') ?>>Team</a>
        <a href="leaderboard.php"<?= nav_current('leaderboard.php') ?>>Leaderboard</a>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="lobby.php"<?= nav_current('lobby.php') ?>>Entry Hall</a>
            <form method="post" action="logout.php">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--ghost btn--sm">Log out</button>
            </form>
        <?php else: ?>
            <a href="index.php"<?= nav_current('index.php') ?>>Log in</a>
            <a href="register.php" class="nav-cta"<?= nav_current('register.php') ?>>Register</a>
        <?php endif; ?>
    </nav>
</header>
<main id="main" class="site-main">
