/* Interaksi & animasi halaman publik. Tanpa library. Semua fitur opsional dan aman bila elemennya tidak ada. */
(function () {
  var doc = document;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hasIO = 'IntersectionObserver' in window;
  function each(list, fn) { Array.prototype.forEach.call(list, fn); }

  /* 1. Muncul saat di-scroll */
  var revealEls = doc.querySelectorAll('[data-reveal]');
  if (revealEls.length && hasIO && !reduce) {
    var rio = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        e.target.classList.remove('is-pending');
        e.target.classList.add('is-revealed');
        rio.unobserve(e.target);
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    each(revealEls, function (el) { el.classList.add('is-pending'); rio.observe(el); });
  }

  /* 2. Angka berhitung naik */
  var counters = doc.querySelectorAll('[data-count]');
  function countUp(el) {
    var target = parseInt(el.getAttribute('data-count'), 10) || 0;
    if (target <= 0) { el.textContent = '0'; return; }
    var dur = 1000, start = null;
    el.textContent = '0';
    function step(ts) {
      if (start === null) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      el.textContent = String(Math.round(target * (1 - Math.pow(1 - p, 3))));
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  if (counters.length && hasIO && !reduce) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        countUp(e.target);
        cio.unobserve(e.target);
      });
    }, { threshold: 0.6 });
    each(counters, function (el) { cio.observe(el); });
  }

  /* 3. Kartu miring mengikuti kursor (hanya perangkat dengan mouse) */
  if (!reduce && window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    each(doc.querySelectorAll('[data-tilt]'), function (card) {
      var raf = null;
      card.addEventListener('mousemove', function (e) {
        var r = card.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5;
        var y = (e.clientY - r.top) / r.height - 0.5;
        if (raf) cancelAnimationFrame(raf);
        raf = requestAnimationFrame(function () {
          card.style.transform = 'perspective(900px) rotateX(' + (-y * 6).toFixed(2) + 'deg) rotateY(' + (x * 8).toFixed(2) + 'deg) translateY(-4px)';
        });
      });
      card.addEventListener('mouseleave', function () {
        if (raf) cancelAnimationFrame(raf);
        card.style.transform = '';
      });
    });
  }

  /* 4. Progress scroll + tombol ke atas */
  var bar = doc.getElementById('scroll-progress');
  var topBtn = doc.getElementById('to-top');
  function onScroll() {
    var h = doc.documentElement;
    var max = h.scrollHeight - h.clientHeight;
    if (bar) bar.style.transform = 'scaleX(' + (max > 0 ? Math.min(h.scrollTop / max, 1) : 0) + ')';
    if (topBtn) topBtn.classList.toggle('is-on', h.scrollTop > 600);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
  if (topBtn) topBtn.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
  });

  /* 5. Teks mengetik bergantian */
  each(doc.querySelectorAll('[data-typewriter]'), function (el) {
    var words;
    try { words = JSON.parse(el.getAttribute('data-typewriter')); } catch (e) { return; }
    if (!words || !words.length || reduce) return;
    var w = 0, i = 0, deleting = false;
    el.textContent = '';
    (function tick() {
      var word = String(words[w]);
      if (!deleting) {
        i++;
        el.textContent = word.slice(0, i);
        if (i >= word.length) { deleting = true; setTimeout(tick, 1500); return; }
        setTimeout(tick, 70);
        return;
      }
      i--;
      el.textContent = word.slice(0, i);
      if (i <= 0) { deleting = false; w = (w + 1) % words.length; setTimeout(tick, 350); return; }
      setTimeout(tick, 35);
    })();
  });

  /* 6. Navigasi section: scroll halus + sorot section aktif */
  var links = doc.querySelectorAll('[data-spy]');
  if (links.length) {
    var map = {};
    each(links, function (a) {
      var id = (a.getAttribute('href') || '').replace('#', '');
      map[id] = a;
      a.addEventListener('click', function (e) {
        var t = doc.getElementById(id);
        if (!t) return;
        e.preventDefault();
        t.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
      });
    });
    if (hasIO) {
      var sio = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (!e.isIntersecting || !map[e.target.id]) return;
          each(links, function (a) { a.classList.remove('is-active'); });
          map[e.target.id].classList.add('is-active');
        });
      }, { rootMargin: '-40% 0px -55% 0px' });
      Object.keys(map).forEach(function (id) { var s = doc.getElementById(id); if (s) sio.observe(s); });
    }
  }

  /* 7. Filter proyek (tab jenis + klik keahlian) */
  var grid = doc.getElementById('pf-grid');
  if (grid) {
    var items = grid.querySelectorAll('.pf-item');
    var tabs = doc.querySelectorAll('[data-filter-kind]');
    var chips = doc.querySelectorAll('[data-skill]');
    var status = doc.getElementById('pf-status');
    var statusText = doc.getElementById('pf-status-text');
    var empty = doc.getElementById('pf-empty');
    var kind = 'all', tech = '';

    function apply() {
      var shown = 0;
      each(items, function (it) {
        var techs = (it.getAttribute('data-tech') || '').split('|');
        var okKind = kind === 'all' || it.getAttribute('data-kind') === kind;
        var okTech = !tech || techs.indexOf(tech) !== -1;
        var show = okKind && okTech;
        var wasHidden = it.classList.contains('is-hidden');
        it.classList.toggle('is-hidden', !show);
        if (show) {
          shown++;
          if (wasHidden && it.classList.contains('is-revealed') && !reduce) {
            it.classList.remove('pf-pop');
            void it.offsetWidth;
            it.classList.add('pf-pop');
          }
        }
      });
      each(tabs, function (b) { b.classList.toggle('is-active', b.getAttribute('data-filter-kind') === kind); });
      each(chips, function (c) { c.classList.toggle('is-active', !!tech && c.getAttribute('data-skill') === tech); });
      if (status) {
        status.hidden = !tech;
        if (tech && statusText) {
          var active = doc.querySelector('[data-skill="' + tech.replace(/"/g, '') + '"]');
          statusText.textContent = 'Menampilkan proyek dengan: ' + (active ? active.textContent.trim() : tech);
        }
      }
      if (empty) empty.hidden = shown !== 0 || items.length === 0;
    }

    each(tabs, function (b) {
      b.addEventListener('click', function () { kind = b.getAttribute('data-filter-kind'); apply(); });
    });
    each(chips, function (c) {
      c.addEventListener('click', function () {
        var v = c.getAttribute('data-skill');
        tech = tech === v ? '' : v;
        apply();
        if (tech) {
          var sec = doc.getElementById('proyek');
          if (sec) sec.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        }
      });
    });
    each(doc.querySelectorAll('[data-filter-reset]'), function (b) {
      b.addEventListener('click', function () { tech = ''; kind = 'all'; apply(); });
    });
    apply();
  }
})();
