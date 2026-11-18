<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/scoring.php';
require __DIR__ . '/includes/run.php';
$runs = top_runs();
$title = 'Session Leaderboard';
require __DIR__ . '/includes/header.php';
?>
<h1>Session Leaderboard</h1>
<?php if ($runs === []): ?>
    <p>No escapes this session yet.</p>
    <a href="<?= isset($_SESSION['user']) ? 'lobby.php' : 'index.php' ?>">Enter the vault</a>
<?php else: ?>
    <div class="table-wrap" tabindex="0" role="region" aria-label="Ranked escapes">
        <table>
            <caption>Top escapes in this session</caption>
            <thead><tr><th scope="col">Rank</th><th scope="col">Agent</th><th scope="col">Score</th><th scope="col">Time</th><th scope="col">Hints</th><th scope="col">Tiers</th></tr></thead>
            <tbody>
            <?php foreach ($runs as $index => $entry): ?>
                <tr>
                    <th scope="row"><?= e($index + 1) ?></th>
                    <td><?= e($entry['name']) ?></td>
                    <td><?= e(number_format($entry['score'])) ?></td>
                    <td><?= e(format_time($entry['seconds'])) ?></td>
                    <td><?= e($entry['hints']) ?></td>
                    <td><?= e($entry['tiers']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
