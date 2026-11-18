<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/auth_guard.php';
require __DIR__ . '/includes/scoring.php';
require __DIR__ . '/includes/run.php';

$run = $_SESSION['run'] ?? null;
if ($run === null || $run['finished_at'] === null) {
    redirect(run_destination());
}
record_run($run, $_SESSION['user']);
$score = score_breakdown($run, true);
$elapsed = $run['finished_at'] - $run['started_at'];
$title = 'Vault Open';
require __DIR__ . '/includes/header.php';
?>
<section class="panel escape-panel">
    <h1>Vault Open</h1>
    <p>Escaped in <strong><?= e(format_time($elapsed)) ?></strong> with <strong><?= e(number_format($score['total'])) ?></strong> points.</p>
    <h2>Score breakdown</h2>
    <dl>
        <?php foreach ($score['items'] as $label => $points): ?>
            <dt><?= e($label) ?></dt><dd><?= e(number_format($points)) ?></dd>
        <?php endforeach; ?>
    </dl>
    <div class="actions">
        <form method="post" action="lobby.php">
            <?= csrf_field() ?>
            <button name="action" value="start">Play again</button>
        </form>
        <a href="leaderboard.php">Leaderboard</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
