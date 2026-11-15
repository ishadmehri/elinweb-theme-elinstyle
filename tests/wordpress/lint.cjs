const fs=require('fs'),path=require('path'),{spawnSync}=require('child_process');
const root=path.resolve(__dirname,'../../wordpress');let count=0;
function walk(dir){for(const entry of fs.readdirSync(dir,{withFileTypes:true})){const file=path.join(dir,entry.name);if(entry.isDirectory())walk(file);else if(file.endsWith('.php')){const r=spawnSync(process.env.PHP_BINARY||'php',['-n','-l',file],{encoding:'utf8'});if(r.error||r.status!==0)throw new Error(r.error?.message||r.stdout+r.stderr);count++;}}}
walk(root);console.log(`PHP syntax passed: ${count} files.`);
