<?php $pageTitle = 'Students'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="tab-strip" data-tab-group="students">
  <button class="tab-btn" data-tab-btn="list"><i class="fa-solid fa-list mr-1"></i> All Students</button>
  <?php if (can_manage()): ?>
    <button class="tab-btn" data-tab-btn="admission"><i class="fa-solid fa-user-plus mr-1"></i> New Admission</button>
  <?php endif; ?>
</div>

<!-- ===== TAB: All Students ===== -->
<div data-tab-panel-group="students" data-tab-panel="list" class="hidden">

  <div class="card mb-4 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
    <form method="GET" class="flex items-center gap-2">
      <input type="hidden" name="tab" value="list">
      <label class="text-sm text-gray-500">Class:</label>
      <select name="class_filter" onchange="this.form.submit()" class="border border-gray-200 bg-gray-50 rounded-xl px-3 py-2 text-sm" <?= (is_teacher() && !is_admin()) ? 'disabled' : '' ?>>
        <option value="all_active" <?= $filter === 'all_active' ? 'selected' : '' ?>>All Active Students</option>
        <?php foreach ($classes as $c): ?>
          <option value="<?= $c['id'] ?>" <?= (string)$filter === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
        <option value="graduates" <?= $filter === 'graduates' ? 'selected' : '' ?>>Graduates</option>
      </select>
    </form>
    <div class="relative w-full sm:w-72">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-300 text-xs"></i>
      <input type="text" placeholder="Search by name or admission number..." data-search-input="tbody tr[data-row]"
             class="w-full border border-gray-200 bg-gray-50 rounded-xl pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:bg-white outline-none transition">
    </div>
  </div>

  <div class="card !p-0 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left text-gray-400 text-xs uppercase tracking-wide bg-gray-50"><th class="py-3 px-4 font-medium">Admission No</th><th class="font-medium">Full Name</th><th class="font-medium">Class</th><th class="font-medium"></th></tr></thead>
      <tbody>
      <?php foreach ($students as $s): ?>
        <tr data-row data-search-text="<?= e($s['admission_no'] . ' ' . $s['full_name']) ?>" class="border-b border-gray-50 last:border-0 hover:bg-purple-50/40 transition">
          <td class="py-3 px-4 font-semibold text-gray-800"><?= e($s['admission_no']) ?></td>
          <td class="text-gray-700"><?= e($s['full_name']) ?></td>
          <td class="text-gray-500"><?= e($s['class_name'] ?? '—') ?></td>
          <td class="px-4"><a href="<?= base_url('students/' . $s['id']) ?>" class="text-purple-600 font-medium hover:underline">View <i class="fa-solid fa-chevron-right text-[10px] ml-0.5"></i></a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($students)): ?>
        <tr><td colspan="4" class="py-10 text-center text-gray-300"><i class="fa-solid fa-user-group text-2xl mb-2 block"></i>No students found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="flex justify-center gap-1 py-4">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?page=<?= $i ?>&class_filter=<?= e($filter) ?>&tab=list"
             class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium <?= $i === $page ? 'bg-purple-600 text-white' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ===== TAB: New Admission ===== -->
