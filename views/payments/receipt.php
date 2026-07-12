<?php $pageTitle = 'Receipt: ' . $payment['receipt_no']; require __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-3xl">
  <div class="flex justify-between items-center mb-4 no-print flex-wrap gap-2">
    <a href="<?= base_url('payments') ?>" class="text-purple-600 text-sm hover:underline"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Payments</a>
    <div class="flex gap-2">
      <button data-modal-open="sms-modal" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg"><i class="fa-solid fa-sms mr-1"></i> Send SMS Receipt</button>
      <button data-modal-open="reprint-modal" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg"><i class="fa-solid fa-clock-rotate-left mr-1"></i> Log Reprint</button>
      <button onclick="printPage()" class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg"><i class="fa-solid fa-print mr-1"></i> Print</button>
    </div>
  </div>

  <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 print-area">
    <div class="text-center border-b pb-4 mb-4">
      <h2 class="text-lg font-bold text-gray-800"><?= e(get_setting('school_name')) ?></h2>
      <p class="text-xs text-gray-500"><?= e(get_setting('school_address')) ?> &middot; <?= e(get_setting('school_phone')) ?> &middot; <?= e(get_setting('school_email')) ?></p>
      <p class="text-xs text-gray-500 mt-0.5">Term: <?= e(get_setting('current_term')) ?></p>
    </div>

    <div class="flex justify-between text-sm mb-4 flex-wrap gap-2">
      <div><span class="text-gray-500">Receipt No:</span> <span class="font-bold text-purple-700"><?= e($payment['receipt_no']) ?></span></div>
      <div><span class="text-gray-500">Date:</span> <span class="font-medium"><?= date('d M Y, h:i A', strtotime($payment['payment_date'])) ?></span></div>
    </div>

    <div class="grid grid-cols-2 gap-3 text-sm mb-4 bg-gray-50 rounded-lg p-4">
      <div><span class="text-gray-500">Student:</span> <span class="font-medium"><?= e($payment['student_name']) ?></span></div>
      <div><span class="text-gray-500">Admission No:</span> <span class="font-medium"><?= e($payment['admission_no']) ?></span></div>
      <div><span class="text-gray-500">Class:</span> <span class="font-medium"><?= e($payment['class_name'] ?? '—') ?></span></div>
      <div><span class="text-gray-500">Payer:</span> <span class="font-medium"><?= e($payment['payer_name'] ?: $payment['guardian1_name']) ?></span></div>
    </div>

    <div class="grid grid-cols-2 gap-3 text-sm mb-4">
      <div><span class="text-gray-500">Bill Type:</span> <span class="font-medium"><?= e($payment['bill_type_name']) ?></span></div>
      <div><span class="text-gray-500">Payment Method:</span> <span class="font-medium"><?= e($payment['payment_method']) ?></span></div>
      <?php $details = !empty($payment['payment_details']) ? json_decode($payment['payment_details'], true) : null; ?>
      <?php if ($details && !empty($details['transaction_code'])): ?>
        <div><span class="text-gray-500">M-Pesa Code:</span> <span class="font-medium"><?= e($details['transaction_code']) ?></span></div>
      <?php endif; ?>
    </div>

    <div class="text-center bg-purple-50 rounded-xl py-4 mb-6">
      <p class="text-gray-500 text-sm">Amount Paid</p>
      <p class="text-3xl font-bold text-purple-700"><?= money($payment['amount_paid']) ?></p>
    </div>

    <h4 class="font-semibold text-gray-700 text-sm mb-2">Fee Breakdown</h4>
    <?php foreach ($termsGrouped as $term => $termBills): ?>
      <div class="mb-4">
        <p class="text-xs font-semibold text-purple-700 uppercase mb-1"><?= e($term) ?></p>
        <table class="w-full text-sm border-collapse mb-1">
          <thead><tr class="bg-gray-50 text-left text-gray-500"><th class="px-2 py-1.5">Bill Type</th><th class="px-2 py-1.5">Amount</th><th class="px-2 py-1.5">Paid</th><th class="px-2 py-1.5">Remaining</th><th class="px-2 py-1.5">Status</th></tr></thead>
          <tbody>
          <?php $termBalance = 0; foreach ($termBills as $b): $termBalance += $b['remaining']; $status = Bill::statusLabel($b); ?>
            <tr class="border-b">
              <td class="px-2 py-1.5"><?= e($b['bill_type_name']) ?></td>
              <td class="px-2 py-1.5"><?= money($b['final_amount']) ?></td>
              <td class="px-2 py-1.5"><?= money($b['paid_amount']) ?></td>
              <td class="px-2 py-1.5 <?= $b['remaining'] > 0.009 ? 'balance-positive' : 'balance-zero' ?>"><?= money($b['remaining']) ?></td>
              <td class="px-2 py-1.5"><span class="badge badge-<?= strtolower($status) ?>"><?= $status ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <p class="text-xs text-right text-gray-500">Term Balance: <span class="font-semibold <?= $termBalance > 0.009 ? 'balance-positive' : 'balance-zero' ?>"><?= money($termBalance) ?></span></p>
      </div>
    <?php endforeach; ?>

    <div class="border-t pt-4 grid grid-cols-3 gap-3 text-center text-sm">
      <div><p class="text-gray-500">Total Billed</p><p class="font-bold"><?= money($totalBilled) ?></p></div>
      <div><p class="text-gray-500">Total Paid</p><p class="font-bold text-green-600"><?= money($totalPaid) ?></p></div>
      <div><p class="text-gray-500">Outstanding</p><p class="font-bold <?= $outstanding > 0.009 ? 'text-red-600' : 'text-green-600' ?>"><?= money($outstanding) ?></p></div>
    </div>

    <div class="border-t mt-4 pt-4 text-sm text-gray-500 flex justify-between">
      <span>Collected By: <span class="font-medium text-gray-700"><?= e($payment['collected_by_name'] ?? 'N/A') ?></span></span>
    </div>

    <p class="text-center text-sm text-gray-600 mt-6">Thank you for your payment!</p>
    <p class="text-center text-xs text-gray-400 mt-1">Powered by AstraCampus</p>
  </div>
