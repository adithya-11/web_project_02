<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/users_store.php';

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
<section class="panel">
    <h1>Create an account</h1>
    <?php if (isset($errors['form'])): ?><p role="alert"><?= e($errors['form']) ?></p><?php endif; ?>
    <form method="post" action="register.php">
        <?= csrf_field() ?>
        <label for="username">Username</label>
        <input id="username" name="username" value="<?= e($username) ?>" maxlength="20" autocomplete="username" aria-describedby="username-note" required>
        <p id="username-note"><?= e($errors['username'] ?? '3–20 letters, digits, or underscores.') ?></p>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="new-password" aria-describedby="password-note" minlength="8" required>
        <p id="password-note"><?= e($errors['password'] ?? 'At least 8 characters.') ?></p>
        <label for="confirmation">Confirm password</label>
        <input id="confirmation" name="confirmation" type="password" autocomplete="new-password" aria-describedby="confirmation-note" required>
        <p id="confirmation-note"><?= e($errors['confirmation'] ?? '') ?></p>
        <button type="submit">Register</button>
    </form>
    <p><a href="index.php">Back to login</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
