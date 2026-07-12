<?php
/** AstraCampus - Parent Portal Controller (public, no login required) */

class ParentController
{
    public static function index(): void
    {
        require __DIR__ . '/../views/parent/index.php';
    }

    public static function lookup(): void
    {
        // 3-second cooldown to prevent duplicate searches
        if (!empty($_SESSION['last_parent_search']) && (microtime(true) - $_SESSION['last_parent_search']) < 3) {
            flash('warning', 'Please wait a moment before searching again.');
            redirect('/parent');
        }
        $_SESSION['last_parent_search'] = microtime(true);

        $input = clean_input($_POST['admission_no'] ?? '');
        if (empty($input)) {
            flash('error', 'Please enter an admission number.');
            redirect('/parent');
        }

        $admissionNo = format_admission_search($input);
        $student = Student::findByAdmissionNo($admissionNo);

        if (!$student) {
            flash('error', 'No student found with that admission number.');
            redirect('/parent');
        }

        $bills = Bill::forStudent((int) $student['id']);
        $balance = Student::balanceFor((int) $student['id']);

        // Build payments list with receipt info per bill
        $billIds = array_column($bills, 'id');
        $payments = [];
        if (!empty($billIds)) {
            $placeholders = implode(',', array_fill(0, count($billIds), '?'));
            $stmt = db()->prepare(
                "SELECT p.*, u.full_name AS collected_by_name FROM payments p
                 LEFT JOIN users u ON u.id = p.collected_by
                 WHERE p.bill_id IN ($placeholders) AND p.deleted_at IS NULL
                 ORDER BY p.payment_date DESC"
            );
            $stmt->execute($billIds);
            foreach ($stmt->fetchAll() as $p) {
                $payments[$p['bill_id']][] = $p;
            }
        }

        require __DIR__ . '/../views/parent/result.php';
    }
}
