"""Create self-contained brand SVGs. Lettering is converted to vector paths."""
from pathlib import Path
import os
from fontTools.ttLib import TTFont
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.boundsPen import BoundsPen
from fontTools.pens.transformPen import TransformPen
import json,hashlib
ROOT=Path(__file__).resolve().parent.parent/'generated/brand'
ROOT.mkdir(parents=True,exist_ok=True)
font_path=Path(os.environ.get('ELINSTYLE_FONT_ROOT', r'D:\Fonts\fontiran\YekanBakh3-ProPlus\Pro+\Yekan Bakh Family\ttf'))/'YekanBakh-Bold.ttf'
font=TTFont(font_path); glyphs=font.getGlyphSet(); cmap=font.getBestCmap()
pen=SVGPathPen(glyphs); bounds=BoundsPen(glyphs);cursor=0
for char in 'elinstyle':
    name=cmap[ord(char)]
    transform=(1,0,0,1,cursor,0)
    glyphs[name].draw(TransformPen(pen,transform));glyphs[name].draw(TransformPen(bounds,transform))
    cursor+=font['hmtx'][name][0]+8
x0,y0,x1,y1=bounds.bounds
scale=30/(y1-y0);width=(x1-x0)*scale
total=round(52+width+2,2)
word_path=pen.getCommands()
def mark(color,fold):
    return f'<path fill="{color}" fill-rule="evenodd" d="M15 4H28L36 12V22H12V26Q12 30 16 30H31V38H15Q4 38 4 27V16Q4 4 15 4ZM12 14V16H28V14Z"/><path fill="{fold}" d="M28 4L36 12H28Z"/>'
def svg(name,title,content,viewbox,w,h):
    (ROOT/name).write_text(f'<svg xmlns="http://www.w3.org/2000/svg" width="{w}" height="{h}" viewBox="{viewbox}" role="img" aria-labelledby="brand-title"><title id="brand-title">{title}</title>{content}</svg>\n',encoding='utf-8')
for filename,color,fold,text in [('logo.svg','#4D593C','#9AA781','#242622'),('logo-light.svg','#F7F5EF','#A6B58E','#F7F5EF')]:
    content=mark(color,fold)+f'<path fill="{text}" transform="translate({52-x0*scale:.4f} {5+y1*scale:.4f}) scale({scale:.6f} {-scale:.6f})" d="{word_path}"/>'
    svg(filename,'elinstyle — پوشاک روزمره',content,f'0 0 {total} 42',total,42)
svg('mark.svg','elinstyle — نشانهٔ لیبل تاخورده',mark('#4D593C','#9AA781'),'0 0 40 42',40,42)
# Solid tile preserves a legible silhouette against both browser tab themes.
svg('favicon.svg','elinstyle', '<rect width="48" height="48" rx="10" fill="#4D593C"/><g transform="translate(4 3)">'+mark('#F7F5EF','#A6B58E')+'</g>','0 0 48 48',48,48)
(ROOT/'brand-evidence.json').write_text(json.dumps({'fontSource':str(font_path),'fontSha256':hashlib.sha256(font_path.read_bytes()).hexdigest(),'lettering':'elinstyle lowercase; outlined paths only','logoViewBox':[0,0,total,42],'externalDependencies':False},ensure_ascii=False,indent=2),encoding='utf-8')
print(f'Created four brand SVGs; wordmark viewBox {total} x 42')
