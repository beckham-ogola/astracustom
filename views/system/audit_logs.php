<?php $pageTitle = 'Audit Logs'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="bg-white rounded-xl shadow-sm p-5 mb-4 flex flex-wrap gap-3 items-center justify-between no-print">
  <input type="text" placeholder="Search logs..." data-search-input="tbody tr[data-row]"
         class="w-full sm:w-80 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
  <button onclick="printPage()" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg"><i class="fa-solid fa-print mr-1"></i> Print</button>
</div>

<div class="bg-white rounded-xl shadow-sm p-5 print-area">
  <div class="overflow-x-auto">
  <table class="w-full text-xs">
    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Timestamp</th><th>User</th><th>Action</th><th>Table</th><th>Record ID</th><th>Details</th><th>IP Address</th></tr></thead>
    <tbody>
    <?php foreach ($logs as $log): ?>
      <tr data-row data-search-text="<?= e($log['user_name'] . ' ' . $log['action'] . ' ' . $log['table_name'] . ' ' . $log['details'] . ' ' . $log['ip_address']) ?>" class="border-b last:border-0">
        <td class="py-2"><?= date('d M Y, h:i A', strtotime($log['created_at'])) ?></td>
        <td><?= e($log['user_name'] ?? 'System') ?></td>
        <td><span class="badge badge-active"><?= e($log['action']) ?></span></td>
        <td><?= e($log['table_name'] ?? '—') ?></td>
        <td><?= e($log['record_id'] ?? '—') ?></td>
        <td><?= e($log['details'] ?? '—') ?></td>
        <td><?= e($log['ip_address'] ?? '—') ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($logs)): ?><tr><td colspan="7" class="py-6 text-center text-gray-400">No audit log entries.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="flex justify-center gap-1 mt-5 no-print">
      <?php for ($i = 1; $i <= min($totalPages, 20); $i++): ?>
        <a href="?page=<?= $i ?>" class="px-3 py-1.5 rounded-lg text-sm <?= $i === $page ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
