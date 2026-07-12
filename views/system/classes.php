<?php $pageTitle = 'Class Management'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Classes</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">#</th><th>Class Name</th><th>Level</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($classes as $i => $c): ?>
        <tr class="border-b last:border-0">
          <td class="py-2"><?= $i + 1 ?></td>
          <td class="font-medium"><?= e($c['name']) ?></td>
          <td><?= $c['level'] ?></td>
          <td><?= e($c['description'] ?: '—') ?></td>
          <td><span class="badge <?= $c['is_active'] ? 'badge-active' : 'badge-withdrawn' ?>"><?= $c['is_active'] ? 'Active' : 'Inactive' ?></span></td>
          <td class="whitespace-nowrap space-x-1">
            <form method="POST" action="<?= base_url('classes/' . $c['id'] . '/move-up') ?>" class="inline"><?= csrf_field() ?><button class="text-gray-500 hover:text-purple-600" title="Move Up"><i class="fa-solid fa-arrow-up"></i></button></form>
            <form method="POST" action="<?= base_url('classes/' . $c['id'] . '/move-down') ?>" class="inline"><?= csrf_field() ?><button class="text-gray-500 hover:text-purple-600" title="Move Down"><i class="fa-solid fa-arrow-down"></i></button></form>
            <button data-modal-open="edit-class-<?= $c['id'] ?>" class="text-purple-600 hover:underline text-xs">Edit</button>
            <form method="POST" action="<?= base_url('classes/' . $c['id'] . '/delete') ?>" class="inline" data-confirm="Delete this class? This can only be done if no students, teachers, or fee structures are linked.">
              <?= csrf_field() ?>
              <button class="text-red-600 hover:underline text-xs">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($classes)): ?><tr><td colspan="6" class="py-4 text-center text-gray-400">No classes yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-4">Add Class</h3>
      <form method="POST" action="<?= base_url('classes') ?>" data-loading class="space-y-3">
        <?= csrf_field() ?>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Name *</label><input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Description</label><input type="text" name="description" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
        <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm w-full"><i class="fa-solid fa-plus mr-1"></i> Add Class</button>
      </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-4">Whole Class Promotion</h3>
      <form method="POST" action="<?= base_url('classes/promote') ?>" data-loading data-confirm="Promote all active students in this class? Students at the highest level will be graduated." class="space-y-3">
        <?= csrf_field() ?>
        <select name="class_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Select a class</option>
          <?php foreach ($classes as $c): if (!$c['is_active']) continue; ?>
            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= ClassModel::studentCount($c['id']) ?> students)</option>
          <?php endforeach; ?>
        </select>
        <button class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm w-full"><i class="fa-solid fa-arrow-up mr-1"></i> Promote Whole Class</button>
      </form>
    </div>
  </div>
</div>

<?php foreach ($classes as $c): ?>
<div id="edit-class-<?= $c['id'] ?>" class="modal-backdrop hidden">
  <div class="modal-box p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Edit Class</h3>
    <form method="POST" action="<?= base_url('classes/' . $c['id'] . '/update') ?>" data-loading class="space-y-3">
      <?= csrf_field() ?>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Name</label><input type="text" name="name" value="<?= e($c['name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Description</label><input type="text" name="description" value="<?= e($c['description']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div class="flex items-center gap-2"><input type="checkbox" name="is_active" <?= $c['is_active'] ? 'checked' : '' ?> class="rounded text-purple-600"> <label class="text-sm">Active</label></div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-modal-close class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm">Save</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