<?php if (can_manage()): ?>
<div data-tab-panel-group="students" data-tab-panel="admission" class="hidden">

  <form method="POST" action="<?= base_url('students') ?>" enctype="multipart/form-data" data-loading class="space-y-4" id="admission-form">
    <?= csrf_field() ?>

    <div class="card">
      <h3 class="font-display font-bold text-gray-900 mb-4 text-sm"><i class="fa-solid fa-user mr-1.5 text-purple-600"></i> Student Information</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
          <input type="text" name="full_name" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-purple-500 focus:bg-white outline-none transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
          <input type="date" name="dob" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-purple-500 focus:bg-white outline-none transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
          <select name="gender" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-purple-500 focus:bg-white outline-none transition">
            <option value="">Select gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Birth Certificate Number *</label>
          <input type="text" name="birth_cert_no" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-purple-500 focus:bg-white outline-none transition">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Class *</label>
          <select name="class_id" id="admission_class_id" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-purple-500 focus:bg-white outline-none transition">
            <option value="">Select class</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Admission Date</label>
          <input type="date" name="admission_date" value="<?= date('Y-m-d') ?>" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-purple-500 focus:bg-white outline-none transition">
        </div>
        <div class="flex items-center gap-2 mt-6">
          <input type="checkbox" name="photo_consent" id="photo_consent" checked class="rounded text-purple-600 focus:ring-purple-500 w-4 h-4">
          <label for="photo_consent" class="text-sm text-gray-700">Photo consent granted</label>
        </div>
      </div>
    </div>

    <!-- ===== Bills to Create — selectable, with live running total ===== -->
    <div class="card">
      <div class="flex items-center justify-between mb-1">
        <h3 class="font-display font-bold text-gray-900 text-sm"><i class="fa-solid fa-file-invoice-dollar mr-1.5 text-purple-600"></i> Bills to Create</h3>
        <span class="text-xs text-gray-400">Term: <?= e(get_setting('current_term')) ?></span>
      </div>
      <p class="text-xs text-gray-400 mb-3">Pick a class above to see its fee structure, then choose which bills to raise for this student now. You can always bill them later from the Billing tab.</p>

      <div id="admission-bills-empty" class="text-center py-8 text-gray-300 text-sm">
        <i class="fa-solid fa-arrow-up text-xl mb-2 block"></i> Select a class to see available bills.
      </div>
      <div id="admission-bills-list" class="hidden"></div>

      <div id="admission-bills-total" class="hidden mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
        <span class="text-sm font-medium text-gray-600">Total to be billed</span>
        <span id="admission-bills-total-value" class="text-lg font-display font-bold text-purple-700">KES 0.00</span>
      </div>
    </div>

    <details class="card">
      <summary class="font-display font-bold text-gray-900 text-sm cursor-pointer"><i class="fa-solid fa-people-roof mr-1.5 text-purple-600"></i> Guardian 1 (Primary) *</summary>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label><input type="text" name="guardian1_name" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Relationship *</label><input type="text" name="guardian1_relation" required placeholder="e.g. Mother, Father" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">National ID *</label><input type="text" name="guardian1_id" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label><input type="text" name="guardian1_phone" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Alternative Phone</label><input type="text" name="guardian1_phone_alt" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label><input type="text" name="guardian1_address" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
      </div>
    </details>

    <details class="card">
      <summary class="font-display font-bold text-gray-900 text-sm cursor-pointer"><i class="fa-solid fa-people-roof mr-1.5 text-purple-600"></i> Guardian 2 (Optional)</summary>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label><input type="text" name="guardian2_name" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label><input type="text" name="guardian2_relation" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">National ID</label><input type="text" name="guardian2_id" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label><input type="text" name="guardian2_phone" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Alternative Phone</label><input type="text" name="guardian2_phone_alt" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label><input type="text" name="guardian2_address" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5"></div>
      </div>
    </details>

    <details class="card">
      <summary class="font-display font-bold text-gray-900 text-sm cursor-pointer"><i class="fa-solid fa-notes-medical mr-1.5 text-purple-600"></i> Medical Information</summary>
      <textarea name="medical_conditions" rows="3" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 mt-3" placeholder="Optional"></textarea>
    </details>

    <details class="card">
      <summary class="font-display font-bold text-gray-900 text-sm cursor-pointer"><i class="fa-solid fa-paperclip mr-1.5 text-purple-600"></i> Admission Form Upload</summary>
      <input type="file" name="admission_form" accept=".pdf,.jpg,.jpeg,.png" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 mt-3">
      <p class="text-xs text-gray-400 mt-1">Accepted: PDF, JPG, JPEG, PNG. Max size: 5MB.</p>
    </details>

    <button type="submit" class="btn btn-primary w-full sm:w-auto py-3 px-8"><i class="fa-solid fa-check"></i> Admit Student</button>
  </form>
</div>
<?php endif; ?>

<?php if (can_manage()): ?>
<script>
const feeStructureByClass = <?= json_encode($feeStructureByClass) ?>;

function renderAdmissionBills() {
  const classId = document.getElementById('admission_class_id').value;
  const emptyState = document.getElementById('admission-bills-empty');
  const list = document.getElementById('admission-bills-list');
  const totalWrap = document.getElementById('admission-bills-total');
  const items = feeStructureByClass[classId] || [];

  list.innerHTML = '';

  if (!classId || items.length === 0) {
    emptyState.classList.remove('hidden');
    emptyState.textContent = classId ? 'No fee structure has been set for this class yet.' : '';
    if (!classId) emptyState.innerHTML = '<i class="fa-solid fa-arrow-up text-xl mb-2 block"></i> Select a class to see available bills.';
    list.classList.add('hidden');
    totalWrap.classList.add('hidden');
    return;
  }

  emptyState.classList.add('hidden');
  list.classList.remove('hidden');
  totalWrap.classList.remove('hidden');

  items.forEach(item => {
    const row = document.createElement('div');
    row.className = 'bill-row';
    row.innerHTML = `
      <input type="checkbox" name="bill_type_id[]" value="${item.bill_type_id}" checked class="rounded text-purple-600 w-4 h-4 admission-bill-checkbox" data-amount="${item.amount}">
      <span class="flex-1 text-sm text-gray-700">${item.name}</span>
      <span class="text-sm font-semibold text-gray-800 w-24 text-right">KES ${Number(item.amount).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</span>
    `;
    list.appendChild(row);
  });

  list.querySelectorAll('.admission-bill-checkbox').forEach(cb => cb.addEventListener('change', updateAdmissionTotal));
  updateAdmissionTotal();
}

function updateAdmissionTotal() {
  let total = 0;
  document.querySelectorAll('.admission-bill-checkbox:checked').forEach(cb => total += parseFloat(cb.dataset.amount || 0));
  document.getElementById('admission-bills-total-value').textContent = 'KES ' + total.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
}

document.getElementById('admission_class_id').addEventListener('change', renderAdmissionBills);
</script>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
