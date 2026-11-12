/* ============================================================
 * local-ui.js — remplace Chart.js et FullCalendar SANS internet.
 *  - Chart  : line, bar (groupé ou empilé), doughnut  (API compatible Chart.js)
 *  - FullCalendar.Calendar : grille mensuelle simple avec événements
 * ============================================================ */
(function () {
    'use strict';

    function css(name, fallback) {
        var v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        return v || fallback;
    }
    function niceMax(v) {
        if (v <= 0) return 1;
        var p = Math.pow(10, Math.floor(Math.log10(v))), n = v / p;
        return (n <= 1 ? 1 : n <= 2 ? 2 : n <= 5 ? 5 : 10) * p;
    }
    function fmt(n) {
        return Math.abs(n) >= 1000 ? (Math.round(n / 100) / 10) + 'k' : String(Math.round(n * 100) / 100);
    }

    /* ---------------------------------------------------------- Chart */
    /* Types : line (courbe lissée), bar (histogramme groupé), doughnut, gauge.
     * Animations d'entrée, survol, clic sur la légende pour masquer une série. */
    var instances = [];
    function ease(t) { return 1 - Math.pow(1 - t, 3); }
    function palette() {
        return [css('--ui-primary', '#0fa968'), css('--ui-accent', '#f7c32e'), css('--ui-info', '#14b8c4'), css('--ui-warning', '#ff9f43'), css('--ui-danger', '#ff5b5c')];
    }

    function Chart(target, config) {
        var canvas = target && target.canvas ? target.canvas : target;
        this.canvas = canvas; this.config = config; this.tip = null; this.hits = []; this.hidden = {}; this.legendHits = []; this.hoverKey = null; this.p = 0;
        var self = this;
        this._resize = function () { self.render(1); };
        window.addEventListener('resize', this._resize);
        canvas.addEventListener('mousemove', function (e) { self.hover(e); });
        canvas.addEventListener('mouseleave', function () { self.hoverKey = null; self.hideTip(); self.render(1); });
        canvas.addEventListener('click', function (e) { self.click(e); });
        instances.push(this);
        this.animate();
    }

    Chart.prototype.animate = function () {
        var self = this, start = null, dur = 900;
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return this.render(1); }
        function step(ts) {
            if (start === null) start = ts;
            var t = Math.min(1, (ts - start) / dur);
            self.render(ease(t));
            if (t < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    };

    Chart.prototype.render = function (p) {
        this.p = p;
        var c = this.canvas, cfg = this.config, type = cfg.type || 'line';
        var box = c.parentNode.getBoundingClientRect();
        var W = Math.max(200, Math.floor(box.width) || c.clientWidth), H = parseInt(c.getAttribute('height'), 10) || 260;
        var dpr = window.devicePixelRatio || 1;
        c.width = W * dpr; c.height = H * dpr; c.style.width = W + 'px'; c.style.height = H + 'px';
        var g = c.getContext('2d'); g.setTransform(dpr, 0, 0, dpr, 0, 0); g.clearRect(0, 0, W, H);
        this.W = W; this.H = H; this.hits = []; this.legendHits = [];
        this.text = css('--ui-muted', '#6f8179'); this.grid = css('--ui-line', '#e3ebe7'); this.ink = css('--ui-text', '#1d2b26'); this.card = css('--ui-card', '#fff');
        g.font = '12px system-ui, -apple-system, "Segoe UI", Roboto, sans-serif';
        if (type === 'doughnut' || type === 'pie') return this.donut(g);
        if (type === 'gauge') return this.gauge(g);
        this.cartesian(g, type);
    };

    Chart.prototype.color = function (s, k) {
        var col = s.backgroundColor || s.borderColor || palette()[k % 5];
        return Array.isArray(col) ? col[0] : col;
    };

    Chart.prototype.cartesian = function (g, type) {
        var cfg = this.config, W = this.W, H = this.H, data = cfg.data || {}, labels = data.labels || [], sets = data.datasets || [], self = this;
        var visible = sets.map(function (s, i) { return !self.hidden[i]; });
        var n = labels.length || (sets[0] ? sets[0].data.length : 0), max = 0, i, j;
        for (j = 0; j < sets.length; j++) if (visible[j]) for (i = 0; i < n; i++) max = Math.max(max, +sets[j].data[i] || 0);
        max = niceMax(max);
        var hasLegend = sets.length > 1 || (sets[0] && sets[0].label);
        var pl = 40, pr = 12, pt = 16, pb = hasLegend ? 52 : 28, pw = W - pl - pr, ph = H - pt - pb, step = pw / Math.max(n, 1);
        g.lineWidth = 1; g.textBaseline = 'middle'; g.textAlign = 'right';
        for (i = 0; i <= 4; i++) {
            var y = pt + ph - ph * i / 4;
            g.strokeStyle = this.grid; g.setLineDash([3, 5]); g.beginPath(); g.moveTo(pl, y); g.lineTo(W - pr, y); g.stroke(); g.setLineDash([]);
            g.fillStyle = this.text; g.fillText(fmt(max * i / 4), pl - 8, y);
        }
        g.textAlign = 'center'; g.textBaseline = 'top';
        var every = Math.ceil(n / Math.max(1, Math.floor(pw / 46)));
        for (i = 0; i < n; i++) if (i % every === 0) { g.fillStyle = this.hoverKey && this.hoverKey.i === i ? this.ink : this.text; g.fillText(String(labels[i]), pl + step * i + step / 2, pt + ph + 8); }

        var p = this.p, active = this.hoverKey;
        if (type === 'bar') {
            var vis = visible.filter(Boolean).length || 1, gap = Math.max(2, step * 0.06), bw = Math.min(30, (step * 0.7 - gap * (vis - 1)) / vis), gw = bw * vis + gap * (vis - 1), idx;
            for (i = 0; i < n; i++) {
                idx = 0;
                for (j = 0; j < sets.length; j++) {
                    if (!visible[j]) continue;
                    var v = +sets[j].data[i] || 0, h = ph * v / max * p, x = pl + step * i + (step - gw) / 2 + idx * (bw + gap), yy = pt + ph - h;
                    var on = active && active.i === i && active.j === j;
                    g.globalAlpha = active && !on ? .55 : 1; g.fillStyle = this.color(sets[j], j);
                    var r = Math.min(7, bw / 2, h);
                    g.beginPath(); g.moveTo(x, yy + h); g.lineTo(x, yy + r); g.quadraticCurveTo(x, yy, x + r, yy); g.lineTo(x + bw - r, yy); g.quadraticCurveTo(x + bw, yy, x + bw, yy + r); g.lineTo(x + bw, yy + h); g.closePath(); g.fill();
                    g.globalAlpha = 1;
                    this.hits.push({ i: i, j: j, x: x - 2, y: pt, w: bw + 4, h: ph, text: (sets[j].label ? sets[j].label + ' · ' : '') + labels[i] + ' : ' + fmt(v), tx: x + bw / 2, ty: yy });
                    idx++;
                }
            }
        } else {
            for (j = 0; j < sets.length; j++) {
                if (!visible[j]) continue;
                var col = this.color(sets[j], j), pts = [];
                for (i = 0; i < n; i++) pts.push([pl + step * i + step / 2, pt + ph - ph * (+sets[j].data[i] || 0) / max * p]);
                if (!pts.length) continue;
                var path = function (move) {
                    if (move) g.moveTo(pts[0][0], pts[0][1]);
                    for (var k = 1; k < pts.length; k++) {
                        var a = pts[k - 1], b = pts[k], mx = (a[0] + b[0]) / 2;
                        g.bezierCurveTo(mx, a[1], mx, b[1], b[0], b[1]);
                    }
                };
                if (sets[j].fill !== false) {
                    var grad = g.createLinearGradient(0, pt, 0, pt + ph); grad.addColorStop(0, col + '55'); grad.addColorStop(1, col + '00');
                    g.beginPath(); g.moveTo(pts[0][0], pt + ph); g.lineTo(pts[0][0], pts[0][1]); path(false); g.lineTo(pts[pts.length - 1][0], pt + ph); g.closePath(); g.fillStyle = grad; g.fill();
                }
                g.beginPath(); path(true); g.strokeStyle = col; g.lineWidth = 3; g.lineJoin = 'round'; g.lineCap = 'round'; g.stroke();
                for (i = 0; i < pts.length; i++) {
                    var on2 = active && active.i === i;
                    g.beginPath(); g.arc(pts[i][0], pts[i][1], on2 ? 6 : 3.5, 0, 6.3); g.fillStyle = this.card; g.fill(); g.lineWidth = 2.5; g.strokeStyle = col; g.stroke();
                    this.hits.push({ i: i, j: j, x: pts[i][0] - step / 2, y: pt, w: step, h: ph, text: (sets[j].label ? sets[j].label + ' · ' : '') + labels[i] + ' : ' + fmt(+sets[j].data[i] || 0), tx: pts[i][0], ty: pts[i][1] });
                }
            }
        }
        if (hasLegend) this.legend(g, H - 14, sets);
    };

    Chart.prototype.legend = function (g, y, sets) {
        var x = 40; g.textAlign = 'left'; g.textBaseline = 'middle';
        for (var k = 0; k < sets.length; k++) {
            if (!sets[k].label) continue;
            var off = !!this.hidden[k], w = g.measureText(sets[k].label).width + 30;
            g.globalAlpha = off ? .35 : 1; g.fillStyle = this.color(sets[k], k); g.beginPath(); g.arc(x + 6, y, 6, 0, 6.3); g.fill();
            g.fillStyle = this.text; g.fillText(sets[k].label, x + 18, y); g.globalAlpha = 1;
            this.legendHits.push({ k: k, x: x, y: y - 12, w: w, h: 24 }); x += w + 12;
        }
    };

    Chart.prototype.donut = function (g) {
        var sets = (this.config.data || {}).datasets || [], d = (sets[0] && sets[0].data) || [], labels = (this.config.data || {}).labels || [];
        var total = d.reduce(function (a, b) { return a + (+b || 0); }, 0), W = this.W, H = this.H;
        var cx = W / 2, cy = H / 2, R = Math.min(W, H) / 2 - 8, a0 = -Math.PI / 2, cols = (sets[0] && sets[0].backgroundColor) || palette(), sweep = Math.PI * 2 * this.p;
        for (var i = 0; i < d.length; i++) {
            var frac = total ? (+d[i] / total) : 0, a1 = a0 + frac * sweep, on = this.hoverKey && this.hoverKey.i === i, rr = on ? R + 5 : R;
            g.beginPath(); g.arc(cx, cy, rr, a0, a1); g.arc(cx, cy, rr * 0.64, a1, a0, true); g.closePath();
            g.fillStyle = Array.isArray(cols) ? cols[i % cols.length] : cols; g.fill();
            var mid = a0 + frac * Math.PI;
            this.hits.push({ i: i, arc: true, cx: cx, cy: cy, r0: R * .64, r1: R + 6, a0: a0 + Math.PI / 2, a1: a1 + Math.PI / 2, text: (labels[i] ? labels[i] + ' : ' : '') + d[i] });
            a0 = a1;
        }
        g.fillStyle = this.ink; g.textAlign = 'center'; g.textBaseline = 'middle'; g.font = 'bold 22px system-ui, sans-serif';
        g.fillText(String(Math.round(total * this.p)), cx, cy);
    };

    Chart.prototype.gauge = function (g) {
        var sets = (this.config.data || {}).datasets || [], val = +((sets[0] && sets[0].data && sets[0].data[0]) || 0), W = this.W, H = this.H;
        var cx = W / 2, cy = H / 2 + 4, R = Math.min(W, H) / 2 - 12, col = this.color(sets[0] || {}, 0);
        g.lineWidth = 16; g.lineCap = 'round';
        g.strokeStyle = this.grid; g.beginPath(); g.arc(cx, cy, R, Math.PI * .75, Math.PI * 2.25); g.stroke();
        g.strokeStyle = col; g.beginPath(); g.arc(cx, cy, R, Math.PI * .75, Math.PI * (.75 + 1.5 * Math.min(100, val) / 100 * this.p)); g.stroke();
        g.fillStyle = this.ink; g.textAlign = 'center'; g.textBaseline = 'middle'; g.font = 'bold 28px system-ui, sans-serif';
        g.fillText(Math.round(val * this.p) + '%', cx, cy - 4);
        g.font = '12px system-ui, sans-serif'; g.fillStyle = this.text; g.fillText(sets[0] && sets[0].label ? sets[0].label : '', cx, cy + 22);
    };

    Chart.prototype.pos = function (e) { var r = this.canvas.getBoundingClientRect(); return { x: e.clientX - r.left, y: e.clientY - r.top }; };
    Chart.prototype.hover = function (e) {
        var m = this.pos(e), hit = null, best = 1e9, k;
        for (k = 0; k < this.hits.length; k++) {
            var h = this.hits[k];
            if (h.arc) {
                var dx = m.x - h.cx, dy = m.y - h.cy, rr = Math.sqrt(dx * dx + dy * dy), ang = Math.atan2(dy, dx) + Math.PI / 2; if (ang < 0) ang += Math.PI * 2;
                if (rr >= h.r0 && rr <= h.r1 && ang >= h.a0 && ang <= h.a1) hit = h;
            } else if (m.x >= h.x && m.x <= h.x + h.w && m.y >= h.y && m.y <= h.y + h.h) {
                var d = Math.abs((h.ty || 0) - m.y); if (d < best) { best = d; hit = h; }
            }
        }
        var onLegend = this.legendHits.some(function (l) { return m.x >= l.x && m.x <= l.x + l.w && m.y >= l.y && m.y <= l.y + l.h; });
        this.canvas.style.cursor = onLegend ? 'pointer' : 'default';
        var key = hit ? { i: hit.i, j: hit.j } : null;
        if (!hit) { this.hideTip(); if (this.hoverKey) { this.hoverKey = null; this.render(1); } return; }
        if (!this.hoverKey || this.hoverKey.i !== key.i || this.hoverKey.j !== key.j) { this.hoverKey = key; this.render(1); }
        if (!this.tip) { this.tip = document.createElement('div'); this.tip.className = 'chart-tip'; document.body.appendChild(this.tip); }
        this.tip.textContent = hit.text; this.tip.style.display = 'block';
        this.tip.style.left = (e.pageX + 14) + 'px'; this.tip.style.top = (e.pageY - 34) + 'px';
    };
    Chart.prototype.click = function (e) {
        var m = this.pos(e);
        for (var k = 0; k < this.legendHits.length; k++) {
            var l = this.legendHits[k];
            if (m.x >= l.x && m.x <= l.x + l.w && m.y >= l.y && m.y <= l.y + l.h) { this.hidden[l.k] = !this.hidden[l.k]; this.render(1); return; }
        }
    };
    Chart.prototype.update = function (data) { if (data) this.config.data = data; this.animate(); };
    Chart.prototype.hideTip = function () { if (this.tip) this.tip.style.display = 'none'; };
    Chart.prototype.destroy = function () { window.removeEventListener('resize', this._resize); this.hideTip(); instances = instances.filter(function (x) { return x !== this; }, this); };
    window.addEventListener('themechange', function () { instances.forEach(function (c) { c.render(1); }); });
    window.Chart = Chart;

    /* ------------------------------------------------- FullCalendar */
    function Calendar(el, opts) { this.el = el; this.opts = opts || {}; var d = new Date(); this.y = d.getFullYear(); this.m = d.getMonth(); }
    Calendar.prototype.render = function () {
        var self = this, cal = window.CAL || {}, months = cal.months || ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        var days = cal.days_short || ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'], first = cal.first_day === undefined ? 1 : cal.first_day;
        var ev = {}; (this.opts.events || []).forEach(function (e) {
            var s = new Date(String(e.start).substr(0, 10) + 'T00:00:00'), en = e.end ? new Date(String(e.end).substr(0, 10) + 'T00:00:00') : s, k = 0;
            for (var d = new Date(s); d <= en && k < 62; d.setDate(d.getDate() + 1), k++) {
                var key = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate(); (ev[key] = ev[key] || []).push(e);
            }
        });
        var html = '<div class="mc-head"><button type="button" class="mc-btn" data-d="-1"><i class="fas fa-chevron-left"></i></button><strong>' +
            months[this.m] + ' ' + this.y + '</strong><button type="button" class="mc-btn" data-d="1"><i class="fas fa-chevron-right"></i></button></div><div class="mc-grid">';
        for (var i = 0; i < 7; i++) html += '<div class="mc-dow">' + days[(i + first) % 7] + '</div>';
        var start = new Date(this.y, this.m, 1), lead = (start.getDay() - first + 7) % 7, dim = new Date(this.y, this.m + 1, 0).getDate(), today = new Date();
        for (i = 0; i < lead; i++) html += '<div class="mc-cell mc-empty"></div>';
        for (var d = 1; d <= dim; d++) {
            var list = ev[this.y + '-' + (this.m + 1) + '-' + d] || [], isToday = today.getFullYear() === this.y && today.getMonth() === this.m && today.getDate() === d;
            html += '<div class="mc-cell' + (isToday ? ' mc-today' : '') + '"><span class="mc-num">' + d + '</span>';
            list.slice(0, 3).forEach(function (e) {
                var t = String(e.title).replace(/</g, '&lt;');
                html += '<span class="mc-ev" style="background:' + (e.color || e.backgroundColor || '#0fa968') + '" title="' + t + '">' + t + '</span>';
            });
            if (list.length > 3) html += '<span class="mc-more">+' + (list.length - 3) + '</span>';
            html += '</div>';
        }
        this.el.innerHTML = html + '</div>'; this.el.classList.add('mini-cal');
        this.el.querySelectorAll('.mc-btn').forEach(function (b) {
            b.addEventListener('click', function () { self.m += +b.getAttribute('data-d'); if (self.m < 0) { self.m = 11; self.y--; } if (self.m > 11) { self.m = 0; self.y++; } self.render(); });
        });
    };
    window.FullCalendar = { Calendar: Calendar };
})();
