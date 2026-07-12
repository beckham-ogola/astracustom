<?php $pageTitle = 'General Settings'; require __DIR__ . '/../layouts/header.php'; ?>

<form method="POST" action="<?= base_url('settings') ?>" data-loading class="max-w-2xl">
  <?= csrf_field() ?>
  <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
    <div><label class="block text-sm font-medium text-gray-700 mb-1">School Name</label><input type="text" name="school_name" value="<?= e($settings['school_name']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700 mb-1">School Phone</label><input type="text" name="school_phone" value="<?= e($settings['school_phone']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700 mb-1">School Email</label><input type="email" name="school_email" value="<?= e($settings['school_email']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700 mb-1">School Address</label><input type="text" name="school_address" value="<?= e($settings['school_address']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    <div><label class="block text-sm font-medium text-gray-700 mb-1">School Motto</label><input type="text" name="school_motto" value="<?= e($settings['school_motto']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Current Term</label><input type="text" name="current_term" value="<?= e($settings['current_term']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Current Year</label><input type="text" name="current_year" value="<?= e($settings['current_year']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2"></div>
    </div>
    <p class="text-xs text-gray-400">Current Term is applied to new admissions and billing operations.</p>
    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2.5 rounded-lg"><i class="fa-solid fa-check mr-1"></i> Save Settings</button>
  </div>

  <div class="bg-white rounded-xl shadow-sm p-6 space-y-4 mt-6">
    <div class="flex items-center justify-between">
      <h3 class="font-semibold text-gray-800 text-sm">M-Pesa Integration (Advanced)</h3>
      <span class="badge <?= $mpesaConfigured ? 'badge-active' : 'badge-withdrawn' ?>"><?= $mpesaConfigured ? 'Configured' : 'Not Configured' ?></span>
    </div>
    <p class="text-xs text-gray-500">Connects the Payments screen's "Send M-Pesa Prompt" (STK Push) button to your Safaricom Daraja app. Get your Till Number, Passkey, Consumer Key, and Consumer Secret from <a href="https://developer.safaricom.co.ke" target="_blank" class="text-purple-600 hover:underline">developer.safaricom.co.ke</a>. Without this, staff can still record M-Pesa payments manually using the transaction code.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Environment</label>
        <select name="mpesa_environment" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="sandbox" <?= $mpesa['mpesa_environment'] === 'sandbox' ? 'selected' : '' ?>>Sandbox (testing)</option>
          <option value="live" <?= $mpesa['mpesa_environment'] === 'live' ? 'selected' : '' ?>>Live (production)</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
        <select name="mpesa_transaction_type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
          <option value="CustomerBuyGoodsOnline" <?= $mpesa['mpesa_transaction_type'] === 'CustomerBuyGoodsOnline' ? 'selected' : '' ?>>Till Number (Buy Goods)</option>
          <option value="CustomerPayBillOnline" <?= $mpesa['mpesa_transaction_type'] === 'CustomerPayBillOnline' ? 'selected' : '' ?>>Paybill Number</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Till / Paybill (Shortcode) Number</label>
        <input type="text" name="mpesa_till_number" value="<?= e($mpesa['mpesa_till_number']) ?>" placeholder="e.g. 174379" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Passkey</label>
        <input type="password" name="mpesa_passkey" value="<?= e($mpesa['mpesa_passkey']) ?>" placeholder="From Safaricom Daraja portal" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Consumer Key</label>
        <input type="text" name="mpesa_consumer_key" value="<?= e($mpesa['mpesa_consumer_key']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Consumer Secret</label>
        <input type="password" name="mpesa_consumer_secret" value="<?= e($mpesa['mpesa_consumer_secret']) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2">
      </div>
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Callback URL</label>
        <input type="text" name="mpesa_callback_url" value="<?= e($mpesa['mpesa_callback_url']) ?>" placeholder="<?= e($suggestedCallbackUrl) ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2">
        <p class="text-xs text-gray-400 mt-1">Must be a public HTTPS URL Safaricom can reach — localhost will not work. Suggested: <code class="bg-gray-100 px-1 rounded"><?= e($suggestedCallbackUrl) ?></code> (use a tunnel like ngrok while testing locally).</p>
      </div>
    </div>

    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2.5 rounded-lg"><i class="fa-solid fa-check mr-1"></i> Save M-Pesa Settings</button>
  </div>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
