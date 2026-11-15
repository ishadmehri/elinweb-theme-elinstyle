const {chromium}=require('playwright');
const assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path'),crypto=require('node:crypto'),{pathToFileURL}=require('node:url');
const root=process.env.ELINSTYLE_ROOT||path.resolve(__dirname,'../../html');
const out=path.resolve(process.env.ELINSTYLE_OUTPUT||path.join(__dirname,'output'));fs.mkdirSync(out,{recursive:true});
(async()=>{
 const names=['index','shop','product','cart','checkout','article','about','contact','login','my-account'];
 const missing=[];
 for(const file of fs.readdirSync(root).filter(name=>/\.(html|css)$/.test(name))){
  const text=fs.readFileSync(path.join(root,file),'utf8');
  for(const m of text.matchAll(/(?:src|href)=["']([^"']+)["']|url\(["']?([^)'"\s]+)/g)){
   const ref=m[1]||m[2];if(ref.startsWith('#')||/^(data:|mailto:|tel:)/.test(ref))continue;
   assert.ok(!/^(https?:|\/\/)/.test(ref),'Unexpected remote runtime resource');
   if(!fs.existsSync(path.resolve(root,ref.split(/[?#]/)[0])))missing.push({file,ref});
  }
 }
 assert.deepEqual(missing,[]);
 const manifest=JSON.parse(fs.readFileSync(path.resolve(__dirname,'../../docs/frontend/source-manifest.json'),'utf8'));
 for(const entry of manifest.files){const local=path.resolve(__dirname,'../..',entry.path);assert.equal(crypto.createHash('sha256').update(fs.readFileSync(local)).digest('hex'),entry.sha256,'Runtime copy differs: '+entry.path)}
 const browser=await chromium.launch({headless:true,executablePath:process.env.ELINSTYLE_BROWSER||undefined});
 const report={date:'2026-11-15',pages:[],errors:[],external:[],failed:[],runtimeFilesMatch:manifest.files.length,localReferences:true};
 try{
  for(const width of [1440,390])for(const name of names){
   const page=await browser.newPage({viewport:{width,height:900},reducedMotion:'reduce'});
   page.on('pageerror',e=>report.errors.push(e.message));page.on('requestfailed',r=>report.failed.push(r.url()));
   await page.route('**/*',route=>{if(/^https?:/.test(route.request().url())){report.external.push(route.request().url());return route.abort()}return route.continue()});
   await page.goto(pathToFileURL(path.join(root,name+'.html')).href);await page.evaluate(()=>document.fonts.ready);
   assert.equal(await page.locator('h1').isVisible(),true);
   assert.equal(await page.locator('header a.account-link').getAttribute('href'),'login.html');
   assert.equal(await page.locator('#mobile-nav a[href="login.html"]').count(),1);
   assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),true,'Overflow: '+name+' '+width);
   assert.equal(await page.evaluate(()=>document.fonts.check('16px Yekan')&&Array.from(document.fonts).some(face=>face.family==='Yekan'&&face.status==='loaded')),true);
   report.pages.push({name,width,loaded:true,noOverflow:true,accountRoute:true});await page.close();
  }
  assert.deepEqual(report.errors,[]);assert.deepEqual(report.external,[]);assert.deepEqual(report.failed,[]);
  fs.writeFileSync(path.join(out,'smoke-report.json'),JSON.stringify(report,null,2)+'\n');console.log(JSON.stringify({passedPages:report.pages.length,runtimeFilesMatch:report.runtimeFilesMatch,errors:report.errors,external:report.external,failed:report.failed}));
 }finally{await browser.close()}
})().catch(error=>{console.error(error);process.exit(1)});
