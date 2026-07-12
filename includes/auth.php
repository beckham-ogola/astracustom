<?php
/**
 * AstraCampus - Authentication & Authorization Guards
 */

require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Roles allowed into the "Admin Dashboard" area (with feature-level restriction inside) */
const STAFF_ROLES = ['developer', 'admin', 'accountant', 'teacher'];

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('/login');
    }
}

function require_role(array $roles): void
{
    require_login();
    $user = current_user();
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
}

function is_developer(): bool { return current_user() && current_user()['role'] === 'developer'; }
function is_admin(): bool { return current_user() && in_array(current_user()['role'], ['developer', 'admin'], true); }
function is_accountant(): bool { return current_user() && current_user()['role'] === 'accountant'; }
function is_teacher(): bool { return current_user() && current_user()['role'] === 'teacher'; }

/** Can manage students, classes, bill types, fee structure, users, settings */
function can_manage(): bool
{
    return is_developer() || is_admin();
}

/** Can process payments / view financial reports */
function can_handle_payments(): bool
{
    $user = current_user();
    return $user && in_array($user['role'], ['developer', 'admin', 'accountant', 'teacher'], true);
}
