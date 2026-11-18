<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/run.php';

if (!isset($_SESSION['run']) || $_SESSION['run']['finished_at'] !== null) {
    redirect(run_destination());
}

$run = &$_SESSION['run'];
$number = $run['room'];
$room = &$run['rooms'][$number];
$puzzle = puzzle_for($run['seed'], $number, $room['tier']);
$roomKey = $run['id'] . ':' . $number;
$now = time();

if (is_post()) {
    require_post();
    if (!hash_equals($roomKey, post_string('room_key'))) {
        redirect('room.php');
    }

    // The lock lives in the session, so a forged form sent during it is refused too.
    if (sentinel_lock_left($room, $now) > 0) {
        $_SESSION['room_feedback'] = ['lock', 'The answer box is sealed. Wait for the lock to clear.', null];
        redirect('room.php');
    }

    [$answer, $input] = read_answer($puzzle['type']);
    if ($answer === $puzzle['answer']) {
        // Graduate extension: Sentinel picks the next room's tier from this room's play.
        $nextTier = $room['tier'];
        $change = null;
        if ($number < count(ROOMS)) {
            $decision = sentinel_next_tier($room['tier'], $now - $room['entered_at'], $room['wrong'], $room['hints'], $number);
            $nextTier = $decision['tier'];
            $change = ['direction' => $decision['direction'], 'text' => sentinel_explain($number + 1, $decision)];
        }
        unset($room);
        advance_run($run, $now, $nextTier, $change);
        if ($run['finished_at'] !== null) {
            record_run($run, $_SESSION['user']);
        }
        unset($_SESSION['room_feedback'], $_SESSION['hint_notice']);
        $_SESSION['door_open'] = true;
        redirect(run_destination());
    }

    if ($answer === null) {
        $message = match ($puzzle['type']) {
            'number' => 'Enter a whole number, such as 42 or -7. Nothing was deducted.',
            'phrase' => 'Use letters and single spaces only. Nothing was deducted.',
            'grid' => 'Select cells using the nine squares. Nothing was deducted.',
        };
        $_SESSION['room_feedback'] = ['error', $message, $input];
    } else {
        $message = sentinel_feedback($puzzle, $answer, $room['tier']) . ' 50 points deducted.';
        if (sentinel_register_wrong($room, $now)) {
            $message .= ' Three wrong in a row: Sentinel sealed the answer box for ' . LOCK_SECONDS . ' seconds.';
        }
        $_SESSION['room_feedback'] = ['wrong', $message, $input];
    }
    redirect('room.php');
}

[$feedbackType, $message, $input] = take('room_feedback', ['', '', null]);
$doorOpen = take('door_open', false);
$tierNotice = $room['change']['text'] ?? '';
$hintNotice = take('hint_notice', '');
$lockLeft = sentinel_lock_left($room, $now);
$hints = array_slice(sentinel_hints($puzzle, $number, $room['tier']), 0, $room['hints']);
$score = score_breakdown($run, false)['total'];
$clearance = sentinel_clearance($run);
$refreshAfter = $lockLeft > 0 ? $lockLeft + 1 : null;
$title = ROOMS[$number];
require __DIR__ . '/includes/header.php';
?>
<?php if ($doorOpen): ?>
    <div class="door-transition" aria-hidden="true">
        <div class="door-frame">
            <div class="vault-door">
                <div class="vault-door__ring"></div>
                <div class="vault-door__wheel"><i></i><i></i><i></i></div>
                <div class="vault-door__bolts"></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<section class="room-hero reveal">
    <p class="eyebrow">Room <?= e($number) ?> of <?= e(count(ROOMS)) ?> · Tier <?= e($room['tier']) ?></p>
    <h1 class="display"><?= reveal($title) ?></h1>
    <ol class="door-rail" aria-label="Vault progress">
        <?php foreach (ROOMS as $index => $name): ?>
            <?php $state = $index < $number ? 'open' : ($index === $number ? 'current' : 'sealed'); ?>
            <li class="door-rail__step is-<?= $state ?>"<?= $index === $number ? ' aria-current="step"' : '' ?>>
                <span class="door-rail__knob" aria-hidden="true"></span>
                <span><?= e($name) ?><span class="sr-only"> (<?= e($state) ?>)</span></span>
            </li>
        <?php endforeach; ?>
    </ol>
