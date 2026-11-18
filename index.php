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
<div class="hero-split">
<section class="hero reveal">
    <p class="eyebrow">Topic 04 · The PHP Vault</p>
    <h1 class="display display--xl"><?= reveal('Locked in after closing time.') ?></h1>
    <p class="lede">Three security doors stand between you and the street. Sentinel, the vault's guard program, won't hand you an answer, but it will tell you what kind of mistake you made.</p>
    <ul class="feature-list">
        <li style="--i:0"><span class="feature-list__n">01</span> The Ledger <span class="muted">· number sequences</span></li>
        <li style="--i:1"><span class="feature-list__n">02</span> The Cipher Gate <span class="muted">· shifted phrases</span></li>
        <li style="--i:2"><span class="feature-list__n">03</span> The Pattern Lock <span class="muted">· hidden grid rules</span></li>
    </ul>
    <div class="hero-door" aria-hidden="true">
        <div class="vault-door vault-door--idle">
            <div class="vault-door__ring"></div>
            <div class="vault-door__wheel"><i></i><i></i><i></i></div>
            <div class="vault-door__bolts"></div>
        </div>
    </div>
</section>
<section class="panel panel--glow auth reveal" style="--d:2" aria-labelledby="login-title">
    <h2 id="login-title" class="panel__title">Agent login</h2>
    <?php if ($notice !== ''): ?><p class="feedback feedback--ok" role="status"><?= e($notice) ?></p><?php endif; ?>
    <?php if ($error !== ''): ?><p class="feedback feedback--wrong" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" action="index.php" class="stack">
        <?= csrf_field() ?>
        <div>
            <label for="username" class="field-label">Username</label>
            <input id="username" name="username" class="field" value="<?= e($username) ?>" maxlength="20" autocomplete="username" required>
        </div>
        <div>
            <label for="password" class="field-label">Password</label>
            <input id="password" name="password" class="field" type="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn--primary btn--block">Enter the vault</button>
    </form>
    <p class="muted small">New here? <a href="register.php">Create an account</a></p>
</section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
