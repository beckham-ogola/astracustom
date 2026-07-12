<?php
/** AstraCampus - Class Model (named ClassModel.php to avoid PHP reserved word 'Class') */

class ClassModel
{
    public static function all(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM classes WHERE deleted_at IS NULL';
        if ($activeOnly) { $sql .= ' AND is_active = 1'; }
        $sql .= ' ORDER BY level ASC';
        return db()->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM classes WHERE id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function studentCount(int $classId): int
    {
        $stmt = db()->prepare("SELECT COUNT(*) FROM students WHERE class_id = :id AND status = 'Active' AND deleted_at IS NULL");
        $stmt->execute(['id' => $classId]);
        return (int) $stmt->fetchColumn();
    }

    public static function create(string $name, ?string $description, ?int $createdBy): int
    {
        $maxLevel = (int) db()->query('SELECT COALESCE(MAX(level),0) FROM classes WHERE deleted_at IS NULL')->fetchColumn();
        $stmt = db()->prepare('INSERT INTO classes (name, level, description, created_by) VALUES (:n, :l, :d, :c)');
        $stmt->execute(['n' => $name, 'l' => $maxLevel + 1, 'd' => $description, 'c' => $createdBy]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $name, ?string $description, bool $isActive): void
    {
        $stmt = db()->prepare('UPDATE classes SET name = :n, description = :d, is_active = :a WHERE id = :id');
        $stmt->execute(['n' => $name, 'd' => $description, 'a' => $isActive ? 1 : 0, 'id' => $id]);
    }

    /** Returns [bool ok, string message] */
    public static function delete(int $id): array
    {
        $class = self::find($id);
        if (!$class) return [false, 'Class not found.'];

        $stmt = db()->prepare("SELECT COUNT(*) FROM students WHERE class_id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        if ((int)$stmt->fetchColumn() > 0) return [false, 'Cannot delete: students are linked to this class.'];

        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE class_assigned = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        if ((int)$stmt->fetchColumn() > 0) return [false, 'Cannot delete: teachers are linked to this class.'];

        $stmt = db()->prepare('SELECT COUNT(*) FROM fee_structure WHERE class_id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        if ((int)$stmt->fetchColumn() > 0) return [false, 'Cannot delete: fee structures are linked to this class.'];

        $stmt = db()->prepare('UPDATE classes SET deleted_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
        self::reorderLevels();
        return [true, 'Class deleted successfully.'];
    }

    public static function moveUp(int $id): void
    {
        $class = self::find($id);
        if (!$class) return;
        $stmt = db()->prepare('SELECT * FROM classes WHERE level < :lvl AND deleted_at IS NULL ORDER BY level DESC LIMIT 1');
        $stmt->execute(['lvl' => $class['level']]);
        $above = $stmt->fetch();
        if ($above) self::swapLevels($class['id'], $class['level'], $above['id'], $above['level']);
    }

    public static function moveDown(int $id): void
    {
        $class = self::find($id);
        if (!$class) return;
        $stmt = db()->prepare('SELECT * FROM classes WHERE level > :lvl AND deleted_at IS NULL ORDER BY level ASC LIMIT 1');
        $stmt->execute(['lvl' => $class['level']]);
        $below = $stmt->fetch();
        if ($below) self::swapLevels($class['id'], $class['level'], $below['id'], $below['level']);
    }

    private static function swapLevels(int $id1, int $lvl1, int $id2, int $lvl2): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            // temp level to avoid unique constraint clash
            $pdo->prepare('UPDATE classes SET level = -1 WHERE id = :id')->execute(['id' => $id1]);
            $pdo->prepare('UPDATE classes SET level = :l WHERE id = :id')->execute(['l' => $lvl1, 'id' => $id2]);
            $pdo->prepare('UPDATE classes SET level = :l WHERE id = :id')->execute(['l' => $lvl2, 'id' => $id1]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function reorderLevels(): void
    {
        $classes = self::all();
        $pdo = db();
        $level = 1;
        foreach ($classes as $c) {
            $pdo->prepare('UPDATE classes SET level = :l WHERE id = :id')->execute(['l' => $level, 'id' => $c['id']]);
            $level++;
        }
    }

    public static function getNext(int $currentClassId): ?array
    {
        $current = self::find($currentClassId);
        if (!$current) return null;
        $stmt = db()->prepare('SELECT * FROM classes WHERE level = :lvl AND deleted_at IS NULL');
        $stmt->execute(['lvl' => $current['level'] + 1]);
        return $stmt->fetch() ?: null;
    }

    public static function getPrevious(int $currentClassId): ?array
    {
        $current = self::find($currentClassId);
        if (!$current) return null;
        $stmt = db()->prepare('SELECT * FROM classes WHERE level = :lvl AND deleted_at IS NULL');
        $stmt->execute(['lvl' => $current['level'] - 1]);
        return $stmt->fetch() ?: null;
    }

    public static function isHighestLevel(int $classId): bool
    {
        return self::getNext($classId) === null;
    }

    public static function isLowestLevel(int $classId): bool
    {
        return self::getPrevious($classId) === null;
    }
}
