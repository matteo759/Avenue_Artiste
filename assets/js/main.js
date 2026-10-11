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
