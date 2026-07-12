<?php $pageTitle = 'Edit Student'; require __DIR__ . '/../layouts/header.php'; ?>

<form method="POST" action="<?= base_url('students/' . $student['id'] . '/edit') ?>" enctype="multipart/form-data" data-loading class="space-y-6 max-w-4xl">
  <?= csrf_field() ?>

  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4"><i class="fa-solid fa-user mr-1 text-purple-600"></i> Student Information</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
        <input type="text" name="full_name" required value="<?= e($student['full_name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
        <input type="date" name="dob" required value="<?= e($student['dob']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
        <select name="gender" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
          <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
          <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Birth Certificate Number *</label>
        <input type="text" name="birth_cert_no" required value="<?= e($student['birth_cert_no']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Class *</label>
        <select name="class_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
          <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (int)$student['class_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
        <input type="text" name="term" value="<?= e($student['term']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500">
      </div>
      <div class="flex items-center gap-2 mt-6">
        <input type="checkbox" name="photo_consent" id="photo_consent" <?= $student['photo_consent'] ? 'checked' : '' ?> class="rounded text-purple-600 focus:ring-purple-500">
        <label for="photo_consent" class="text-sm text-gray-700">Photo consent granted</label>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4"><i class="fa-solid fa-people-roof mr-1 text-purple-600"></i> Guardian 1 (Primary) *</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label><input type="text" name="guardian1_name" required value="<?= e($student['guardian1_name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Relationship *</label><input type="text" name="guardian1_relation" required value="<?= e($student['guardian1_relation']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">National ID *</label><input type="text" name="guardian1_id" required value="<?= e($student['guardian1_id']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label><input type="text" name="guardian1_phone" required value="<?= e($student['guardian1_phone']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Alternative Phone</label><input type="text" name="guardian1_phone_alt" value="<?= e($student['guardian1_phone_alt']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label><input type="text" name="guardian1_address" value="<?= e($student['guardian1_address']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4"><i class="fa-solid fa-people-roof mr-1 text-purple-600"></i> Guardian 2 (Optional)</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label><input type="text" name="guardian2_name" value="<?= e($student['guardian2_name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Relationship</label><input type="text" name="guardian2_relation" value="<?= e($student['guardian2_relation']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">National ID</label><input type="text" name="guardian2_id" value="<?= e($student['guardian2_id']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label><input type="text" name="guardian2_phone" value="<?= e($student['guardian2_phone']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Alternative Phone</label><input type="text" name="guardian2_phone_alt" value="<?= e($student['guardian2_phone_alt']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Physical Address</label><input type="text" name="guardian2_address" value="<?= e($student['guardian2_address']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    </div>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4"><i class="fa-solid fa-notes-medical mr-1 text-purple-600"></i> Medical Information</h3>
    <textarea name="medical_conditions" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2"><?= e($student['medical_conditions']) ?></textarea>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="font-semibold text-gray-800 mb-4"><i class="fa-solid fa-paperclip mr-1 text-purple-600"></i> Admission Form</h3>
    <?php if (!empty($student['admission_form_path'])): ?>
      <p class="text-sm mb-2"><a href="<?= base_url('uploads/admission_forms/' . $student['admission_form_path']) ?>" target="_blank" class="text-purple-600 hover:underline"><i class="fa-solid fa-file mr-1"></i>View current file</a></p>
    <?php endif; ?>
    <input type="file" name="admission_form" accept=".pdf,.jpg,.jpeg,.png" class="w-full border border-gray-300 rounded-lg px-3 py-2">
    <p class="text-xs text-gray-400 mt-1">Upload a new file to replace the current one. Max size: 5MB.</p>
  </div>

  <div class="flex gap-3">
    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg"><i class="fa-solid fa-check mr-1"></i> Save Changes</button>
    <a href="<?= base_url('students/' . $student['id']) ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-lg">Cancel</a>
  </div>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
