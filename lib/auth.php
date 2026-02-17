<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function is_logged_in(): bool
{
    start_session();
    return isset($_SESSION['admin_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function login(string $username, string $password): bool
{
    $stmt = get_db()->prepare('SELECT id, password_hash FROM admins WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return false;
    }

    if (!password_verify($password, $admin['password_hash'])) {
        return false;
    }

    start_session();
    $_SESSION['admin_id'] = (int)$admin['id'];

    return true;
}

function logout(): void
{
    start_session();
    $_SESSION = [];
    session_destroy();
}
