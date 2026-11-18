<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/csrf.php';
require __DIR__ . '/includes/auth_guard.php';
require __DIR__ . '/includes/run.php';
require __DIR__ . '/includes/scoring.php';

$run = $_SESSION['run'] ?? null;
if ($run === null || $run['finished_at'] !== null) {
    redirect(run_destination());
}
$title = ROOMS[$run['room']];
$gridLegend = 'Pattern Lock controls';
$checkedCells = [];
require __DIR__ . '/includes/header.php';
?>
<h1><?= e($title) ?></h1>
<div class="split">
<section class="panel">
    <h2>Room <?= e($run['room']) ?> of 3</h2>
    <p>Puzzle play opens in Phase 5.</p>
    <?php require __DIR__ . '/includes/pattern_input.php'; ?>
    <a href="lobby.php">Return to the Entry Hall</a>
</section>
<aside class="panel">
    <h2>Security clearance</h2>
    <meter min="0" max="100" value="50">50%</meter>
    <p>50%</p>
</aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
