<?php
declare(strict_types=1);

function score_breakdown(array $run, bool $finished): array
{
    $wrong = array_sum(array_column($run['rooms'], 'wrong'));
    $hints = array_sum(array_column($run['rooms'], 'hints'));
    $tierBonus = 0;
    foreach ($run['rooms'] as $room) {
        if ($room['solved_at'] !== null) {
            $tierBonus += 75 * ($room['tier'] - 1);
        }
    }

    $seconds = $finished ? $run['finished_at'] - $run['started_at'] : 0;
    $breakdown = [
        'Base score' => 1000,
        'Wrong answers' => -50 * $wrong,
        'Hints' => -100 * $hints,
        'Time bonus' => $finished ? max(0, min(500, (600 - $seconds) * 10)) : 0,
        'Tier bonus' => $tierBonus,
    ];
    return ['items' => $breakdown, 'total' => max(0, array_sum($breakdown))];
}

function format_time(int $seconds): string
{
    return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
}
