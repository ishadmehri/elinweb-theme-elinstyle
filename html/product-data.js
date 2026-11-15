// DESIGN FIXTURE ONLY. These keys are not WooCommerce product/variation IDs.
(() => {
  const variants = [
    {key:'DEMO-SHIRT-IV-S', color:'شیری', size:'S', regularPrice:1690000, salePrice:1490000, stock:2, image:'front'},
    {key:'DEMO-SHIRT-IV-M', color:'شیری', size:'M', regularPrice:1690000, salePrice:1490000, stock:3, image:'front'},
    {key:'DEMO-SHIRT-IV-L', color:'شیری', size:'L', regularPrice:1790000, salePrice:1590000, stock:1, image:'front'},
    {key:'DEMO-SHIRT-IV-XL', color:'شیری', size:'XL', regularPrice:1790000, salePrice:1590000, stock:0, image:'front'}
  ].map(Object.freeze);
  function resolve(attributes) {
    if (!attributes.color || !attributes.size) return {status:'incomplete', variation:null};
    const variation = variants.find(v=>v.color===attributes.color && v.size===attributes.size);
    return {status:!variation?'invalid':variation.stock>0?'available':'unavailable', variation:variation||null};
  }
  window.ElinstyleProductFixture = Object.freeze({source:'design-fixture',parentKey:'DEMO-SHIRT-01',name:'پیراهن آزاد زنانه',currency:'TOMAN',variants:Object.freeze(variants),resolve});
})();