</section>

<?php if ($tierNotice !== ''): ?>
    <p class="transmission reveal" role="status"><span class="transmission__tag">Sentinel</span> <?= e($tierNotice) ?></p>
<?php endif; ?>

<div class="room-layout">
<section class="panel panel--glow puzzle reveal" style="--d:1" aria-labelledby="puzzle-title">
    <h2 id="puzzle-title" class="panel__title">
        <?= e(match ($puzzle['type']) {
            'number' => 'Find the next number',
            'phrase' => 'Decode the phrase',
            'grid' => 'Complete the pattern',
        }) ?>
    </h2>

    <?php if ($puzzle['type'] === 'number'): ?>
        <p class="muted">Each number follows from the one before it. What comes next?</p>
        <ol class="ledger" aria-label="Sequence">
            <?php foreach ($puzzle['examples'] as $index => $value): ?>
                <li style="--i:<?= e($index) ?>"><?= e($value) ?></li>
            <?php endforeach; ?>
            <li class="ledger__unknown" style="--i:5"><span aria-hidden="true">?</span><span class="sr-only">next number</span></li>
        </ol>
    <?php elseif ($puzzle['type'] === 'phrase'): ?>
        <p class="muted">Every letter was shifted forward by the same amount, between
            <?= e($puzzle['shift_range'][0]) ?> and <?= e($puzzle['shift_range'][1]) ?> places.
            Shift each one back, wrapping from A to Z.</p>
        <p class="sr-only">Cipher text: <?= e($puzzle['examples']) ?></p>
        <div class="cipher" aria-hidden="true">
            <?php $letter = 0; foreach (explode(' ', $puzzle['examples']) as $word): ?>
                <span class="cipher__word">
                    <?php foreach (str_split($word) as $char): ?>
                        <span class="cipher__char" style="--i:<?= e($letter++) ?>"><?= e($char) ?></span>
                    <?php endforeach; ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="muted">Three grids follow one hidden rule. Fill in the fourth.</p>
        <div class="examples">
            <?php foreach ($puzzle['examples'] as $index => $mask): ?>
                <figure class="example" style="--i:<?= e($index) ?>">
                    <div class="grid grid--example" role="img"
                         aria-label="Grid <?= e($index + 1) ?>: filled cells <?= e(implode(', ', array_map(
                             static fn (int $c): string => 'row ' . (intdiv($c, 3) + 1) . ' column ' . ($c % 3 + 1),
                             array_values(array_filter(range(0, 8), static fn (int $c): bool => ($mask & (1 << $c)) !== 0))
                         ))) ?>">
                        <?php for ($cell = 0; $cell < 9; $cell++): ?>
                            <span class="cell<?= ($mask & (1 << $cell)) !== 0 ? ' filled' : '' ?>" style="--c:<?= e($cell) ?>"></span>
                        <?php endfor; ?>
                    </div>
                    <figcaption>Grid <?= e($index + 1) ?></figcaption>
                </figure>
            <?php endforeach; ?>
            <figure class="example example--next" style="--i:3">
                <div class="grid grid--example grid--unknown" aria-hidden="true"><span>?</span></div>
                <figcaption>Grid 4</figcaption>
            </figure>
        </div>
    <?php endif; ?>

    <?php if ($message !== ''): ?>
        <p class="feedback feedback--<?= e($feedbackType) ?>" role="alert"><?= e($message) ?></p>
    <?php endif; ?>

    <?php if ($lockLeft > 0): ?>
        <div class="lock" role="status">
            <p><strong>Answer box sealed.</strong> It reopens in <?= e($lockLeft) ?> seconds and this page refreshes on its own.</p>
            <div class="lock__bar" style="--lock:<?= e($lockLeft) ?>s"><span></span></div>
        </div>
    <?php endif; ?>

    <form method="post" action="room.php" class="answer-form">
        <?= csrf_field() ?>
        <input type="hidden" name="room_key" value="<?= e($roomKey) ?>">
        <fieldset class="answer-form__fields" <?= $lockLeft > 0 ? 'disabled' : '' ?>>
            <?php if ($puzzle['type'] === 'grid'): ?>
                <?php
                $gridLegend = 'Grid 4: tick the filled cells';
                $checkedCells = is_array($input) ? $input : [];
                require __DIR__ . '/includes/pattern_input.php';
                ?>
            <?php else: ?>
                <label for="answer" class="field-label"><?= $puzzle['type'] === 'number' ? 'Next number' : 'Decoded phrase' ?></label>
                <input id="answer" name="answer" class="field<?= $puzzle['type'] === 'phrase' ? ' field--mono' : '' ?>"
                    value="<?= e(is_string($input) ? $input : '') ?>"
                    maxlength="<?= $puzzle['type'] === 'number' ? '12' : '100' ?>"
                    inputmode="<?= $puzzle['type'] === 'number' ? 'numeric' : 'text' ?>"
                    autocomplete="off" spellcheck="false" required
                    <?= $message !== '' ? 'aria-invalid="true"' : '' ?>>
            <?php endif; ?>
            <button type="submit" class="btn btn--primary">Try the lock</button>
        </fieldset>
    </form>
