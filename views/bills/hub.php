<?php $pageTitle = 'Billing'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="tab-strip" data-tab-group="billing">
  <?php if ($canManage): ?>
    <button class="tab-btn" data-tab-btn="types">Bill Types</button>
    <button class="tab-btn" data-tab-btn="structure">Fee Structure</button>
  <?php endif; ?>
  <button class="tab-btn" data-tab-btn="student">Bill a Student</button>
  <button class="tab-btn" data-tab-btn="batch">Batch Billing</button>
</div>

<?php if ($canManage): ?>
<!-- ===== TAB: Bill Types (advanced / setup) ===== -->
<div data-tab-panel-group="billing" data-tab-panel="types" class="hidden">
  <div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <h3 class="font-semibold text-gray-800 mb-4 text-sm">Add Bill Type</h3>
    <form method="POST" action="<?= base_url('bill-types') ?>" data-loading class="flex flex-col sm:flex-row gap-3">
      <?= csrf_field() ?>
      <input type="text" name="name" placeholder="Name (e.g. Transport Fee)" required class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
      <input type="text" name="description" placeholder="Description (optional)" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
      <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm whitespace-nowrap"><i class="fa-solid fa-plus mr-1"></i> Add</button>
    </form>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-5">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Name</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($billTypes as $bt): ?>
        <tr class="border-b last:border-0">
          <td class="py-2 font-medium"><?= e($bt['name']) ?></td>
          <td><?= e($bt['description'] ?: '—') ?></td>
          <td><span class="badge <?= $bt['is_active'] ? 'badge-active' : 'badge-withdrawn' ?>"><?= $bt['is_active'] ? 'Active' : 'Inactive' ?></span></td>
          <td class="whitespace-nowrap">
            <button data-modal-open="edit-bt-<?= $bt['id'] ?>" class="text-purple-600 hover:underline text-xs">Edit</button>
            <form method="POST" action="<?= base_url('bill-types/' . $bt['id'] . '/delete') ?>" class="inline" data-confirm="Delete this bill type?">
              <?= csrf_field() ?>
              <button class="text-red-600 hover:underline text-xs ml-2">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($billTypes)): ?><tr><td colspan="4" class="py-4 text-center text-gray-400">No bill types yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- ===== TAB: Fee Structure (advanced / setup) ===== -->
