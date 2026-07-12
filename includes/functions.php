<?php
/**
 * AstraCampus - Shared Helper Functions
 */

require_once __DIR__ . '/../config/database.php';

// -------------------------------------------------------------------
// Output / Sanitization
// -------------------------------------------------------------------

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function clean_input($value)
{
    if (is_array($value)) {
        return array_map('clean_input', $value);
    }
    return trim($value ?? '');
}

// -------------------------------------------------------------------
// CSRF Protection
// -------------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(419);
        die('Invalid or expired security token. Please go back, refresh the page, and try again.');
    }
}

// -------------------------------------------------------------------
// Flash Messages
// -------------------------------------------------------------------

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

// -------------------------------------------------------------------
// Passwords (MD5 as specified — see README for bcrypt upgrade path)
// -------------------------------------------------------------------

function hash_password(string $password): string
{
    return md5($password);
}

function verify_password(string $password, string $hash): bool
{
    return hash_equals($hash, md5($password));
}

// -------------------------------------------------------------------
// Settings
// -------------------------------------------------------------------

function get_setting(string $key, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $stmt = db()->query('SELECT setting_key, setting_value FROM settings');
        foreach ($stmt->fetchAll() as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache[$key] ?? $default;
}

function set_setting(string $key, string $value, ?int $userId = null): void
{
    $stmt = db()->prepare(
        'INSERT INTO settings (setting_key, setting_value, updated_by) VALUES (:k, :v, :u)
         ON DUPLICATE KEY UPDATE setting_value = :v2, updated_by = :u2'
    );
    $stmt->execute(['k' => $key, 'v' => $value, 'u' => $userId, 'v2' => $value, 'u2' => $userId]);
}

// -------------------------------------------------------------------
// Number Generators
// -------------------------------------------------------------------

function next_admission_no(): string
{
    $current = get_setting('next_admission_no', 'ACS-001');
    // increment sequence for next time
    if (preg_match('/^([A-Za-z]+-)(\d+)$/', $current, $m)) {
        $prefix = $m[1];
        $num = (int) $m[2];
        $next = $prefix . str_pad((string)($num + 1), strlen($m[2]), '0', STR_PAD_LEFT);
        set_setting('next_admission_no', $next);
    }
    return $current;
}

function next_receipt_no(): string
{
    $current = get_setting('next_receipt_no', 'RCP-' . date('Y') . '-00001');
    if (preg_match('/^([A-Za-z]+-\d{4}-)(\d+)$/', $current, $m)) {
        $prefix = $m[1];
        $num = (int) $m[2];
        $next = $prefix . str_pad((string)($num + 1), strlen($m[2]), '0', STR_PAD_LEFT);
        set_setting('next_receipt_no', $next);
    }
    return $current;
}

function format_admission_search(string $input): string
{
    $input = trim($input);
    if (is_numeric($input)) {
        return 'ACS-' . str_pad($input, 3, '0', STR_PAD_LEFT);
    }
    if (preg_match('/^ACS-?\d+$/i', $input)) {
        // normalize e.g. ACS1 -> ACS-001
        preg_match('/(\d+)$/', $input, $m);
        return 'ACS-' . str_pad($m[1], 3, '0', STR_PAD_LEFT);
    }
    return strtoupper($input);
}

// -------------------------------------------------------------------
// Formatting
// -------------------------------------------------------------------

function money(float $amount): string
{
    return 'KES ' . number_format($amount, 2);
}

function calculate_age(string $dob): int
{
    $birth = new DateTime($dob);
    $today = new DateTime('today');
    return $birth->diff($today)->y;
}

// -------------------------------------------------------------------
// Audit Log
// -------------------------------------------------------------------

function audit_log(string $action, ?string $table = null, ?int $recordId = null, $oldData = null, $newData = null, ?string $details = null): void
{
    $stmt = db()->prepare(
        'INSERT INTO audit_logs (user_id, action, table_name, record_id, old_data, new_data, details, ip_address, user_agent)
         VALUES (:user_id, :action, :table_name, :record_id, :old_data, :new_data, :details, :ip, :ua)'
    );
    $stmt->execute([
        'user_id'    => $_SESSION['user']['id'] ?? null,
        'action'     => $action,
        'table_name' => $table,
        'record_id'  => $recordId,
        'old_data'   => $oldData !== null ? json_encode($oldData) : null,
        'new_data'   => $newData !== null ? json_encode($newData) : null,
        'details'    => $details,
        'ip'         => $_SERVER['REMOTE_ADDR'] ?? null,
        'ua'         => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);
}

// -------------------------------------------------------------------
// Redirects
// -------------------------------------------------------------------

function redirect(string $path): void
{
    header('Location: ' . base_url($path));
    exit;
}

function base_url(string $path = ''): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    if ($base === '/' || $base === '\\') { $base = ''; }
    return $base . '/' . ltrim($path, '/');
}

function full_url(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . base_url($path);
}

// -------------------------------------------------------------------
// File Uploads
// -------------------------------------------------------------------

function handle_upload(array $file, string $destDir, array $allowedExt, int $maxBytes): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload error code: ' . $file['error']);
    }
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('File exceeds maximum allowed size.');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        throw new RuntimeException('File type not allowed. Allowed: ' . implode(', ', $allowedExt));
    }
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $filename = bin2hex(random_bytes(16)) . '_' . time() . '.' . $ext;
    $target = rtrim($destDir, '/') . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Failed to store uploaded file.');
    }
    return $filename;
}

