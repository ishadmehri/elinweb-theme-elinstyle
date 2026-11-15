// Local motion only: state changes happen immediately; animations never queue.
(() => {
  const preference = matchMedia('(prefers-reduced-motion: reduce)');
  const running = new WeakMap(), active = new Set(), dialogs = new WeakMap();
  function cancel(element) {
    const current = running.get(element);
    if (!current) return;
    running.delete(element); active.delete(current); current.animation.cancel();
  }
  function animate(element, frames, duration, finish = () => {}) {
    cancel(element);
    if (preference.matches || !element.animate) { finish(); return; }
    const animation = element.animate(frames, {duration, easing:'ease-out', fill:'both'});
    const current = {element, animation, finish};
    running.set(element, current); active.add(current);
    animation.finished.then(() => {
      if (running.get(element) !== current) return;
      cancel(element); finish();
    }, () => {});
  }
  function reveal(element) {
    if (element) animate(element, [{opacity:.65, transform:'translateY(3px)'}, {opacity:1, transform:'none'}], 180);
  }
  function bind(dialog) {
    if (dialogs.has(dialog)) return dialogs.get(dialog);
    const state = {closing:false}; dialogs.set(dialog, state);
    dialog.addEventListener('cancel', event => { event.preventDefault(); close(dialog); });
    dialog.addEventListener('close', () => {
      // A queued close event from a quick reopen must not cancel its new animation.
      if (dialog.open) return;
      cancel(dialog); state.closing = false;
      dialog.classList.remove('motion-opening', 'motion-closing');
    });
    return state;
  }
  function open(dialog) {
    const state = bind(dialog);
    if (dialog.open && !state.closing) return;
    const start = dialog.open ? {opacity:getComputedStyle(dialog).opacity, transform:getComputedStyle(dialog).transform} : {opacity:0, transform:'translateY(8px)'};
    state.closing = false; cancel(dialog);
    dialog.classList.remove('motion-closing'); dialog.classList.add('motion-opening');
    if (!dialog.open) dialog.showModal();
    animate(dialog, [start, {opacity:1, transform:'none'}], 240, () => dialog.classList.remove('motion-opening'));
  }
  function close(dialog) {
    const state = bind(dialog);
    if (!dialog.open || state.closing) return;
    const start = {opacity:getComputedStyle(dialog).opacity, transform:getComputedStyle(dialog).transform};
    state.closing = true; cancel(dialog);
    dialog.classList.remove('motion-opening'); dialog.classList.add('motion-closing');
    animate(dialog, [start, {opacity:0, transform:'translateY(6px)'}], 220, () => {
      if (!state.closing) return;
      dialog.classList.remove('motion-closing');
      dialog.close();
    });
  }
  preference.addEventListener('change', () => {
    if (!preference.matches) return;
    for (const current of [...active]) { cancel(current.element); current.finish(); }
  });
  window.ElinstyleMotion = {open, close, reveal};
})();
