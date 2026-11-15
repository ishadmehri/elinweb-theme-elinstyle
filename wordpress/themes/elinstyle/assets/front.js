/* Front only; no demo inventory or simulated checkout. */
(() => {
  const menu = document.querySelector('[data-menu]'), nav = document.getElementById('mobile-nav');
  if (menu && nav) {
    menu.onclick = () => {nav.hidden = !nav.hidden; menu.setAttribute('aria-expanded', String(!nav.hidden));};
    document.addEventListener('keydown', e => {if(e.key === 'Escape' && !nav.hidden && !document.querySelector('dialog[open]')) {nav.hidden = true;menu.setAttribute('aria-expanded','false');menu.focus();}});
  }
  document.addEventListener('click', e => {
    const control = e.target.closest('[data-demo]'), notice = document.getElementById('notice');
    if(e.target.closest('#notice .close') && notice) window.ElinstyleMotion.close(notice);
    if(!control || !notice) return;
    if(/راهنمای اندازه|اندازه و تن/.test(control.dataset.demo)) return;
    const url = /سبد خرید/.test(control.dataset.demo) ? elinstyleLinks.cart : /حساب کاربری|پیگیری سفارش/.test(control.dataset.demo) ? elinstyleLinks.account : '';
    if(url && url !== '-1') {location.href = url;return;}
    document.getElementById('notice-title').textContent = control.dataset.demo;
    notice.querySelector('p').textContent = elinstyleLinks.demo;
    window.ElinstyleMotion.open(notice);
  });
  document.querySelectorAll('[data-elin-contact]').forEach(form => form.addEventListener('submit', e => {e.preventDefault();form.querySelector('[role="status"]').textContent = elinstyleLinks.demo;}));
})();
