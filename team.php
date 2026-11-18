<?php
require_once __DIR__ . '/includes/config.php';
$title = 'Team';
$members = [
    ['Aishwarya Nuni', 'Project Leader · Product Owner', 'Game logic and the Sentinel AI: puzzles, scoring, room flow, escape screen.', 'AN'],
    ['Adithya Godavarthi', 'Scrum Master', 'Login, registration, sessions, security checks and the leaderboard.', 'AG'],
    ['Nirupamarayudu Chitturi', 'Developer · QA Lead', 'Page layout, CSS, animations, accessibility and the test plan.', 'NC'],
];
require __DIR__ . '/includes/header.php';
?>
<section class="room-hero reveal">
    <p class="eyebrow">Team Session Breakers · CSC 6370</p>
    <h1 class="display display--xl"><?= reveal('Vault Sentinel') ?></h1>
    <p class="lede">An escape game with three PHP-checked puzzle rooms, guarded by a rule-based AI called Sentinel that gives graded hints and adjusts each room to how the player is doing.</p>
</section>
<ul class="team-grid">
    <?php foreach ($members as $index => [$name, $role, $work, $initials]): ?>
        <li class="panel member reveal" style="--d:<?= e($index + 1) ?>">
            <span class="member__badge" aria-hidden="true"><?= e($initials) ?></span>
            <h2 class="member__name"><?= e($name) ?></h2>
            <p class="member__role"><?= e($role) ?></p>
            <p class="muted"><?= e($work) ?></p>
        </li>
    <?php endforeach; ?>
</ul>
<?php require __DIR__ . '/includes/footer.php'; ?>
