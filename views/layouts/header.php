<?php
/**
 * Shared header layout — desktop sidebar + top bar. On mobile the sidebar
 * is hidden and views/layouts/bottom_nav.php (included from footer.php)
 * takes over instead. Both navs are built from the same build_nav_sections()
 * so they can never show different things for the same role.
 */
$user = current_user();
$pageTitle = $pageTitle ?? 'AstraCampus';
$schoolName = get_setting('school_name', 'AstraCampus School');
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$navSections = build_nav_sections($user['role'], $currentPath);
$bottomNavItems = build_bottom_nav($navSections);
$initials = strtoupper(substr($user['full_name'], 0, 1) . (strpos($user['full_name'], ' ') !== false ? substr(strrchr($user['full_name'], ' '), 1, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> · AstraCampus</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Lexend:wght@600;700;800&display=swap" rel="stylesheet">
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: { sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui'], display: ['Lexend', 'ui-sans-serif', 'system-ui'] },
        colors: {
          brand: { DEFAULT: '#6238e0', dark: '#4f28bd', light: '#8b6ff0' },
          purple: { 50:'#f5f3ff',100:'#ece8fe',200:'#dad2fd',300:'#bcabfa',400:'#9b7ef5',500:'#7c5cf0',600:'#6238e0',700:'#502dc4',800:'#41279e',900:'#362277' },
          gray: { 50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a' },
          mint: { 50:'#ecfdf5',100:'#d1fae5',500:'#10b981',600:'#059669',700:'#047857' },
        },
        borderRadius: { lg: '0.625rem', xl: '1rem', '2xl': '1.25rem' },
        boxShadow: {
          sm: '0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.06)',
          DEFAULT: '0 1px 2px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.06)',
          lg: '0 12px 28px -8px rgba(98,56,224,.16), 0 6px 12px -6px rgba(15,23,42,.06)',
        },
      }
    }
  }
</script>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="bg-gray-50 min-h-screen font-sans text-gray-800 antialiased">

<div class="md:flex md:min-h-screen">

  <!-- ============ Desktop sidebar (hidden below md) ============ -->
  <aside class="hidden md:flex md:flex-col md:w-64 md:shrink-0 bg-white border-r border-gray-100 md:h-screen md:sticky md:top-0 no-print">
    <div class="flex items-center gap-2.5 px-5 py-5 border-b border-gray-100 min-w-0">
      <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-600 to-brand-light flex items-center justify-center text-white shadow-sm shrink-0">
        <i class="fa-solid fa-graduation-cap text-sm"></i>
      </span>
      <span class="font-display font-bold text-gray-900 truncate"><?= e($schoolName) ?></span>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5">
      <?php foreach ($navSections as $section): ?>
        <div>
          <?php if ($section['label']): ?>
            <p class="px-3 mb-1.5 text-[11px] font-bold uppercase tracking-wide text-gray-400"><?= e($section['label']) ?></p>
          <?php endif; ?>
          <div class="space-y-0.5">
            <?php foreach ($section['items'] as $item): ?>
              <a href="<?= base_url($item['href']) ?>"
                 class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors <?= $item['active'] ? 'bg-purple-50 text-purple-700' : 'text-gray-600 hover:bg-gray-50' ?>">
                <i class="fa-solid <?= $item['icon'] ?> w-4 text-center <?= $item['active'] ? 'text-purple-600' : 'text-gray-400' ?>"></i>
                <?= e($item['label']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </nav>

    <div class="border-t border-gray-100 p-3 space-y-0.5">
      <a href="<?= base_url('account') ?>" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm hover:bg-gray-50 <?= $currentPath === '/account' ? 'bg-purple-50 text-purple-700' : 'text-gray-600' ?>">
        <span class="w-7 h-7 rounded-full bg-purple-600 text-white flex items-center justify-center text-[11px] font-bold shrink-0"><?= e($initials ?: '?') ?></span>
        <span class="min-w-0 flex-1 truncate">
          <span class="block font-medium text-gray-800 truncate text-xs"><?= e($user['full_name']) ?></span>
          <span class="block text-[11px] text-gray-400 capitalize"><?= e($user['role']) ?></span>
        </span>
      </a>
      <a href="<?= base_url('logout') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-gray-400 hover:bg-red-50 hover:text-red-600">
        <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Logout
      </a>
    </div>
  </aside>

  <!-- ============ Main column ============ -->
  <div class="flex-1 min-w-0 pb-24 md:pb-0">

    <header class="bg-white/90 backdrop-blur sticky top-0 z-20 px-4 py-3 border-b border-gray-100 md:px-6 no-print">
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
          <span class="flex md:hidden w-9 h-9 rounded-xl bg-gradient-to-br from-purple-600 to-brand-light items-center justify-center text-white shadow-sm shrink-0">
            <i class="fa-solid fa-graduation-cap text-sm"></i>
          </span>
          <h1 class="font-display font-bold text-gray-900 truncate"><?= e($pageTitle) ?></h1>
        </div>
        <div class="flex items-center gap-3 shrink-0">
          <span class="hidden lg:inline-flex items-center gap-1.5 text-xs font-medium text-purple-700 bg-purple-50 border border-purple-100 rounded-full px-3 py-1">
            <i class="fa-solid fa-calendar-days text-[10px]"></i> <?= e(get_setting('current_term')) ?>
          </span>
          <?php if (is_admin()): ?>
          <a href="<?= base_url('administration') ?>"
             class="w-8 h-8 rounded-full flex items-center justify-center transition-colors <?= strpos($currentPath, '/administration') === 0 || in_array($currentPath, ['/classes','/users','/templates','/audit-logs','/settings'], true) ? 'bg-purple-50 text-purple-700' : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600' ?>"
             title="Administration">
            <i class="fa-solid fa-gear text-sm"></i>
          </a>
          <?php endif; ?>
          <a href="<?= base_url('account') ?>" class="md:hidden w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center text-xs font-bold shadow-sm" title="<?= e($user['full_name']) ?>">
            <?= e($initials ?: '?') ?>
          </a>
        </div>
      </div>
    </header>

    <main class="max-w-5xl mx-auto p-4 md:p-6">
      <?php foreach (get_flashes() as $flash): ?>
        <?php
          $colors = [
            'success' => 'bg-mint-50 text-mint-700 border-mint-100',
            'error'   => 'bg-red-50 text-red-700 border-red-100',
            'warning' => 'bg-amber-50 text-amber-700 border-amber-100',
            'info'    => 'bg-blue-50 text-blue-700 border-blue-100',
          ];
          $cls = $colors[$flash['type']] ?? $colors['info'];
        ?>
        <div class="border <?= $cls ?> px-4 py-3 rounded-xl mb-4 flex items-start gap-2.5 text-sm shadow-sm animate-fadein">
          <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : ($flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-triangle-exclamation') ?> mt-0.5"></i>
          <span class="flex-1"><?= e($flash['message']) ?></span>
        </div>
      <?php endforeach; ?>
