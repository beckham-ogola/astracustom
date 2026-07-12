<?php $pageTitle = 'Teacher Dashboard'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
  <h3 class="font-semibold text-gray-800 mb-1">Your Class: <?= e($class['name'] ?? 'Not assigned') ?></h3>
  <p class="text-gray-500 text-sm"><?= count($students) ?> active student(s)</p>
</div>

<div class="bg-white rounded-xl shadow-sm p-5">
  <h3 class="font-semibold text-gray-800 mb-3">Students</h3>
  <input type="text" placeholder="Search by name or admission number..." data-search-input="tbody tr[data-row]"
         class="w-full sm:w-80 border border-gray-300 rounded-lg px-3 py-2 mb-3 text-sm focus:ring-2 focus:ring-purple-500">
  <div class="overflow-x-auto">
  <table class="w-full text-sm">
    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Admission No</th><th>Full Name</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($students as $s): ?>
      <tr data-row data-search-text="<?= e($s['admission_no'] . ' ' . $s['full_name']) ?>" class="border-b last:border-0">
        <td class="py-2"><?= e($s['admission_no']) ?></td>
        <td><?= e($s['full_name']) ?></td>
        <td><a href="<?= base_url('students/' . $s['id']) ?>" class="text-purple-600 hover:underline">View</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($students)): ?>
      <tr><td colspan="3" class="py-4 text-center text-gray-400">No students in your class yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
