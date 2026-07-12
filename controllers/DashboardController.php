<?php
/** AstraCampus - Dashboard Controller */

class DashboardController
{
    public static function index(): void
    {
        require_role(['developer', 'admin', 'accountant']);

        $stats = [
            'active_students' => Student::countByStatus('Active'),
            'total_classes'   => count(ClassModel::all(true)),
            'today_collection'=> Payment::summaryForDate(date('Y-m-d'))['total'],
            'graduates'       => count(Graduate::all()),
        ];

        $recentPayments = array_slice(Payment::forDate(date('Y-m-d')), -5);
        $balances = Student::balances();
        $totalOutstanding = array_sum(array_map(fn($b) => (float)$b['balance'], $balances));

        require __DIR__ . '/../views/dashboard/index.php';
    }
}
