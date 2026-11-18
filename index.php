<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/users_store.php';

if (is_post()) {
    require_post();
    $username = trim(post_string('username'));
    $password = post_string('password');
    $user = null;
    $error = 'Username or password is incorrect.';
    try {
        if (preg_match('/\A[A-Za-z0-9_]{3,20}\z/', $username) && strlen($password) <= 72) {
            $user = verify_user($username, $password);
        }
    } catch (RuntimeException | JsonException $exception) {
        $error = 'Accounts are temporarily unavailable.';
    }
    if ($user !== null) {
        session_regenerate_id(true);
        $_SESSION = ['user' => $user, 'last_active' => time()];
        redirect('lobby.php');
    }
    $_SESSION['login_form'] = [$username, $error];
    redirect('index.php');
}

[$username, $error] = take('login_form', ['', '']);
$notice = take('notice', '');
$title = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="split">
<section>
    <h1>Vault Sentinel</h1>
    <p>Three rooms stand between you and the exit. Open each lock in order to escape.</p>
</section>
<section class="panel">
    <h2>Agent login</h2>
    <?php if ($notice !== ''): ?><p role="status"><?= e($notice) ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" action="index.php">
        <?= csrf_field() ?>
        <label for="username">Username</label>
        <input id="username" name="username" value="<?= e($username) ?>" maxlength="20" autocomplete="username" required>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="submit">Log in</button>
    </form>
    <p><a href="register.php">Create an account</a></p>
</section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
