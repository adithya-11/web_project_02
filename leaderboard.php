<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/run.php';
$runs = top_runs();
$title = 'Session Leaderboard';
require __DIR__ . '/includes/header.php';
?>
<section class="room-hero reveal">
    <p class="eyebrow">This session · top ten</p>
    <h1 class="display display--xl"><?= reveal('Leaderboard') ?></h1>
</section>
<?php if ($runs === []): ?>
    <section class="panel empty reveal" style="--d:1">
        <p class="empty__icon" aria-hidden="true">◎</p>
        <p>No one has escaped this session yet. Be the first.</p>
        <a class="btn btn--primary" href="<?= isset($_SESSION['user']) ? 'lobby.php' : 'index.php' ?>">Enter the vault</a>
    </section>
<?php else: ?>
    <?php $top = $runs[0]; ?>
    <section class="champion panel panel--glow reveal" style="--d:1" aria-label="Top escape">
        <span class="champion__crown" aria-hidden="true">♛</span><span class="champion__rank" aria-hidden="true">01</span>
        <div>
            <p class="eyebrow">Top escape</p>
            <p class="champion__name"><?= e($top['name']) ?></p>
            <p class="muted"><?= e(number_format($top['score'])) ?> points · <?= e(format_time($top['seconds'])) ?> · <?= e($top['profile'] ?? '') ?></p>
        </div>
    </section>
    <div class="panel table-wrap reveal" style="--d:2" tabindex="0" role="region" aria-label="Ranked escapes">
        <table class="board">
            <caption class="sr-only">Top escapes in this session, sorted by score then time</caption>
            <thead><tr><th scope="col">Rank</th><th scope="col">Agent</th><th scope="col">Score</th><th scope="col">Time</th><th scope="col">Hints</th><th scope="col">Tiers</th><th scope="col">Profile</th></tr></thead>
            <tbody>
            <?php foreach ($runs as $index => $entry): ?>
                <tr style="--i:<?= e($index) ?>"<?= $index < 3 ? ' class="is-podium"' : '' ?>>
                    <th scope="row"><?= e(sprintf('%02d', $index + 1)) ?></th>
                    <td><?= e($entry['name']) ?></td>
                    <td class="num"><?= e(number_format($entry['score'])) ?></td>
                    <td class="num"><?= e(format_time($entry['seconds'])) ?></td>
                    <td class="num"><?= e($entry['hints']) ?></td>
                    <td><?= e($entry['tiers']) ?></td>
                    <td><?= e($entry['profile'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
