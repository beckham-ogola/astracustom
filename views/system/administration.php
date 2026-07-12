<?php $pageTitle = 'Administration'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-2xl">
  <p class="text-sm text-gray-500 mb-5">Setup and configuration — classes, staff accounts, PDF templates, the audit trail, and school-wide settings.</p>

  <div class="bg-white rounded-xl shadow-sm p-5">
    <div class="grid grid-cols-2 gap-2">
      <a href="<?= base_url('classes') ?>" class="flex items-center gap-2 bg-gray-50 hover:bg-purple-50 hover:text-purple-700 rounded-lg px-3 py-3 text-sm"><i class="fa-solid fa-school w-5"></i> Classes</a>
      <a href="<?= base_url('users') ?>" class="flex items-center gap-2 bg-gray-50 hover:bg-purple-50 hover:text-purple-700 rounded-lg px-3 py-3 text-sm"><i class="fa-solid fa-users w-5"></i> Users</a>
      <a href="<?= base_url('templates') ?>" class="flex items-center gap-2 bg-gray-50 hover:bg-purple-50 hover:text-purple-700 rounded-lg px-3 py-3 text-sm"><i class="fa-solid fa-file-pdf w-5"></i> PDF Templates</a>
      <a href="<?= base_url('audit-logs') ?>" class="flex items-center gap-2 bg-gray-50 hover:bg-purple-50 hover:text-purple-700 rounded-lg px-3 py-3 text-sm"><i class="fa-solid fa-clock-rotate-left w-5"></i> Audit Logs</a>
      <a href="<?= base_url('settings') ?>" class="flex items-center gap-2 bg-gray-50 hover:bg-purple-50 hover:text-purple-700 rounded-lg px-3 py-3 text-sm"><i class="fa-solid fa-sliders w-5"></i> Settings</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
