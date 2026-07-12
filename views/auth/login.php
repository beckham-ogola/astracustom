<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Login · AstraCampus</title>
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
<body class="min-h-screen font-sans bg-gray-900 relative overflow-hidden flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-gradient-to-br from-purple-700 via-purple-800 to-gray-900"></div>
  <div class="absolute -top-24 -right-24 w-96 h-96 bg-purple-500/30 rounded-full blur-3xl"></div>
  <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-400/20 rounded-full blur-3xl"></div>

  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 animate-fadein">
    <div class="text-center mb-6">
      <span class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-purple-400 items-center justify-center text-white shadow-lg mb-3">
        <i class="fa-solid fa-graduation-cap text-2xl"></i>
      </span>
      <h1 class="font-display font-bold text-2xl text-gray-900">AstraCampus</h1>
      <p class="text-gray-400 text-sm mt-0.5">Staff Login</p>
    </div>

    <?php foreach (get_flashes() as $flash): ?>
      <div class="mb-4 text-sm px-4 py-3 rounded-xl border <?= $flash['type'] === 'error' ? 'bg-red-50 text-red-700 border-red-100' : 'bg-amber-50 text-amber-700 border-amber-100' ?>">
        <?= e($flash['message']) ?>
      </div>
    <?php endforeach; ?>

    <form method="POST" action="<?= base_url('login') ?>" data-loading class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="username" required autofocus class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white outline-none transition">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">ID Number</label>
        <input type="text" name="id_number" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white outline-none transition">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white outline-none transition">
      </div>
      <button type="submit" class="btn btn-primary w-full py-3 mt-2">
        <i class="fa-solid fa-right-to-bracket"></i> Sign In
      </button>
    </form>

    <div class="text-center mt-6">
      <a href="<?= base_url('parent') ?>" class="text-purple-600 text-sm font-medium hover:underline"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Parent Portal</a>
    </div>
  </div>
</body>
</html>
