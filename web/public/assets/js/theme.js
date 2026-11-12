/* ============================================================
 * theme.js — modes Jour / Nuit / Système (sans dépendance)
 * Chargé dans <head> pour éviter tout « flash » au chargement.
 * Les boutons .theme-switch [data-mode] sont reliés automatiquement.
 * ============================================================ */
(function () {
    'use strict';
    var KEY = 'theme-mode';
    var mq = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

    function stored() { try { return localStorage.getItem(KEY) || 'system'; } catch (e) { return 'system'; } }
    function resolve(mode) { return mode === 'system' ? (mq && mq.matches ? 'dark' : 'light') : mode; }

    function apply(mode) {
        var theme = resolve(mode);
        var root = document.documentElement;
        root.setAttribute('data-bs-theme', theme);
        root.setAttribute('data-theme-mode', mode);
        root.style.colorScheme = theme;
        document.querySelectorAll('.theme-switch [data-mode]').forEach(function (b) {
            var on = b.getAttribute('data-mode') === mode;
            b.classList.toggle('active', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        try { window.dispatchEvent(new CustomEvent('themechange', { detail: { mode: mode, theme: theme } })); } catch (e) {}
    }

    function set(mode) {
        try { localStorage.setItem(KEY, mode); } catch (e) {}
        apply(mode);
    }

    apply(stored());                                   // avant le premier affichage
    if (mq) {
        var onSys = function () { if (stored() === 'system') apply('system'); };
        if (mq.addEventListener) mq.addEventListener('change', onSys); else if (mq.addListener) mq.addListener(onSys);
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.theme-switch [data-mode]').forEach(function (b) {
            b.addEventListener('click', function () { set(b.getAttribute('data-mode')); });
        });
        apply(stored());
    });
    window.Theme = { set: set, get: stored, resolved: function () { return resolve(stored()); } };
})();
