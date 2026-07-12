<?php
/** AstraCampus - Auth Controller: login, logout, user management */

class AuthController
{
    public static function showLogin(): void
    {
        if (is_logged_in()) { redirect('/dashboard'); }
        require __DIR__ . '/../views/auth/login.php';
    }

    public static function login(): void
    {
        verify_csrf();

        // 3-second cooldown to prevent duplicate submissions
        if (!empty($_SESSION['last_login_attempt']) && (microtime(true) - $_SESSION['last_login_attempt']) < 3) {
            flash('warning', 'Please wait a moment before trying again.');
            redirect('/login');
        }
        $_SESSION['last_login_attempt'] = microtime(true);

        $username = clean_input($_POST['username'] ?? '');
        $idNumber = clean_input($_POST['id_number'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = User::findByUsernameAndId($username, $idNumber);

        if (!$user || !$user['is_active'] || !verify_password($password, $user['password'])) {
            audit_log('login_failed', 'users', $user['id'] ?? null, null, null, "Failed login attempt for username: {$username}");
            flash('error', 'Invalid username, ID number, or password.');
            redirect('/login');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'             => $user['id'],
            'username'       => $user['username'],
            'full_name'      => $user['full_name'],
            'role'           => $user['role'],
            'class_assigned' => $user['class_assigned'],
        ];

        User::updateLastLogin($user['id']);
        audit_log('login', 'users', $user['id'], null, null, 'Successful login');

        if ($user['role'] === 'teacher') {
            redirect('/teacher/dashboard');
        }
        redirect('/dashboard');
    }

    public static function logout(): void
    {
        $user = current_user();
        if ($user) {
            audit_log('logout', 'users', $user['id'], null, null, 'User logged out');
        }
        $_SESSION = [];
        session_destroy();
        redirect('/parent');
    }

    // ---------------- User Management ----------------

    public static function users(): void
    {
        require_role(['developer', 'admin']);
        $users = User::all();
        $classes = ClassModel::all(true);
        require __DIR__ . '/../views/system/users.php';
    }

    public static function storeUser(): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();

        $data = [
            'username'       => clean_input($_POST['username'] ?? ''),
            'id_number'      => clean_input($_POST['id_number'] ?? ''),
            'password'       => $_POST['password'] ?? '',
            'full_name'      => clean_input($_POST['full_name'] ?? ''),
            'email'          => clean_input($_POST['email'] ?? ''),
            'phone'          => clean_input($_POST['phone'] ?? ''),
            'role'           => clean_input($_POST['role'] ?? 'teacher'),
            'class_assigned' => $_POST['class_assigned'] ?? null,
            'created_by'     => current_user()['id'],
        ];

        if ($data['role'] === 'developer' && !is_developer()) {
            flash('error', 'Only a developer can create another developer account.');
            redirect('/users');
        }

        if (empty($data['username']) || empty($data['id_number']) || empty($data['password']) || empty($data['full_name'])) {
            flash('error', 'Please fill in all required fields.');
            redirect('/users');
        }

        if (User::usernameOrIdExists($data['username'], $data['id_number'])) {
            flash('error', 'Username or ID number already exists.');
            redirect('/users');
        }

        $id = User::create($data);
        audit_log('create_user', 'users', $id, null, $data, 'Created new user');
        flash('success', 'User created successfully.');
        redirect('/users');
    }

    public static function resetPassword(array $params): void
    {
        require_role(['developer', 'admin']);
        verify_csrf();
        $id = (int) $params['id'];
        $newPassword = $_POST['new_password'] ?? '';
        if (strlen($newPassword) < 4) {
            flash('error', 'Password must be at least 4 characters.');
            redirect('/users');
        }
        User::resetPassword($id, $newPassword);
        audit_log('reset_password', 'users', $id, null, null, 'Password reset by admin');
        flash('success', 'Password reset successfully.');
        redirect('/users');
    }

    public static function deleteUser(array $params): void
    {
        require_role(['developer']);
        verify_csrf();
        $id = (int) $params['id'];
        User::softDelete($id);
        audit_log('delete_user', 'users', $id, null, null, 'User deleted');
        flash('success', 'User deleted successfully.');
        redirect('/users');
    }

    // ---------------- Account Management ----------------

    public static function account(): void
    {
        require_login();
        require __DIR__ . '/../views/system/account.php';
    }

    public static function changePassword(): void
    {
        require_login();
        verify_csrf();
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($new) < 4) {
            flash('error', 'New password must be at least 4 characters.');
            redirect('/account');
        }
        if ($new !== $confirm) {
            flash('error', 'New password and confirmation do not match.');
            redirect('/account');
        }

        $ok = User::changePassword(current_user()['id'], $current, $new);
        if (!$ok) {
            flash('error', 'Current password is incorrect.');
            redirect('/account');
        }
        audit_log('change_password', 'users', current_user()['id'], null, null, 'User changed own password');
        flash('success', 'Password changed successfully.');
        redirect('/account');
    }
}
