<?php $pageTitle = 'Receive Payment'; require __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-xl">
  <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <h3 class="font-semibold text-gray-800"><?= e($student['full_name']) ?></h3>
    <p class="text-gray-500 text-sm"><?= e($student['admission_no']) ?> &middot; <?= e($student['class_name'] ?? '—') ?></p>
    <p class="text-gray-500 text-sm mt-1">Guardian: <?= e($student['guardian1_name']) ?> (<?= e($student['guardian1_phone']) ?>)</p>
    <p class="mt-3 text-2xl font-bold <?= $balance > 0.009 ? 'balance-positive' : 'balance-zero' ?>">Balance: <?= money($balance) ?></p>
  </div>

  <?php if (empty($unpaidBills)): ?>
    <div class="bg-white rounded-xl shadow-sm p-6 text-center text-gray-400">This student has no unpaid bills.</div>
  <?php else: ?>

  <div class="bg-white rounded-xl shadow-sm p-6">
    <div class="space-y-4" id="shared-fields">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Select Bill *</label>
        <select id="bill_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="">Choose a bill</option>
          <?php foreach ($unpaidBills as $b): ?>
            <option value="<?= $b['id'] ?>" data-remaining="<?= $b['remaining'] ?>" data-remaining-formatted="<?= money($b['remaining']) ?>">
              <?= e($b['bill_type_name']) ?> (<?= e($b['term']) ?>) — Remaining: <?= money($b['remaining']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p id="remaining-hint" class="text-xs text-gray-400 mt-1"></p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Amount to Pay *</label>
        <input type="number" step="0.01" min="0.01" id="amount" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
        <select id="payment_method" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="Cash">Cash</option>
          <option value="M-Pesa">M-Pesa</option>
          <option value="Bank Transfer">Bank Transfer</option>
          <option value="Cheque">Cheque</option>
        </select>
      </div>
    </div>

    <!-- ===== Cash / Bank Transfer / Cheque — regular form submit ===== -->
    <form method="POST" action="<?= base_url('payments') ?>" data-loading id="standard-payment-form" class="mt-4 space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
      <input type="hidden" name="bill_id" id="std_bill_id" required>
      <input type="hidden" name="amount" id="std_amount" required>
      <input type="hidden" name="payment_method" id="std_payment_method" value="Cash">

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Payer Name</label>
        <input type="text" name="payer_name" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Optional">
      </div>

      <!-- Manual M-Pesa entry (fallback / when STK Push isn't configured or didn't complete) -->
      <div id="mpesa-manual-fields" class="hidden space-y-3 border-t pt-3">
        <p class="text-xs text-gray-500"><i class="fa-solid fa-circle-info mr-1"></i> Use this if the customer already paid via Till/Paybill and you have their confirmation SMS.</p>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">M-Pesa Transaction Code *</label>
          <input type="text" name="mpesa_code" id="mpesa_code_manual" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">M-Pesa Phone Number *</label>
          <input type="text" name="mpesa_phone" id="mpesa_phone_manual" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>
      </div>

      <div class="flex gap-3 pt-2" id="standard-submit-row">
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2.5 rounded-lg"><i class="fa-solid fa-check mr-1"></i> Process Payment</button>
        <a href="<?= base_url('payments') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-2.5 rounded-lg">Cancel</a>
      </div>
    </form>

    <!-- ===== M-Pesa STK Push panel ===== -->
    <div id="mpesa-stk-panel" class="hidden mt-4 border-t pt-4 space-y-3">
      <?php if ($mpesaConfigured): ?>
        <div class="flex gap-2 mb-1">
          <button type="button" id="stk-mode-btn" class="tab-btn tab-active" onclick="setMpesaMode('stk')">Send Prompt to Phone</button>
          <button type="button" id="manual-mode-btn" class="tab-btn" onclick="setMpesaMode('manual')">Enter Manually</button>
        </div>

        <div id="stk-request-area">
          <label class="block text-sm font-medium text-gray-700 mb-1">Customer's M-Pesa Phone Number *</label>
          <input type="text" id="stk_phone" placeholder="07XXXXXXXX" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <button type="button" id="send-stk-btn" onclick="sendStkPush()" class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-lg">
            <i class="fa-solid fa-mobile-screen-button mr-1"></i> Send M-Pesa Prompt
          </button>
          <p class="text-xs text-gray-400 mt-2">The customer will get a prompt on their phone to enter their M-Pesa PIN for the Till Number on file.</p>
        </div>

        <div id="stk-waiting-area" class="hidden text-center py-6">
          <span class="spinner"></span>
          <p class="text-sm text-gray-600 mt-3" id="stk-status-text">Waiting for the customer to approve the prompt on their phone…</p>
          <button type="button" onclick="cancelStkWait()" class="mt-4 text-sm text-gray-400 hover:text-gray-600 underline">Cancel and use another method</button>
        </div>

        <div id="stk-error-area" class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3"></div>
      <?php else: ?>
        <div class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3 mb-3">
          <i class="fa-solid fa-triangle-exclamation mr-1"></i> M-Pesa prompts aren't set up yet. Ask an admin to add your Till Number and API credentials under Settings → M-Pesa Integration. You can still record M-Pesa payments manually below.
        </div>
        <button type="button" onclick="setMpesaMode('manual')" class="hidden"></button>
        <script>document.addEventListener('DOMContentLoaded', () => setMpesaMode('manual'));</script>
      <?php endif; ?>
    </div>
  </div>

  <?php endif; ?>
</div>

<script>
const csrfToken = "<?= csrf_token() ?>";
const studentId = <?= (int) $student['id'] ?>;
const mpesaConfigured = <?= $mpesaConfigured ? 'true' : 'false' ?>;
let pollTimer = null;
let pollAttempts = 0;

const billSelect = document.getElementById('bill_id');
const amountInput = document.getElementById('amount');
const methodSelect = document.getElementById('payment_method');
const stdBillId = document.getElementById('std_bill_id');
const stdAmount = document.getElementById('std_amount');
const stdMethod = document.getElementById('std_payment_method');
const mpesaManualFields = document.getElementById('mpesa-manual-fields');
const mpesaStkPanel = document.getElementById('mpesa-stk-panel');
const standardForm = document.getElementById('standard-payment-form');
const standardSubmitRow = document.getElementById('standard-submit-row');

function syncSharedFields() {
  stdBillId.value = billSelect.value;
  stdAmount.value = amountInput.value;
  stdMethod.value = methodSelect.value;
}
billSelect.addEventListener('change', function () {
  const opt = billSelect.options[billSelect.selectedIndex];
  document.getElementById('remaining-hint').textContent = opt.dataset.remaining ? ('Remaining: ' + opt.dataset.remainingFormatted) : '';
  amountInput.max = opt.dataset.remaining || '';
  syncSharedFields();
});
amountInput.addEventListener('input', syncSharedFields);

standardForm.addEventListener('submit', function (e) {
  syncSharedFields();
  if (!stdBillId.value || !stdAmount.value || parseFloat(stdAmount.value) <= 0) {
    e.preventDefault();
    alert('Please select a bill and enter a valid amount.');
  }
});

methodSelect.addEventListener('change', function () {
  syncSharedFields();
  const isMpesa = methodSelect.value === 'M-Pesa';
  mpesaStkPanel.classList.toggle('hidden', !isMpesa);
  if (isMpesa && mpesaConfigured) {
    setMpesaMode('stk');
  } else if (isMpesa) {
    setMpesaMode('manual');
  } else {
    mpesaManualFields.classList.add('hidden');
    standardSubmitRow.classList.remove('hidden');
  }
});

function setMpesaMode(mode) {
  const stkBtn = document.getElementById('stk-mode-btn');
  const manualBtn = document.getElementById('manual-mode-btn');
  if (mode === 'stk') {
    if (stkBtn) stkBtn.classList.add('tab-active');
    if (manualBtn) manualBtn.classList.remove('tab-active');
    document.getElementById('stk-request-area')?.classList.remove('hidden');
    mpesaManualFields.classList.add('hidden');
    standardSubmitRow.classList.add('hidden');
  } else {
    if (stkBtn) stkBtn.classList.remove('tab-active');
    if (manualBtn) manualBtn.classList.add('tab-active');
    document.getElementById('stk-request-area')?.classList.add('hidden');
    document.getElementById('stk-waiting-area')?.classList.add('hidden');
    mpesaManualFields.classList.remove('hidden');
    standardSubmitRow.classList.remove('hidden');
  }
}

function sendStkPush() {
  syncSharedFields();
  const billId = billSelect.value;
  const amount = amountInput.value;
  const phone = document.getElementById('stk_phone').value.trim();
  const errorArea = document.getElementById('stk-error-area');
  errorArea.classList.add('hidden');

  if (!billId || !amount || parseFloat(amount) <= 0) {
    errorArea.textContent = 'Select a bill and enter a valid amount first.';
    errorArea.classList.remove('hidden');
    return;
  }
  if (!phone) {
    errorArea.textContent = 'Enter the customer\'s phone number.';
    errorArea.classList.remove('hidden');
    return;
  }

  const btn = document.getElementById('send-stk-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Sending prompt...';

  const body = new URLSearchParams({
    csrf_token: csrfToken, student_id: studentId, bill_id: billId, amount: amount, phone: phone,
  });

  fetch('<?= base_url('payments/mpesa/stk-push') ?>', { method: 'POST', body })
    .then(r => r.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-mobile-screen-button mr-1"></i> Send M-Pesa Prompt';
      if (data.success) {
        document.getElementById('stk-request-area').classList.add('hidden');
        document.getElementById('stk-waiting-area').classList.remove('hidden');
        pollAttempts = 0;
        pollStkStatus(data.checkout_request_id);
      } else {
        errorArea.textContent = data.message || 'Could not send the prompt. Please try again.';
        errorArea.classList.remove('hidden');
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-mobile-screen-button mr-1"></i> Send M-Pesa Prompt';
      errorArea.textContent = 'Network error. Please check your connection and try again.';
      errorArea.classList.remove('hidden');
    });
}

function pollStkStatus(checkoutId) {
  pollTimer = setInterval(() => {
    pollAttempts++;
    fetch('<?= base_url('payments/mpesa/status/') ?>' + checkoutId)
      .then(r => r.json())
      .then(data => {
        if (data.status === 'success') {
          clearInterval(pollTimer);
          document.getElementById('stk-status-text').textContent = 'Payment received! Opening receipt…';
          window.location.href = '<?= base_url('receipts/') ?>' + data.payment_id;
        } else if (data.status === 'failed' || data.status === 'cancelled') {
          clearInterval(pollTimer);
          showStkFailure(data.message || 'The customer did not complete the payment.');
        } else if (pollAttempts >= 30) {
          clearInterval(pollTimer);
          showStkFailure('Still no response after 90 seconds. The customer may not have completed the prompt — you can try again or use manual entry.');
        }
      })
      .catch(() => { /* keep polling despite transient network errors */ });
  }, 3000);
}

function showStkFailure(message) {
  document.getElementById('stk-waiting-area').classList.add('hidden');
  document.getElementById('stk-request-area').classList.remove('hidden');
  const errorArea = document.getElementById('stk-error-area');
  errorArea.textContent = message;
  errorArea.classList.remove('hidden');
}

function cancelStkWait() {
  if (pollTimer) clearInterval(pollTimer);
  document.getElementById('stk-waiting-area').classList.add('hidden');
  setMpesaMode('manual');
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
