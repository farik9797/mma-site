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
  // Автосмена каждые 5 с; вручную — полосы-кнопки снизу, стрелки клавиатуры и свайп. Пауза: наведение
  // на полосы, фокус с клавиатуры внутри hero, фоновая вкладка. При prefers-reduced-motion не листает.
  const hero = document.querySelector('[data-hero]');
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
      bars.forEach((b, k) => { b.classList.toggle('is-done', k < n); b.classList.remove('is-active'); b.setAttribute('aria-current', String(k === n)); });
      if (bars[n]) { void bars[n].offsetWidth; bars[n].classList.add('is-active'); }
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

    const statusBox = hero.querySelector('.hero-status');
    bars.forEach((b, i) => b.addEventListener('click', () => go(i, i > current ? 1 : -1)));
    statusBox.addEventListener('keydown', (e) => {
      if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
      e.preventDefault();
      const dir = e.key === 'ArrowRight' ? 1 : -1;
      go(current + dir, dir);
      bars[current].focus();
    });

    // Пауза при наведении мыши на полосы-кнопки (касание на телефоне не останавливает)
    statusBox.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse') { hovered = true; pause(); } });
    statusBox.addEventListener('pointerleave', (e) => { if (e.pointerType === 'mouse') { hovered = false; resume(); } });
    // Фокус с клавиатуры внутри hero — пауза, чтобы слайд не уехал из-под фокуса (клик мышью не считается)
    hero.addEventListener('focusin', (e) => { if (e.target.matches(':focus-visible')) { focused = true; pause(); } });
    hero.addEventListener('focusout', (e) => { if (!hero.contains(e.relatedTarget)) { focused = false; resume(); } });
    document.addEventListener('visibilitychange', () => (document.hidden ? pause() : resume()));

    // Свайп пальцем/стилусом: мышью листают полосами, протяжка мышью — это выделение текста
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

  // Кнопка «Наверх»: показываем, когда hero ушёл из вида; прокрутка без «#top» в адресе.
  // Кнопки быстрой связи в этот момент поднимаются над ней.
  const toTop = document.getElementById('to-top');
  const topSection = document.getElementById('top');
  const fabStack = document.getElementById('fab-stack');
  if (toTop && topSection) {
    new IntersectionObserver(([entry]) => {
      toTop.classList.toggle('is-visible', !entry.isIntersecting);
      if (fabStack) fabStack.classList.toggle('is-raised', !entry.isIntersecting);
    }).observe(topSection);
    toTop.addEventListener('click', (e) => {
      e.preventDefault();
      const smooth = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      window.scrollTo({ top: 0, behavior: smooth ? 'smooth' : 'auto' });
      document.querySelector('#site-header a').focus({ preventScroll: true }); // фокус — в начало страницы
    });
  }

  // Telegram: два номера — кнопка открывает меню выбора; закрытие — выбор номера, клик мимо, Esc
  const tgToggle = document.querySelector('[data-tg-toggle]');
  const tgMenu = document.getElementById('tg-menu');
  if (tgToggle && tgMenu) {
    const setTgMenu = (open) => {
      tgMenu.hidden = !open;
      tgToggle.setAttribute('aria-expanded', String(open));
    };
    tgToggle.addEventListener('click', () => {
      const open = tgMenu.hidden;
      setTgMenu(open);
      if (open) tgMenu.querySelector('a').focus();
    });
    tgMenu.addEventListener('click', (e) => { if (e.target.closest('a')) setTgMenu(false); });
    document.addEventListener('click', (e) => { if (!tgMenu.hidden && !e.target.closest('.fab-tg-wrap')) setTgMenu(false); });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !tgMenu.hidden) { setTgMenu(false); tgToggle.focus(); }
    });
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

  // Общая обвязка попапов на <dialog>: анимация, блокировка прокрутки, закрытие по Esc / фону / крестику
  const reduceMotionMQ = window.matchMedia('(prefers-reduced-motion: reduce)');
  // Прокрутку страницы возвращаем, только если не осталось других открытых попапов
  // (при переходе «услуга → заявка» событие close первого приходит уже после открытия второго)
  const unlockScroll = (except) => {
    const othersOpen = [...document.querySelectorAll('dialog[open]')].some((d) => d !== except);
    if (!othersOpen) document.documentElement.classList.remove('overflow-hidden');
  };
  const dialogController = (dlg) => {
    let closeTimer = null;
    const open = () => {
      clearTimeout(closeTimer);
      if (!dlg.open) dlg.showModal();
      document.documentElement.classList.add('overflow-hidden');
      void dlg.offsetWidth; // фиксируем стартовое состояние, чтобы сработал transition
      dlg.classList.add('is-visible');
    };
    const close = () => new Promise((resolve) => {
      dlg.classList.remove('is-visible');
      unlockScroll(dlg);
      clearTimeout(closeTimer);
      closeTimer = setTimeout(() => { if (dlg.open) dlg.close(); resolve(); }, reduceMotionMQ.matches ? 0 : 260);
    });
    dlg.querySelectorAll('[data-modal-close]').forEach((btn) => btn.addEventListener('click', () => close()));
    dlg.addEventListener('click', (e) => { if (e.target === dlg) close(); }); // клик по затемнению
    dlg.addEventListener('cancel', (e) => { e.preventDefault(); close(); }); // Esc — с анимацией
    dlg.addEventListener('close', () => {
      unlockScroll(dlg);
      dlg.classList.remove('is-visible');
    });
    return { open, close };
  };
  let openLeadModal = null; // попап услуги открывает заявку с уже выбранной услугой

  // Попап «Оставить заявку» (нативный <dialog>). Без JS кнопки ведут к форме в блоке #cta.
  const modal = document.getElementById('lead-modal');
  if (modal && typeof modal.showModal === 'function') {
    const lead = dialogController(modal);
    const modalForm = modal.querySelector('.js-modal-form');
    const views = {
      form: modal.querySelector('[data-modal-view="form"]'),
      success: modal.querySelector('[data-modal-view="success"]'),
    };
    const submitBtn = modalForm.querySelector('[type="submit"]');

    const showView = (name) => {
      views.form.classList.toggle('hidden', name !== 'form');
      views.success.classList.toggle('hidden', name !== 'success');
    };

    openLeadModal = (service) => {
      showView('form');
      lead.open();
      modalForm.elements.service.value = service || ''; // услуга из попапа «Подробнее» или пусто
      modalForm.querySelector('input').focus();
    };

    document.querySelectorAll('[data-modal-open]').forEach((btn) => {
      btn.addEventListener('click', (e) => { e.preventDefault(); openLeadModal(btn.dataset.service); });
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

  // Попапы с панелями: «Подробнее об услуге» (карточки услуг, hero) и «Новости и полезные материалы».
  // Ссылка data-<name>-open="ключ" показывает панель data-<name>-panel="ключ";
  // кнопка data-<name>-cta закрывает попап и открывает заявку (значение — услуга в форме).
  const panelModal = (id, name, titlePrefix) => {
    const dlg = document.getElementById(id);
    if (!dlg || typeof dlg.showModal !== 'function') return;
    const ctrl = dialogController(dlg);
    const attr = (suffix) => `data-${name}-${suffix}`;
    const panels = [...dlg.querySelectorAll(`[${attr('panel')}]`)];
    const open = (key) => {
      panels.forEach((p) => { p.hidden = p.getAttribute(attr('panel')) !== key; });
      dlg.setAttribute('aria-labelledby', `${titlePrefix}-${key}`);
      ctrl.open();
      dlg.scrollTop = 0;
      dlg.querySelector('[data-modal-close]').focus();
    };
    document.querySelectorAll(`[${attr('open')}]`).forEach((link) => {
      link.addEventListener('click', (e) => { e.preventDefault(); open(link.getAttribute(attr('open'))); });
    });
    dlg.querySelectorAll(`[${attr('cta')}]`).forEach((btn) => {
      btn.addEventListener('click', async () => {
        await ctrl.close();
        if (openLeadModal) openLeadModal(btn.getAttribute(attr('cta')));
        else document.getElementById('cta').scrollIntoView({ behavior: 'smooth' });
      });
    });
  };
  panelModal('service-modal', 'service', 'svc-title');
  panelModal('news-modal', 'news', 'news-title');

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
