<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/scoring.php';
require_once __DIR__ . '/includes/run.php';

$run = $_SESSION['run'] ?? null;
if ($run === null || $run['finished_at'] === null) {
    redirect(run_destination());
}
record_run($run, $_SESSION['user']);
$score = score_breakdown($run, true);
$elapsed = $run['finished_at'] - $run['started_at'];
[$profile, $profileNote] = sentinel_profile($run);
$times = [];
foreach ($run['rooms'] as $number => $room) {
    $times[$number] = $room['solved_at'] - $room['entered_at'];
}
$longest = max(1, ...array_values($times));
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
    <p>Sentinel profile: <strong><?= e($profile) ?></strong>. <?= e($profileNote) ?></p>
    <h2>Sentinel report</h2>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Room by room report">
        <table>
            <thead><tr><th scope="col">Room</th><th scope="col">Tier</th><th scope="col">Time</th><th scope="col">Wrong</th><th scope="col">Hints</th></tr></thead>
            <tbody>
            <?php foreach ($run['rooms'] as $number => $room): ?>
                <tr>
                    <th scope="row"><?= e(ROOMS[$number]) ?></th>
                    <td><?= e($room['tier']) ?></td>
                    <td><?= e(format_time($times[$number])) ?></td>
                    <td><?= e($room['wrong']) ?></td>
                    <td><?= e($room['hints']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <h3>Time in each room</h3>
    <ul class="bars">
        <?php foreach ($times as $number => $seconds): ?>
            <li>
                <span class="bars__label">Room <?= e($number) ?></span>
                <span class="bars__track"><span class="bars__fill" style="--w:<?= e(round($seconds / $longest * 100)) ?>%"></span></span>
                <span class="bars__value"><?= e(format_time($seconds)) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <h3>Tier decisions</h3>
    <ol>
        <li>Room 1 always starts at Tier 2.</li>
        <?php foreach ($run['rooms'] as $room): ?>
            <?php if (!empty($room['change'])): ?><li><?= e($room['change']['text']) ?></li><?php endif; ?>
        <?php endforeach; ?>
    </ol>
    <div class="actions">
        <form method="post" action="lobby.php">
            <?= csrf_field() ?>
            <button name="action" value="start">Play again</button>
        </form>
        <a href="leaderboard.php">Leaderboard</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
