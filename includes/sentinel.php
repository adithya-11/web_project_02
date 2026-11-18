<?php
declare(strict_types=1);

require_once __DIR__ . '/puzzles.php';
require_once __DIR__ . '/scoring.php';

/*
 * Sentinel: the vault's rule-based guard.
 * Every decision here reads only server-side session data, so a player
 * cannot talk Sentinel into anything by editing a form.
 */

const HINT_COST = 100;
const MAX_HINTS = 3;

/* ---------- Graded hints ---------- */

function sentinel_hints(array $puzzle, int $room, int $tier): array
{
    if ($room === 1) {
        return match ($tier) {
            1 => [
                'Look at the gap between each pair of neighbouring numbers.',
                'Every step changes the number by exactly the same amount.',
                'Add ' . $puzzle['addition'] . ' to the last number.',
            ],
            2 => [
                'Each number is built from the one before it using two operations.',
                'The first operation is a multiplication, the second is an addition.',
                'Multiply the last number by ' . $puzzle['multiplier'] . ', then add ' . $puzzle['addition'] . '.',
            ],
            default => [
                'Two different rules take turns from one step to the next.',
                'One step adds a fixed amount; the step after it multiplies.',
                'The next step adds ' . $puzzle['addition'] . ' to the last number.',
            ],
        };
    }

    if ($room === 2) {
        $words = explode(' ', $puzzle['answer']);
        return [
            'Every letter was pushed forward by the same amount. Push them back.',
            'Decode just the first word (' . strlen($words[0]) . ' letters) and try each shift in the range.',
            'The shift is ' . $puzzle['shift'] . '. Move every letter back ' . $puzzle['shift'] . ' places.',
        ];
    }

    return match ($tier) {
        1 => [
            'Track one filled cell from Grid 1 to Grid 2 to Grid 3.',
            'Every filled cell moves in the same direction, and the grid does not turn.',
            'Each filled cell slides one column to the right, wrapping to the left edge.',
        ],
        2 => [
            'The whole grid moves as a single piece.',
            'It turns around the centre cell instead of sliding.',
            'Each grid is the previous one turned a quarter turn clockwise.',
        ],
        default => [
            'Two things happen on every step. Count the filled cells in each grid.',
            'Filled and empty cells swap places, and the grid also turns.',
            'Turn the last grid a quarter turn clockwise, then swap filled and empty.',
        ],
    };
}
