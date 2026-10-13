(() => {
  // Иконки Lucide (тонкая линия под корпоративный стиль)
  if (window.lucide) lucide.createIcons({ attrs: { 'stroke-width': 1.75 } });

  // Закреплённая шапка: тень после начала прокрутки
  const header = document.getElementById('site-header');
  const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  // Мобильное меню
  const toggle = document.getElementById('menu-toggle');
  const menu = document.getElementById('mobile-menu');
  const setMenu = (open) => {
    menu.classList.toggle('hidden', !open);
    toggle.setAttribute('aria-expanded', String(open));
    toggle.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
    toggle.querySelector('.icon-open').classList.toggle('hidden', open);
    toggle.querySelector('.icon-close').classList.toggle('hidden', !open);
    document.body.classList.toggle('overflow-hidden', open);
  };
  toggle.addEventListener('click', () => setMenu(toggle.getAttribute('aria-expanded') !== 'true'));
  menu.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => setMenu(false)));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setMenu(false); });
  window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => { if (e.matches) setMenu(false); });

  // Подсветка активного пункта меню: последний раздел, верх которого выше середины экрана
  const navLinks = [...document.querySelectorAll('[data-nav]')];
  const navSections = navLinks
    .map((l) => document.getElementById(l.dataset.nav))
    .filter(Boolean)
    .sort((a, b) => a.offsetTop - b.offsetTop);
  let spyTick = false;
  const updateNav = () => {
    spyTick = false;
    const line = window.scrollY + window.innerHeight * 0.45;
    const current = navSections.filter((s) => s.offsetTop <= line).pop() || navSections[0];
    navLinks.forEach((l) => l.classList.toggle('is-active', l.dataset.nav === current.id));
  };
  updateNav();
  window.addEventListener('scroll', () => { if (!spyTick) { spyTick = true; requestAnimationFrame(updateNav); } }, { passive: true });

  // Hero-слайдер (по образцу expertecogroup): фото и тексты меняются вместе.
  // Автосмена каждые 5 с; вкладки, стрелки клавиатуры и свайп — вручную. Пауза: наведение на
  // вкладки, фокус с клавиатуры внутри hero, фоновая вкладка. При prefers-reduced-motion не листает.
  const hero = document.querySelector('[data-hero]');
  const tabs = hero ? [...hero.querySelectorAll('.hero-tab')] : [];
  const panes = hero ? [...hero.querySelectorAll('.hero-pane')] : [];
  const slides = hero ? [...hero.querySelectorAll('.hero-slide')] : [];
  const bars = hero ? [...hero.querySelectorAll('.hero-status__bar')] : [];
  if (panes.length > 1) {
    const DURATION = 5000;
    const total = panes.length;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    hero.style.setProperty('--slide-dur', DURATION + 'ms');
    let current = 0;
    let timer = null;
    let startedAt = 0;
    let remaining = DURATION;
    let hovered = false;
    let focused = false;

    const isBlocked = () => reduceMotion.matches || document.hidden || hovered || focused;
    const schedule = (ms) => {
      clearTimeout(timer);
      timer = null;
      if (isBlocked()) return;
      startedAt = performance.now();
      remaining = ms;
      timer = setTimeout(() => go(current + 1, 1), ms);
    };
    const mark = (list, i) => list.forEach((el, k) => el.classList.toggle('is-active', k === i));

    const go = (n, dir) => {
      n = (n + total) % total;
      if (n === current) return;
      const from = panes[current];
      const to = panes[n];
      // входящий слайд ставим за краем без анимации, затем отпускаем в нулевую позицию
      to.style.transition = 'none';
      to.style.transform = `translateX(${dir * 100}%)`;
      to.style.opacity = '0';
      void to.offsetWidth;
      to.style.transition = '';
      to.style.transform = 'translateX(0)';
      to.style.opacity = '1';
      from.style.transform = `translateX(${-dir * 100}%)`;
      from.style.opacity = '0';

      current = n;
      mark(panes, n);
      mark(slides, n);
      mark(tabs, n);
      bars.forEach((b, k) => { b.classList.toggle('is-done', k < n); b.classList.remove('is-active'); });
      if (bars[n]) { void bars[n].offsetWidth; bars[n].classList.add('is-active'); }
      tabs.forEach((t, k) => t.setAttribute('aria-selected', String(k === n)));
      panes.forEach((p, k) => { p.setAttribute('aria-hidden', String(k !== n)); p.inert = k !== n; });
      remaining = DURATION; // новый слайд — отсчёт с начала
      schedule(DURATION);
    };
    panes.forEach((p, k) => { p.inert = k !== current; });

    const pause = () => {
      if (timer) remaining = Math.max(0, remaining - (performance.now() - startedAt));
      clearTimeout(timer);
      timer = null;
      hero.classList.add('is-paused');
    };
    const resume = () => {
      if (isBlocked()) return;
      hero.classList.remove('is-paused');
      schedule(remaining || DURATION);
    };

    tabs.forEach((t, i) => t.addEventListener('click', () => go(i, i > current ? 1 : -1)));
    hero.querySelector('.hero-tabs').addEventListener('keydown', (e) => {
      if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
      e.preventDefault();
      const dir = e.key === 'ArrowRight' ? 1 : -1;
      go(current + dir, dir);
      tabs[current].focus();
    });

    // Пауза при наведении мыши на вкладки (касание на телефоне не останавливает)
    const tabsBox = hero.querySelector('.hero-tabs');
    tabsBox.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') { hovered = true; pause(); } });
    tabsBox.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') { hovered = false; resume(); } });
    // Фокус с клавиатуры внутри hero — пауза, чтобы слайд не уехал из-под фокуса (клик мышью не считается)
    hero.addEventListener('focusin', (e) => { if (e.target.matches(':focus-visible')) { focused = true; pause(); } });
    hero.addEventListener('focusout', (e) => { if (!hero.contains(e.relatedTarget)) { focused = false; resume(); } });
    document.addEventListener('visibilitychange', () => (document.hidden ? pause() : resume()));

    // Свайп пальцем/стилусом: мышью листают вкладками, протяжка мышью — это выделение текста
    let sx = 0;
    let sy = 0;
    let tracking = false;
    hero.addEventListener('pointerdown', (e) => {
      if (e.pointerType === 'mouse') return;
      sx = e.clientX; sy = e.clientY; tracking = true;
    });
    hero.addEventListener('pointerup', (e) => {
      if (!tracking) return;
      tracking = false;
      const dx = e.clientX - sx;
      const dy = e.clientY - sy;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.2) go(current + (dx < 0 ? 1 : -1), dx < 0 ? 1 : -1);
    });
    hero.addEventListener('pointercancel', () => { tracking = false; });

    schedule(DURATION);
  }

  // Кнопка «Получить консультацию»: на мобильном раскрывает форму, на десктопе — фокус на первое поле
  const ctaOpen = document.getElementById('cta-open');
  const ctaForm = document.getElementById('cta-form');
  if (ctaOpen && ctaForm) {
    ctaOpen.addEventListener('click', (e) => {
      e.preventDefault();
      if (!window.matchMedia('(min-width: 1024px)').matches) {
        const open = ctaForm.classList.toggle('hidden') === false;
        ctaOpen.setAttribute('aria-expanded', String(open));
        if (!open) return;
      }
      ctaForm.querySelector('input').focus({ preventScroll: false });
    });
  }

  // Маска телефона Узбекистана: +998 (90) 123-45-67 — для всех полей type="tel"
  const PHONE_CODE = '+998';
  // Местная часть номера (9 цифр) без кода страны
  const phoneDigits = (value) => {
    let d = value.replace(/\D/g, '');
    const hasCode = value.trim().startsWith('+') || d.length > 9;
    if (hasCode && d.startsWith('998')) d = d.slice(3);
    else if (hasCode && '998'.startsWith(d)) d = ''; // стёрли часть кода — считаем поле пустым
    return d.slice(0, 9);
  };
  const formatPhone = (d) => {
    if (!d) return PHONE_CODE + ' ';
    let out = PHONE_CODE + ' (' + d.slice(0, 2);
    if (d.length >= 2) out += ')';
    if (d.length > 2) out += ' ' + d.slice(2, 5);
    if (d.length > 5) out += '-' + d.slice(5, 7);
    if (d.length > 7) out += '-' + d.slice(7, 9);
    return out;
  };
  const isPhoneComplete = (value) => phoneDigits(value).length === 9;

  document.querySelectorAll('input[type="tel"]').forEach((input) => {
    const caretToEnd = () => { const n = input.value.length; input.setSelectionRange(n, n); };
    input.addEventListener('focus', () => {
      if (!input.value) { input.value = formatPhone(''); input.dataset.digits = ''; }
      requestAnimationFrame(caretToEnd);
    });
    input.addEventListener('input', (e) => {
      let d = phoneDigits(input.value);
      // Стёрли скобку/дефис — цифр столько же, поэтому убираем последнюю цифру
      if (e.inputType && e.inputType.startsWith('delete') && d === (input.dataset.digits || '')) d = d.slice(0, -1);
      input.dataset.digits = d;
      input.value = formatPhone(d);
      caretToEnd();
    });
    input.addEventListener('blur', () => {
      if (!phoneDigits(input.value)) { input.value = ''; input.dataset.digits = ''; }
    });
  });

  // Формы: проверка обязательных полей.
  // Макет: отправка не подключена — на WordPress подключить обработчик (CF7 / admin-ajax).
  document.querySelectorAll('.js-form').forEach((form) => {
    const status = form.querySelector('.form-status');
    const isValid = (f) => {
      if (f.type === 'checkbox') return f.checked;
      if (f.type === 'tel') return isPhoneComplete(f.value);
      return f.value.trim() !== '';
    };

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      let firstInvalid = null;
      form.querySelectorAll('[required]').forEach((f) => {
        const ok = isValid(f);
        f.classList.toggle('is-invalid', !ok);
        f.setAttribute('aria-invalid', String(!ok));
        if (!ok && !firstInvalid) firstInvalid = f;
      });

      status.classList.remove('hidden', 'text-[#C2410C]', 'text-[#15803D]');
      if (firstInvalid) {
        status.textContent = 'Заполните обязательные поля и подтвердите согласие на обработку данных.';
        status.classList.add('text-[#C2410C]');
        firstInvalid.focus();
        return;
      }
      status.textContent = 'Демо-режим макета: отправка формы будет подключена на сайте.';
      status.classList.add('text-[#15803D]');
      form.reset();
    });

    form.addEventListener('input', (e) => {
      if (e.target.classList.contains('is-invalid') && isValid(e.target)) {
        e.target.classList.remove('is-invalid');
        e.target.removeAttribute('aria-invalid');
      }
    });
  });

  // Попап «Оставить заявку» (нативный <dialog>). Без JS кнопки ведут к форме в блоке #cta.
  const modal = document.getElementById('lead-modal');
  if (modal && typeof modal.showModal === 'function') {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const modalForm = modal.querySelector('.js-modal-form');
    const views = {
      form: modal.querySelector('[data-modal-view="form"]'),
      success: modal.querySelector('[data-modal-view="success"]'),
    };
    const submitBtn = modalForm.querySelector('[type="submit"]');
    let closeTimer = null;

    const showView = (name) => {
      views.form.classList.toggle('hidden', name !== 'form');
      views.success.classList.toggle('hidden', name !== 'success');
    };

    const openModal = () => {
      clearTimeout(closeTimer);
      showView('form');
      if (!modal.open) modal.showModal();
      document.documentElement.classList.add('overflow-hidden');
      void modal.offsetWidth; // фиксируем стартовое состояние, чтобы сработал transition
      modal.classList.add('is-visible');
      modalForm.querySelector('input').focus();
    };

    const finishClose = () => {
      if (modal.open) modal.close();
    };

    const closeModal = () => {
      modal.classList.remove('is-visible');
      document.documentElement.classList.remove('overflow-hidden');
      closeTimer = setTimeout(finishClose, reduceMotion.matches ? 0 : 260);
    };

    document.querySelectorAll('[data-modal-open]').forEach((btn) => {
      btn.addEventListener('click', (e) => { e.preventDefault(); openModal(); });
    });
    modal.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', closeModal));
    // Клик по затемнению (вне панели) закрывает попап
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    // Esc: перехватываем, чтобы закрыть с анимацией
    modal.addEventListener('cancel', (e) => { e.preventDefault(); closeModal(); });
    modal.addEventListener('close', () => {
      document.documentElement.classList.remove('overflow-hidden');
      modal.classList.remove('is-visible');
    });

    const rules = {
      name: (f) => f.value.trim() !== '',
      phone: (f) => isPhoneComplete(f.value),
      consent: (f) => f.checked,
    };
    const setFieldState = (f, ok) => {
      f.classList.toggle('is-invalid', !ok);
      if (ok) f.removeAttribute('aria-invalid'); else f.setAttribute('aria-invalid', 'true');
      const err = document.getElementById(f.getAttribute('aria-describedby'));
      if (err) err.classList.toggle('hidden', ok);
    };

    modalForm.addEventListener('submit', (e) => {
      e.preventDefault();
      let firstInvalid = null;
      Object.entries(rules).forEach(([name, check]) => {
        const f = modalForm.elements[name];
        const ok = check(f);
        setFieldState(f, ok);
        if (!ok && !firstInvalid) firstInvalid = f;
      });
      if (firstInvalid) { firstInvalid.focus(); return; }

      // Макет: имитация отправки. На WordPress заменить на реальный запрос (CF7 / admin-ajax).
      submitBtn.disabled = true;
      submitBtn.textContent = 'Отправляем…';
      setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Отправить заявку';
        modalForm.reset();
        showView('success');
        views.success.querySelector('[data-modal-close]').focus();
      }, 700);
    });

    modalForm.addEventListener('input', (e) => {
      const f = e.target;
      if (f.classList.contains('is-invalid') && rules[f.name] && rules[f.name](f)) setFieldState(f, true);
    });
  }

  // Анимации появления (GSAP). Без JS/GSAP контент виден как есть.
  if (!window.gsap || !window.ScrollTrigger) return;
  gsap.registerPlugin(ScrollTrigger);
  gsap.matchMedia().add('(prefers-reduced-motion: no-preference)', () => {
    gsap.from('.hero-anim', { y: 24, opacity: 0, duration: 0.8, ease: 'power3.out', stagger: 0.08 });
    gsap.from('.hero-slides', { opacity: 0, duration: 1.2, ease: 'power2.out' });

    gsap.set('[data-reveal]', { y: 28, opacity: 0 });
    ScrollTrigger.batch('[data-reveal]', {
      start: 'top 90%',
      once: true,
      onEnter: (els) => gsap.to(els, { y: 0, opacity: 1, duration: 0.7, ease: 'power3.out', stagger: 0.08, overwrite: true }),
    });
  });
})();
