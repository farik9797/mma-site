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

  // Анимации появления (GSAP). Без JS/GSAP контент виден как есть.
  if (!window.gsap || !window.ScrollTrigger) return;
  gsap.registerPlugin(ScrollTrigger);
  gsap.matchMedia().add('(prefers-reduced-motion: no-preference)', () => {
    gsap.from('.hero-anim', { y: 24, opacity: 0, duration: 0.8, ease: 'power3.out', stagger: 0.08 });
    gsap.from('.hero-media img', { scale: 1.06, opacity: 0, duration: 1.4, ease: 'power2.out' });

    gsap.set('[data-reveal]', { y: 28, opacity: 0 });
    ScrollTrigger.batch('[data-reveal]', {
      start: 'top 90%',
      once: true,
      onEnter: (els) => gsap.to(els, { y: 0, opacity: 1, duration: 0.7, ease: 'power3.out', stagger: 0.08, overwrite: true }),
    });
  });
})();
