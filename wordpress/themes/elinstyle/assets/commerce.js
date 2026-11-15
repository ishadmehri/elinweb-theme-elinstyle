/* Native Woo forms and nonces own all writes. Replace UI only after server response. */
(() => {
  let pending=false;
  const __=s=>wp.i18n.__(s,'elinstyle');
  function bounds(){document.querySelectorAll('.cart-quantity').forEach(group=>{const input=group.querySelector('input.qty');if(!input)return;group.querySelector('[data-cart-step="-1"]').disabled=Number(input.value)<=Number(input.min||1);group.querySelector('[data-cart-step="1"]').disabled=!!input.max&&Number(input.value)>=Number(input.max);});}
  async function refresh(url,options={},focusName=''){
    if(pending)return;const current=document.querySelector('[data-commerce-body]');if(!current)return;pending=true;current.setAttribute('aria-busy','true');const status=current.querySelector('[data-commerce-status]');status.textContent=__('در حال به‌روزرسانی…');
    try{const response=await fetch(url,{credentials:'same-origin',cache:'no-store',...options});if(!response.ok)throw new Error('HTTP '+response.status);const doc=new DOMParser().parseFromString(await response.text(),'text/html'),next=doc.querySelector('[data-commerce-body]');if(!next)throw new Error('Invalid cart response');current.replaceWith(next);bounds();if(focusName){const target=[...next.querySelectorAll('[name]')].find(e=>e.name===focusName);target?.focus({preventScroll:true});}window.jQuery?.(document.body).trigger('updated_wc_div');
    }catch(_){status.textContent=__('سبد به‌روز نشد؛ دوباره تلاش کن.');}finally{pending=false;current.removeAttribute('aria-busy');}
  }
  document.addEventListener('submit',e=>{if(!e.target.matches('.woocommerce-cart-form'))return;e.preventDefault();e.stopImmediatePropagation();if(pending)return;const form=e.target;if(!form.reportValidity())return;const body=new FormData(form);const submitter=e.submitter;if(submitter?.name)body.set(submitter.name,submitter.value||'1');else body.set('update_cart','1');refresh(form.action,{method:'POST',body},document.activeElement?.name||'');},true);
  document.addEventListener('click',e=>{const remove=e.target.closest('[data-commerce-body] a.remove,[data-commerce-body] .woocommerce-remove-coupon');if(!remove)return;e.preventDefault();e.stopImmediatePropagation();refresh(remove.href);},true);
  document.addEventListener('change',e=>{if(!e.target.matches('.woocommerce-cart-form input.qty')||pending)return;const form=e.target.form;if(form.reportValidity())form.requestSubmit(form.querySelector('[name="update_cart"]'));},true);
  document.addEventListener('click',e=>{const button=e.target.closest('[data-cart-step]');if(!button||pending)return;const input=button.closest('.cart-quantity').querySelector('input.qty');input.value=Math.max(Number(input.min||1),Math.min(Number(input.max)||Number.MAX_SAFE_INTEGER,Number(input.value)+Number(button.dataset.cartStep)));bounds();input.dispatchEvent(new Event('change',{bubbles:true}));});
  const normalize=s=>String(s).replace(/[۰-۹٠-٩]/g,c=>String(c.charCodeAt(0)-(c.charCodeAt(0)>=1776?1776:1632)));
  document.addEventListener('input',e=>{if(e.target.matches('#billing_phone,#billing_postcode,#shipping_postcode'))e.target.value=normalize(e.target.value);});
  document.addEventListener('change',e=>{if(!e.target.matches('#billing_state,#shipping_state'))return;const city=document.getElementById(e.target.id.replace('state','city'));if(city){city.value='';city.disabled=e.target.value==='';}});
  bounds();
})();
