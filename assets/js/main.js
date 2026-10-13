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

  // Формы: проверка обязательных полей.
  // Макет: отправка не подключена — на WordPress подключить обработчик (CF7 / admin-ajax).
  document.querySelectorAll('.js-form').forEach((form) => {
    const status = form.querySelector('.form-status');
    const isValid = (f) => {
      if (f.type === 'checkbox') return f.checked;
      if (f.type === 'tel') return f.value.replace(/\D/g, '').length >= 9;
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
      phone: (f) => f.value.replace(/\D/g, '').length >= 9,
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
    gsap.from('.hero-img', { scale: 1.05, opacity: 0, duration: 1.4, ease: 'power2.out' });

    gsap.set('[data-reveal]', { y: 28, opacity: 0 });
    ScrollTrigger.batch('[data-reveal]', {
      start: 'top 90%',
      once: true,
      onEnter: (els) => gsap.to(els, { y: 0, opacity: 1, duration: 0.7, ease: 'power3.out', stagger: 0.08, overwrite: true }),
    });
  });
})();
