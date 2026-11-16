// Visual QA of brand SVGs only; writes solely inside assets/brand.
const {chromium}=require('@playwright/test');
const fs=require('fs'),path=require('path');
(async()=>{
 const root=__dirname;
 const data=n=>'data:image/svg+xml;base64,'+fs.readFileSync(path.join(root,n)).toString('base64');
 const browser=await chromium.launch({executablePath:process.env.BROWSER_EXECUTABLE||undefined,headless:true});
 const page=await browser.newPage({viewport:{width:1000,height:640},deviceScaleFactor:1});
 await page.setContent(`<style>body{margin:0;background:#f7f5ef;font:14px Arial;color:#64675e}section{box-sizing:border-box;padding:40px 48px;height:250px}header{margin-bottom:32px;font-size:12px;letter-spacing:2px}.dark{background:#242622;color:#d5d9ce}.row{display:flex;align-items:center;gap:72px}.label{font-size:12px;margin-top:20px}.icons{height:140px;display:flex;align-items:center;gap:56px;padding:0 48px}</style><section><header>ELINSTYLE / BRAND SIGNATURE</header><div class="row"><div><img src="${data('logo.svg')}" height="64"><div class="label">Primary / 64 px</div></div><div><img src="${data('logo.svg')}" height="32"><div class="label">Header / 32 px</div></div></div></section><section class="dark"><header>INVERSE ON CHARCOAL</header><div class="row"><div><img src="${data('logo-light.svg')}" height="64"><div class="label">Inverse / 64 px</div></div><div><img src="${data('logo-light.svg')}" height="32"><div class="label">Header / 32 px</div></div></div></section><div class="icons"><img src="${data('mark.svg')}" height="52"><span>Mark</span><img src="${data('favicon.svg')}" height="32"><span>32 px</span><img src="${data('favicon.svg')}" height="16"><span>16 px</span></div>`);
 await page.evaluate(()=>Promise.all([...document.images].map(i=>i.decode())));
 await page.screenshot({path:path.join(root,'brand-preview.png')});
 console.log('Rendered brand-preview.png at 1000x640, including 32px headers and 16px favicon.');
 await browser.close();
})();
