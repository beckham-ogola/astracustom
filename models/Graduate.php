<?php
/** AstraCampus - Graduate Model (thin wrapper; primary logic lives in Student::graduate) */

class Graduate
{
    public static function all(): array
    {
        return Student::graduatesList();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM graduates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
