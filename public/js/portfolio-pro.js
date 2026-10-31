/* Interaksi halaman portfolio agency (daftar + detail). Tanpa library; aman bila elemennya tidak ada. */
(function () {
  var doc = document;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function each(list, fn) { Array.prototype.forEach.call(list, fn); }
  function norm(s) { return String(s || '').toLowerCase().trim(); }

  /* 1. Daftar: cari + filter kategori + urutkan (instan, tanpa muat ulang) */
  var grid = doc.getElementById('pp-grid');
  if (grid) {
    var items = grid.querySelectorAll('.pp-item');
    var pills = doc.querySelectorAll('[data-pp-cat]');
    var search = doc.getElementById('pp-search');
    var sortSel = doc.getElementById('pp-sort');
    var countEl = doc.getElementById('pp-count');
    var empty = doc.getElementById('pp-empty');
    var params = new URLSearchParams(window.location.search);
    var cat = params.get('category') || grid.getAttribute('data-initial') || '';
    var q = params.get('q') || '';
    var sort = 'terbaru';
    if (search) search.value = q;

    function num(el, attr) { return parseInt(el.getAttribute(attr), 10) || 0; }
    function compare(a, b) {
      if (sort === 'tahun') { return (num(b, 'data-year') - num(a, 'data-year')) || (num(a, 'data-i') - num(b, 'data-i')); }
      if (sort === 'nama') { return String(a.getAttribute('data-name')).localeCompare(String(b.getAttribute('data-name')), 'id'); }
      return num(a, 'data-i') - num(b, 'data-i');
    }
    function syncUrl() {
      var p = new URLSearchParams();
      if (cat) p.set('category', cat);
      if (q) p.set('q', q);
      var s = p.toString();
      try { history.replaceState(null, '', window.location.pathname + (s ? '?' + s : '')); } catch (e) {}
    }
    function apply(animate) {
      var shown = [];
      each(items, function (it) {
        var okCat = !cat || norm(it.getAttribute('data-cat')) === norm(cat);
        var okQ = !q || norm(it.getAttribute('data-text')).indexOf(norm(q)) !== -1;
        var show = okCat && okQ;
        it.classList.toggle('is-hidden', !show);
        if (show) {
          if (animate && !reduce) {
            it.classList.remove('pp-pop');
            void it.offsetWidth;
            it.style.setProperty('--pd', Math.min(shown.length, 12) * 45 + 'ms');
            it.classList.add('pp-pop');
          }
          shown.push(it);
        }
      });
      shown.sort(compare).forEach(function (it, i) { it.style.order = i; });
      each(pills, function (b) { b.classList.toggle('is-active', norm(b.getAttribute('data-pp-cat')) === norm(cat)); });
      if (countEl) countEl.textContent = shown.length + ' proyek' + ((cat || q) ? ' ditemukan' : '');
      if (empty) empty.hidden = shown.length !== 0;
      syncUrl();
    }

    each(pills, function (b) {
      b.addEventListener('click', function () { cat = b.getAttribute('data-pp-cat') || ''; apply(true); });
    });
    var timer = null;
    if (search) {
      search.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { q = search.value; apply(true); }, 120);
      });
      search.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { search.value = ''; q = ''; apply(true); search.blur(); }
      });
      doc.addEventListener('keydown', function (e) {
        var tag = (e.target && e.target.tagName) || '';
        if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(tag) && !e.ctrlKey && !e.metaKey) {
          e.preventDefault();
          search.focus();
        }
      });
    }
    if (sortSel) sortSel.addEventListener('change', function () { sort = sortSel.value; apply(true); });
    each(doc.querySelectorAll('[data-pp-reset]'), function (b) {
      b.addEventListener('click', function () {
        cat = ''; q = ''; if (search) search.value = ''; apply(true);
      });
    });
    apply(false);
  }

  /* 2. Detail: gambar hero bergeser pelan saat scroll (parallax) */
  var heroImg = doc.querySelector('.pp-dhero-img');
  if (heroImg && !reduce) {
    var ticking = false;
    var update = function () {
      ticking = false;
      var y = window.pageYOffset || 0;
      if (y > 1000) return;
      heroImg.style.transform = 'translate3d(0,' + (y * 0.28).toFixed(1) + 'px,0)';
    };
    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }, { passive: true });
  }

  /* 3. Detail: lightbox galeri (panah, keyboard, geser di layar sentuh) */
  var shots = doc.querySelectorAll('[data-pp-full]');
  if (shots.length) {
    var lb = null, lbImg = null, lbCount = null, lbClose = null, lbPrev = null, lbNext = null;
    var idx = 0, lastFocus = null, startX = 0;

    var build = function () {
      lb = doc.createElement('div');
      lb.className = 'pp-lb';
      lb.setAttribute('role', 'dialog');
      lb.setAttribute('aria-modal', 'true');
      lb.setAttribute('aria-label', 'Galeri gambar');
      lb.innerHTML = '<img alt="" />' +
        '<button type="button" class="lb-x" aria-label="Tutup">&times;</button>' +
        '<button type="button" class="lb-prev" aria-label="Sebelumnya">&#8249;</button>' +
        '<button type="button" class="lb-next" aria-label="Berikutnya">&#8250;</button>' +
        '<span class="lb-count"></span>';
      doc.body.appendChild(lb);
      lbImg = lb.querySelector('img');
      lbCount = lb.querySelector('.lb-count');
      lbClose = lb.querySelector('.lb-x');
      lbPrev = lb.querySelector('.lb-prev');
      lbNext = lb.querySelector('.lb-next');
      if (shots.length < 2) { lbPrev.style.display = 'none'; lbNext.style.display = 'none'; }
      lbClose.addEventListener('click', close);
      lbPrev.addEventListener('click', function (e) { e.stopPropagation(); show(idx - 1); });
      lbNext.addEventListener('click', function (e) { e.stopPropagation(); show(idx + 1); });
      lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
      lb.addEventListener('touchstart', function (e) { startX = e.changedTouches[0].clientX; }, { passive: true });
      lb.addEventListener('touchend', function (e) {
        var dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 50) show(idx + (dx < 0 ? 1 : -1));
      }, { passive: true });
    };
    var show = function (i) {
      idx = (i + shots.length) % shots.length;
      lbImg.classList.remove('is-in');
      lbImg.onload = function () { lbImg.classList.add('is-in'); };
      lbImg.src = shots[idx].getAttribute('data-pp-full');
      lbImg.alt = shots[idx].getAttribute('data-alt') || '';
      if (lbImg.complete) lbImg.classList.add('is-in');
      lbCount.textContent = (idx + 1) + ' / ' + shots.length;
    };
    var open = function (i) {
      if (!lb) build();
      lastFocus = doc.activeElement;
      lb.classList.add('is-open');
      doc.body.style.overflow = 'hidden';
      show(i);
      lbClose.focus();
    };
    var close = function () {
      if (!lb) return;
      lb.classList.remove('is-open');
      doc.body.style.overflow = '';
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    };
    each(shots, function (el, i) { el.addEventListener('click', function () { open(i); }); });
    doc.addEventListener('keydown', function (e) {
      if (!lb || !lb.classList.contains('is-open')) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') show(idx - 1);
      else if (e.key === 'ArrowRight') show(idx + 1);
    });
  }

  /* 4. Detail: tombol bagikan / salin tautan */
  each(doc.querySelectorAll('[data-pp-share]'), function (btn) {
    var label = btn.querySelector('[data-label]') || btn;
    function flash(msg) {
      var old = label.textContent;
      label.textContent = msg;
      setTimeout(function () { label.textContent = old; }, 2000);
    }
    btn.addEventListener('click', function () {
      var url = window.location.href.split('#')[0];
      if (navigator.share && /Mobi|Android/i.test(navigator.userAgent)) {
        navigator.share({ title: doc.title, url: url }).catch(function () {});
        return;
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () { flash('Tautan disalin \u2713'); }, function () { flash('Salin dari kolom alamat'); });
      } else {
        flash('Salin dari kolom alamat');
      }
    });
  });
})();
