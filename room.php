<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/run.php';
require_once __DIR__ . '/includes/scoring.php';
require_once __DIR__ . '/includes/puzzles.php';
require_once __DIR__ . '/includes/sentinel.php';

if (!isset($_SESSION['run']) || $_SESSION['run']['finished_at'] !== null) {
    redirect(run_destination());
}

$run = &$_SESSION['run'];
$number = $run['room'];
$room = &$run['rooms'][$number];
$puzzle = puzzle_for($run['seed'], $number, $room['tier']);
$roomKey = $run['id'] . ':' . $number;
if (is_post()) {
    require_post();
    if (!hash_equals($roomKey, post_string('room_key'))) {
        redirect('room.php');
    }

    [$answer, $input] = read_answer($puzzle['type']);
    if ($answer === $puzzle['answer']) {
        advance_run($run, time());
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
            'grid' => 'Select cells using the nine checkboxes. Nothing was deducted.',
        };
    } else {
        $room['wrong']++;
        $room['wrong_streak']++;
        $message = sentinel_feedback($puzzle, $answer, $room['tier']) . ' 50 points deducted.';
    }
    $_SESSION['room_feedback'] = [$message, $input];
    redirect('room.php');
}

[$message, $input] = take('room_feedback', ['', null]);
$doorOpen = take('door_open', false);
$hintNotice = take('hint_notice', '');
$hints = array_slice(sentinel_hints($puzzle, $number, $room['tier']), 0, $room['hints']);
$score = score_breakdown($run, false)['total'];
$title = ROOMS[$number];
require __DIR__ . '/includes/header.php';
?>
<h1><?= e($title) ?></h1>
<div class="split">
<section class="panel<?= $doorOpen ? ' door-open' : '' ?>">
    <h2>Room <?= e($number) ?> of 3 · Tier <?= e($room['tier']) ?></h2>
    <?php if ($puzzle['type'] === 'number'): ?>
        <p>Find the next number in this sequence.</p>
        <p><strong><?= e(implode(', ', $puzzle['examples'])) ?></strong></p>
    <?php elseif ($puzzle['type'] === 'phrase'): ?>
        <p>Shift each letter backward by the same number of places. Wrap around from A to Z.</p>
        <p>The shift is between <?= e($puzzle['shift_range'][0]) ?> and <?= e($puzzle['shift_range'][1]) ?>.</p>
        <p><strong><?= e($puzzle['examples']) ?></strong></p>
    <?php else: ?>
        <p>Study the three examples, then select the filled cells in the next grid.</p>
        <p>■ Filled · □ Empty</p>
        <div class="examples">
            <?php foreach ($puzzle['examples'] as $index => $mask): ?>
                <figure>
                    <figcaption>Grid <?= e($index + 1) ?></figcaption>
                    <div class="grid">
                        <?php for ($cell = 0; $cell < 9; $cell++): ?>
                            <?php $filled = ($mask & (1 << $cell)) !== 0; ?>
                            <span class="cell<?= $filled ? ' filled' : '' ?>"><?= $filled ? '■' : '□' ?></span>
                        <?php endfor; ?>
                    </div>
                </figure>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($message !== ''): ?><p class="error" role="alert"><?= e($message) ?></p><?php endif; ?>
    <form method="post" action="room.php">
        <?= csrf_field() ?>
        <input type="hidden" name="room_key" value="<?= e($roomKey) ?>">
        <?php if ($puzzle['type'] === 'grid'): ?>
            <?php
            $gridLegend = 'Next grid';
            $checkedCells = is_array($input) ? $input : [];
            require __DIR__ . '/includes/pattern_input.php';
            ?>
        <?php else: ?>
            <label for="answer"><?= $puzzle['type'] === 'number' ? 'Next number' : 'Decoded phrase' ?></label>
            <input id="answer" name="answer" value="<?= e(is_string($input) ? $input : '') ?>"
                maxlength="<?= $puzzle['type'] === 'number' ? '12' : '100' ?>"
                inputmode="<?= $puzzle['type'] === 'number' ? 'numeric' : 'text' ?>" required>
        <?php endif; ?>
        <button type="submit">Try the lock</button>
    </form>
</section>
<aside class="panel">
    <h2>Run status</h2>
    <p>Current score <strong><?= e(number_format($score)) ?></strong></p>
    <p>Wrong answers in this room <strong><?= e($room['wrong']) ?></strong></p>
    <p>Hints used in this room <strong><?= e($room['hints']) ?> of <?= e(MAX_HINTS) ?></strong></p>
    <h3>Sentinel hints (−<?= e(HINT_COST) ?> each)</h3>
    <?php if ($hintNotice !== ''): ?><p role="status"><?= e($hintNotice) ?></p><?php endif; ?>
    <?php if ($hints !== []): ?>
        <ol>
            <?php foreach ($hints as $hint): ?><li><?= e($hint) ?></li><?php endforeach; ?>
        </ol>
    <?php endif; ?>
    <form method="post" action="hint.php">
        <?= csrf_field() ?>
        <input type="hidden" name="room_key" value="<?= e($roomKey) ?>">
        <button type="submit">Request hint</button>
    </form>
    <a href="lobby.php">Return to the Entry Hall</a>
</aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
