<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fee Statement · <?= e($student['full_name']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Lexend:wght@600;700;800&display=swap" rel="stylesheet">
<script>
  tailwind.config = { theme: { extend: {
    fontFamily: { sans: ['Plus Jakarta Sans','ui-sans-serif','system-ui'], display: ['Lexend','ui-sans-serif','system-ui'] },
    colors: { purple: { 50:'#f5f3ff',100:'#ece8fe',200:'#dad2fd',300:'#bcabfa',400:'#9b7ef5',500:'#7c5cf0',600:'#6238e0',700:'#502dc4',800:'#41279e',900:'#362277' } }
  } } }
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="min-h-screen bg-gray-50 font-sans">
<header class="bg-gradient-to-br from-purple-700 to-purple-900 text-white py-6 px-4 text-center no-print">
  <i class="fa-solid fa-graduation-cap text-2xl"></i>
  <h1 class="font-display font-bold text-xl mt-1"><?= e(get_setting('school_name', 'AstraCampus School')) ?></h1>
</header>

<main class="max-w-3xl mx-auto px-4 py-8">
  <div class="mb-4 flex justify-between items-center no-print">
    <a href="<?= base_url('parent') ?>" class="text-purple-600 text-sm font-medium hover:underline"><i class="fa-solid fa-arrow-left mr-1"></i> New Search</a>
    <button onclick="printPage()" class="btn btn-primary text-sm"><i class="fa-solid fa-print"></i> Print Statement</button>
  </div>

  <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 print-area">
    <div class="text-center border-b pb-4 mb-4">
      <h2 class="text-lg font-bold text-gray-800"><?= e(get_setting('school_name')) ?></h2>
      <p class="text-xs text-gray-500"><?= e(get_setting('school_address')) ?> &middot; <?= e(get_setting('school_phone')) ?></p>
      <p class="text-sm font-semibold text-purple-700 mt-1">Fee Statement</p>
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm mb-6">
      <div><span class="text-gray-500">Full Name:</span> <span class="font-semibold"><?= e($student['full_name']) ?></span></div>
      <div><span class="text-gray-500">Admission No:</span> <span class="font-semibold"><?= e($student['admission_no']) ?></span></div>
      <div><span class="text-gray-500">Class:</span> <span class="font-semibold"><?= e($student['class_name'] ?? '—') ?></span></div>
      <div><span class="text-gray-500">Gender:</span> <span class="font-semibold"><?= e($student['gender']) ?></span></div>
      <div><span class="text-gray-500">Date of Birth:</span> <span class="font-semibold"><?= date('d M Y', strtotime($student['dob'])) ?></span></div>
      <div><span class="text-gray-500">Admission Date:</span> <span class="font-semibold"><?= date('d M Y', strtotime($student['admission_date'])) ?></span></div>
      <div><span class="text-gray-500">Guardian:</span> <span class="font-semibold"><?= e($student['guardian1_name']) ?></span></div>
      <div><span class="text-gray-500">Guardian Phone:</span> <span class="font-semibold"><?= e($student['guardian1_phone']) ?></span></div>
    </div>

    <div class="text-center mb-6">
      <p class="text-gray-500 text-sm">Current Balance</p>
      <p class="text-3xl font-bold <?= $balance > 0.009 ? 'balance-positive' : 'balance-zero' ?>"><?= money($balance) ?></p>
    </div>

    <table class="w-full text-sm border-collapse">
      <thead>
        <tr class="bg-purple-50 text-left text-gray-600">
          <th class="px-3 py-2">Bill Type</th>
          <th class="px-3 py-2">Amount</th>
          <th class="px-3 py-2">Discount</th>
          <th class="px-3 py-2">Final</th>
          <th class="px-3 py-2">Paid</th>
          <th class="px-3 py-2">Remaining</th>
          <th class="px-3 py-2">Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($bills as $bill): ?>
        <tr class="border-b">
          <td class="px-3 py-2"><?= e($bill['bill_type_name']) ?> <span class="text-gray-400 text-xs">(<?= e($bill['term']) ?>)</span></td>
          <td class="px-3 py-2"><?= money($bill['amount']) ?></td>
          <td class="px-3 py-2"><?= money($bill['discount_applied']) ?></td>
          <td class="px-3 py-2"><?= money($bill['final_amount']) ?></td>
          <td class="px-3 py-2"><?= money($bill['paid_amount']) ?></td>
          <td class="px-3 py-2 <?= $bill['remaining'] > 0.009 ? 'balance-positive' : 'balance-zero' ?>"><?= money($bill['remaining']) ?></td>
          <td class="px-3 py-2">
            <?php $status = Bill::statusLabel($bill); ?>
            <span class="badge badge-<?= strtolower($status) ?>"><?= e($status) ?></span>
          </td>
        </tr>
        <?php if (!empty($payments[$bill['id']])): ?>
          <?php foreach ($payments[$bill['id']] as $p): ?>
            <tr class="text-xs text-gray-500 bg-gray-50">
              <td class="px-3 py-1 pl-6" colspan="7">
                <i class="fa-solid fa-receipt mr-1"></i>
                Paid <?= money($p['amount_paid']) ?> via <?= e($p['payment_method']) ?> — Receipt <?= e($p['receipt_no']) ?> on <?= date('d M Y, h:i A', strtotime($p['payment_date'])) ?> (Collected by <?= e($p['collected_by_name'] ?? 'N/A') ?>)
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php if (empty($bills)): ?>
        <tr><td colspan="7" class="px-3 py-4 text-center text-gray-400">No bills recorded yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
