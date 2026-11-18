<?php
declare(strict_types=1);

require_once __DIR__ . '/sentinel.php';

const ROOMS = [1 => 'The Ledger', 2 => 'The Cipher Gate', 3 => 'The Pattern Lock'];

function new_room(int $tier, int $enteredAt): array
{
    return [
        'tier' => $tier,
        'entered_at' => $enteredAt,
        'solved_at' => null,
        'wrong' => 0,
        'hints' => 0,
        'wrong_streak' => 0,
        'locked_until' => 0,
    ];
}

function start_run(int $now): void
{
    unset($_SESSION['door_open'], $_SESSION['room_feedback']);
    $_SESSION['run'] = [
        'id' => bin2hex(random_bytes(16)),
        'seed' => bin2hex(random_bytes(32)),
        'room' => 1,
        'started_at' => $now,
        'finished_at' => null,
        'rooms' => [1 => new_room(2, $now)],
    ];
}

function advance_run(array &$run, int $now, int $nextTier = 2, ?array $change = null): void
{
    $room = $run['room'];
    $run['rooms'][$room]['solved_at'] = $now;
    if ($room === count(ROOMS)) {
        $run['finished_at'] = $now;
        return;
    }
    $run['room']++;
    $run['rooms'][$run['room']] = new_room($nextTier, $now) + ['change' => $change];
}

function run_destination(): string
{
    $run = $_SESSION['run'] ?? null;
    if ($run === null) {
        return 'lobby.php';
    }
    if ($run['finished_at'] !== null) {
        return 'escape.php';
    }
    return 'room.php';
}

function record_run(array $run, string $user): void
{
    // Only finished runs count, and each run is recorded once even if escape.php is reloaded.
    if ($run['finished_at'] === null || isset($_SESSION['recorded_runs'][$run['id']])) {
        return;
    }
    $_SESSION['recorded_runs'][$run['id']] = true;
    $_SESSION['scores'][] = [
        'name' => $user,
        'score' => score_breakdown($run, true)['total'],
        'seconds' => $run['finished_at'] - $run['started_at'],
        'hints' => array_sum(array_column($run['rooms'], 'hints')),
        'tiers' => implode(', ', array_column($run['rooms'], 'tier')),
        'profile' => sentinel_profile($run)[0],
    ];
}

function top_runs(): array
{
    // Sort by score (high first), break ties by time (fast first), keep the top ten.
    $runs = $_SESSION['scores'] ?? [];
    usort($runs, static fn (array $a, array $b): int =>
        ($b['score'] <=> $a['score']) ?: ($a['seconds'] <=> $b['seconds']));
    return array_slice($runs, 0, 10);
}
