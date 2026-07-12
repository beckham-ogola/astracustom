<?php $pageTitle = 'Edit Fee Structure: ' . $billType['name']; require __DIR__ . '/../layouts/header.php'; ?>

<form method="POST" action="<?= base_url('fee-structure/' . $billType['id'] . '/update') ?>" data-loading class="max-w-2xl">
  <?= csrf_field() ?>
  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-1"><?= e($billType['name']) ?></h3>
    <p class="text-gray-500 text-sm mb-4">Set the amount for each class. Leave 0 to skip billing this class.</p>

    <div class="space-y-3">
      <?php foreach ($rows as $r): ?>
        <div class="flex items-center justify-between gap-4">
          <label class="text-sm font-medium text-gray-700 w-40"><?= e($r['class_name']) ?></label>
          <input type="number" step="0.01" min="0" name="amount[<?= $r['class_id'] ?>]" value="<?= e($r['amount']) ?>"
                 class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
        </div>
      <?php endforeach; ?>
    </div>

    <div class="flex gap-3 mt-6">
      <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2.5 rounded-lg"><i class="fa-solid fa-check mr-1"></i> Save Amounts</button>
      <a href="<?= base_url('billing?tab=structure') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-2.5 rounded-lg">Cancel</a>
    </div>
  </div>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
