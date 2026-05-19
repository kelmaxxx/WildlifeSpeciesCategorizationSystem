<?php
/**
 * Session guard for public pages that require a logged-in user
 * (mirror of admin/auth.php). Bounces to login.php if no session.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/mongo.php';
require_once __DIR__ . '/lib/csrf.php';

function require_user(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}
