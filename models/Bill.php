<?php
/** AstraCampus - Bill Model */

class Bill
{
    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT b.*, bt.name AS bill_type_name,
                    (b.final_amount - COALESCE((SELECT SUM(p.amount_paid) FROM payments p WHERE p.bill_id = b.id AND p.deleted_at IS NULL),0)) AS remaining
             FROM bills b JOIN bill_types bt ON bt.id = b.bill_type_id
             WHERE b.id = :id AND b.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function forStudent(int $studentId): array
    {
        $stmt = db()->prepare(
            'SELECT b.*, bt.name AS bill_type_name,
                    COALESCE((SELECT SUM(p.amount_paid) FROM payments p WHERE p.bill_id = b.id AND p.deleted_at IS NULL),0) AS paid_amount,
                    (b.final_amount - COALESCE((SELECT SUM(p.amount_paid) FROM payments p WHERE p.bill_id = b.id AND p.deleted_at IS NULL),0)) AS remaining
             FROM bills b
             JOIN bill_types bt ON bt.id = b.bill_type_id
             WHERE b.student_id = :sid AND b.deleted_at IS NULL
             ORDER BY b.term DESC, bt.name ASC'
        );
        $stmt->execute(['sid' => $studentId]);
        return $stmt->fetchAll();
    }

    public static function unpaidForStudent(int $studentId): array
    {
        $all = self::forStudent($studentId);
        return array_values(array_filter($all, fn($b) => (float)$b['remaining'] > 0.009));
    }

    public static function existsForTerm(int $studentId, int $billTypeId, string $term): bool
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM bills WHERE student_id = :s AND bill_type_id = :b AND term = :t AND deleted_at IS NULL'
        );
        $stmt->execute(['s' => $studentId, 'b' => $billTypeId, 't' => $term]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(int $studentId, int $billTypeId, float $amount, float $discount, bool $sponsored, string $term, ?int $createdBy, ?int $sponsoredBy = null): int
    {
        $final = $sponsored ? 0.0 : max(0, $amount - $discount);
        $discountApplied = $sponsored ? $amount : $discount;
        $stmt = db()->prepare(
            'INSERT INTO bills (student_id, bill_type_id, amount, discount_applied, final_amount, is_sponsored, sponsored_by, term, created_by)
             VALUES (:s, :bt, :a, :d, :f, :sp, :spby, :term, :c)'
        );
        $stmt->execute([
            's' => $studentId, 'bt' => $billTypeId, 'a' => $amount, 'd' => $discountApplied,
            'f' => $final, 'sp' => $sponsored ? 1 : 0, 'spby' => $sponsored ? $sponsoredBy : null,
            'term' => $term, 'c' => $createdBy,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, float $amount, float $discount, bool $sponsored): void
    {
        $final = $sponsored ? 0.0 : max(0, $amount - $discount);
        $discountApplied = $sponsored ? $amount : $discount;
        $stmt = db()->prepare(
            'UPDATE bills SET amount = :a, discount_applied = :d, final_amount = :f, is_sponsored = :sp WHERE id = :id'
        );
        $stmt->execute(['a' => $amount, 'd' => $discountApplied, 'f' => $final, 'sp' => $sponsored ? 1 : 0, 'id' => $id]);
    }

    public static function delete(int $id): array
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM payments WHERE bill_id = :id AND deleted_at IS NULL');
        $stmt->execute(['id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return [false, 'Cannot delete: payments have been made against this bill.'];
        }
        db()->prepare('UPDATE bills SET deleted_at = NOW() WHERE id = :id')->execute(['id' => $id]);
        return [true, 'Bill deleted successfully.'];
    }

    public static function statusLabel(array $bill): string
    {
        if (!empty($bill['is_sponsored'])) return 'Sponsored';
        $remaining = $bill['remaining'] ?? ($bill['final_amount'] - ($bill['paid_amount'] ?? 0));
        return $remaining <= 0.009 ? 'Paid' : 'Active';
    }
}
