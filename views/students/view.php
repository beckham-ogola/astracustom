<?php $pageTitle = 'Student: ' . $student['full_name']; require __DIR__ . '/../layouts/header.php'; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 space-y-6">

    <div class="bg-white rounded-xl shadow-sm p-6">
      <div class="flex justify-between items-start flex-wrap gap-3">
        <div>
          <h3 class="text-lg font-bold text-gray-800"><?= e($student['full_name']) ?></h3>
          <p class="text-gray-500 text-sm"><?= e($student['admission_no']) ?> &middot; <?= e($student['class_name'] ?? '—') ?></p>
        </div>
        <span class="badge badge-<?= strtolower($student['status']) ?>"><?= e($student['status']) ?></span>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm mt-5">
        <div><span class="text-gray-500">Gender:</span><br><span class="font-medium"><?= e($student['gender']) ?></span></div>
        <div><span class="text-gray-500">Date of Birth:</span><br><span class="font-medium"><?= date('d M Y', strtotime($student['dob'])) ?></span></div>
        <div><span class="text-gray-500">Age:</span><br><span class="font-medium"><?= (int)$student['age'] ?> years</span></div>
        <div><span class="text-gray-500">Birth Cert No:</span><br><span class="font-medium"><?= e($student['birth_cert_no']) ?></span></div>
        <div><span class="text-gray-500">Admission Date:</span><br><span class="font-medium"><?= date('d M Y', strtotime($student['admission_date'])) ?></span></div>
        <div><span class="text-gray-500">Photo Consent:</span><br><span class="font-medium"><?= $student['photo_consent'] ? 'Yes' : 'No' ?></span></div>
        <div><span class="text-gray-500">Admitted By:</span><br><span class="font-medium"><?= e($student['admitted_by_name'] ?? '—') ?></span></div>
        <div>
          <span class="text-gray-500">Admission Form:</span><br>
          <?php if (!empty($student['admission_form_path'])): ?>
            <a href="<?= base_url('uploads/admission_forms/' . $student['admission_form_path']) ?>" target="_blank" class="text-purple-600 hover:underline font-medium"><i class="fa-solid fa-file mr-1"></i>View File</a>
          <?php else: ?><span class="text-gray-400">None uploaded</span><?php endif; ?>
        </div>
      </div>

      <div class="mt-5 border-t pt-4">
        <h4 class="font-semibold text-gray-700 text-sm mb-2">Guardian 1 (Primary)</h4>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
          <div><span class="text-gray-500">Name:</span> <?= e($student['guardian1_name']) ?></div>
          <div><span class="text-gray-500">Relation:</span> <?= e($student['guardian1_relation']) ?></div>
          <div><span class="text-gray-500">ID:</span> <?= e($student['guardian1_id']) ?></div>
          <div><span class="text-gray-500">Phone:</span> <?= e($student['guardian1_phone']) ?></div>
          <div><span class="text-gray-500">Alt Phone:</span> <?= e($student['guardian1_phone_alt'] ?: '—') ?></div>
          <div><span class="text-gray-500">Address:</span> <?= e($student['guardian1_address'] ?: '—') ?></div>
        </div>
      </div>

      <?php if (!empty($student['guardian2_name'])): ?>
      <div class="mt-5 border-t pt-4">
        <h4 class="font-semibold text-gray-700 text-sm mb-2">Guardian 2</h4>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
          <div><span class="text-gray-500">Name:</span> <?= e($student['guardian2_name']) ?></div>
          <div><span class="text-gray-500">Relation:</span> <?= e($student['guardian2_relation']) ?></div>
          <div><span class="text-gray-500">ID:</span> <?= e($student['guardian2_id']) ?></div>
          <div><span class="text-gray-500">Phone:</span> <?= e($student['guardian2_phone']) ?></div>
          <div><span class="text-gray-500">Alt Phone:</span> <?= e($student['guardian2_phone_alt'] ?: '—') ?></div>
          <div><span class="text-gray-500">Address:</span> <?= e($student['guardian2_address'] ?: '—') ?></div>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($student['medical_conditions'])): ?>
      <div class="mt-5 border-t pt-4">
        <h4 class="font-semibold text-gray-700 text-sm mb-2">Medical Conditions / Allergies</h4>
        <p class="text-sm text-gray-600"><?= nl2br(e($student['medical_conditions'])) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-4">Bills</h3>
      <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Bill Type</th><th>Term</th><th>Amount</th><th>Discount</th><th>Final</th><th>Paid</th><th>Balance</th><th>Status</th><?php if (can_manage()): ?><th></th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($bills as $b): $status = Bill::statusLabel($b); ?>
          <tr class="border-b last:border-0">
            <td class="py-2"><?= e($b['bill_type_name']) ?></td>
            <td><?= e($b['term']) ?></td>
            <td><?= money($b['amount']) ?></td>
            <td><?= money($b['discount_applied']) ?></td>
            <td><?= money($b['final_amount']) ?></td>
            <td><?= money($b['paid_amount']) ?></td>
            <td class="<?= $b['remaining'] > 0.009 ? 'balance-positive' : 'balance-zero' ?>"><?= money($b['remaining']) ?></td>
            <td><span class="badge badge-<?= strtolower($status) ?>"><?= $status ?></span></td>
            <?php if (can_manage()): ?>
            <td class="whitespace-nowrap">
              <button data-modal-open="edit-bill-<?= $b['id'] ?>" class="text-purple-600 hover:underline text-xs">Edit</button>
              <form method="POST" action="<?= base_url('bills/' . $b['id'] . '/delete') ?>" class="inline" data-confirm="Delete this bill? This cannot be undone.">
                <?= csrf_field() ?>
                <button class="text-red-600 hover:underline text-xs ml-2">Delete</button>
              </form>
            </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($bills)): ?>
          <tr><td colspan="9" class="py-4 text-center text-gray-400">No bills yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
      </div>
      <?php if (can_manage()): ?>
        <a href="<?= base_url('billing?tab=student&admission_no=' . urlencode($student['admission_no'])) ?>" class="inline-block mt-4 text-purple-600 text-sm hover:underline"><i class="fa-solid fa-plus mr-1"></i> Add Bills</a>
      <?php endif; ?>
    </div>

    <?php if (can_manage()): foreach ($bills as $b): ?>
      <div id="edit-bill-<?= $b['id'] ?>" class="modal-backdrop hidden">
        <div class="modal-box p-6">
          <h3 class="font-semibold text-gray-800 mb-4">Edit Bill: <?= e($b['bill_type_name']) ?></h3>
          <form method="POST" action="<?= base_url('bills/' . $b['id'] . '/update') ?>" data-loading class="space-y-3">
            <?= csrf_field() ?>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Amount</label><input type="number" step="0.01" name="amount" value="<?= e($b['amount']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Discount</label><input type="number" step="0.01" name="discount" value="<?= e($b['discount_applied']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
            <div class="flex items-center gap-2"><input type="checkbox" name="sponsored" <?= $b['is_sponsored'] ? 'checked' : '' ?> class="rounded text-purple-600"> <label class="text-sm">Sponsored (full amount waived)</label></div>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" data-modal-close class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancel</button>
              <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm">Save</button>
            </div>
          </form>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <div class="space-y-6">
    <?php if (can_manage()): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-4">Actions</h3>
      <div class="space-y-2">
        <a href="<?= base_url('students/' . $student['id'] . '/edit') ?>" class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm"><i class="fa-solid fa-pen mr-1"></i> Edit</a>

        <form method="POST" action="<?= base_url('students/' . $student['id'] . '/promote') ?>" data-confirm="Promote this student to the next class?">
          <?= csrf_field() ?>
          <button class="block w-full text-center bg-blue-50 hover:bg-blue-100 text-blue-700 py-2 rounded-lg text-sm"><i class="fa-solid fa-arrow-up mr-1"></i> Promote</button>
        </form>

        <form method="POST" action="<?= base_url('students/' . $student['id'] . '/demote') ?>" data-confirm="Demote this student to the previous class?">
          <?= csrf_field() ?>
          <button class="block w-full text-center bg-amber-50 hover:bg-amber-100 text-amber-700 py-2 rounded-lg text-sm"><i class="fa-solid fa-arrow-down mr-1"></i> Demote</button>
        </form>

        <form method="POST" action="<?= base_url('students/' . $student['id'] . '/graduate') ?>" data-confirm="Graduate this student now?">
          <?= csrf_field() ?>
          <button class="block w-full text-center bg-green-50 hover:bg-green-100 text-green-700 py-2 rounded-lg text-sm"><i class="fa-solid fa-award mr-1"></i> Graduate</button>
        </form>

        <?php if (is_developer()): ?>
        <button data-modal-open="delete-student-modal" class="block w-full text-center bg-red-50 hover:bg-red-100 text-red-700 py-2 rounded-lg text-sm"><i class="fa-solid fa-trash mr-1"></i> Permanently Delete</button>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (can_handle_payments()): ?>
    <div class="bg-white rounded-xl shadow-sm p-6">
      <h3 class="font-semibold text-gray-800 mb-3">Payment</h3>
      <a href="<?= base_url('payments/' . $student['id'] . '/pay') ?>" class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg text-sm"><i class="fa-solid fa-money-bill-wave mr-1"></i> Receive Payment</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if (is_developer()): ?>
<div id="delete-student-modal" class="modal-backdrop hidden">
  <div class="modal-box p-6">
    <h3 class="font-semibold text-red-700 mb-2"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Permanently Delete Student</h3>
    <p class="text-sm text-gray-600 mb-4">This will permanently remove <strong><?= e($student['full_name']) ?></strong> and all related bills and payments. This action is <strong>irreversible</strong>. Enter your password to confirm.</p>
    <form method="POST" action="<?= base_url('students/' . $student['id'] . '/delete') ?>" data-loading class="space-y-3">
      <?= csrf_field() ?>
      <input type="password" name="password" required placeholder="Your password" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-modal-close class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm">Delete Permanently</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