// -------------------------------------------------------------------
// Messaging (SMS) - simulated gateway, logs to `messages` table
// -------------------------------------------------------------------

function send_sms(string $phone, string $message, ?string $recipientName = null, string $type = 'receipt'): bool
{
    // NOTE: Integrate with a real SMS gateway (e.g. Africa's Talking) here.
    // For now, we log the message as sent so the workflow is fully functional.
    $stmt = db()->prepare(
        'INSERT INTO messages (recipient_phone, recipient_name, message, message_type, sent_by, status)
         VALUES (:phone, :name, :msg, :type, :sent_by, :status)'
    );
    $stmt->execute([
        'phone'    => $phone,
        'name'     => $recipientName,
        'msg'      => $message,
        'type'     => $type,
        'sent_by'  => $_SESSION['user']['id'] ?? null,
        'status'   => 'sent',
    ]);
    return true;
}

// -------------------------------------------------------------------
// Navigation — grouped sections drive the desktop sidebar; a flattened,
// capped-at-5 subset (everyday items only — setup/admin screens are
// reached via the header's Administration icon instead) drives the
// mobile bottom bar. Both are role-aware and built from one definition
// so the two surfaces never drift out of sync.
// -------------------------------------------------------------------

function build_nav_sections(string $role, string $currentPath): array
{
    $isActive = function (array $prefixes) use ($currentPath): bool {
        foreach ($prefixes as $p) {
            if (strpos($currentPath, $p) === 0) return true;
        }
        return false;
    };

    $sections = [];

    // ---- Overview ----
    if (in_array($role, ['developer', 'admin', 'accountant'], true)) {
        $home = ['href' => 'dashboard', 'label' => 'Home', 'icon' => 'fa-house', 'active' => $isActive(['/dashboard'])];
    } else {
        $home = ['href' => 'teacher/dashboard', 'label' => 'Home', 'icon' => 'fa-house', 'active' => $isActive(['/teacher/dashboard'])];
    }
    $sections[] = ['label' => null, 'items' => [$home]];

    // ---- Academics ----
    if (in_array($role, ['developer', 'admin', 'teacher'], true)) {
        $sections[] = ['label' => 'Academics', 'items' => [
            ['href' => 'students', 'label' => 'Students', 'icon' => 'fa-user-graduate', 'active' => $isActive(['/students'])],
        ]];
    }

    // ---- Finance ----
    $finance = [];
    if (in_array($role, ['developer', 'admin', 'accountant'], true)) {
        $finance[] = ['href' => 'billing', 'label' => 'Billing', 'icon' => 'fa-file-invoice', 'active' => $isActive(['/billing', '/bill-types', '/fee-structure', '/bills'])];
    }
    if (in_array($role, ['developer', 'admin', 'accountant', 'teacher'], true)) {
        $finance[] = ['href' => 'payments', 'label' => 'Payments', 'icon' => 'fa-money-bill-wave', 'active' => $isActive(['/payments', '/receipts'])];
    }
    if (in_array($role, ['developer', 'admin', 'accountant'], true)) {
        $finance[] = ['href' => 'reports', 'label' => 'Reports', 'icon' => 'fa-chart-line', 'active' => $isActive(['/reports'])];
    } elseif ($role === 'teacher') {
        $finance[] = ['href' => 'teacher/class-report', 'label' => 'Reports', 'icon' => 'fa-chart-line', 'active' => $isActive(['/teacher/class-report'])];
    }
    if ($finance) $sections[] = ['label' => 'Finance', 'items' => $finance];

    // ---- Administration (setup/config screens, developer & admin only) ----
    if (in_array($role, ['developer', 'admin'], true)) {
        $sections[] = ['label' => 'Administration', 'items' => [
            ['href' => 'classes', 'label' => 'Classes', 'icon' => 'fa-school', 'active' => $isActive(['/classes'])],
            ['href' => 'users', 'label' => 'Users', 'icon' => 'fa-users', 'active' => $isActive(['/users'])],
            ['href' => 'templates', 'label' => 'PDF Templates', 'icon' => 'fa-file-pdf', 'active' => $isActive(['/templates'])],
            ['href' => 'audit-logs', 'label' => 'Audit Logs', 'icon' => 'fa-clock-rotate-left', 'active' => $isActive(['/audit-logs'])],
            ['href' => 'settings', 'label' => 'Settings', 'icon' => 'fa-sliders', 'active' => $isActive(['/settings'])],
        ]];
    }

    return $sections;
}

/**
 * Flattens the nav sections into the mobile bottom bar's items. The
 * Administration group is deliberately left out here — it's config/setup
 * work staff do rarely, so on mobile it lives behind the header's gear
 * icon instead of eating one of the bottom bar's 5 precious slots.
 */
function build_bottom_nav(array $navSections): array
{
    $items = [];
    foreach ($navSections as $section) {
        if ($section['label'] === 'Administration') continue;
        foreach ($section['items'] as $item) $items[] = $item;
    }
    return array_slice($items, 0, 5);
}

// -------------------------------------------------------------------
// Pagination helper
// -------------------------------------------------------------------

function paginate_params(int $perPage): array
{
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $perPage;
    return [$page, $offset];
}
