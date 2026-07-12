<?php $pageTitle = 'Class Report'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="bg-white rounded-xl shadow-sm p-5 no-print flex justify-between items-center mb-4">
  <h3 class="font-semibold text-gray-800">Class Report: <?= e($class['name'] ?? '') ?></h3>
  <button onclick="printPage()" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg"><i class="fa-solid fa-print mr-1"></i> Print</button>
</div>

<div class="bg-white rounded-xl shadow-sm p-5 print-area">
  <div class="overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Admission No</th><th>Full Name</th><th>Total Billed</th><th>Total Paid</th><th>Balance</th></tr></thead>
    <tbody>
    <?php foreach ($balances as $b): ?>
      <tr class="border-b last:border-0">
        <td class="py-2"><?= e($b['admission_no']) ?></td>
        <td><?= e($b['full_name']) ?></td>
        <td><?= money($b['total_billed']) ?></td>
        <td><?= money($b['total_paid']) ?></td>
        <td class="<?= $b['balance'] > 0.009 ? 'balance-positive' : 'balance-zero' ?>"><?= money($b['balance']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($balances)): ?>
      <tr><td colspan="5" class="py-4 text-center text-gray-400">No students in your class yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
