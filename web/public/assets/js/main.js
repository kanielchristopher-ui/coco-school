window.tr = window.tr || function (s) { return s; };
/* ============================================================
   School Management System - Global JavaScript
   ============================================================ */

(function () {
    'use strict';

    // ---------- Confirm / delete modal ----------
    function openConfirm(url, message, label, buttonClass) {
        const modalEl = document.getElementById('confirmModal');
        if (!modalEl) {
            if (window.confirm(message)) {
                window.location.href = url;
            }
            return;
        }
        const btn = document.getElementById('confirmActionBtn');
        const body = modalEl.querySelector('.modal-body');
        if (btn) {
            btn.href = url;
            btn.className = 'btn ' + buttonClass + ' btn-sm';
            btn.textContent = label;
        }
        if (body) {
            body.textContent = message;
        }
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    window.confirmDelete = function (url) {
        openConfirm(url, tr('Are you sure you want to delete this record? This action cannot be undone.'), tr('Yes, delete'), 'btn-danger');
    };

    window.confirmDeleteStudent = function (btn) {
        const url = btn.getAttribute('data-delete-url');
        const name = btn.getAttribute('data-student-name');
        const who = name ? ' "' + name + '"' : '';
        const message = tr('Permanently delete student') + who + ' ?\n\n'
            + tr('This will remove the student profile, their login account and notifications, attendance records, exam results, student fees and payments, fee collections, and parent-student links. This action cannot be undone.');
        openConfirm(url, message, tr('Yes, delete student'), 'btn-danger');
    };

    window.confirmDeleteTeacher = function (btn) {
        const url = btn.getAttribute('data-delete-url');
        const name = btn.getAttribute('data-teacher-name');
        const who = name ? ' "' + name + '"' : '';
        const message = tr('Permanently delete teacher') + who + ' ?\n\n'
            + tr('This will remove the teacher profile, their login account and notifications, class and subject assignments, payroll records, teacher attendance, and results they created. This action cannot be undone.');
        openConfirm(url, message, tr('Yes, delete teacher'), 'btn-danger');
    };

    document.addEventListener('DOMContentLoaded', function () {
        // ---------- Sidebar (mobile off-canvas) ----------
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('open');
            if (overlay) overlay.classList.add('show');
        }
        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('show');
        }

        if (openBtn) openBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);

        // Close sidebar after navigating on small screens.
        const navLinks = sidebar ? sidebar.querySelectorAll('.nav-link') : [];
        navLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) closeSidebar();
            });
        });

        // Highlight the sidebar entry for the current module (visual only).
        const currentPath = location.pathname.replace(/\/+$/, '');
        navLinks.forEach(function (link) {
            const href = link.getAttribute('href');
            if (!href || href === '#' || href.charAt(0) === '#') return;
            let linkPath;
            try { linkPath = new URL(href, location.href).pathname.replace(/\/+$/, ''); }
            catch (e) { return; }
            if (linkPath && (currentPath === linkPath || currentPath.indexOf(linkPath + '/') === 0)) {
                link.classList.add('active');
            }
        });

        // Ensure the confirm modal button clears state afterwards.
        const confirmModalEl = document.getElementById('confirmModal');
        if (confirmModalEl) {
            confirmModalEl.addEventListener('hidden.bs.modal', function () {
                const btn = document.getElementById('confirmActionBtn');
                if (btn) btn.href = '#';
            });
        }

        // ---------- Modal backdrop / state cleanup ----------
        // Prevents the "stuck screen" symptom: a leftover modal backdrop or the
        // body.modal-open class can survive navigation and browser back, leaving
        // the page frozen under a dead overlay. Clean up on hide and on every
        // page restore (normal load, forward/back, bfcache).
        function cleanupModalState() {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            for (let i = 0; i < backdrops.length; i++) backdrops[i].remove();
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
            document.querySelectorAll('.modal.show').forEach(function (el) {
                const inst = bootstrap.Modal.getInstance(el);
                if (inst) inst.hide();
            });
        }

        document.addEventListener('hidden.bs.modal', cleanupModalState, true);
        window.addEventListener('pageshow', cleanupModalState);
        window.addEventListener('popstate', cleanupModalState);
    });
})();

/* ---------- Tableaux adaptatifs ----------
   1) tout tableau hors .table-responsive est enveloppé pour défiler horizontalement ;
   2) les tableaux simples (≤ 7 colonnes, sans colspan/rowspan) reçoivent des libellés
      de colonne et s'empilent en cartes sur téléphone. Les autres défilent. */
(function () {
    'use strict';
    function adapt() {
        document.querySelectorAll('table.table').forEach(function (t) {
            if (t.closest('.no-stack, .print-area, [data-no-adapt]')) return;
            if (!t.closest('.table-responsive')) {
                var w = document.createElement('div');
                w.className = 'table-responsive';
                t.parentNode.insertBefore(w, t);
                w.appendChild(t);
            }
            var heads = t.querySelectorAll('thead tr:last-child th');
            var rows = t.querySelectorAll('tbody tr');
            if (!heads.length || heads.length > 7 || !rows.length || t.querySelector('[colspan], [rowspan]')) return;
            var labels = [].map.call(heads, function (h) { return h.textContent.replace(/\s+/g, ' ').trim(); });
            rows.forEach(function (tr) {
                [].forEach.call(tr.children, function (td, i) {
                    if (td.tagName === 'TD' && !td.hasAttribute('data-label')) td.setAttribute('data-label', labels[i] || '');
                });
            });
            t.classList.add('table-stack');
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', adapt); else adapt();
})();
