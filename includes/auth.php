<?php

session_start();

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function isEmployee(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employee';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ../public/login.php');
        exit;
    }
}

function requireEmployee(): void
{
    if (!isEmployee()) {
        http_response_code(403);
        die('Je hebt geen toegang tot deze pagina.');
    }
}