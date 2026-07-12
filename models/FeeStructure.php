<?php
/** AstraCampus - Fee Structure Model */

class FeeStructure
{
    public static function forClass(int $classId): array
    {
        $stmt = db()->prepare(
            'SELECT fs.*, bt.name AS bill_type_name FROM fee_structure fs
             JOIN bill_types bt ON bt.id = fs.bill_type_id
             WHERE fs.class_id = :c AND fs.deleted_at IS NULL AND bt.is_active = 1
             ORDER BY bt.name ASC'
        );
        $stmt->execute(['c' => $classId]);
        return $stmt->fetchAll();
    }

    public static function forBillType(int $billTypeId): array
    {
        $stmt = db()->prepare(
            'SELECT c.id AS class_id, c.name AS class_name, fs.id AS fee_structure_id, COALESCE(fs.amount, 0) AS amount
             FROM classes c
             LEFT JOIN fee_structure fs ON fs.class_id = c.id AND fs.bill_type_id = :bt AND fs.deleted_at IS NULL
             WHERE c.deleted_at IS NULL AND c.is_active = 1
             ORDER BY c.level ASC'
        );
        $stmt->execute(['bt' => $billTypeId]);
        return $stmt->fetchAll();
    }

    public static function get(int $billTypeId, int $classId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM fee_structure WHERE bill_type_id = :b AND class_id = :c AND deleted_at IS NULL');
        $stmt->execute(['b' => $billTypeId, 'c' => $classId]);
        return $stmt->fetch() ?: null;
    }

    public static function upsert(int $billTypeId, int $classId, float $amount, ?int $declaredBy): void
    {
        $stmt = db()->prepare(
            'INSERT INTO fee_structure (bill_type_id, class_id, amount, declared_by)
             VALUES (:b, :c, :a, :d)
             ON DUPLICATE KEY UPDATE amount = :a2, declared_by = :d2, deleted_at = NULL'
        );
        $stmt->execute(['b' => $billTypeId, 'c' => $classId, 'a' => $amount, 'd' => $declaredBy, 'a2' => $amount, 'd2' => $declaredBy]);
    }

    public static function all(): array
    {
        return db()->query(
            'SELECT fs.*, bt.name AS bill_type_name, c.name AS class_name FROM fee_structure fs
             JOIN bill_types bt ON bt.id = fs.bill_type_id
             JOIN classes c ON c.id = fs.class_id
             WHERE fs.deleted_at IS NULL
             ORDER BY bt.name, c.level'
        )->fetchAll();
    }
}
