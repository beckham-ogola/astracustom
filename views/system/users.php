<?php $pageTitle = 'User Management'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4">All Users</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Username</th><th>Full Name</th><th>Role</th><th>Class</th><th>Last Login</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr class="border-b last:border-0">
          <td class="py-2 font-medium"><?= e($u['username']) ?></td>
          <td><?= e($u['full_name']) ?></td>
          <td class="capitalize"><?= e($u['role']) ?></td>
          <td><?= e($u['class_name'] ?? '—') ?></td>
          <td><?= $u['last_login_at'] ? date('d M Y, h:i A', strtotime($u['last_login_at'])) : 'Never' ?></td>
          <td class="whitespace-nowrap">
            <button data-modal-open="reset-pw-<?= $u['id'] ?>" class="text-purple-600 hover:underline text-xs">Reset Password</button>
            <?php if (is_developer() && $u['id'] !== current_user()['id']): ?>
              <form method="POST" action="<?= base_url('users/' . $u['id'] . '/delete') ?>" class="inline" data-confirm="Delete this user account?">
                <?= csrf_field() ?>
                <button class="text-red-600 hover:underline text-xs ml-2">Delete</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($users)): ?><tr><td colspan="6" class="py-4 text-center text-gray-400">No users found.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Create User</h3>
    <form method="POST" action="<?= base_url('users') ?>" data-loading class="space-y-3">
      <?= csrf_field() ?>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Username *</label><input type="text" name="username" required class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">ID Number *</label><input type="text" name="id_number" required class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Password *</label><input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label><input type="text" name="full_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone</label><input type="text" name="phone" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
        <select name="role" id="role-select" required onchange="document.getElementById('class-field').classList.toggle('hidden', this.value !== 'teacher')" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <?php if (is_developer()): ?><option value="developer">Developer</option><?php endif; ?>
          <option value="admin">Admin</option>
          <option value="accountant">Accountant</option>
          <option value="teacher" selected>Teacher</option>
        </select>
      </div>
      <div id="class-field">
        <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Class (Teacher only)</label>
        <select name="class_assigned" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">None</option>
          <?php foreach ($classes as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm w-full"><i class="fa-solid fa-user-plus mr-1"></i> Create User</button>
    </form>
  </div>
</div>

<?php foreach ($users as $u): ?>
<div id="reset-pw-<?= $u['id'] ?>" class="modal-backdrop hidden">
  <div class="modal-box p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Reset Password: <?= e($u['username']) ?></h3>
    <form method="POST" action="<?= base_url('users/' . $u['id'] . '/reset-password') ?>" data-loading class="space-y-3">
      <?= csrf_field() ?>
      <input type="password" name="new_password" required minlength="4" placeholder="New password (min 4 characters)" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-modal-close class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm">Reset</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
