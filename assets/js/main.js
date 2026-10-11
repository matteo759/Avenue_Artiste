// Menu mobile
const toggle = document.querySelector('.nav-toggle');
const menu = document.getElementById('menu');
if (toggle && menu) {
  toggle.addEventListener('click', () => {
    const open = menu.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(open));
  });
}

// Confirmation avant les actions sensibles : <button data-confirm="Message">
document.addEventListener('click', (ev) => {
  const el = ev.target.closest('[data-confirm]');
  if (el && !window.confirm(el.dataset.confirm)) ev.preventDefault();
});

// Accueil : le projecteur doré suit la souris (sauf si l'utilisateur réduit les animations)
const hero = document.querySelector('.hero');
if (hero && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
  hero.addEventListener('pointermove', (ev) => {
    const r = hero.getBoundingClientRect();
    hero.style.setProperty('--mx', (ev.clientX - r.left) + 'px');
    hero.style.setProperty('--my', (ev.clientY - r.top) + 'px');
  });
}
