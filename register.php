<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/users_store.php';

if (is_post()) {
    require_post();
    $username = trim(post_string('username'));
    $password = post_string('password');
    $confirmation = post_string('confirmation');
    $errors = [];
    if (!preg_match('/\A[A-Za-z0-9_]{3,20}\z/', $username)) {
        $errors['username'] = 'Use 3–20 letters, digits, or underscores.';
    }
    if (!preg_match('/\A.{8,}\z/us', $password) || strlen($password) > 72 || str_contains($password, "\0")) {
        $errors['password'] = 'Use at least 8 characters and at most 72 UTF-8 bytes.';
    }
    if ($password !== $confirmation) {
        $errors['confirmation'] = 'Passwords must match.';
    }
    if ($errors === []) {
        try {
            if (create_user($username, $password)) {
                $_SESSION['notice'] = 'Account created. Please log in.';
                redirect('index.php');
            }
            $errors['username'] = 'That username is already taken.';
        } catch (RuntimeException | JsonException $exception) {
            $errors['form'] = 'Accounts are temporarily unavailable.';
        }
    }
    $_SESSION['registration_form'] = [$username, $errors];
    redirect('register.php');
}
[$username, $errors] = take('registration_form', ['', []]);
$title = 'Create an account';
require __DIR__ . '/includes/header.php';
?>
<div class="narrow">
<section class="panel panel--glow auth reveal" aria-labelledby="register-title">
    <p class="eyebrow">New agent</p>
    <h1 id="register-title" class="display"><?= reveal('Create an account') ?></h1>
    <?php if (isset($errors['form'])): ?><p class="feedback feedback--wrong" role="alert"><?= e($errors['form']) ?></p><?php endif; ?>
    <form method="post" action="register.php" class="stack" novalidate>
        <?= csrf_field() ?>
        <div>
            <label for="username" class="field-label">Username</label>
            <input id="username" name="username" class="field" value="<?= e($username) ?>" maxlength="20" autocomplete="username" aria-describedby="username-note" required
                <?= isset($errors['username']) ? 'aria-invalid="true"' : '' ?>>
            <p id="username-note" class="note<?= isset($errors['username']) ? ' note--error' : '' ?>"><?= e($errors['username'] ?? '3–20 letters, digits, or underscores.') ?></p>
        </div>
        <div>
            <label for="password" class="field-label">Password</label>
            <input id="password" name="password" class="field" type="password" autocomplete="new-password" aria-describedby="password-note" minlength="8" required
                <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>>
            <p id="password-note" class="note<?= isset($errors['password']) ? ' note--error' : '' ?>"><?= e($errors['password'] ?? 'At least 8 characters.') ?></p>
        </div>
        <div>
            <label for="confirmation" class="field-label">Confirm password</label>
            <input id="confirmation" name="confirmation" class="field" type="password" autocomplete="new-password" aria-describedby="confirmation-note" required
                <?= isset($errors['confirmation']) ? 'aria-invalid="true"' : '' ?>>
            <p id="confirmation-note" class="note<?= isset($errors['confirmation']) ? ' note--error' : '' ?>"><?= e($errors['confirmation'] ?? 'Type the same password again.') ?></p>
        </div>
        <button type="submit" class="btn btn--primary btn--block">Register</button>
    </form>
    <p class="muted small">Already registered? <a href="index.php">Back to login</a></p>
</section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
