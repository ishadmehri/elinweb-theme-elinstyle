// Tab-scoped demo transport. No customer details or cart are written to localStorage.
(() => {
  const fixture=window.ElinstyleProductFixture, prefix='elinstyle-demo-v1:', key='elinstyle-demo-session-v1';
  const digits=value=>String(value??'').replace(/[۰-۹]/g,d=>'۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[٠-٩]/g,d=>'٠١٢٣٤٥٦٧٨٩'.indexOf(d));
  let state={version:1,items:[],draft:{}}, recovered=false, temporary=false;
  const fields={firstName:100,lastName:100,phone:30,email:254,province:40,city:40,address:600,postcode:20,shipping:30};
  function cleanDraft(input){const out={};if(!input||typeof input!=='object')return out;for(const [name,max] of Object.entries(fields))if(typeof input[name]==='string')out[name]=input[name].slice(0,max);out.terms=input.terms===true;return out}
  try {
    let raw=window.name.startsWith(prefix)?window.name.slice(prefix.length):null;
    if(raw===null)try{raw=sessionStorage.getItem(key)}catch{temporary=true}
    if(raw){if(raw.length>20000)throw Error('size');const parsed=JSON.parse(raw);if(parsed.version!==1||!Array.isArray(parsed.items)||parsed.items.length>20)throw Error('schema');const rows=new Map();for(const item of parsed.items){const v=fixture.variants.find(v=>v.key===item?.variationKey);if(!v||v.stock<1||!Number.isInteger(item.quantity)||item.quantity<1){recovered=true;continue}const requested=(rows.get(v.key)||0)+item.quantity;const quantity=Math.min(requested,v.stock);if(requested!==quantity)recovered=true;rows.set(v.key,quantity)}state.items=[...rows].map(([variationKey,quantity])=>({variationKey,quantity}));state.draft=cleanDraft(parsed.draft)}
  }catch{recovered=true}
  function persist(){const raw=JSON.stringify(state);try{window.name=prefix+raw}catch{temporary=true}try{sessionStorage.setItem(key,raw)}catch{temporary=true}}
  function changed(){persist();window.dispatchEvent(new Event('elin-cart-change'))}
  function items(){return state.items.map(row=>{const v=fixture.variants.find(v=>v.key===row.variationKey);return {source:'design-fixture',parentKey:fixture.parentKey,variationKey:v.key,name:fixture.name,color:v.color,size:v.size,quantity:row.quantity,unitPrice:v.salePrice,stock:v.stock,currency:fixture.currency,image:v.image}})}
  function add(variationKey,quantity){const v=fixture.variants.find(v=>v.key===variationKey),previous=state.items.find(row=>row.variationKey===variationKey)?.quantity||0;if(!v||!Number.isInteger(quantity)||quantity<1||previous+quantity>v.stock)return {ok:false,message:'افزودن به سبد نمایشی انجام نشد؛ جمع تعداد این نمونه از سقف موجودی بیشتر است.'};const row=state.items.find(row=>row.variationKey===variationKey);if(row)row.quantity+=quantity;else state.items.push({variationKey,quantity});changed();return {ok:true}}
  function setQuantity(variationKey,value){const row=state.items.find(r=>r.variationKey===variationKey),v=fixture.variants.find(v=>v.key===variationKey);if(!row||!v)return {ok:false};const n=Number(digits(value).trim()),valid=Number.isInteger(n)&&n>=1&&n<=v.stock;row.quantity=Number.isFinite(n)?Math.max(1,Math.min(v.stock,Math.trunc(n)||1)):row.quantity;changed();return {ok:true,corrected:!valid,quantity:row.quantity,stock:v.stock}}
  function remove(variationKey){state.items=state.items.filter(row=>row.variationKey!==variationKey);changed()}
  function clear(){state.items=[];changed()}
  function saveDraft(draft){state.draft=cleanDraft(draft);persist()}
  window.ElinstyleDemoStore=Object.freeze({items,add,setQuantity,remove,clear,digits,getDraft:()=>({...state.draft}),saveDraft,clearDraft:()=>saveDraft({}),status:()=>({recovered,temporary}),subtotal:()=>items().reduce((sum,row)=>sum+row.quantity*row.unitPrice,0)});
  function headerCount(){const count=items().reduce((n,row)=>n+row.quantity,0);document.querySelectorAll('[data-cart-count]').forEach(el=>{el.textContent=new Intl.NumberFormat('fa-IR').format(count);el.hidden=!count})}
  window.addEventListener('elin-cart-change',headerCount);headerCount();
})();
