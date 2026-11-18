<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/run.php';

$run = $_SESSION['run'] ?? null;
if ($run === null || $run['finished_at'] === null) {
    redirect(run_destination());
}
record_run($run, $_SESSION['user']);
$firstView = take('door_open', false);
$score = score_breakdown($run, true);
$elapsed = $run['finished_at'] - $run['started_at'];
[$profile, $profileNote] = sentinel_profile($run);
$clearance = sentinel_clearance($run);

$times = [];
foreach ($run['rooms'] as $number => $room) {
    $times[$number] = $room['solved_at'] - $room['entered_at'];
}
$longest = max(1, ...array_values($times));
$forensics = sentinel_forensics($run, $times);
$title = 'Vault Open';
require __DIR__ . '/includes/header.php';
?>
<?php if ($firstView): ?>
    <div class="door-transition door-transition--final" aria-hidden="true">
        <div class="door-frame">
            <div class="vault-door">
                <div class="vault-door__ring"></div>
                <div class="vault-door__wheel"><i></i><i></i><i></i></div>
                <div class="vault-door__bolts"></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= particles(36, 'confetti', $run['id']) ?>
<section class="escape-hero reveal">
    <div class="burst" aria-hidden="true"></div>
    <p class="eyebrow">Run complete · <?= e(format_time($elapsed)) ?></p>
    <h1 class="display display--xl"><?= reveal('The vault is open') ?></h1>
    <p class="score-big"><span class="score-big__value count-up" style="--to:<?= e($score['total']) ?>" aria-hidden="true"></span><span class="sr-only"><?= e(number_format($score['total'])) ?></span> <span class="score-big__unit">points</span></p>
    <div class="medal reveal" style="--d:3" aria-hidden="true">
        <div class="medal__coin">
            <?php for ($z = -6; $z <= 6; $z += 2): ?><i class="medal__edge" style="--z:<?= e($z) ?>px"></i><?php endfor; ?>
            <span class="medal__face medal__face--front"><?= e(implode('', array_map(static fn (string $w): string => $w[0], preg_split('/[\s-]+/', $profile)))) ?></span>
            <span class="medal__face medal__face--back">VS</span>
        </div>
    </div>
    <p class="profile-badge"><span>Sentinel profile</span> <strong><?= e($profile) ?></strong></p>
    <p class="muted"><?= e($profileNote) ?></p>
</section>

<div class="report-grid">
    <section class="panel reveal" style="--d:1" aria-labelledby="breakdown-title">
        <h2 id="breakdown-title" class="panel__title">Score breakdown</h2>
        <dl class="breakdown">
            <?php foreach ($score['items'] as $label => $points): ?>
                <div class="<?= $points < 0 ? 'is-neg' : ($points > 0 ? 'is-pos' : '') ?>">
                    <dt><?= e($label) ?></dt><dd><?= e(($points > 0 && $label !== 'Base score' ? '+' : '') . number_format($points)) ?></dd>
                </div>
            <?php endforeach; ?>
            <div class="breakdown__total"><dt>Total</dt><dd><?= e(number_format($score['total'])) ?></dd></div>
        </dl>
        <p class="muted small">Final clearance <?= e($clearance) ?>%</p>
    </section>

    <section class="panel reveal" style="--d:2" aria-labelledby="report-title">
        <h2 id="report-title" class="panel__title">Sentinel's report</h2>
        <div class="table-wrap" tabindex="0" role="region" aria-label="Room by room report">
            <table class="report">
                <thead><tr><th scope="col">Room</th><th scope="col">Tier</th><th scope="col">Time</th><th scope="col">Wrong</th><th scope="col">Hints</th></tr></thead>
                <tbody>
                <?php foreach ($run['rooms'] as $number => $room): ?>
                    <tr>
                        <th scope="row"><?= e(ROOMS[$number]) ?></th>
                        <td><span class="tier-chip tier-chip--<?= e($room['tier']) ?>"><?= e($room['tier']) ?></span></td>
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
                <li style="--i:<?= e($number) ?>">
                    <span class="bars__label">Room <?= e($number) ?></span>
                    <span class="bars__track"><span class="bars__fill" style="--w:<?= e(round($seconds / $longest * 100)) ?>%"></span></span>
                    <span class="bars__value"><?= e(format_time($seconds)) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <h3>Forensics</h3>
        <dl class="breakdown forensics">
            <div class="is-neg"><dt>Critical bottleneck</dt><dd><?= e(ROOMS[$forensics['slowest']]) ?> · <?= e(format_time($times[$forensics['slowest']])) ?></dd></div>
            <div><dt>Most hints requested</dt><dd><?= e($forensics['hint_room'] === null ? 'None' : ROOMS[$forensics['hint_room']] . ' · ' . $run['rooms'][$forensics['hint_room']]['hints']) ?></dd></div>
        </dl>
        <h3>What Sentinel learned about you</h3>
        <p class="muted learned"><?= e($forensics['summary']) ?></p>

        <h3>Tier changes</h3>
        <ol class="changes">
            <li class="changes__item is-same">Room 1 always starts at Tier 2.</li>
            <?php foreach ($run['rooms'] as $room): ?>
                <?php if (!empty($room['change'])): ?>
                    <li class="changes__item is-<?= e($room['change']['direction']) ?>"><?= e($room['change']['text']) ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    </section>
</div>

<div class="actions actions--center reveal" style="--d:3">
    <form method="post" action="lobby.php">
        <?= csrf_field() ?>
        <button name="action" value="start" class="btn btn--primary">Play again</button>
    </form>
    <a class="btn btn--ghost" href="leaderboard.php">View leaderboard</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
