<?php $pageTitle = 'PDF Templates'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="bg-white rounded-xl shadow-sm p-6 mb-6 max-w-lg">
  <h3 class="font-semibold text-gray-800 mb-4">Upload Template</h3>
  <form method="POST" action="<?= base_url('templates') ?>" enctype="multipart/form-data" data-loading class="space-y-3">
    <?= csrf_field() ?>
    <div><label class="block text-sm font-medium text-gray-700 mb-1">Name</label><input type="text" name="name" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700 mb-1">Description</label><input type="text" name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700 mb-1">PDF File *</label><input type="file" name="file" accept=".pdf" required class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm"><i class="fa-solid fa-upload mr-1"></i> Upload Template</button>
  </form>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
  <h3 class="font-semibold text-gray-800 mb-4">Uploaded Templates</h3>
  <div class="overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">File Name</th><th>Uploaded By</th><th>Uploaded At</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($templates as $t): ?>
      <tr class="border-b last:border-0">
        <td class="py-2 font-medium"><?= e($t['name']) ?></td>
        <td><?= e($t['uploaded_by_name'] ?? 'N/A') ?></td>
        <td><?= date('d M Y, h:i A', strtotime($t['uploaded_at'])) ?></td>
        <td class="whitespace-nowrap">
          <a href="<?= base_url('uploads/templates/' . $t['file_path']) ?>" target="_blank" class="text-purple-600 hover:underline text-xs">View</a>
          <form method="POST" action="<?= base_url('templates/' . $t['id'] . '/delete') ?>" class="inline" data-confirm="Delete this template?">
            <?= csrf_field() ?>
            <button class="text-red-600 hover:underline text-xs ml-2">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($templates)): ?><tr><td colspan="4" class="py-4 text-center text-gray-400">No templates uploaded.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
