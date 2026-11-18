<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/run.php';
require_once __DIR__ . '/includes/sentinel.php';

require_post();
if (!isset($_SESSION['run']) || $_SESSION['run']['finished_at'] !== null) {
    redirect(run_destination());
}

$run = &$_SESSION['run'];
$number = $run['room'];
if (!hash_equals($run['id'] . ':' . $number, post_string('room_key'))) {
    redirect('room.php');
}

$room = &$run['rooms'][$number];
// Brute-force detector: while the lockout is active, the hint button is locked too.
if (sentinel_lock_left($room, time()) > 0) {
    $_SESSION['hint_notice'] = 'Suspicious activity detected. Hints are locked until the lockout ends. Nothing was deducted.';
} elseif ($room['hints'] < MAX_HINTS) {
    $room['hints']++;
    $_SESSION['hint_notice'] = 'Sentinel released hint ' . $room['hints'] . ' of ' . MAX_HINTS
        . '. ' . HINT_COST . ' points deducted.';
} else {
    $_SESSION['hint_notice'] = 'No hints left for this room. Nothing was deducted.';
}
redirect('room.php');
