const {spawnSync}=require('node:child_process'),fs=require('node:fs'),path=require('node:path');
const checks=['smoke-check','product-check','commerce-check','commerce-edge-check','account-check','account-edge-check','interaction-check','motion-check','size-guide-integration'];
const output=path.resolve(process.env.ELINSTYLE_OUTPUT||path.join(__dirname,'output'));fs.mkdirSync(output,{recursive:true});
const report={date:'2026-11-15',checks:[]};
for(const name of checks){const started=Date.now();const result=spawnSync(process.execPath,[path.join(__dirname,name+'.cjs')],{env:process.env,encoding:'utf8',windowsHide:true,timeout:180000});report.checks.push({name,passed:result.status===0,seconds:Math.round((Date.now()-started)/100)/10});console.log(name+': '+(result.status===0?'PASS':'FAIL'));if(result.status!==0){console.error(result.stderr||result.error||result.stdout);fs.writeFileSync(path.join(output,'regression-summary.json'),JSON.stringify(report,null,2)+'\n');process.exit(1)}}
fs.writeFileSync(path.join(output,'regression-summary.json'),JSON.stringify(report,null,2)+'\n');
