/* Live WooCommerce adapter. No design fixtures, browser cart, or simulated success. */
(() => {
  const root=document.querySelector('[data-elinstyle-product]');if(!root||!window.elinstyleProduct)return;
  const __=(s)=>wp.i18n.__(s,'elinstyle'), find=id=>document.getElementById(id), motion=window.ElinstyleMotion;
  let data=JSON.parse(root.dataset.elinstyleProduct),nonce=elinstyleProduct.nonce,activeView=0,busy=false;
  const selected={},quantity=find('product-quantity'),form=find('product-form'),add=find('product-add'),status=find('product-cart-status');
  data.groups.forEach(g=>selected[g.name]=g.values.length===1?g.values[0].value:'');
  const format=n=>new Intl.NumberFormat('fa-IR').format(n), html=key=>{const d=document.createElement('div');d.innerHTML=key;return d.textContent;};
  function resolve(){if(data.groups.some(g=>!selected[g.name]))return {state:'incomplete',variant:null};const v=data.variants.find(v=>data.groups.every(g=>!v.attributes[g.name]||v.attributes[g.name]===selected[g.name]));return {state:!v?'invalid':v.available?'available':'unavailable',variant:v};}
  function ceiling(v){return !v?1:v.max<0?Number.MAX_SAFE_INTEGER:v.max;}
  function update(announce=true){
    const result=resolve(),v=result.variant,available=result.state==='available',max=ceiling(available?v:null);
    const value=Number(quantity.value);quantity.value=Math.max(1,Math.min(max,Math.trunc(value)||1));
    if(max===Number.MAX_SAFE_INTEGER)quantity.removeAttribute('max');else quantity.max=String(Math.max(1,max));
    quantity.disabled=busy||!available;find('quantity-minus').disabled=busy||!available||Number(quantity.value)<=1;find('quantity-plus').disabled=busy||!available||Number(quantity.value)>=max;
    add.disabled=busy||!available;add.setAttribute('aria-busy',String(busy));add.textContent=busy?__('در حال افزودن…'):__('افزودن به سبد خرید');
    const missing=[];
    root.querySelectorAll('[data-product-attribute]').forEach(b=>{
      const attr=b.dataset.productAttribute,val=b.dataset.value;
      const matches=data.variants.filter(v=>data.groups.every(g=>{const choice=g.name===attr?val:selected[g.name];return !choice||!v.attributes[g.name]||v.attributes[g.name]===choice;}));
      const exists=matches.length>0,inStock=matches.some(v=>v.available);
      b.disabled=busy||!exists||!inStock;b.setAttribute('aria-pressed',String(selected[attr]===val));
      b.title=!exists?__('این ترکیب ارائه نمی‌شود'):!inStock?__('در این رنگ ناموجود است'):'';
      if(exists&&!inStock)missing.push(b.textContent.trim());
    });
    data.groups.forEach(g=>{const option=g.values.find(v=>v.value===selected[g.name]);root.querySelector('[data-attribute-label="'+g.name+'"]').textContent=option?option.label:__('انتخاب کن');});
    find('unavailable-sizes').textContent=missing.length?missing.join('، ')+__(' در این رنگ ناموجود است.'):'';
    find('product-price').innerHTML=result.state==='invalid'?data.price:(v?v.price:data.price);
    find('product-regular').hidden=!v||!v.regular;find('product-discount').hidden=!v||!v.discount;
    if(v){find('product-regular').innerHTML=v.regular;find('product-discount').textContent=format(v.discount)+__('٪ تخفیف');}
    find('product-sku').textContent=v?v.sku:data.sku;
    if(announce)find('selection-message').textContent=({incomplete:__('رنگ و اندازه را انتخاب کن.'),invalid:__('این ترکیب ارائه نمی‌شود.'),unavailable:__('این اندازه در رنگ انتخابی ناموجود است.'),available:__('موجود؛ قیمت و موجودی انتخاب به‌روز شد.')})[result.state];
  }
  root.querySelectorAll('[data-product-attribute]').forEach(b=>b.addEventListener('click',()=>{selected[b.dataset.productAttribute]=b.dataset.value;find('quantity-error').textContent='';update();const v=resolve().variant;if(v&&v.image&&v.image!==data.gallery[0].url){data.gallery[0]={...data.gallery[0],url:v.image};selectView(0);}}));
  find('clear-selection').onclick=()=>{if(busy)return;data.groups.forEach(g=>selected[g.name]='');quantity.value=1;find('quantity-error').textContent='';selectView(0);update();};
  function normalize(value){return String(value).replace(/[۰-۹٠-٩]/g,c=>String(c.charCodeAt(0)-(c.charCodeAt(0)>=1776?1776:1632)));}
  function setQuantity(value){const v=resolve().variant,max=ceiling(v),n=Number(normalize(value));const valid=Number.isInteger(n)&&n>=1&&n<=max;quantity.value=Number.isFinite(n)?Math.max(1,Math.min(max,Math.trunc(n))):1;update(false);find('quantity-error').textContent=valid?'':__('تعداد نامعتبر بود؛ به محدودهٔ موجودی فعلی اصلاح شد.');}
  quantity.onchange=()=>setQuantity(quantity.value);find('quantity-minus').onclick=()=>setQuantity(Number(quantity.value)-1);find('quantity-plus').onclick=()=>setQuantity(Number(quantity.value)+1);
  function updateCart(cart){const button=document.querySelector('[data-demo="سبد خرید"]');if(button){let badge=button.querySelector('[data-cart-count]');if(!badge){badge=document.createElement('span');badge.dataset.cartCount='';button.append(badge);}badge.textContent=format(cart.items_count||0);badge.hidden=!cart.items_count;button.setAttribute('aria-label',__('سبد خرید، تعداد کالا: ')+format(cart.items_count||0));}document.dispatchEvent(new CustomEvent('elinstyle:cart-updated',{detail:cart}));}
  async function cartRequest(path,options={}){const response=await fetch(elinstyleProduct.api+path,{credentials:'same-origin',cache:'no-store',...options,headers:{'Content-Type':'application/json',Nonce:nonce,...options.headers}});nonce=response.headers.get('Nonce')||nonce;const body=await response.json();if(!response.ok){const error=new Error(html(body.message||__('افزودن انجام نشد؛ دوباره تلاش کن.')));error.code=body.code;throw error;}return body;}
  const cartReady=cartRequest('cart').then(updateCart).catch(()=>{});
  form.addEventListener('submit',async event=>{
    event.preventDefault();if(busy)return;setQuantity(quantity.value);const result=resolve();
    if(result.state!=='available'){update();root.querySelector('[data-product-attribute]:not(:disabled)')?.focus();return;}
    const variant=result.variant,snapshot={...selected},amount=Number(quantity.value);busy=true;status.textContent='';find('product-view-cart').hidden=true;update(false);
    try{
      await cartReady;
      const cart=await cartRequest('cart/add-item',{method:'POST',body:JSON.stringify({id:variant.id,quantity:amount,variation:data.groups.map(g=>({attribute:g.name,value:snapshot[g.name]})),elinstyle_revision:variant.revision})});
      updateCart(cart);const labels=data.groups.map(g=>g.values.find(v=>v.value===snapshot[g.name])?.label).filter(Boolean);
      status.textContent=data.name+'، '+labels.join('، ')+__(' به سبد اضافه شد.');find('product-view-cart').hidden=false;
    }catch(error){
      status.textContent=error.message||__('افزودن انجام نشد؛ دوباره تلاش کن.');
      if(error.code==='elinstyle_product_changed'){
        try{const response=await fetch(elinstyleProduct.products+data.id,{credentials:'same-origin',cache:'no-store'});if(response.ok)data=await response.json();}catch(_){/* Preserve the last selection; never retry an add automatically. */}
      }
    }finally{busy=false;update(false);}
  });
  // Gallery uses only Woo media URLs; captions distinguish editorial/style views.
  function art(view){const ns='http://www.w3.org/2000/svg',svg=document.createElementNS(ns,'svg'),image=document.createElementNS(ns,'image');svg.setAttribute('viewBox',view.box);svg.setAttribute('preserveAspectRatio','xMidYMin slice');svg.setAttribute('role','img');svg.setAttribute('aria-label',data.name+'؛ '+view.label);image.setAttribute('href',view.url);image.setAttribute('width',view.width);image.setAttribute('height',view.height);svg.append(image);return svg;}
  function selectView(index){activeView=index;const view=data.gallery[index];find('gallery-main').replaceChildren(art(view));find('gallery-main').setAttribute('aria-label',__('بزرگ‌نمایی ')+view.label);find('gallery-caption').textContent=view.caption;root.querySelectorAll('[data-gallery]').forEach(b=>b.setAttribute('aria-pressed',String(Number(b.dataset.gallery)===index)));motion.reveal(find('gallery-main'));}
  const thumbs=[...root.querySelectorAll('[data-gallery]')];thumbs.forEach((b,i)=>{b.onclick=()=>selectView(i);b.onkeydown=e=>{let next;if(e.key==='ArrowLeft')next=(i+1)%thumbs.length;if(e.key==='ArrowRight')next=(i+thumbs.length-1)%thumbs.length;if(e.key==='Home')next=0;if(e.key==='End')next=thumbs.length-1;if(next!==undefined){e.preventDefault();thumbs[next].focus();selectView(next);}};});
  const zoom=find('product-zoom');let opener,overflow='';find('gallery-main').onclick=()=>{opener=document.activeElement;overflow=document.body.style.overflow;document.body.style.overflow='hidden';const view=data.gallery[activeView];find('zoom-title').textContent=view.label;find('zoom-caption').textContent=view.caption;find('zoom-viewport').replaceChildren(art(view));find('zoom-viewport').classList.remove('is-enlarged');find('zoom-toggle').setAttribute('aria-pressed','false');motion.open(zoom);zoom.querySelector('.zoom-close').focus();};zoom.querySelectorAll('.zoom-close').forEach(b=>b.onclick=()=>motion.close(zoom));find('zoom-toggle').onclick=()=>find('zoom-toggle').setAttribute('aria-pressed',String(find('zoom-viewport').classList.toggle('is-enlarged')));zoom.addEventListener('close',()=>{if(!zoom.open){document.body.style.overflow=overflow;opener?.focus({preventScroll:true});}});zoom.addEventListener('keydown',e=>{if(e.key!=='Tab')return;const controls=[...zoom.querySelectorAll('button,[tabindex="0"]')],first=controls[0],last=controls[controls.length-1];if(e.shiftKey&&document.activeElement===first){e.preventDefault();last.focus();}else if(!e.shiftKey&&document.activeElement===last){e.preventDefault();first.focus();}});
  const favorite=find('product-favorite');let favorites=[];try{const stored=JSON.parse(localStorage.getItem('elinstyle-favs')||'[]');if(Array.isArray(stored))favorites=stored;}catch(_){}favorite.setAttribute('aria-pressed',String(favorites.includes(data.name)));favorite.onclick=()=>{favorites=favorites.includes(data.name)?favorites.filter(n=>n!==data.name):[...favorites,data.name];favorite.setAttribute('aria-pressed',String(favorites.includes(data.name)));try{localStorage.setItem('elinstyle-favs',JSON.stringify(favorites));}catch(_){status.textContent=__('علاقه‌مندی فقط در این بازدید حفظ شد.');}};
  update();
})();
