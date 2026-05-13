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

function logout(): void
{
    startSessionIfNeeded();
    $_SESSION = [];
    session_destroy();
}
