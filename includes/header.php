<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | Vault Sentinel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <a href="index.php">Vault Sentinel</a>
    <nav aria-label="Main navigation">
        <a href="team.php">Team</a>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="lobby.php">Entry Hall</a>
            <?php require_once __DIR__ . '/csrf.php'; ?>
            <form method="post" action="logout.php">
                <?= csrf_field() ?>
                <button type="submit">Log out</button>
            </form>
        <?php else: ?>
            <a href="index.php">Log in</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main id="main">
