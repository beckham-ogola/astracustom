<?php
/** AstraCampus - Bill Type Model */

class BillType
{
    public static function all(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM bill_types WHERE deleted_at IS NULL';
        if ($activeOnly) $sql .= ' AND is_active = 1';
        $sql .= ' ORDER BY name ASC';
        return db()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM bill_types WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(string $name, ?string $description, ?int $createdBy): int
    {
        $stmt = db()->prepare('INSERT INTO bill_types (name, description, created_by) VALUES (:n, :d, :c)');
        $stmt->execute(['n' => $name, 'd' => $description, 'c' => $createdBy]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $name, ?string $description, bool $isActive): void
    {
        $stmt = db()->prepare('UPDATE bill_types SET name = :n, description = :d, is_active = :a WHERE id = :id');
        $stmt->execute(['n' => $name, 'd' => $description, 'a' => $isActive ? 1 : 0, 'id' => $id]);
    }

    public static function delete(int $id): array
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM bills WHERE bill_type_id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return [false, 'Cannot delete: bills exist for this bill type.'];
        }
        db()->prepare('UPDATE bill_types SET deleted_at = NOW() WHERE id = :id')->execute(['id' => $id]);
        db()->prepare('UPDATE fee_structure SET deleted_at = NOW() WHERE bill_type_id = :id')->execute(['id' => $id]);
        return [true, 'Bill type deleted successfully.'];
    }
}
