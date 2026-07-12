<?php $pageTitle = 'Reports'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="tab-strip" data-tab-group="reports">
  <button class="tab-btn" data-tab-btn="daily-collection">Daily Collection</button>
  <button class="tab-btn" data-tab-btn="student-balances">Student Balances</button>
  <button class="tab-btn" data-tab-btn="class-collection">Class Collection</button>
  <button class="tab-btn" data-tab-btn="graduates">Graduates</button>
</div>

<!-- ===== TAB: Daily Collection ===== -->
<div data-tab-panel-group="reports" data-tab-panel="daily-collection" class="hidden">
  <div class="bg-white rounded-xl shadow-sm p-5 mb-4 flex flex-wrap gap-3 items-center justify-between no-print">
    <form method="GET" class="flex items-center gap-2">
      <input type="hidden" name="tab" value="daily-collection">
      <label class="text-sm text-gray-600">Date:</label>
      <input type="date" name="date" value="<?= e($date) ?>" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
    </form>
    <button onclick="printPage()" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg"><i class="fa-solid fa-print mr-1"></i> Print</button>
  </div>

  <div class="print-area">
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-gray-500 text-xs">Total Collected</p><p class="text-xl font-bold text-purple-700"><?= money($dailySummary['total']) ?></p></div>
      <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-gray-500 text-xs">Cash</p><p class="text-xl font-bold"><?= money($dailySummary['cash']) ?></p></div>
      <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-gray-500 text-xs">M-Pesa</p><p class="text-xl font-bold"><?= money($dailySummary['mpesa']) ?></p></div>
      <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-gray-500 text-xs">Bank Transfer</p><p class="text-xl font-bold"><?= money($dailySummary['bank']) ?></p></div>
      <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-gray-500 text-xs">Transactions</p><p class="text-xl font-bold"><?= (int)$dailySummary['count'] ?></p></div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
      <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Time</th><th>Receipt No</th><th>Student</th><th>Adm No</th><th>Bill Type</th><th>Amount</th><th>Method</th><th>Collector</th></tr></thead>
        <tbody>
        <?php foreach ($transactions as $t): ?>
          <tr class="border-b last:border-0">
            <td class="py-2"><?= date('h:i A', strtotime($t['payment_date'])) ?></td>
            <td><?= e($t['receipt_no']) ?></td>
            <td><?= e($t['student_name']) ?></td>
            <td><?= e($t['admission_no']) ?></td>
            <td><?= e($t['bill_type_name']) ?></td>
            <td><?= money($t['amount_paid']) ?></td>
            <td><?= e($t['payment_method']) ?></td>
            <td><?= e($t['collector'] ?? 'N/A') ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($transactions)): ?><tr><td colspan="8" class="py-6 text-center text-gray-400">No transactions on this date.</td></tr><?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<!-- ===== TAB: Student Balances ===== -->
