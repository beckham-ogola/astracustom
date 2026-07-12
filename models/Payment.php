<?php
/** AstraCampus - Payment Model */

class Payment
{
    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT p.*, s.full_name AS student_name, s.admission_no, s.class_id, c.name AS class_name,
                    s.guardian1_name, s.guardian1_phone, s.guardian1_phone_alt,
                    bt.name AS bill_type_name, u.full_name AS collected_by_name
             FROM payments p
             JOIN students s ON s.id = p.student_id
             LEFT JOIN classes c ON c.id = s.class_id
             JOIN bills b ON b.id = p.bill_id
             JOIN bill_types bt ON bt.id = b.bill_type_id
             LEFT JOIN users u ON u.id = p.collected_by
             WHERE p.id = :id AND p.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByReceiptNo(string $receiptNo): ?array
    {
        $stmt = db()->prepare(
            'SELECT p.*, s.full_name AS student_name, s.admission_no, s.class_id, c.name AS class_name,
                    s.guardian1_name, s.guardian1_phone, s.guardian1_phone_alt,
                    bt.name AS bill_type_name, u.full_name AS collected_by_name
             FROM payments p
             JOIN students s ON s.id = p.student_id
             LEFT JOIN classes c ON c.id = s.class_id
             JOIN bills b ON b.id = p.bill_id
             JOIN bill_types bt ON bt.id = b.bill_type_id
             LEFT JOIN users u ON u.id = p.collected_by
             WHERE p.receipt_no = :r AND p.deleted_at IS NULL'
        );
        $stmt->execute(['r' => $receiptNo]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $d): int
    {
        $receiptNo = next_receipt_no();
        $stmt = db()->prepare(
            'INSERT INTO payments (student_id, bill_id, amount_paid, payment_method, receipt_no, payment_details, payer_name, collected_by)
             VALUES (:sid, :bid, :amt, :method, :receipt, :details, :payer, :by)'
        );
        $stmt->execute([
            'sid'     => $d['student_id'],
            'bid'     => $d['bill_id'],
            'amt'     => $d['amount_paid'],
            'method'  => $d['payment_method'],
            'receipt' => $receiptNo,
            'details' => !empty($d['payment_details']) ? json_encode($d['payment_details']) : null,
            'payer'   => $d['payer_name'] ?: null,
            'by'      => $d['collected_by'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function recent(int $limit = 50): array
    {
        $stmt = db()->prepare(
            'SELECT p.*, s.full_name AS student_name, s.admission_no, bt.name AS bill_type_name, u.full_name AS collected_by_name
             FROM payments p
             JOIN students s ON s.id = p.student_id
             JOIN bills b ON b.id = p.bill_id
             JOIN bill_types bt ON bt.id = b.bill_type_id
             LEFT JOIN users u ON u.id = p.collected_by
             WHERE p.deleted_at IS NULL
             ORDER BY p.payment_date DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function forDate(string $date): array
    {
        $stmt = db()->prepare(
            'SELECT p.*, s.full_name AS student_name, s.admission_no, bt.name AS bill_type_name, u.full_name AS collector
             FROM payments p
             JOIN students s ON s.id = p.student_id
             JOIN bills b ON b.id = p.bill_id
             JOIN bill_types bt ON bt.id = b.bill_type_id
             LEFT JOIN users u ON u.id = p.collected_by
             WHERE DATE(p.payment_date) = :d AND p.deleted_at IS NULL
             ORDER BY p.payment_date ASC'
        );
        $stmt->execute(['d' => $date]);
        return $stmt->fetchAll();
    }

    public static function summaryForDate(string $date): array
    {
        $rows = self::forDate($date);
        $summary = ['total' => 0.0, 'cash' => 0.0, 'mpesa' => 0.0, 'bank' => 0.0, 'cheque' => 0.0, 'count' => count($rows)];
        foreach ($rows as $r) {
            $summary['total'] += (float) $r['amount_paid'];
            switch ($r['payment_method']) {
                case 'Cash': $summary['cash'] += (float) $r['amount_paid']; break;
                case 'M-Pesa': $summary['mpesa'] += (float) $r['amount_paid']; break;
                case 'Bank Transfer': $summary['bank'] += (float) $r['amount_paid']; break;
                case 'Cheque': $summary['cheque'] += (float) $r['amount_paid']; break;
            }
        }
        return $summary;
    }

    public static function addReprint(string $receiptNo, ?int $userId, ?string $originalDate, string $reason = ''): void
    {
        $stmt = db()->prepare(
            'INSERT INTO receipt_reprints (receipt_no, reprinted_by, original_date, reason) VALUES (:r, :u, :od, :reason)'
        );
        $stmt->execute(['r' => $receiptNo, 'u' => $userId, 'od' => $originalDate, 'reason' => $reason]);
    }
}
