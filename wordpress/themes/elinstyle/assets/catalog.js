/* Progressive enhancement of server-rendered GET filters. No browser inventory. */
(() => {
  const selector='[data-elinstyle-catalog]';if(!document.querySelector(selector))return;
  const __=s=>wp.i18n.__(s,'elinstyle');let controller,request=0,timer;
  const root=()=>document.querySelector(selector),form=()=>document.getElementById('elinstyle-catalog-form');
  function search(){document.querySelectorAll('form.search').forEach(el=>{const input=el.querySelector('input');if(input)input.value=root().dataset.keyword||'';});}
  function url(){const current=form(),target=new URL(current.action);for(const [name,value] of new FormData(current)){if(value!=='')target.searchParams.append(name,value);}return target;}
  function priceActive(){const all=form().querySelector('[name=price]');if(all)all.remove();}
  function syncPrice(input){
    const min=form().querySelector('[name=minprice]'),max=form().querySelector('[name=maxprice]'),range=form().querySelector('[data-catalog-range]');
    if(input===range)max.value=range.value;
    if(Number(min.value)>Number(max.value))max.value=min.value;
    range.max=String(Math.max(Number(range.max),Number(max.value)));range.value=max.value;
    priceActive();
  }
  function focusKey(){const e=document.activeElement;return e&&root().contains(e)?{name:e.name,value:e.value,kind:e.type}:null;}
  async function refresh(target,{historyMode='push',focus=null}={}){
    clearTimeout(timer);controller?.abort();controller=new AbortController();const serial=++request,previous=root();
    previous.setAttribute('aria-busy','true');const status=previous.querySelector('[data-catalog-status]');status.classList.remove('is-error');status.textContent=__('در حال به‌روزرسانی محصولات…');
    try {
      const response=await fetch(target,{credentials:'same-origin',cache:'no-store',signal:controller.signal});if(!response.ok)throw new Error('HTTP '+response.status);
      const doc=new DOMParser().parseFromString(await response.text(),'text/html'),next=doc.querySelector(selector);if(!next)throw new Error('Missing catalog');if(serial!==request)return;
      previous.replaceWith(next);if(historyMode==='push')history.pushState(null,'',target);search();
      next.querySelector('[data-catalog-status]').textContent=next.querySelector('#catalog-count').textContent+__('؛ نتایج به‌روز شد.');
      if(focus){const field=[...next.querySelectorAll('[name]')].find(e=>e.name===focus.name&&(focus.kind!=='checkbox'||e.value===focus.value));field?.focus({preventScroll:true});}
      else next.querySelector('.filter-title h3')?.focus({preventScroll:true});
      next.setAttribute('aria-busy','false');
    } catch(error) {
      if(error.name==='AbortError'||serial!==request)return;
      status.classList.add('is-error');status.textContent=__('محصولات به‌روز نشدند؛ دوباره انتخاب کن یا صفحه را بارگذاری کن.');previous.setAttribute('aria-busy','false');
    }
  }
  document.addEventListener('change',event=>{
    const input=event.target;if(!root().contains(input)||!input.matches('input[type=checkbox],input[type=number],[data-catalog-range],select[name=sort]'))return;
    if(input.matches('input[type=number],[data-catalog-range]')){if(!input.checkValidity())return;syncPrice(input);}
    refresh(url(),{focus:focusKey()});
  });
  document.addEventListener('input',event=>{
    const input=event.target;if(!root().contains(input)||!input.matches('[data-catalog-range]'))return;
    syncPrice(input);clearTimeout(timer);const focus=focusKey();timer=setTimeout(()=>refresh(url(),{focus}),300);
  });
  document.addEventListener('submit',event=>{
    if(event.target===form()){event.preventDefault();if(form().reportValidity())refresh(url(),{focus:focusKey()});}
    else if(event.target.matches('form.search')){event.preventDefault();form().querySelector('[name=keyword]').value=event.target.querySelector('input').value.trim();refresh(url());}
  });
  document.addEventListener('click',event=>{
    const link=event.target.closest('[data-catalog-reset],[data-catalog-chip],[data-all],.pagination a');
    if(!link||!root().contains(link)||event.button!==0||event.metaKey||event.ctrlKey||event.shiftKey||event.altKey)return;
    event.preventDefault();refresh(link.href);
  });
  window.addEventListener('popstate',()=>refresh(location.href,{historyMode:'none'}));
  search();
})();
