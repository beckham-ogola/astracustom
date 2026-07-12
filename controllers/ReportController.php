<?php
/** AstraCampus - Report Controller */

class ReportController
{
    /**
     * Unified Reports hub: Daily Collection, Student Balances, Class
     * Collection, Graduates — now a promoted top-level section (previously
     * buried under the old "More" menu) so finance staff reach it in one tap.
     */
    public static function hub(): void
    {
        require_role(['developer', 'admin', 'accountant']);

        // ---- Daily Collection ----
        $date = clean_input($_GET['date'] ?? date('Y-m-d'));
        $transactions = Payment::forDate($date);
        $dailySummary = Payment::summaryForDate($date);

        // ---- Student Balances ----
        $classes = ClassModel::all(true);
        $balances = array_filter(Student::balances(), fn($b) => $b['status'] === 'Active');
        $classFilter = $_GET['class_filter'] ?? '';
        if (!empty($classFilter) && is_numeric($classFilter)) {
            $balances = array_filter($balances, fn($b) => (int)$b['class_id'] === (int)$classFilter);
        }
        $balances = array_values($balances);
        $balanceSummary = [
            'active'       => count($balances),
            'with_balance' => count(array_filter($balances, fn($b) => (float)$b['balance'] > 0.009)),
            'fully_paid'   => count(array_filter($balances, fn($b) => (float)$b['balance'] <= 0.009)),
            'outstanding'  => array_sum(array_map(fn($b) => (float)$b['balance'], $balances)),
        ];

        // ---- Class Collection ----
        $classRows = db()->query('SELECT * FROM v_class_collection ORDER BY class_id')->fetchAll();
        foreach ($classRows as &$r) {
            $r['balance'] = (float)$r['total_fees'] - (float)$r['collected_amount'];
            $r['collection_pct'] = $r['total_fees'] > 0 ? round(($r['collected_amount'] / $r['total_fees']) * 100, 1) : 0;
        }
        unset($r);
        $grandTotals = [
            'students'  => array_sum(array_column($classRows, 'student_count')),
            'fees'      => array_sum(array_column($classRows, 'total_fees')),
            'collected' => array_sum(array_column($classRows, 'collected_amount')),
            'balance'   => array_sum(array_column($classRows, 'balance')),
        ];

        // ---- Graduates ----
        $graduates = Graduate::all();

        require __DIR__ . '/../views/reports/hub.php';
    }
}
