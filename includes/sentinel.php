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

/* ---------- Mistake analysis ---------- */

function mirror_grid(int $mask): int
{
    $result = 0;
    for ($index = 0; $index < 9; $index++) {
        if (($mask & (1 << $index)) !== 0) {
            $row = intdiv($index, 3);
            $result |= 1 << ($row * 3 + (2 - $index % 3));
        }
    }
    return $result;
}

function sentinel_feedback(array $puzzle, int|string $answer, int $tier): string
{
    if ($puzzle['type'] === 'number') {
        if ($answer === $puzzle['skip_answer']) {
            return 'You jumped one step too far ahead. Sentinel wants the very next number.';
        }
        return $answer > $puzzle['answer']
            ? 'Too high. The next number is smaller than that.'
            : 'Too low. The next number is larger than that.';
    }

    if ($puzzle['type'] === 'phrase') {
        $cipher = $puzzle['examples'];
        for ($back = 1; $back < 26; $back++) {
            if ($back !== $puzzle['shift'] && shift_letters($cipher, -$back) === $answer) {
                return $back <= 13
                    ? 'Right direction, wrong amount. You shifted back ' . $back . ' places.'
                    : 'You shifted the wrong way. Move the letters backward, not forward.';
            }
        }
        $expected = explode(' ', $puzzle['answer']);
        $given = explode(' ', (string) $answer);
        $right = 0;
        foreach ($expected as $position => $word) {
            if (($given[$position] ?? '') === $word) {
                $right++;
            }
        }
        if ($right > 0) {
            return $right . ' of ' . count($expected) . ' words are already right.';
        }
        return 'Not quite. Check that every letter moves by the same amount.';
    }

    $last = $puzzle['examples'][2];
    $mirror = mirror_grid($last);
    if ($tier === 3) {
        $mirror ^= 511;
    }
    if ($tier >= 2 && $answer === $mirror) {
        return 'You mirrored the grid. Sentinel expected it to turn, not flip.';
    }
    $right = 9 - substr_count(decbin(((int) $answer ^ $puzzle['answer']) & 511), '1');
    return $right . ' of 9 cells are right.';
}

/* ---------- Graduate extension: adaptive tiers ---------- */

function sentinel_next_tier(int $tier, int $seconds, int $wrong, int $hints, int $room = 1): array
{
    $plural = static fn (int $n, string $word): string => $n . ' ' . $word . ($n === 1 ? '' : 's');

    if ($seconds < 60 && $hints === 0 && $wrong <= 1) {
        $next = min(3, $tier + 1);
        $reason = 'you cleared Room ' . $room . ' in ' . $seconds . ' seconds without hints'
            . ($wrong === 1 ? ' and with only one mistake' : '');
        $direction = $next > $tier ? 'up' : 'max';
    } elseif ($seconds > 180 || $hints >= 2 || $wrong >= 4) {
        $next = max(1, $tier - 1);
        $causes = [];
        if ($seconds > 180) {
            $causes[] = 'Room ' . $room . ' took you ' . format_time($seconds);
        }
        if ($hints >= 2) {
            $causes[] = 'you used ' . $plural($hints, 'hint');
        }
        if ($wrong >= 4) {
            $causes[] = 'you gave ' . $plural($wrong, 'wrong answer');
        }
        $last = array_pop($causes);
        $reason = $causes === [] ? $last : implode(', ', $causes) . ' and ' . $last;
        if ($seconds <= 180) {
            $reason .= ' in Room ' . $room;
        }
        $direction = $next < $tier ? 'down' : 'min';
    } else {
        $next = $tier;
        $reason = 'you solved Room ' . $room . ' in ' . format_time($seconds) . ' with '
            . $plural($wrong, 'wrong answer') . ' and ' . $plural($hints, 'hint');
        $direction = 'same';
    }
    return ['tier' => $next, 'reason' => $reason, 'direction' => $direction];
}

function sentinel_explain(int $nextRoom, array $decision): string
{
    $verb = match ($decision['direction']) {
        'up' => 'moves up to Tier ' . $decision['tier'],
        'down' => 'drops to Tier ' . $decision['tier'],
        'max' => 'stays at Tier 3, the top tier,',
        'min' => 'stays at Tier 1, the easiest tier,',
        default => 'stays at Tier ' . $decision['tier'],
    };
    return 'Room ' . $nextRoom . ' ' . $verb . ' because ' . $decision['reason'] . '.';
}
