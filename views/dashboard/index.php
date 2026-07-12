<?php $pageTitle = 'Dashboard'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
  <div class="stat-tile bg-gradient-to-br from-purple-600 to-purple-700">
    <i class="fa-solid fa-user-graduate stat-icon"></i>
    <p class="text-purple-100 text-xs font-medium">Active Students</p>
    <p class="text-2xl font-display font-bold mt-1"><?= number_format($stats['active_students']) ?></p>
  </div>
  <div class="stat-tile bg-gradient-to-br from-blue-500 to-blue-600">
    <i class="fa-solid fa-school stat-icon"></i>
    <p class="text-blue-100 text-xs font-medium">Active Classes</p>
    <p class="text-2xl font-display font-bold mt-1"><?= number_format($stats['total_classes']) ?></p>
  </div>
  <div class="stat-tile bg-gradient-to-br from-mint-600 to-mint-700">
    <i class="fa-solid fa-sack-dollar stat-icon"></i>
    <p class="text-green-100 text-xs font-medium">Today's Collection</p>
    <p class="text-xl font-display font-bold mt-1"><?= money($stats['today_collection']) ?></p>
  </div>
  <div class="stat-tile bg-gradient-to-br from-amber-500 to-amber-600">
    <i class="fa-solid fa-award stat-icon"></i>
    <p class="text-amber-100 text-xs font-medium">Graduates</p>
    <p class="text-2xl font-display font-bold mt-1"><?= number_format($stats['graduates']) ?></p>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
  <div class="lg:col-span-2 card">
    <h3 class="font-display font-bold text-gray-900 mb-4 text-sm">Recent Payments Today</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-400 text-xs uppercase tracking-wide border-b border-gray-100"><th class="py-2 font-medium">Time</th><th class="font-medium">Student</th><th class="font-medium">Bill</th><th class="font-medium">Amount</th><th class="font-medium">Method</th></tr></thead>
      <tbody>
      <?php foreach ($recentPayments as $p): ?>
        <tr class="border-b border-gray-50 last:border-0">
          <td class="py-2.5 text-gray-500"><?= date('h:i A', strtotime($p['payment_date'])) ?></td>
          <td class="font-medium text-gray-800"><?= e($p['student_name']) ?></td>
          <td class="text-gray-500"><?= e($p['bill_type_name']) ?></td>
          <td class="font-semibold text-gray-800"><?= money($p['amount_paid']) ?></td>
          <td><span class="badge badge-active"><?= e($p['payment_method']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($recentPayments)): ?>
        <tr><td colspan="5" class="py-8 text-center text-gray-300"><i class="fa-solid fa-inbox text-2xl mb-2 block"></i>No payments recorded today yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="card">
    <h3 class="font-display font-bold text-gray-900 mb-3 text-sm">Outstanding Balance</h3>
    <p class="text-3xl font-display font-bold balance-positive"><?= money($totalOutstanding) ?></p>
    <p class="text-gray-400 text-xs mt-1">Across all active students</p>
    <div class="mt-5 space-y-1">
      <a href="<?= base_url('reports/student-balances') ?>" class="flex items-center justify-between text-sm text-gray-700 hover:text-purple-700 hover:bg-purple-50 rounded-xl px-3 py-2.5 transition"><span><i class="fa-solid fa-scale-balanced w-5 text-gray-400"></i> View balances report</span><i class="fa-solid fa-chevron-right text-xs text-gray-300"></i></a>
      <a href="<?= base_url('billing?tab=batch') ?>" class="flex items-center justify-between text-sm text-gray-700 hover:text-purple-700 hover:bg-purple-50 rounded-xl px-3 py-2.5 transition"><span><i class="fa-solid fa-layer-group w-5 text-gray-400"></i> Run batch billing</span><i class="fa-solid fa-chevron-right text-xs text-gray-300"></i></a>
      <a href="<?= base_url('students?tab=admission') ?>" class="flex items-center justify-between text-sm text-gray-700 hover:text-purple-700 hover:bg-purple-50 rounded-xl px-3 py-2.5 transition"><span><i class="fa-solid fa-user-plus w-5 text-gray-400"></i> Admit a new student</span><i class="fa-solid fa-chevron-right text-xs text-gray-300"></i></a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
