<?php

function startSessionIfNeeded(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool
{
    startSessionIfNeeded();
    return isset($_SESSION['user']);
}

function currentUser(): ?array
{
    startSessionIfNeeded();

    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }

    return null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();

    if ($_SESSION['user']['role'] !== 'admin') {
        echo 'Access denied';
        exit;
    }
}

function logout(): void
{
    startSessionIfNeeded();
    $_SESSION = [];
    session_destroy();
}
