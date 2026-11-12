/* ============================================================
   School Management System - Public Website JavaScript
   Sticky header, mobile nav, scroll reveal, news modal,
   contact form validation and Bootstrap-style alert dismissal.
   Vanilla JS, no dependencies.
   ============================================================ */
(function () {
    'use strict';

    var prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Sticky header ---------- */
    var header = document.getElementById('wsHeader');
    function onScroll() {
        if (!header) return;
        var scrolled = (window.scrollY || 0) > 8;
        header.classList.toggle('is-scrolled', scrolled);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ---------- Mobile navigation toggle ---------- */
    var toggle = document.getElementById('wsNavToggle');
    var nav = document.getElementById('wsNav');
    function closeNav() {
        if (!nav || !toggle) return;
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Toggle navigation');
    }
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Toggle navigation');
        });
        nav.addEventListener('click', function (e) {
            if (e.target.closest('a')) closeNav();
        });
        document.addEventListener('click', function (e) {
            if (nav.classList.contains('is-open') && !nav.contains(e.target) && !toggle.contains(e.target)) {
                closeNav();
            }
        });
    }
    window.addEventListener('resize', function () {
        if (window.innerWidth > 768) closeNav();
    });

    /* ---------- Scroll reveal ---------- */
    var revealEls = document.querySelectorAll('.ws-reveal');
    if (revealEls.length) {
        if (prefersReduced || !('IntersectionObserver' in window)) {
            Array.prototype.forEach.call(revealEls, function (el) { el.classList.add('is-visible'); });
        } else {
            var revealObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            Array.prototype.forEach.call(revealEls, function (el) { revealObserver.observe(el); });
        }
    }

    /* ---------- News / Event detail modal ---------- */
    var modal = document.getElementById('wsNewsModal');
    var modalTitle = document.getElementById('wsNewsModalTitle');
    var modalDate = document.getElementById('wsNewsModalDate');
    var modalLocation = document.getElementById('wsNewsModalLocation');
    var modalDesc = document.getElementById('wsNewsModalDescription');

    function openModal() {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        var closeBtn = modal.querySelector('.ws-modal-close');
        if (closeBtn) closeBtn.focus();
    }
    function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
    function bindNewsModal() {
        if (!modal) return;
        var triggers = document.querySelectorAll('.ws-news-more[data-event-title]');
        Array.prototype.forEach.call(triggers, function (btn) {
            btn.addEventListener('click', function () {
                if (modalTitle) modalTitle.textContent = btn.getAttribute('data-event-title') || '';
                if (modalDate) modalDate.textContent = btn.getAttribute('data-event-date') || '';
                if (modalLocation) {
                    var loc = btn.getAttribute('data-event-location') || '';
                    modalLocation.style.display = loc ? '' : 'none';
                    modalLocation.textContent = loc ? ('Location: ' + loc) : '';
                }
                if (modalDesc) modalDesc.textContent = btn.getAttribute('data-event-description') || '';
                openModal();
            });
        });
        var overlay = modal.querySelector('.ws-modal-overlay');
        var closeBtns = modal.querySelectorAll('[data-ws-modal-close]');
        Array.prototype.forEach.call(closeBtns, function (el) {
            el.addEventListener('click', closeModal);
        });
        if (overlay) overlay.addEventListener('click', closeModal);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
        });
    }
    bindNewsModal();

    /* ---------- Contact form client-side validation ---------- */
    var form = document.getElementById('wsContactForm');
    if (form) {
        var fields = [
            { input: form.querySelector('#name'),    error: 'Please enter your full name.' },
            { input: form.querySelector('#email'),   error: 'Please enter a valid email address.' },
            { input: form.querySelector('#subject'), error: 'Please enter a subject.' },
            { input: form.querySelector('#message'), error: 'Please write a message (at least 5 characters).' }
        ];
        var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        function clearError(wrap) {
            if (!wrap) return;
            wrap.classList.remove('has-error');
            var msg = wrap.querySelector('.ws-field-error');
            if (msg) msg.remove();
        }
        function showError(wrap, message) {
            if (!wrap) return;
            wrap.classList.add('has-error');
            var msg = wrap.querySelector('.ws-field-error');
            if (!msg) {
                msg = document.createElement('span');
                msg.className = 'ws-field-error';
                wrap.appendChild(msg);
            }
            msg.textContent = message;
        }
        function validate() {
            var firstInvalid = null;
            fields.forEach(function (f) {
                var wrap = f.input ? f.input.closest('.ws-field') : null;
                clearError(wrap);
                if (!f.input) return;
                var value = f.input.value.trim();
                if (value === '') {
                    showError(wrap, f.error);
                    if (!firstInvalid) firstInvalid = f.input;
                } else if (f.input.type === 'email' && !emailRe.test(value)) {
                    showError(wrap, 'Please enter a valid email address.');
                    if (!firstInvalid) firstInvalid = f.input;
                }
            });
            if (firstInvalid) {
                firstInvalid.focus();
                return false;
            }
            return true;
        }
        fields.forEach(function (f) {
            if (f.input) f.input.addEventListener('blur', validate);
        });
        form.addEventListener('submit', function (e) {
            if (!validate()) e.preventDefault();
        });
    }

    /* ---------- Alert dismissal (Bootstrap markup without Bootstrap JS) ---------- */
    function bindAlerts() {
        Array.prototype.forEach.call(document.querySelectorAll('.alert'), function (alert) {
            if (alert._wsBound) return;
            alert._wsBound = true;
            var close = alert.querySelector('.btn-close[data-bs-dismiss="alert"]');
            if (close) {
                close.addEventListener('click', function () {
                    alert.style.transition = 'opacity .2s ease';
                    alert.style.opacity = '0';
                    setTimeout(function () {
                        if (alert.parentNode) alert.parentNode.removeChild(alert);
                    }, 200);
                });
            }
        });
    }
    bindAlerts();
})();
