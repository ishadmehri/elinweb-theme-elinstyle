// Local design renderer: licensed font bytes stay in memory and generated/ is ignored.
const fs=require('fs'),path=require('path');
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const root=path.resolve(__dirname,'..'),inputs=path.join(__dirname,'artboards');
const fontRoot=process.env.ELINSTYLE_FONT_ROOT||'D:/Fonts/fontiran/YekanBakh3-ProPlus/Pro+/Yekan Bakh Family/ttf';
const weights=[[400,'Regular'],[600,'SemiBold'],[700,'Bold']];
const css=weights.map(([weight,name])=>`@font-face{font-family:Yekan;src:url(data:font/ttf;base64,${fs.readFileSync(path.join(fontRoot,`YekanBakh-${name}.ttf`)).toString('base64')});font-weight:${weight}}`).join('');
function localAsset(ref,base){
 const file=path.resolve(base,ref),relative=path.relative(root,file);
 if(relative.startsWith('..')||path.isAbsolute(relative))throw new Error('Asset outside design folder');
 const mime=file.endsWith('.svg')?'image/svg+xml':'image/png';
 return `data:${mime};base64,${fs.readFileSync(file).toString('base64')}`;
}
(async()=>{
 const options={headless:true};if(process.env.CHROME_PATH)options.executablePath=process.env.CHROME_PATH;
 const browser=await chromium.launch(options);
 try{
  const names=process.argv.slice(2);const files=names.length?names:fs.readdirSync(inputs).filter(f=>f.endsWith('.svg'));
  fs.mkdirSync(path.join(root,'generated/previews'),{recursive:true});fs.mkdirSync(path.join(root,'generated/qa'),{recursive:true});
  for(const name of files){
   if(path.basename(name)!==name||!name.endsWith('.svg'))throw new Error('Use an artboard filename');
   let svg=fs.readFileSync(path.join(inputs,name),'utf8');
   const width=Number(svg.match(/<svg[^>]*\bwidth="([0-9.]+)"/)[1]),height=Number(svg.match(/<svg[^>]*\bheight="([0-9.]+)"/)[1]);
   svg=svg.replace(/((?:xlink:)?href=")([^"#][^"]*)(")/g,(all,start,ref,end)=>start+localAsset(ref,inputs)+end);
   svg=svg.replace(/(<svg\b[^>]*>)/,`$1<defs><style>${css}</style></defs>`);
   const page=await browser.newPage({viewport:{width,height},deviceScaleFactor:1});
   await page.setContent('<meta charset="utf-8"><style>body{margin:0}svg{display:block}</style>'+svg);
   await page.evaluate(()=>document.fonts.ready);
   const report=await page.evaluate(()=>({fonts:[...document.fonts].map(f=>({family:f.family,weight:f.weight,status:f.status})),overflow:[...document.querySelectorAll('text')].map(t=>({text:t.textContent,box:t.getBoundingClientRect().toJSON()})).filter(t=>t.box.x< -1||t.box.right>innerWidth+1||t.box.bottom>innerHeight+1)}));
   await page.screenshot({path:path.join(root,'generated/previews',name.replace(/\.svg$/,'.png'))});
   fs.writeFileSync(path.join(root,'generated/qa',name.replace(/\.svg$/,'.json')),JSON.stringify(report,null,2));
   if(report.overflow.length||report.fonts.some(f=>f.status!=='loaded'))throw new Error(`Review failed: ${name}`);
   console.log(`Rendered ${name}; local fonts loaded; no text overflow.`);await page.close();
  }
 }finally{await browser.close();}
})().catch(e=>{console.error(e.message);process.exitCode=1;});
