/* ============================================================
   Mobile menu + dropdown toggle
   Carica in WPCode Box come snippet JS (footer, tutte le pagine)
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
  const toggle   = document.querySelector('.nav-toggle');
  const nav      = document.querySelector('#main-nav');

  if (!toggle || !nav) return;

  /* Overlay — appeso all'header per condividere lo stesso
     stacking context del nav (fix per Oxygen e builder simili) */
  const header  = toggle.closest('.header');
  const overlay = document.createElement('div');
  overlay.className = 'nav-overlay';
  (header || document.body).appendChild(overlay);

  /* Open / Close --------------------------------------------- */
  function openNav() {
    nav.classList.add('is-open');
    overlay.classList.add('is-open');
    toggle.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  function closeNav() {
    nav.classList.remove('is-open');
    overlay.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';

    dropItems.forEach(item => {
      item.classList.remove('is-open');
      const link = item.querySelector(':scope > .nav-link, :scope > a');
      if (link) link.setAttribute('aria-expanded', 'false');
    });
  }

  /* Hamburger toggle ----------------------------------------- */
  toggle.addEventListener('click', () => {
    nav.classList.contains('is-open') ? closeNav() : openNav();
  });

  /* Click sull'overlay chiude il menu ------------------------- */
  overlay.addEventListener('click', closeNav);

  /* Dropdown — solo mobile, desktop usa CSS hover ------------- */
  const dropItems = nav.querySelectorAll('.has-dropdown, .menu-item-has-children');

  dropItems.forEach(item => {
    const link = item.querySelector(':scope > .nav-link, :scope > a');
    if (!link) return;

    link.addEventListener('click', (e) => {
      if (window.innerWidth > 767) return;

      e.preventDefault();

      const isOpen = item.classList.contains('is-open');

      dropItems.forEach(el => {
        el.classList.remove('is-open');
        const l = el.querySelector(':scope > .nav-link, :scope > a');
        if (l) l.setAttribute('aria-expanded', 'false');
      });

      if (!isOpen) {
        item.classList.add('is-open');
        link.setAttribute('aria-expanded', 'true');
      }
    });
  });

  /* Tasto ESC chiude il menu ---------------------------------- */
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && nav.classList.contains('is-open')) closeNav();
  });

});
