<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/run.php';

if (is_post()) {
    require_post();
    switch (post_string('action')) {
        case 'theme':
            $theme = post_string('theme');
            if (!in_array($theme, ['dark', 'contrast'], true)) {
                http_response_code(400);
                exit('Unknown theme.');
            }
            setcookie('vault_theme', $theme, [
                'expires' => time() + 31536000,
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            redirect('lobby.php');
            break;
        case 'start':
            if (!isset($_SESSION['run']) || $_SESSION['run']['finished_at'] !== null) {
                start_run(time());
            }
            redirect(run_destination());
            break;
        case 'resume':
            redirect(run_destination());
            break;
        case 'abandon':
            unset($_SESSION['run'], $_SESSION['room_feedback'], $_SESSION['door_open']);
            redirect('lobby.php');
            break;
        default:
            http_response_code(400);
            exit('Unknown action.');
    }
}
$run = $_SESSION['run'] ?? null;
$active = $run !== null && $run['finished_at'] === null;
$title = 'Vault Entry Hall';
require __DIR__ . '/includes/header.php';
?>
<h1>Vault Entry Hall</h1>
<p>Welcome, <?= e($_SESSION['user']) ?>.</p>
<div class="split">
<section class="panel">
    <h2>Vault map</h2>
    <ol class="room-map">
        <?php foreach (ROOMS as $number => $name): ?>
            <li <?= $active && $run['room'] === $number ? 'aria-current="step"' : '' ?>><?= e($name) ?></li>
        <?php endforeach; ?>
    </ol>
    <form method="post" action="lobby.php" class="actions">
        <?= csrf_field() ?>
        <?php if ($active): ?>
            <button name="action" value="resume">Resume Room <?= e($run['room']) ?></button>
            <button name="action" value="abandon">Abandon run</button>
        <?php else: ?>
            <button name="action" value="start"><?= $run === null ? 'Enter the vault' : 'Play again' ?></button>
            <?php if ($run !== null): ?><a href="escape.php">View last result</a><?php endif; ?>
        <?php endif; ?>
    </form>
</section>
<section class="panel">
    <h2>Rules and scoring</h2>
    <p>Solve each room in order. Your run starts at 1,000 points and loses 50 points for each wrong answer.</p>
    <p>Earn 10 points per second under 10 minutes, up to 500, plus 75 points per tier above Tier 1 for each solved room.</p>
    <h2>Appearance</h2>
    <form method="post" action="lobby.php" class="actions">
        <?= csrf_field() ?>
        <label for="theme">Theme</label>
        <select name="theme" id="theme">
            <option value="dark" <?= vault_theme() === 'dark' ? 'selected' : '' ?>>Dark</option>
            <option value="contrast" <?= vault_theme() === 'contrast' ? 'selected' : '' ?>>High contrast</option>
        </select>
        <button name="action" value="theme">Save theme</button>
    </form>
</section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
