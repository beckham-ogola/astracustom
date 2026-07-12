/**
 * AstraCampus - Shared client-side behaviors
 * - Real-time table search filtering (data-search-input / data-search-target)
 * - Confirm dialogs for destructive actions (data-confirm)
 * - Simple modal open/close helpers
 * - Loading spinner on form submit
 */

document.addEventListener('DOMContentLoaded', function () {

  // ---- Real-time client-side search filtering ----
  document.querySelectorAll('[data-search-input]').forEach(function (input) {
    const targetSelector = input.getAttribute('data-search-input');
    const rows = document.querySelectorAll(targetSelector);
    input.addEventListener('input', function () {
      const q = input.value.trim().toLowerCase();
      rows.forEach(function (row) {
        const haystack = row.getAttribute('data-search-text') || row.textContent;
        row.style.display = haystack.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });

  // ---- Confirm dialogs for destructive actions ----
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('submit', function (e) {
      const msg = el.getAttribute('data-confirm') || 'Are you sure?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
    el.addEventListener('click', function (e) {
      if (el.tagName === 'A') {
        const msg = el.getAttribute('data-confirm') || 'Are you sure?';
        if (!confirm(msg)) {
          e.preventDefault();
        }
      }
    });
  });

  // ---- Loading spinner on submit ----
  document.querySelectorAll('form[data-loading]').forEach(function (form) {
    form.addEventListener('submit', function () {
      const btn = form.querySelector('[type="submit"]');
      if (btn && !btn.disabled) {
        btn.disabled = true;
        btn.dataset.originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span> Processing...';
      }
    });
  });

  // ---- Modal open/close via data attributes ----
  document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const modal = document.getElementById(btn.getAttribute('data-modal-open'));
      if (modal) modal.classList.remove('hidden');
    });
  });
  document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const modal = btn.closest('.modal-backdrop');
      if (modal) modal.classList.add('hidden');
    });
  });

  // ---- Sponsor checkbox auto-disables discount input ----
  document.querySelectorAll('[data-sponsor-toggle]').forEach(function (checkbox) {
    const targetId = checkbox.getAttribute('data-sponsor-toggle');
    const discountInput = document.getElementById(targetId);
    if (!discountInput) return;
    checkbox.addEventListener('change', function () {
      discountInput.disabled = checkbox.checked;
      if (checkbox.checked) discountInput.value = '';
    });
  });

  // ---- Tab switching (Students / Billing / Payments hubs) ----
  document.querySelectorAll('[data-tab-group]').forEach(function (group) {
    const groupId = group.getAttribute('data-tab-group');
    const buttons = group.querySelectorAll('[data-tab-btn]');
    const panels = document.querySelectorAll('[data-tab-panel-group="' + groupId + '"]');

    function activate(target) {
      buttons.forEach(function (b) {
        const isActive = b.getAttribute('data-tab-btn') === target;
        b.classList.toggle('tab-active', isActive);
      });
      panels.forEach(function (p) {
        p.classList.toggle('hidden', p.getAttribute('data-tab-panel') !== target);
      });
      const url = new URL(window.location);
      url.searchParams.set('tab', target);
      window.history.replaceState({}, '', url);
    }

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        activate(btn.getAttribute('data-tab-btn'));
      });
    });

    const requested = new URL(window.location).searchParams.get('tab');
    const validTargets = Array.from(buttons).map(b => b.getAttribute('data-tab-btn'));
    if (requested && validTargets.includes(requested)) {
      activate(requested);
    } else if (buttons.length) {
      activate(buttons[0].getAttribute('data-tab-btn'));
    }
  });

  // ---- SMS character counter ----
  const smsBox = document.getElementById('sms-message');
  const smsCounter = document.getElementById('sms-counter');
  if (smsBox && smsCounter) {
    const update = () => { smsCounter.textContent = smsBox.value.length + ' / 160'; };
    smsBox.addEventListener('input', update);
    update();
  }
});

function printPage() {
  window.print();
}
