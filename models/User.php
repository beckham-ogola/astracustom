<?php
/** AstraCampus - User Model */

class User
{
    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByUsernameAndId(string $username, string $idNumber): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = :u AND id_number = :i AND deleted_at IS NULL');
        $stmt->execute(['u' => $username, 'i' => $idNumber]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        return db()->query(
            'SELECT u.*, c.name AS class_name FROM users u
             LEFT JOIN classes c ON c.id = u.class_assigned
             WHERE u.deleted_at IS NULL ORDER BY u.created_at DESC'
        )->fetchAll();
    }

    public static function usernameOrIdExists(string $username, string $idNumber): bool
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE (username = :u OR id_number = :i) AND deleted_at IS NULL');
        $stmt->execute(['u' => $username, 'i' => $idNumber]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO users (username, id_number, password, full_name, email, phone, role, class_assigned, created_by)
             VALUES (:username, :id_number, :password, :full_name, :email, :phone, :role, :class_assigned, :created_by)'
        );
        $stmt->execute([
            'username'       => $data['username'],
            'id_number'      => $data['id_number'],
            'password'       => hash_password($data['password']),
            'full_name'      => $data['full_name'],
            'email'          => $data['email'] ?: null,
            'phone'          => $data['phone'] ?: null,
            'role'           => $data['role'],
            'class_assigned' => $data['role'] === 'teacher' ? ($data['class_assigned'] ?: null) : null,
            'created_by'     => $data['created_by'] ?? null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function resetPassword(int $id, string $newPassword): void
    {
        $stmt = db()->prepare('UPDATE users SET password = :p WHERE id = :id');
        $stmt->execute(['p' => hash_password($newPassword), 'id' => $id]);
    }

    public static function updateLastLogin(int $id): void
    {
        $stmt = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function softDelete(int $id): void
    {
        $stmt = db()->prepare('UPDATE users SET deleted_at = NOW(), is_active = 0 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function changePassword(int $id, string $current, string $new): bool
    {
        $user = self::findById($id);
        if (!$user || !verify_password($current, $user['password'])) {
            return false;
        }
        self::resetPassword($id, $new);
        return true;
    }
}
