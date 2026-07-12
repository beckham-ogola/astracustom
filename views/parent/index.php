<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(get_setting('school_name', 'AstraCampus School')) ?> · Parent Portal</title>
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
<body class="min-h-screen font-sans bg-gray-50">
<header class="bg-gradient-to-br from-purple-700 to-purple-900 text-white py-10 px-4 text-center relative overflow-hidden">
  <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
  <span class="relative inline-flex w-14 h-14 rounded-2xl bg-white/15 items-center justify-center mb-3">
    <i class="fa-solid fa-graduation-cap text-2xl"></i>
  </span>
  <h1 class="relative font-display font-bold text-2xl"><?= e(get_setting('school_name', 'AstraCampus School')) ?></h1>
  <p class="relative text-purple-200 text-sm mt-1"><?= e(get_setting('school_motto', '')) ?></p>
  <p class="relative text-purple-300 text-xs mt-1"><?= e(get_setting('school_address', '')) ?> &middot; <?= e(get_setting('school_phone', '')) ?></p>
</header>

<main class="max-w-md mx-auto px-4 -mt-6 relative pb-10">
  <div class="card">
    <h2 class="font-display font-bold text-lg text-gray-900 mb-1 text-center">Student Fee Lookup</h2>
    <p class="text-gray-400 text-sm text-center mb-6">Enter your child's admission number to view their fee statement.</p>

    <?php foreach (get_flashes() as $flash): ?>
      <div class="mb-4 text-sm px-4 py-3 rounded-xl border <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border-red-100' : 'bg-amber-50 text-amber-700 border-amber-100' ?>">
        <?= e($flash['message']) ?>
      </div>
    <?php endforeach; ?>

    <form method="POST" action="<?= base_url('parent/lookup') ?>" data-loading class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Admission Number</label>
        <input type="text" name="admission_no" placeholder="e.g. ACS-001 or 1" required autofocus
               class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-3 text-center text-lg font-semibold tracking-wide focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white outline-none transition">
      </div>
      <button type="submit" class="btn btn-primary w-full py-3">
        <i class="fa-solid fa-magnifying-glass"></i> View Fee Statement
      </button>
    </form>
  </div>
</main>

<a href="<?= base_url('login') ?>" class="fixed bottom-6 right-6 bg-gray-900 hover:bg-black text-white rounded-full pl-4 pr-5 py-3 shadow-lg flex items-center gap-2 text-sm font-semibold transition">
  <i class="fa-solid fa-user-shield"></i> Staff Login
</a>
</body>
</html>
