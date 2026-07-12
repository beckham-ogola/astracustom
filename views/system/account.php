<?php $pageTitle = 'My Account'; require __DIR__ . '/../layouts/header.php'; $u = current_user(); ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 max-w-3xl">
  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Account Info</h3>
    <div class="space-y-3 text-sm">
      <div><span class="text-gray-500">Username:</span> <span class="font-medium"><?= e($u['username']) ?></span></div>
      <div><span class="text-gray-500">Full Name:</span> <span class="font-medium"><?= e($u['full_name']) ?></span></div>
      <div><span class="text-gray-500">Role:</span> <span class="font-medium capitalize"><?= e($u['role']) ?></span></div>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Change Password</h3>
    <form method="POST" action="<?= base_url('account/change-password') ?>" data-loading class="space-y-3">
      <?= csrf_field() ?>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Current Password *</label><input type="password" name="current_password" required class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">New Password (min 4 chars) *</label><input type="password" name="new_password" required minlength="4" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password *</label><input type="password" name="confirm_password" required minlength="4" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm"><i class="fa-solid fa-key mr-1"></i> Change Password</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
