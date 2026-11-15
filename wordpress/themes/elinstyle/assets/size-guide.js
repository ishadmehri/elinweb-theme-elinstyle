// Shared local size guide; works with file:// and without external dependencies.
(() => {
  const dialog = document.createElement('dialog');
  dialog.id = 'size-guide';
  dialog.className = 'size-guide';
  dialog.setAttribute('aria-labelledby', 'size-guide-title');
  dialog.setAttribute('aria-describedby', 'size-guide-intro');
  dialog.innerHTML = `
    <header class="sg-header">
      <div><p class="sg-eyebrow">یک قدم نزدیک‌تر به تن‌خور دلخواه</p><h2 id="size-guide-title">راهنمای اندازه</h2></div>
      <button type="button" class="iconbtn sg-close" aria-label="بستن راهنمای اندازه" autofocus>×</button>
    </header>
    <p id="size-guide-intro" class="sg-intro">با یک متر نرم و بدون کشیدن آن، اندازه‌های بدنت را بگیر؛ سپس با جدول مقایسه کن.</p>
    <div class="sg-layout">
      <aside class="sg-how" aria-labelledby="sg-how-title">
        <svg class="sg-tape" viewBox="0 0 80 56" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><ellipse cx="40" cy="15" rx="25" ry="10"/><path d="M15 15v24c0 6 11 11 25 11s25-5 25-11V15M40 25v25M21 22v8m9-5v10m19-10v10m9-13v8"/></svg>
        <h3 id="sg-how-title">چطور اندازه بگیریم؟</h3>
        <ol><li><b>دور سینه</b><span>متر را از برجسته‌ترین بخش سینه عبور بده؛ پشت بدن هم‌سطح بماند.</span></li><li><b>دور کمر</b><span>باریک‌ترین قسمت کمر را، بدون حبس کردن نفس، اندازه بگیر.</span></li><li><b>دور باسن</b><span>پاها را کنار هم بگذار و پهن‌ترین بخش باسن را اندازه بگیر.</span></li></ol>
      </aside>
      <section class="sg-chart" aria-labelledby="sg-chart-title">
        <div class="sg-chart-head"><h3 id="sg-chart-title">جدول اندازهٔ بدن</h3><span>سانتی‌متر</span></div>
        <div class="sg-switch" role="group" aria-label="انتخاب جدول اندازه"><button type="button" data-sg-chart="women" aria-pressed="true">زنانه</button><button type="button" data-sg-chart="men" aria-pressed="false">مردانه</button></div>
        <div class="sg-table-wrap" tabindex="0" role="region" aria-labelledby="sg-caption"><table><caption id="sg-caption">جدول نمونهٔ زنانه؛ اندازه‌ها بر حسب سانتی‌متر</caption><thead><tr><th scope="col">اندازه</th><th scope="col">دور سینه</th><th scope="col">دور کمر</th><th scope="col">دور باسن</th></tr></thead><tbody id="sg-rows"></tbody></table></div>
        <p class="sg-note"><b>این جدول نمونه است.</b> اندازه‌ها مربوط به بدن هستند، نه ابعاد لباس. پیش از خرید، جدول اختصاصی همان محصول را بررسی کن.</p>
        <div class="sg-fit"><h4>بین دو اندازه هستی؟</h4><p>برای بالاتنه به دور سینه و برای شلوار به دور کمر و باسن توجه کن. انتخاب نهایی به برش لباس و تن‌خور دلخواهت بستگی دارد.</p></div>
        <p id="sg-status" class="sg-sr" role="status" aria-live="polite"></p>
      </section>
    </div>
    <footer class="sg-footer"><span>متر را صاف نگه دار؛ نه شل، نه سفت.</span><button type="button" class="btn sg-close">متوجه شدم</button></footer>`;
  document.body.append(dialog);
  const charts = {
    women: [['S','۸۴–۸۸','۶۶–۷۰','۹۲–۹۶'],['M','۸۹–۹۳','۷۱–۷۵','۹۷–۱۰۱'],['L','۹۴–۹۸','۷۶–۸۰','۱۰۲–۱۰۶'],['XL','۹۹–۱۰۳','۸۱–۸۵','۱۰۷–۱۱۱']],
    men: [['S','۹۰–۹۴','۷۶–۸۰','۹۰–۹۴'],['M','۹۵–۹۹','۸۱–۸۵','۹۵–۹۹'],['L','۱۰۰–۱۰۴','۸۶–۹۰','۱۰۰–۱۰۴'],['XL','۱۰۵–۱۰۹','۹۱–۹۵','۱۰۵–۱۰۹']]
  };
  function selectChart(key, announce = true) {
    const label = key === 'women' ? 'زنانه' : 'مردانه';
    dialog.querySelector('#sg-rows').innerHTML = charts[key].map(row => `<tr><th scope="row"><bdi dir="ltr">${row[0]}</bdi></th>${row.slice(1).map(value => `<td><bdi dir="ltr">${value}</bdi></td>`).join('')}</tr>`).join('');
    dialog.querySelectorAll('[data-sg-chart]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.sgChart === key)));
    dialog.querySelector('#sg-caption').textContent = `جدول نمونهٔ ${label}؛ اندازه‌ها بر حسب سانتی‌متر`;
    dialog.querySelector('#sg-status').textContent = announce ? `جدول اندازهٔ ${label} نمایش داده شد.` : '';
    if (announce) window.ElinstyleMotion.reveal(dialog.querySelector('.sg-table-wrap'));
  }
  selectChart('women', false);
  const triggerSelector = '[data-size-guide], [data-demo="راهنمای اندازه"], [data-demo="اندازه و تن‌خور"]';
  const mobileNav = document.getElementById('mobile-nav');
  if (mobileNav && !mobileNav.querySelector('[data-size-guide]')) {
    const button = document.createElement('button');
    button.type = 'button'; button.className = 'textbutton'; button.dataset.sizeGuide = ''; button.textContent = 'راهنمای اندازه';
    mobileNav.append(button);
  }
  document.querySelectorAll(triggerSelector).forEach(button => {
    button.setAttribute('aria-haspopup', 'dialog'); button.setAttribute('aria-controls', dialog.id);
  });
  let opener, previousOverflow, scrollLocked = false, outsideDown = false;
  document.addEventListener('click', event => {
    const trigger = event.target.closest(triggerSelector);
    if (!trigger) return;
    event.preventDefault(); event.stopImmediatePropagation();
    if (dialog.open) { window.ElinstyleMotion.open(dialog); return; }
    opener = trigger;
    if (!scrollLocked) previousOverflow = document.body.style.overflow;
    scrollLocked = true;
    document.body.style.overflow = 'hidden';
    selectChart('women', false);
    window.ElinstyleMotion.open(dialog);
    dialog.querySelector('.sg-close').focus({preventScroll:true});
  }, true);
  dialog.querySelectorAll('.sg-close').forEach(button => button.addEventListener('click', () => window.ElinstyleMotion.close(dialog)));
  dialog.querySelectorAll('[data-sg-chart]').forEach(button => button.addEventListener('click', () => selectChart(button.dataset.sgChart)));
  dialog.addEventListener('keydown', event => {
    if (event.key !== 'Tab') return;
    const controls = [...dialog.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')].filter(element => element.getClientRects().length);
    const first = controls[0], last = controls[controls.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault(); last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault(); first.focus();
    }
  });
  function outside(event) {
    const rect = dialog.getBoundingClientRect();
    return event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom;
  }
  dialog.addEventListener('pointerdown', event => { outsideDown = event.target === dialog && outside(event); });
  dialog.addEventListener('click', event => {
    if (outsideDown && event.target === dialog && outside(event)) window.ElinstyleMotion.close(dialog);
    outsideDown = false;
  });
  dialog.addEventListener('close', () => {
    if (dialog.open) return;
    document.body.style.overflow = previousOverflow || '';
    scrollLocked = false;
    opener?.focus({preventScroll:true});
  });
})();