</section>

<aside class="panel console reveal" style="--d:2" aria-labelledby="console-title">
    <div class="console__head">
        <span class="eye<?= $feedbackType === 'wrong' || $lockLeft > 0 ? ' eye--alert' : '' ?>" aria-hidden="true"><span></span></span>
        <div>
            <h2 id="console-title" class="panel__title">Sentinel</h2>
            <p class="muted small"><?= $lockLeft > 0 ? 'Lock engaged' : 'Watching this room' ?></p>
        </div>
    </div>

    <div class="meter" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= e($clearance) ?>" aria-label="Security clearance">
        <div class="meter__label"><span>Security clearance</span><strong><?= e($clearance) ?>%</strong></div>
        <div class="meter__track"><span style="--v:<?= e($clearance) ?>%"></span></div>
    </div>

    <dl class="stats">
        <div><dt>Score</dt><dd><?= e(number_format($score)) ?></dd></div>
        <div><dt>Wrong here</dt><dd><?= e($room['wrong']) ?></dd></div>
        <div><dt>Hints used</dt><dd><?= e($room['hints']) ?> / <?= e(MAX_HINTS) ?></dd></div>
    </dl>

    <section class="hints" aria-labelledby="hints-title">
        <h3 id="hints-title">Hints <span class="muted small">−<?= e(HINT_COST) ?> each</span></h3>
        <?php if ($hintNotice !== ''): ?><p class="hints__notice" role="status"><?= e($hintNotice) ?></p><?php endif; ?>
        <?php if ($hints === []): ?>
            <p class="muted small">Sentinel won't give you the answer, but it will point you the right way.</p>
        <?php else: ?>
            <ol class="hints__list">
                <?php foreach ($hints as $index => $hint): ?>
                    <li style="--i:<?= e($index) ?>"><?= e($hint) ?></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
        <form method="post" action="hint.php">
            <?= csrf_field() ?>
            <input type="hidden" name="room_key" value="<?= e($roomKey) ?>">
            <button type="submit" class="btn btn--ghost btn--block">
                <?= $room['hints'] < MAX_HINTS ? 'Request hint ' . e($room['hints'] + 1) : 'No hints left' ?>
            </button>
        </form>
    </section>

    <a class="link-quiet" href="lobby.php">← Return to the Entry Hall</a>
</aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