<div data-tab-panel-group="reports" data-tab-panel="student-balances" class="hidden">
  <div class="bg-white rounded-xl shadow-sm p-5 mb-4 flex flex-wrap gap-3 items-center justify-between no-print">
    <div class="flex flex-wrap gap-3 items-center">
      <form method="GET" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="student-balances">
        <label class="text-sm text-gray-600">Class:</label>
        <select name="class_filter" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
          <option value="">All Active Students</option>
          <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (string)($_GET['class_filter'] ?? '') === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
      <input type="text" placeholder="Search by name, admission no, or class..." data-search-input="tbody tr[data-row]"
             class="w-64 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
    </div>
    <button onclick="printPage()" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg"><i class="fa-solid fa-print mr-1"></i> Print</button>
  </div>

  <div class="print-area">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-gray-500 text-xs">Active Students</p><p class="text-xl font-bold"><?= $balanceSummary['active'] ?></p></div>
      <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-gray-500 text-xs">With Balance Owed</p><p class="text-xl font-bold text-red-600"><?= $balanceSummary['with_balance'] ?></p></div>
      <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-gray-500 text-xs">Fully Paid</p><p class="text-xl font-bold text-green-600"><?= $balanceSummary['fully_paid'] ?></p></div>
      <div class="bg-white rounded-xl shadow-sm p-4"><p class="text-gray-500 text-xs">Outstanding Total</p><p class="text-xl font-bold text-purple-700"><?= money($balanceSummary['outstanding']) ?></p></div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
      <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Adm No</th><th>Student Name</th><th>Class</th><th>Total Billed</th><th>Total Paid</th><th>Balance</th><th>Guardian Phone</th></tr></thead>
        <tbody>
        <?php foreach ($balances as $b): ?>
          <tr data-row data-search-text="<?= e($b['admission_no'] . ' ' . $b['full_name'] . ' ' . $b['class_name']) ?>" class="border-b last:border-0">
            <td class="py-2"><?= e($b['admission_no']) ?></td>
            <td><?= e($b['full_name']) ?></td>
            <td><?= e($b['class_name'] ?? '—') ?></td>
            <td><?= money($b['total_billed']) ?></td>
            <td><?= money($b['total_paid']) ?></td>
            <td class="<?= $b['balance'] > 0.009 ? 'balance-positive' : 'balance-zero' ?>"><?= money($b['balance']) ?></td>
            <td><?= e($b['guardian1_phone'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($balances)): ?><tr><td colspan="7" class="py-6 text-center text-gray-400">No students found.</td></tr><?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<!-- ===== TAB: Class Collection ===== -->
<div data-tab-panel-group="reports" data-tab-panel="class-collection" class="hidden">
  <div class="flex justify-end mb-4 no-print">
    <button onclick="printPage()" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg"><i class="fa-solid fa-print mr-1"></i> Print</button>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-5 print-area">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Class</th><th>Students</th><th>Total Fees</th><th>Collected</th><th>Balance</th><th>Collection %</th></tr></thead>
      <tbody>
      <?php foreach ($classRows as $r): ?>
        <tr class="border-b last:border-0">
          <td class="py-2 font-medium"><?= e($r['class_name']) ?></td>
          <td><?= (int)$r['student_count'] ?></td>
          <td><?= money($r['total_fees']) ?></td>
          <td><?= money($r['collected_amount']) ?></td>
          <td class="<?= $r['balance'] > 0.009 ? 'balance-positive' : 'balance-zero' ?>"><?= money($r['balance']) ?></td>
          <td class="w-48">
            <div class="flex items-center gap-2">
              <div class="flex-1 bg-gray-100 rounded-full h-2.5 overflow-hidden">
                <div class="bg-purple-600 h-full" style="width: <?= min(100, $r['collection_pct']) ?>%"></div>
              </div>
              <span class="text-xs text-gray-500 w-10"><?= $r['collection_pct'] ?>%</span>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($classRows)): ?><tr><td colspan="6" class="py-6 text-center text-gray-400">No classes found.</td></tr><?php endif; ?>
      </tbody>
      <tfoot>
        <tr class="border-t-2 font-bold text-gray-800">
          <td class="py-2">Grand Total</td>
          <td><?= (int)$grandTotals['students'] ?></td>
          <td><?= money($grandTotals['fees']) ?></td>
          <td><?= money($grandTotals['collected']) ?></td>
          <td class="<?= $grandTotals['balance'] > 0.009 ? 'balance-positive' : 'balance-zero' ?>"><?= money($grandTotals['balance']) ?></td>
          <td><?= $grandTotals['fees'] > 0 ? round(($grandTotals['collected'] / $grandTotals['fees']) * 100, 1) : 0 ?>%</td>
        </tr>
      </tfoot>
    </table>
    </div>
  </div>
</div>

<!-- ===== TAB: Graduates ===== -->
<div data-tab-panel-group="reports" data-tab-panel="graduates" class="hidden">
  <div class="flex justify-end mb-4 no-print">
    <button onclick="printPage()" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg"><i class="fa-solid fa-print mr-1"></i> Print</button>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-5 print-area">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Adm No</th><th>Student Name</th><th>Last Class</th><th>Graduated Date</th><th>Total Fees</th><th>Total Paid</th><th>Balance</th><th>Graduated By</th><th>Reason</th></tr></thead>
      <tbody>
      <?php foreach ($graduates as $g): $balance = $g['total_fees'] - $g['total_paid']; ?>
        <tr class="border-b last:border-0">
          <td class="py-2"><?= e($g['admission_no']) ?></td>
          <td><?= e($g['full_name']) ?></td>
          <td><?= e($g['last_class_name'] ?? '—') ?></td>
          <td><?= date('d M Y', strtotime($g['graduated_at'])) ?></td>
          <td><?= money($g['total_fees']) ?></td>
          <td><?= money($g['total_paid']) ?></td>
          <td class="<?= $balance > 0.009 ? 'balance-positive' : 'balance-zero' ?>"><?= money($balance) ?></td>
          <td><?= e($g['graduated_by_name'] ?? 'N/A') ?></td>
          <td><?= e($g['reason'] ?? '—') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($graduates)): ?><tr><td colspan="9" class="py-6 text-center text-gray-400">No graduates yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
