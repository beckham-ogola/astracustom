<?php
/**
 * Mobile bottom navigation — floating rounded bar, max 5 items, icon + label,
 * role-aware. Hidden on md+ screens, where the sidebar (header.php) takes over.
 * Expects $bottomNavItems (built in header.php via build_nav_sections() +
 * build_bottom_nav()) to be in scope.
 */
?>
<nav class="md:hidden fixed bottom-0 inset-x-0 z-40 no-print px-3 pb-3" style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom));">
  <div class="max-w-md mx-auto bg-white/95 backdrop-blur border border-gray-100 rounded-2xl shadow-lg flex px-1.5 py-1.5">
    <?php foreach ($bottomNavItems as $item): ?>
      <a href="<?= base_url($item['href']) ?>"
         class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 rounded-xl text-[10.5px] font-medium transition-colors <?= $item['active'] ? 'bg-purple-50 text-purple-700' : 'text-gray-400 hover:text-gray-600' ?>">
        <i class="fa-solid <?= $item['icon'] ?> text-base leading-none <?= $item['active'] ? 'text-purple-600' : '' ?>"></i>
        <span><?= e($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
