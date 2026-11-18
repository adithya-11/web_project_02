<?php
declare(strict_types=1);

function seed_number(string $seed, string $label, int $min, int $max): int
{
    $sample = hexdec(substr(hash('sha256', $seed . ':' . $label), 0, 7));
    return $min + $sample % ($max - $min + 1);
}

function shift_letters(string $phrase, int $shift): string
{
    $shift = ($shift % 26 + 26) % 26;
    return preg_replace_callback('/[A-Z]/', static fn (array $match): string =>
        chr(65 + (ord($match[0]) - 65 + $shift) % 26), strtoupper($phrase));
}

function transform_grid(int $mask, int $tier): int
{
    $result = 0;
    for ($index = 0; $index < 9; $index++) {
        if (($mask & (1 << $index)) === 0) {
            continue;
        }

        $row = intdiv($index, 3);
        $column = $index % 3;
        $destination = $tier === 1
            ? $row * 3 + ($column + 1) % 3
            : $column * 3 + (2 - $row);
        $result |= 1 << $destination;
    }
    return $tier === 3 ? $result ^ 511 : $result;
}

function ledger_puzzle(string $seed, int $tier): array
{
    $terms = [seed_number($seed, 'ledger-start', 2, 8)];
    $addition = seed_number($seed, 'ledger-add', 2, 7);
    $multiplier = seed_number($seed, 'ledger-multiply', 2, 3);
    for ($step = 0; $step < 6; $step++) {
        $last = $terms[$step];
        $terms[] = match ($tier) {
            1 => $last + $addition,
            2 => $last * $multiplier + $addition,
            3 => $step % 2 === 0 ? $last + $addition : $last * $multiplier,
        };
    }
    return [
        'type' => 'number',
        'examples' => array_slice($terms, 0, 5),
        'answer' => $terms[5],
        'skip_answer' => $terms[6],
        'addition' => $addition,
        'multiplier' => $multiplier,
    ];
}

function cipher_puzzle(string $seed, int $tier): array
{
    $phrases = match ($tier) {
        1 => ['OPEN VAULT', 'GOLD KEY', 'LOCK ROOM', 'TURN DIAL'],
        2 => ['CHECK THE LEDGER', 'TURN THE HANDLE', 'FOLLOW THE SIGNAL', 'FIND THE LEVER'],
        3 => ['THE LAST DOOR OPENS', 'READ THE HIDDEN MESSAGE', 'MATCH THE VAULT PATTERN', 'KEEP THE INNER GATE CLOSED'],
    };
    [$minimum, $maximum] = match ($tier) {
        1 => [1, 4],
        2 => [5, 8],
        3 => [9, 12],
    };
    $answer = $phrases[seed_number($seed, 'cipher-phrase', 0, count($phrases) - 1)];
    $shift = seed_number($seed, 'cipher-shift', $minimum, $maximum);
    return [
        'type' => 'phrase',
        'examples' => shift_letters($answer, $shift),
        'answer' => $answer,
        'shift' => $shift,
        'shift_range' => [$minimum, $maximum],
    ];
}

function pattern_puzzle(string $seed, int $tier): array
{
    // One filled corner and one filled edge produce a visible change on every step.
    $corners = [0, 2, 6, 8];
    $edges = [1, 3, 5, 7];
    $first = (1 << $corners[seed_number($seed, 'grid-corner', 0, 3)])
        | (1 << $edges[seed_number($seed, 'grid-edge', 0, 3)]);
    $second = transform_grid($first, $tier);
    $third = transform_grid($second, $tier);
    return [
        'type' => 'grid',
        'examples' => [$first, $second, $third],
        'answer' => transform_grid($third, $tier),
    ];
}

function puzzle_for(string $seed, int $room, int $tier): array
{
    return match ($room) {
        1 => ledger_puzzle($seed, $tier),
        2 => cipher_puzzle($seed, $tier),
        3 => pattern_puzzle($seed, $tier),
    };
}

function read_answer(string $type): array
{
    if ($type === 'grid') {
        $cells = filter_input(INPUT_POST, 'cells', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
        if ($cells === null) {
            return [0, []];
        }
        if (!is_array($cells) || count($cells) > 9) {
            return [null, []];
        }

        $mask = 0;
        $checked = [];
        foreach ($cells as $cell) {
            if (!is_string($cell) || !preg_match('/\A[0-8]\z/', $cell)) {
                return [null, []];
            }
            $index = (int) $cell;
            $bit = 1 << $index;
            if (($mask & $bit) !== 0) {
                return [null, []];
            }
            $mask |= $bit;
            $checked[] = $index;
        }
        return [$mask, $checked];
    }

    $raw = trim(post_string('answer'));
    if ($type === 'number') {
        $value = filter_input(INPUT_POST, 'answer', FILTER_VALIDATE_INT);
        return [is_int($value) ? $value : null, substr($raw, 0, 12)];
    }
    if (strlen($raw) > 100 || !preg_match('/\A[A-Za-z]+(?:[ \t]+[A-Za-z]+)*\z/', $raw)) {
        return [null, substr($raw, 0, 100)];
    }
    return [strtoupper(preg_replace('/[ \t]+/', ' ', $raw)), $raw];
}
