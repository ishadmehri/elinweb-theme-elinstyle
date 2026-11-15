const {chromium,expect}=require('@playwright/test'),fs=require('fs'),path=require('path'),assert=require('assert/strict');
const root=__dirname,base=new URL('shop/',process.env.WP_BASE_URL||'http://elistyle.local/').href,out=path.join(root,'.artifacts');
fs.mkdirSync(out,{recursive:true});
(async()=>{
 const browser=await chromium.launch({headless:true,...(process.env.BROWSER_EXECUTABLE?{executablePath:process.env.BROWSER_EXECUTABLE}:{}),args:['--no-proxy-server']});
 const context=await browser.newContext({viewport:{width:1440,height:1000}}),page=await context.newPage(),report={site:base,queries:[],errors:[]};
 page.on('pageerror',e=>report.errors.push(e.message));
 const count=n=>expect(page.locator('.product')).toHaveCount(n);
 const box=(name,value)=>page.locator(`input[name="${name}[]"][value="${value}"]`);
 const reset=async()=>{await Promise.all([page.waitForURL(base),page.locator('.filter-title [data-catalog-reset]').click()]);await count(4);};
 const changed=async action=>{const before=page.url();await Promise.all([page.waitForURL(u=>u.href!==before),action()]);await expect(page.locator('[data-elinstyle-catalog]')).toHaveAttribute('aria-busy','false');};
 try{
  for(const [query,total] of [['',4],['?category=women',2],['?size=xl',1],['?color=olive',1],['?fabric=cotton',1],['?minprice=1800000',1],['?maxprice=1500000',2],['?category=women&color=cream',1],['?category=women&color=olive',0],['?keyword=NO_MATCH_123',0],['?sort=cheap',4],['?sort=expensive',4],['?category[]=women&category[]=men',3],['?size[]=s&size[]=xl',4],['?minprice=1550000&maxprice=1600000',1],['?minprice=1489999.5&maxprice=1490000',1]]){
   const response=await page.goto(base+query);assert.equal(response.status(),200);await count(total);report.queries.push({query,count:total});
   if(query==='?sort=cheap')assert.match(await page.locator('.product h3').first().innerText(),/بافت/);
   if(query==='?sort=expensive')assert.match(await page.locator('.product h3').first().innerText(),/اورشرت/);
  }
  report.variablePriceOverlap=true;
  await page.goto(base);let documents=0;page.on('request',r=>{if(r.isNavigationRequest()&&r.frame()===page.mainFrame())documents++;});
  await changed(()=>box('category','women').check());await count(2);assert(await box('category','women').isChecked());
  await changed(()=>box('category','men').check());await count(3);
  await changed(()=>box('color','olive').check());await count(1);assert(await box('category','women').isChecked());assert(await box('category','men').isChecked());
  report.multipleSelectionAndCombined=true;
  await changed(()=>page.locator('.catalog-chip').filter({hasText:'زنانه'}).click());await count(1);assert(!(await box('category','women').isChecked()));assert(await box('color','olive').isChecked());report.removeChipPreservesOthers=true;
  await reset();await changed(()=>{const input=page.locator('[name=maxprice]');return input.fill('1500000').then(()=>input.dispatchEvent('change'));});await count(2);assert.equal(await page.locator('[data-catalog-range]').inputValue(),'1500000');
  await changed(()=>page.locator('[data-catalog-range]').evaluate(e=>{e.value='1400000';e.dispatchEvent(new Event('input',{bubbles:true}));}));await count(1);assert.equal(await page.locator('[name=maxprice]').inputValue(),'1400000');report.rangeAndNumberSynchronized=true;
  await changed(()=>page.locator('.catalog-chip').filter({hasText:'قیمت:'}).click());await count(4);assert.equal(await page.locator('.catalog-chip').count(),0);report.removePrice=true;
  await reset();await changed(()=>box('category','women').check());await count(2);
  await changed(()=>page.locator('[name=sort]').selectOption('cheap'));await count(2);assert.match(await page.locator('.product h3').first().innerText(),/پیراهن/);assert(await box('category','women').isChecked());report.sortPreservesFilters=true;
  const selectedUrl=page.url();await page.reload();await count(2);assert(await box('category','women').isChecked());assert.equal(await page.locator('[name=sort]').inputValue(),'cheap');assert.equal(page.url(),selectedUrl);report.reloadPreservesState=true;
  await changed(()=>box('color','olive').check());await count(0);await expect(page.locator('.empty')).toBeVisible();report.empty=true;
  await page.goBack();await count(2);assert(!(await box('color','olive').isChecked()));await page.goForward();await count(0);report.browserHistory=true;
  await reset();await page.locator('form.search input').fill('NO_MATCH_123');await changed(()=>page.locator('form.search').evaluate(e=>e.requestSubmit()));await count(0);await expect(page.locator('.catalog-chip').filter({hasText:'جست‌وجو:'})).toBeVisible();report.search=true;
  await reset();await page.route('**/shop/**',route=>route.request().resourceType()==='fetch'?route.abort():route.continue());
  await box('category','women').check();await expect(page.locator('[data-catalog-status]')).toHaveClass(/is-error/);await count(4);await expect(page.locator('[data-elinstyle-catalog]')).toHaveAttribute('aria-busy','false');report.failurePreservesProducts=true;await page.unroute('**/shop/**');
  await changed(()=>box('category','men').check());await count(3);report.failureRecovery=true;
  await reset();await page.route('**/shop/**',async route=>{if(route.request().resourceType()==='fetch'&&route.request().url().includes('women')&&!route.request().url().includes('men'))await new Promise(r=>setTimeout(r,300));try{await route.continue();}catch(_){}});
  await box('category','women').check();await box('category','men').check();await count(3);await expect(page.locator('[data-elinstyle-catalog]')).toHaveAttribute('aria-busy','false');await page.waitForTimeout(350);await count(3);report.rapidSelectionLatestWins=true;await page.unroute('**/shop/**');
  await reset();await box('size','m').focus();await page.keyboard.press('Space');await expect(box('size','m')).toBeChecked();await expect(page.locator('[data-elinstyle-catalog]')).toHaveAttribute('aria-busy','false');assert.equal(await page.evaluate(()=>document.activeElement?.value),'m');report.keyboardAndFocus=true;
  report.ajaxDocumentNavigations=documents; // Reload above is the only deliberate document navigation.
  await page.setViewportSize({width:390,height:844});assert(!(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth)));assert.equal(await page.locator('.filters').evaluate(e=>getComputedStyle(e).gridTemplateColumns.split(' ').length),2);report.mobile=true;
  const noJs=await browser.newContext({javaScriptEnabled:false,viewport:{width:1440,height:1000}}),fallback=await noJs.newPage();await fallback.goto(base);await fallback.locator('[name="category[]"][value=women]').check();await fallback.locator('[name=sort]').selectOption('cheap');await Promise.all([fallback.waitForURL(u=>u.searchParams.getAll('category[]').includes('women')),fallback.locator('noscript button[type=submit]').click()]);await expect(fallback.locator('.product')).toHaveCount(2);await expect(fallback.locator('[name="category[]"][value=women]')).toBeChecked();report.noJsGetFallback=true;await noJs.close();
  assert.equal(report.errors.length,0);report.passed=true;
 }catch(e){report.failure=e.stack;process.exitCode=1;}
 finally{fs.writeFileSync(path.join(out,'archive-interactions.json'),JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));await browser.close();}
})();
