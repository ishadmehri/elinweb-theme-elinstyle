"""Report design color-pair contrast, not full accessibility compliance."""
from pathlib import Path
import os
import json
root=Path(__file__).resolve().parent.parent
(root/'generated/qa').mkdir(parents=True,exist_ok=True)
tokens=json.loads((root/'tokens/tokens.json').read_text(encoding='utf-8'))
c=tokens['color']
def lum(h):
    s=[int(h[i:i+2],16)/255 for i in (1,3,5)]
    v=[x/12.92 if x<=0.04045 else ((x+0.055)/1.055)**2.4 for x in s]
    return sum(x*y for x,y in zip(v,[.2126,.7152,.0722]))
def contrast(a,b):
    lo,hi=sorted([lum(a),lum(b)])
    return (hi+.05)/(lo+.05)
pairs=[('body/page',c['text']['primary'],c['background']['page']),('secondary/page',c['text']['secondary'],c['background']['page']),('secondary/hero',c['text']['secondary'],c['background']['editorial']),('placeholder/search',c['text']['placeholder'],c['background']['subtle']),('white/primary',c['action']['onPrimary'],c['action']['primary']),('campaign-secondary',c['text']['onAccentSecondary'],c['action']['primary']),('footer-secondary',c['text']['inverseSecondary'],c['background']['inverse']),('disabled',c['action']['disabledText'],c['action']['disabledBackground'])]
for k,v in c['feedback'].items():pairs.append((k,v['text'],v['surface']))
report=[{'pair':n,'foreground':a,'background':b,'ratio':round(contrast(a,b),2),'normalText4_5':contrast(a,b)>=4.5} for n,a,b in pairs]
(root/'generated/qa/contrast-report.json').write_text(json.dumps(report,indent=2),encoding='utf-8')
print(json.dumps(report,indent=2))
