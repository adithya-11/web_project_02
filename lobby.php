<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/auth_guard.php';
$title = 'Vault Entry Hall';
require __DIR__ . '/includes/header.php';
?>
<h1>Vault Entry Hall</h1>
<p>Welcome, <?= e($_SESSION['user']) ?>.</p>
<ol><li>The Ledger</li><li>The Cipher Gate</li><li>The Pattern Lock</li></ol>
<?php require __DIR__ . '/includes/footer.php'; ?>
