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
            unset($_SESSION['door_open'], $_SESSION['hint_notice']);
            if (!isset($_SESSION['run']) || $_SESSION['run']['finished_at'] !== null) {
                start_run(time());
            }
            redirect(run_destination());
            break;
        case 'resume':
            redirect(run_destination());
            break;
        case 'abandon':
            unset($_SESSION['run'], $_SESSION['room_feedback'], $_SESSION['door_open'], $_SESSION['hint_notice']);
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
<section class="room-hero reveal">
    <p class="eyebrow">Welcome, agent <?= e($_SESSION['user']) ?></p>
    <h1 class="display display--xl"><?= reveal('Vault Entry Hall') ?></h1>
    <p class="lede">Solve each room in order. Sentinel watches how you play and sets the next door's difficulty to match.</p>
</section>
<div class="lobby-grid">
<section class="panel panel--glow reveal" style="--d:1" aria-labelledby="map-title">
    <h2 id="map-title" class="panel__title">Vault map</h2>
    <ol class="room-map">
        <?php foreach (ROOMS as $number => $name): ?>
            <?php
            $state = !$active ? 'sealed' : ($number < $run['room'] ? 'open' : ($number === $run['room'] ? 'current' : 'sealed'));
            ?>
            <li class="room-card is-<?= $state ?>" style="--i:<?= e($number) ?>"<?= $state === 'current' ? ' aria-current="step"' : '' ?>>
                <span class="room-card__n"><?= e(sprintf('%02d', $number)) ?></span>
                <span class="room-card__name"><?= e($name) ?></span>
                <span class="room-card__state"><?= e(['open' => 'Opened', 'current' => 'You are here', 'sealed' => 'Sealed'][$state]) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
    <form method="post" action="lobby.php" class="actions">
        <?= csrf_field() ?>
        <?php if ($active): ?>
            <button name="action" value="resume" class="btn btn--primary">Resume Room <?= e($run['room']) ?></button>
            <button name="action" value="abandon" class="btn btn--ghost">Abandon run</button>
        <?php else: ?>
            <button name="action" value="start" class="btn btn--primary"><?= $run === null ? 'Enter the vault' : 'Play again' ?></button>
            <?php if ($run !== null): ?><a class="btn btn--ghost" href="escape.php">View last result</a><?php endif; ?>
        <?php endif; ?>
    </form>
</section>
<section class="panel reveal" style="--d:2" aria-labelledby="rules-title">
    <h2 id="rules-title" class="panel__title">Rules and scoring</h2>
    <ul class="rules">
        <li><strong>1,000</strong> points to start.</li>
        <li><strong>−50</strong> for each wrong answer. Blank or badly formed answers cost nothing.</li>
        <li><strong>−100</strong> for each hint. Each room has three, from a nudge to a near give-away.</li>
        <li><strong>+10</strong> per second under a 10-minute par, up to 500.</li>
        <li><strong>+75</strong> per tier above Tier 1 for every room you clear.</li>
        <li>Three wrong answers in a row seal the answer box for <strong>30 seconds</strong>.</li>
    </ul>
    <h3>Appearance</h3>
    <form method="post" action="lobby.php" class="actions">
        <?= csrf_field() ?>
        <label for="theme" class="sr-only">Theme</label>
        <select name="theme" id="theme" class="field field--inline">
            <option value="dark" <?= vault_theme() === 'dark' ? 'selected' : '' ?>>Dark vault</option>
            <option value="contrast" <?= vault_theme() === 'contrast' ? 'selected' : '' ?>>High contrast</option>
        </select>
        <button name="action" value="theme" class="btn btn--ghost btn--sm">Save theme</button>
    </form>
</section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
