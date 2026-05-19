<?php
/**
 * Per-session CSRF tokens. Pages render csrf_field() inside their <form>;
 * POST handlers call csrf_check() before doing anything else.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
         . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function csrf_check(): void
{
    $supplied = $_POST['csrf_token'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!$expected || !is_string($supplied) || !hash_equals($expected, $supplied)) {
        http_response_code(400);
        die('Invalid or missing CSRF token. <a href="javascript:history.back()">Go back</a> and try again.');
    }
}
