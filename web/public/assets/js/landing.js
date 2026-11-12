/* Accueil : apparition au défilement, compteurs animés, carrousel. Sans dépendance. */
(function () {
    'use strict';
    document.documentElement.classList.add('js');
    document.addEventListener('DOMContentLoaded', function () {
        var els = [].slice.call(document.querySelectorAll('.lp-reveal'));
        var counters = [].slice.call(document.querySelectorAll('[data-count]'));

        function countUp(el) {
            if (el.dataset.done) return; el.dataset.done = '1';
            var to = parseInt(el.getAttribute('data-count'), 10) || 0, suffix = el.getAttribute('data-suffix') || '', t0 = null;
            function step(ts) {
                if (t0 === null) t0 = ts;
                var p = Math.min(1, (ts - t0) / 1400), v = Math.round(to * (1 - Math.pow(1 - p, 3)));
                el.textContent = v.toLocaleString(document.documentElement.lang || undefined) + suffix;
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }

        if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('in'); }); counters.forEach(countUp); }
        else {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (!en.isIntersecting) return;
                    en.target.classList.add('in');
                    if (en.target.hasAttribute('data-count')) countUp(en.target);
                    en.target.querySelectorAll && en.target.querySelectorAll('[data-count]').forEach(countUp);
                    io.unobserve(en.target);
                });
            }, { threshold: .15 });
            els.forEach(function (e) { io.observe(e); });
            counters.forEach(function (e) { io.observe(e); });
        }

        var track = document.getElementById('lpTrack');
        if (track) {
            var move = function (dir) { track.scrollBy({ left: dir * (track.firstElementChild ? track.firstElementChild.getBoundingClientRect().width + 22 : 300), behavior: 'smooth' }); };
            var prev = document.getElementById('lpPrev'), next = document.getElementById('lpNext');
            if (prev) prev.addEventListener('click', function () { move(-1); });
            if (next) next.addEventListener('click', function () { move(1); });
            var timer = setInterval(function () {
                if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 8) track.scrollTo({ left: 0, behavior: 'smooth' }); else move(1);
            }, 6000);
            ['pointerdown', 'mouseenter', 'touchstart'].forEach(function (ev) { track.addEventListener(ev, function () { clearInterval(timer); }, { passive: true }); });
        }
    });
})();
