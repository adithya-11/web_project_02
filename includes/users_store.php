<?php
declare(strict_types=1);

function with_users(bool $write, callable $operation): mixed
{
    $directory = getenv('VAULT_DATA_DIR') ?: dirname(__DIR__) . '/data';
    $path = $directory . '/users.json';
    $lock = @fopen($directory . '/users.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('Account directory is unavailable.');
    }

    try {
        if (!flock($lock, $write ? LOCK_EX : LOCK_SH)) {
            throw new RuntimeException('Account lock is unavailable.');
        }
        $users = [];
        if (is_file($path)) {
            $contents = file_get_contents($path);
            if ($contents === false) {
                throw new RuntimeException('Account file could not be read.');
            }
            $users = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($users)) {
                throw new RuntimeException('Account file is invalid.');
            }
        }
        return $operation($users, $path);
    } finally {
        fclose($lock);
    }
}

function create_user(string $username, string $password): bool
{
    return with_users(true, static function (array $users, string $path) use ($username, $password): bool {
        $key = strtolower($username);
        if (isset($users[$key])) {
            return false;
        }
        $users[$key] = ['name' => $username, 'hash' => password_hash($password, PASSWORD_DEFAULT)];
        $json = json_encode((object) $users, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
        $temporary = dirname($path) . '/.users-' . bin2hex(random_bytes(16));
        try {
            if (file_put_contents($temporary, $json) !== strlen($json) || !rename($temporary, $path)) {
                throw new RuntimeException('Account could not be saved.');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
        return true;
    });
}

function verify_user(string $username, string $password): ?string
{
    $user = with_users(false, static fn (array $users) => $users[strtolower($username)] ?? null);
    if ($user === null || !password_verify($password, $user['hash'])) {
        return null;
    }
    return $user['name'];
}