<div data-tab-panel-group="billing" data-tab-panel="structure" class="hidden">
  <div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <h3 class="font-semibold text-gray-800 mb-1 text-sm">Set Amounts by Bill Type</h3>
    <p class="text-gray-500 text-xs mb-3">Pick a bill type to set its amount for every class at once.</p>
    <div class="flex flex-wrap gap-2">
      <?php foreach (BillType::all(true) as $bt): ?>
        <a href="<?= base_url('fee-structure/' . $bt['id'] . '/edit') ?>" class="bg-purple-50 hover:bg-purple-100 text-purple-700 px-4 py-2 rounded-lg text-sm"><?= e($bt['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-5">
    <h3 class="font-semibold text-gray-800 mb-4 text-sm">Current Fee Structure</h3>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Bill Type</th><th>Class</th><th>Amount</th></tr></thead>
      <tbody>
      <?php foreach ($feeStructure as $fs): ?>
        <tr class="border-b last:border-0">
          <td class="py-2"><?= e($fs['bill_type_name']) ?></td>
          <td><?= e($fs['class_name']) ?></td>
          <td><?= money($fs['amount']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($feeStructure)): ?><tr><td colspan="3" class="py-4 text-center text-gray-400">No fee structure set yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ===== TAB: Bill a Student (everyday task) ===== -->
<div data-tab-panel-group="billing" data-tab-panel="student" class="hidden">
  <div class="bg-white rounded-xl shadow-sm p-5 mb-4">
    <h3 class="font-semibold text-gray-800 mb-3 text-sm">Search Student</h3>
    <form method="GET" action="<?= base_url('billing') ?>" class="flex gap-2">
      <input type="hidden" name="tab" value="student">
      <input type="text" name="admission_no" placeholder="Enter admission number" required value="<?= e($_GET['admission_no'] ?? '') ?>"
             class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
      <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm"><i class="fa-solid fa-magnifying-glass mr-1"></i> Search</button>
    </form>
  </div>

  <?php if ($student): ?>
    <div class="bg-white rounded-xl shadow-sm p-5 mb-4">
      <div class="flex justify-between flex-wrap gap-2">
        <div>
          <h3 class="font-semibold text-gray-800"><?= e($student['full_name']) ?></h3>
          <p class="text-gray-500 text-sm"><?= e($student['admission_no']) ?> &middot; <?= e($student['class_name'] ?? '—') ?></p>
        </div>
        <div class="text-sm text-gray-500">Term: <span class="font-medium text-gray-700"><?= e($currentTerm) ?></span></div>
      </div>
    </div>

    <form method="POST" action="<?= base_url('bills/student') ?>" data-loading>
      <?= csrf_field() ?>
      <input type="hidden" name="student_id" value="<?= $student['id'] ?>">

      <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4 text-sm">Select Bills to Apply</h3>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 w-8"></th><th>Bill Type</th><th>Amount</th><th>Discount</th><th>Sponsored</th></tr></thead>
          <tbody>
          <?php foreach ($feeItems as $item): ?>
            <tr class="border-b last:border-0">
              <td class="py-2"><input type="checkbox" name="bill_type_id[]" value="<?= $item['bill_type_id'] ?>" class="rounded text-purple-600"></td>
              <td><?= e($item['bill_type_name']) ?></td>
              <td><?= money($item['amount']) ?></td>
              <td><input type="number" step="0.01" min="0" id="discount-<?= $item['bill_type_id'] ?>" name="discount[<?= $item['bill_type_id'] ?>]" class="w-28 border border-gray-300 rounded-lg px-2 py-1"></td>
              <td><input type="checkbox" name="sponsored[<?= $item['bill_type_id'] ?>]" data-sponsor-toggle="discount-<?= $item['bill_type_id'] ?>" class="rounded text-purple-600"></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($feeItems)): ?><tr><td colspan="5" class="py-4 text-center text-gray-400">No fee structure set for this class yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
        <p class="text-xs text-gray-400 mt-3">Bills that already exist for this student and term will be skipped automatically.</p>
      </div>

      <button type="submit" class="mt-4 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg w-full sm:w-auto"><i class="fa-solid fa-check mr-1"></i> Create Selected Bills</button>
    </form>
  <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400 text-sm">Search for a student above to bill them.</div>
  <?php endif; ?>
</div>

<!-- ===== TAB: Batch Billing (everyday task) ===== -->
<div data-tab-panel-group="billing" data-tab-panel="batch" class="hidden">
  <form method="POST" action="<?= base_url('bills/batch') ?>" data-loading data-confirm="Apply selected bills to all active students in this class?">
    <?= csrf_field() ?>
    <div class="bg-white rounded-xl shadow-sm p-5 space-y-5">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Class *</label>
        <select name="class_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Select a class</option>
          <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= ClassModel::studentCount($c['id']) ?> active students)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Bill Types to Apply *</label>
        <div class="space-y-2">
          <?php foreach ($activeBillTypes as $bt): ?>
            <label class="flex items-center gap-2 text-sm">
              <input type="checkbox" name="bill_type_id[]" value="<?= $bt['id'] ?>" class="rounded text-purple-600">
              <?= e($bt['name']) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-sm text-purple-800">
        <i class="fa-solid fa-circle-info mr-1"></i> Applies to current term (<?= e($currentTerm) ?>) only. Students with existing bills for a selected type will be skipped.
      </div>

      <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2.5 rounded-lg w-full sm:w-auto"><i class="fa-solid fa-layer-group mr-1"></i> Run Batch Billing</button>
    </div>
  </form>
</div>

<?php if ($canManage): foreach ($billTypes as $bt): ?>
<div id="edit-bt-<?= $bt['id'] ?>" class="modal-backdrop hidden">
  <div class="modal-box p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Edit Bill Type</h3>
    <form method="POST" action="<?= base_url('bill-types/' . $bt['id'] . '/update') ?>" data-loading class="space-y-3">
      <?= csrf_field() ?>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Name</label><input type="text" name="name" value="<?= e($bt['name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Description</label><input type="text" name="description" value="<?= e($bt['description']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div class="flex items-center gap-2"><input type="checkbox" name="is_active" <?= $bt['is_active'] ? 'checked' : '' ?> class="rounded text-purple-600"> <label class="text-sm">Active</label></div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-modal-close class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm">Save</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
