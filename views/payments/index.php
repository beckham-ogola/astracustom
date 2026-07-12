<?php $pageTitle = 'Payments'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="tab-strip" data-tab-group="payments">
  <button class="tab-btn" data-tab-btn="collect">Collect Payment</button>
  <button class="tab-btn" data-tab-btn="recent">Recent Receipts</button>
</div>

<!-- ===== TAB: Collect Payment ===== -->
<div data-tab-panel-group="payments" data-tab-panel="collect" class="hidden">

  <div class="bg-white rounded-xl shadow-sm p-4 mb-4 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
    <form method="GET" class="flex items-center gap-2">
      <input type="hidden" name="tab" value="collect">
      <label class="text-sm text-gray-600">Class:</label>
      <select name="class_filter" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm" <?= (is_teacher() && !is_admin()) ? 'disabled' : '' ?>>
        <option value="all_active" <?= $filter === 'all_active' ? 'selected' : '' ?>>All Active Students</option>
        <?php foreach ($classes as $c): ?>
          <option value="<?= $c['id'] ?>" <?= (string)$filter === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
        <option value="graduates" <?= $filter === 'graduates' ? 'selected' : '' ?>>Graduates</option>
      </select>
    </form>
    <input type="text" placeholder="Search by name or admission number..." data-search-input="tbody tr[data-row]"
           class="w-full sm:w-72 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
  </div>

  <div class="bg-white rounded-xl shadow-sm p-4">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Admission No</th><th>Full Name</th><th>Class</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($students as $s): $isClear = (float)$s['balance'] <= 0.009; ?>
        <tr data-row data-search-text="<?= e($s['admission_no'] . ' ' . $s['full_name']) ?>" class="border-b last:border-0">
          <td class="py-2 font-medium"><?= e($s['admission_no']) ?></td>
          <td><?= e($s['full_name']) ?></td>
          <td><?= e($s['class_name'] ?? '—') ?></td>
          <td class="<?= $isClear ? 'balance-zero' : 'balance-positive' ?>"><?= money($s['balance']) ?></td>
          <td><?php if ($isClear): ?><span class="badge badge-paid">Clear</span><?php else: ?><span class="badge badge-active">Owing</span><?php endif; ?></td>
          <td>
            <?php if (!$isClear): ?>
              <a href="<?= base_url('payments/' . $s['id'] . '/pay') ?>" class="bg-purple-600 hover:bg-purple-700 text-white text-xs px-3 py-1.5 rounded-lg"><i class="fa-solid fa-money-bill-wave mr-1"></i>Pay</a>
            <?php else: ?>
              <span class="text-gray-400 text-xs">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($students)): ?><tr><td colspan="6" class="py-6 text-center text-gray-400">No students found.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- ===== TAB: Recent Receipts ===== -->
<div data-tab-panel-group="payments" data-tab-panel="recent" class="hidden">
  <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
    <input type="text" placeholder="Search receipts by student, admission no, or receipt number..." data-search-input="tbody tr[data-row-recent]"
           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
  </div>

  <div class="bg-white rounded-xl shadow-sm p-4">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Date</th><th>Receipt No</th><th>Student</th><th>Bill Type</th><th>Amount</th><th>Method</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($recentPayments as $p): ?>
        <tr data-row-recent data-search-text="<?= e($p['student_name'] . ' ' . $p['admission_no'] . ' ' . $p['receipt_no']) ?>" class="border-b last:border-0">
          <td class="py-2"><?= date('d M Y, h:i A', strtotime($p['payment_date'])) ?></td>
          <td class="font-medium"><?= e($p['receipt_no']) ?></td>
          <td><?= e($p['student_name']) ?> <span class="text-gray-400">(<?= e($p['admission_no']) ?>)</span></td>
          <td><?= e($p['bill_type_name']) ?></td>
          <td><?= money($p['amount_paid']) ?></td>
          <td><?= e($p['payment_method']) ?></td>
          <td><a href="<?= base_url('receipts/' . $p['id']) ?>" class="text-purple-600 hover:underline text-xs">View</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($recentPayments)): ?><tr><td colspan="7" class="py-6 text-center text-gray-400">No payments recorded yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