</div>

<!-- SMS Modal -->
<div id="sms-modal" class="modal-backdrop hidden">
  <div class="modal-box p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Send SMS Receipt</h3>
    <form method="POST" action="<?= base_url('receipts/' . $payment['id'] . '/sms') ?>" data-loading class="space-y-3">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Phone</label>
        <select name="phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <?php if (!empty($payment['guardian1_phone'])): ?><option value="<?= e($payment['guardian1_phone']) ?>">Guardian 1 (Primary) — <?= e($payment['guardian1_phone']) ?></option><?php endif; ?>
          <?php if (!empty($payment['guardian1_phone_alt'])): ?><option value="<?= e($payment['guardian1_phone_alt']) ?>">Guardian 1 (Alternative) — <?= e($payment['guardian1_phone_alt']) ?></option><?php endif; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Message (max 160 characters)</label>
        <?php
          $balance = Student::balanceFor((int)$payment['student_id']);
          $template = get_setting('school_name') . "\nReceipt: {$payment['receipt_no']}\nStudent: {$payment['student_name']} ({$payment['admission_no']})\nAmount Paid: " . money($payment['amount_paid']) . "\nRemaining: " . money($balance) . "\nThank you for your payment!";
          $template = substr($template, 0, 160);
        ?>
        <textarea name="message" id="sms-message" maxlength="160" rows="5" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"><?= e($template) ?></textarea>
        <p id="sms-counter" class="text-xs text-gray-400 mt-1 text-right"></p>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-modal-close class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">Send SMS</button>
      </div>
    </form>
  </div>
</div>

<!-- Reprint Modal -->
<div id="reprint-modal" class="modal-backdrop hidden">
  <div class="modal-box p-6">
    <h3 class="font-semibold text-gray-800 mb-4">Log Receipt Reprint</h3>
    <form method="POST" action="<?= base_url('receipts/' . $payment['id'] . '/reprint') ?>" data-loading class="space-y-3">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Reprint</label>
        <input type="text" name="reason" placeholder="e.g. Original copy lost" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-modal-close class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-purple-600 text-white text-sm">Log & Print</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
