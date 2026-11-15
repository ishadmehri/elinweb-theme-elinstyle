"""Editable SVG artboards for review only; not a web implementation."""
from pathlib import Path
import os
import base64, html, json, hashlib
from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
(ROOT/'generated/qa').mkdir(parents=True,exist_ok=True)
FONT_ROOT = Path(os.environ.get('ELINSTYLE_FONT_ROOT', r'D:\Fonts\fontiran\YekanBakh3-ProPlus\Pro+\Yekan Bakh Family\ttf'))
COL = dict(bg='#F7F5EF', ink='#242622', muted='#64675E', olive='#4D593C', line='#DADCD2', white='#FFFFFF', sand='#EAE6DA', pale='#E8ECDF')
FONTS = {400:'YekanBakh-Regular.ttf',600:'YekanBakh-SemiBold.ttf',700:'YekanBakh-Bold.ttf'}
def data(path, mime):
    return f'data:{mime};base64,'+base64.b64encode(path.read_bytes()).decode()

class Board:
    def __init__(self,w,h):
        self.w,self.h=w,h
        styles=''.join(f"@font-face{{font-family:Yekan;src:url('{data(FONT_ROOT/f, 'font/ttf')}');font-weight:{weight};}}" for weight,f in FONTS.items())
        self.p=[f'<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="{w}" height="{h}" viewBox="0 0 {w} {h}"><title>روزپوش — طرح صفحه اصلی</title><defs><style>{styles}</style>']
        for id,name in [('hero','editorial-hero-v1.png'),('products','catalog-contact-sheet-v1.png')]:
            file=ROOT/'assets'/name
            iw,ih=Image.open(file).size
            self.p.append(f'<image id="{id}" width="{iw}" height="{ih}" href="{data(file,"image/png")}"/>')
        self.p.append('</defs>')
        self.rect(0,0,w,h,COL['bg'])
    def rect(self,x,y,w,h,fill,stroke='none',r=0):
        self.p.append(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="{r}" fill="{fill}" stroke="{stroke}"/>')
    def line(self,x1,y1,x2,y2,color=None):
        self.p.append(f'<path d="M{x1} {y1}L{x2} {y2}" stroke="{color or COL["line"]}" fill="none"/>')
    def txt(self,t,x,y,size=16,weight=400,color=None,align='right'):
        anchor={'right':'start','left':'end','center':'middle'}[align]
        self.p.append(f'<text x="{x}" y="{y}" direction="rtl" unicode-bidi="plaintext" text-anchor="{anchor}" font-family="Yekan" font-size="{size}" font-weight="{weight}" fill="{color or COL["ink"]}">{html.escape(t)}</text>')
    def latin(self,t,x,y,size=12,color=None):
        self.p.append(f'<text x="{x}" y="{y}" font-family="Yekan" font-size="{size}" fill="{color or COL["muted"]}" letter-spacing="2">{html.escape(t)}</text>')
    def photo(self,id,x,y,w,h,view):
        self.p.append(f'<svg x="{x}" y="{y}" width="{w}" height="{h}" viewBox="{view}" preserveAspectRatio="xMidYMid slice"><use href="#{id}"/></svg>')
    def icon(self,name,x,y,s=24,color=None):
        paths={'bag':'M5 8H19L20 21H4Z M9 8V6A3 3 0 0 1 15 6V8',
        'search':'M16 16L21 21 M18 10A8 8 0 1 1 2 10A8 8 0 1 1 18 10',
        'user':'M16 7A4 4 0 1 1 8 7A4 4 0 1 1 16 7 M4 22V19C4 10 20 10 20 19V22',
        'menu':'M3 6H21 M3 12H21 M3 18H21',
        'arrow':'M20 12H4 M10 6L4 12L10 18',
        'heart':'M12 21L3 12C-3 5 6 -1 12 6C18 -1 27 5 21 12Z',
        'box':'M3 6L12 2L21 6V18L12 22L3 18Z M3 6L12 10L21 6 M12 10V22 M8 4L17 8',
        'check':'M4 12L10 18L21 5',
        'return':'M6 5L2 9L6 13 M2 9H15A6 6 0 1 1 0 12H9',
        'chat':'M3 3H21V17H10L4 22V17H3Z M7 8H17 M7 12H14'}
        self.p.append(f'<g transform="translate({x} {y}) scale({s/24})" fill="none" stroke="{color or COL["ink"]}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="{paths[name]}"/></g>')
    def button(self,t,x,y,w=180,light=False):
        fill=COL['bg'] if light else COL['olive']
        self.rect(x,y,w,48,fill)
        self.txt(t,x+w-20,y+31,16,600,COL['ink'] if light else COL['white'])
        self.icon('arrow',x+16,y+14,20,COL['ink'] if light else COL['white'])
    def save(self,name):
        (ROOT/'generated'/f'homepage-{name}-v1.svg').write_text(''.join(self.p)+'</svg>',encoding='utf-8')

# The quadrants refer to the original image; no garment is redrawn or replaced.
pw,ph=Image.open(ROOT/'assets/catalog-contact-sheet-v1.png').size
Q=[f'0 0 {pw/2} {ph/2}',f'{pw/2} 0 {pw/2} {ph/2}',f'0 {ph/2} {pw/2} {ph/2}',f'{pw/2} {ph/2} {pw/2} {ph/2}']
products=[('پیراهن آزاد زنانه','پنبه‌ای · شیری','۱٬۴۹۰٬۰۰۰ تومان',0,['#EDE7D6','#AEAA9B','#43483D']),('اورشرت مردانه','کتان · زیتونی','۱٬۸۹۰٬۰۰۰ تومان',1,['#62694A','#C7BEA7','#363B35']),('شلوار واید زنانه','پارچه‌ای · زغالی','۱٬۶۹۰٬۰۰۰ تومان',2,['#494B47','#B5AC9A','#1D2323']),('بافت ساده روزمره','بافت ریز · جوگندمی','۱٬۳۹۰٬۰۰۰ تومان',3,['#C4B99F','#676C54','#E8E3D5'])]
def product(b,i,x,y,w,mobile=False):
    title,sub,price,q,colors=products[i]
    ih=w if mobile else 310
    b.photo('products',x,y,w,ih,Q[q])
    b.rect(x+10,y+10,32,32,COL['bg'],r=16)
    b.icon('heart',x+18,y+18,16)
    if i==0:
        b.rect(x+w-54,y+12,42,24,COL['bg'])
        b.txt('تازه',x+w-33,y+29,12,600,align='center')
    fs=15 if mobile else 19
    b.txt(title,x+w,y+ih+30,fs,600)
    b.txt(sub,x+w,y+ih+55,13 if mobile else 15,color=COL['muted'])
    b.txt(price,x+w,y+ih+84,14 if mobile else 17,600)
    for k,c in enumerate(colors):
        b.p.append(f'<circle cx="{x+w-7-k*20}" cy="{y+ih+106}" r="5" fill="{c}" stroke="#969A8D" stroke-width="0.5"/>')

b=Board(1440,2960)
b.rect(0,0,1440,34,COL['olive'])
b.txt('روزپوش؛ برای روزهای معمولیِ دوست‌داشتنی',720,23,13,400,COL['white'],'center')
b.txt('روزپوش',1376,98,40,700)
b.rect(460,60,500,48,'#EEEDE6')
b.txt('دنبال چه لباسی می‌گردید؟',932,91,16,color=COL['muted'])
b.icon('search',479,73,21)
b.icon('bag',70,75,24); b.txt('سبد خرید',176,93,15)
b.icon('user',225,75,24); b.txt('حساب کاربری',360,93,15)
b.line(64,132,1376,132)
for t,x in [('تازه‌ها',1376),('زنانه',1276),('مردانه',1184),('لباس‌های پایه',1088),('مجموعه‌ها',920)]:b.txt(t,x,167,16,600)
b.txt('راهنمای اندازه',64,167,14,color=COL['muted'],align='left')
b.rect(64,204,1312,626,COL['sand'])
b.photo('hero',64,204,790,626,'160 0 1240 1024')
b.latin('THE EVERYDAY EDIT / 01',914,262,12)
b.txt('سادگی،',1320,375,66,700)
b.txt('به سبک تو.',1320,455,66,700)
b.txt('لباس‌هایی برای خودِ واقعی تو؛',1320,516,20)
b.txt('راحت، خوش‌دوخت و همراه هر روز.',1320,552,20)
b.button('کشف مجموعه',1116,594,204)
b.txt('زنانه و مردانه',1320,724,14,color=COL['muted'])
b.line(914,751,1320,751)
b.txt('کمتر، بهتر، ماندگارتر.',1320,784,14,color=COL['muted'])
b.latin('01 — CURATED FOR EVERY DAY',64,887,12)
b.txt('کمدت را از اینجا بساز',1376,944,34,700)
b.txt('انتخاب‌هایی ساده، برای هزار جور خودت بودن.',1376,979,17,color=COL['muted'])
cats=[(948,'زنانه','فرم‌های آزاد، حس خوب','hero','240 70 570 850'),(506,'مردانه','برای ریتم روزهای تو','hero','820 20 540 900'),(64,'لباس‌های پایه','همیشه به‌دردبخور','products',Q[3])]
for x,title,sub,img,v in cats:
    b.photo(img,x,1018,428,300,v)
    b.txt(title,x+428,1360,25,600)
    b.txt(sub,x+428,1390,16,color=COL['muted'])
    b.icon('arrow',x+8,1344,24)
b.line(64,1444,1376,1444)
b.txt('منتخب این روزها',1376,1516,34,700)
b.txt('دیدن همهٔ محصولات',88,1514,16,600,align='left'); b.icon('arrow',64,1496,18)
for label,x,active in [('همه',1334,True),('زنانه',1246,False),('مردانه',1152,False)]:
    if active:b.rect(x-42,1542,84,40,COL['olive'])
    b.txt(label,x,1569,15,600,COL['white'] if active else COL['muted'],'center')
for i in range(4): product(b,i,1066-i*334,1610,310)
b.rect(64,2090,1312,400,COL['olive'])
b.photo('products',64,2090,640,400,Q[3])
b.latin('LESS, BUT BETTER.',754,2146,13,'#E1E6D7')
b.txt('یک کمد ساده.',1320,2234,42,700,COL['white'])
b.txt('ترکیب‌های بی‌شمار.',1320,2294,42,700,COL['white'])
b.txt('با رنگ‌های خنثی، هر روز یک ترکیب تازه بساز.',1320,2342,19,400,'#E1E6D7')
b.button('دیدن لباس‌های پایه',1090,2384,230,True)
for x,title,sub,ico in [(1205,'انتخاب اندازهٔ مطمئن','راهنمای اندازه برای هر لباس','check'),(875,'خرید با خیال راحت','شرایط تعویض روشن و ساده','return'),(545,'ارسال قابل پیگیری','همراه سفارش، تا رسیدن به تو','box'),(215,'پاسخ‌گویی همراه','کمک در انتخاب و خرید','chat')]:
    b.icon(ico,x-14,2550,28,COL['olive']); b.txt(title,x,2610,19,600,align='center'); b.txt(sub,x,2642,14,color=COL['muted'],align='center')
b.rect(0,2698,1440,262,COL['ink'])
b.txt('روزپوش',1376,2775,38,700,COL['bg'])
b.txt('پوشاک روزمره، با نگاهی ساده‌تر.',1376,2814,17,400,'#D5D9CE')
b.txt('همراه روزهای تو.',1376,2845,17,400,'#D5D9CE')
for x,title,rows in [(866,'خرید',['زنانه','مردانه','لباس‌های پایه']),(578,'راهنمای خرید',['اندازه و تن‌خور','ارسال و تعویض','پرسش‌های متداول']),(290,'همراه روزپوش',['دربارهٔ ما','تماس با ما','پیگیری سفارش'])]:
    b.txt(title,x,2752,17,600,COL['bg'])
    for j,t in enumerate(rows):b.txt(t,x,2790+j*30,14,400,'#D5D9CE')
b.line(64,2902,1376,2902,'#4B5047'); b.txt('روزپوش © ۱۴۰۵',1376,2937,13,400,'#D5D9CE'); b.txt('حریم خصوصی  /  شرایط خرید',64,2937,13,400,'#D5D9CE','left')
b.save('desktop')

m=Board(390,3570)
m.rect(0,0,390,28,COL['olive']); m.txt('برای روزهای معمولیِ دوست‌داشتنی',195,20,12,400,COL['white'],'center')
m.icon('menu',346,50,24); m.txt('روزپوش',195,75,30,700,align='center'); m.icon('bag',22,50,24)
m.rect(20,98,350,44,'#EEEDE6'); m.txt('جست‌وجوی لباس...',350,127,15,color=COL['muted']); m.icon('search',34,111,19)
for t,x in [('تازه‌ها',338),('زنانه',253),('مردانه',168),('لباس‌های پایه',67)]:m.txt(t,x,177,14,600,align='center')
m.rect(0,198,390,286,COL['sand']); m.txt('مجموعهٔ روزمره / ۰۱',370,231,12,color=COL['muted'])
m.txt('سادگی، به سبک تو.',370,295,38,700)
m.txt('لباس‌هایی برای خودِ واقعی تو؛',370,336,17)
m.txt('راحت، خوش‌دوخت و همراه هر روز.',370,367,17)
m.button('کشف مجموعه',180,404,190)
m.photo('hero',0,484,390,326,'160 0 1240 1024')
m.txt('کمدت را از اینجا بساز',370,875,26,700)
m.txt('انتخاب‌هایی ساده، برای هر روز تو.',370,906,15,color=COL['muted'])
for x,t,v in [(202,'زنانه','220 0 650 830'),(20,'مردانه','780 0 650 830')]:
    m.photo('hero',x,936,168,214,v); m.txt(t,x+168,1185,20,600); m.icon('arrow',x+4,1168,20)
m.rect(20,1210,350,112,COL['sand']); m.photo('products',20,1210,112,112,Q[3]); m.txt('لباس‌های پایه',350,1251,20,600); m.txt('همیشه به‌دردبخور',350,1279,14,color=COL['muted']); m.icon('arrow',156,1253,20)
m.line(20,1362,370,1362)
m.txt('منتخب این روزها',370,1412,26,700)
for t,x,active in [('همه',330,True),('زنانه',242,False),('مردانه',154,False)]:
    if active:m.rect(x-40,1436,80,40,COL['olive'])
    m.txt(t,x,1463,14,600,COL['white'] if active else COL['muted'],'center')
for i in range(4):product(m,i,202 if i%2==0 else 20,1500+(i//2)*312,168,True)
m.line(20,2136,370,2136); m.txt('دیدن همهٔ محصولات',350,2170,16,600); m.icon('arrow',28,2154,22)
m.rect(0,2210,390,290,COL['olive'])
m.txt('کمتر، بهتر، ماندگارتر.',370,2248,13,400,'#E1E6D7')
m.txt('یک کمد ساده.',370,2307,34,700,COL['white']); m.txt('ترکیب‌های بی‌شمار.',370,2355,34,700,COL['white'])
m.txt('با رنگ‌های خنثی، هر روز یک ترکیب تازه بساز.',370,2392,15,400,'#E1E6D7')
m.button('دیدن لباس‌های پایه',150,2420,220,True)
m.photo('products',0,2500,390,250,Q[3])
for x,y,title,sub,ico in [(286,2790,'انتخاب اندازهٔ مطمئن','راهنمای اندازهٔ لباس','check'),(104,2790,'خرید با خیال راحت','شرایط روشن تعویض','return'),(286,2910,'ارسال قابل پیگیری','همراه سفارش تا مقصد','box'),(104,2910,'پاسخ‌گویی همراه','کمک در انتخاب و خرید','chat')]:
    m.icon(ico,x-12,y,24,COL['olive']); m.txt(title,x,y+52,15,600,align='center'); m.txt(sub,x,y+78,12,color=COL['muted'],align='center')
m.rect(0,3044,390,526,COL['ink']); m.txt('روزپوش',370,3112,32,700,COL['bg']); m.txt('پوشاک روزمره، با نگاهی ساده‌تر.',370,3148,16,400,'#D5D9CE')
for x,t,rows in [(370,'خرید',['زنانه','مردانه','لباس‌های پایه']),(180,'راهنمای خرید',['اندازه و تن‌خور','ارسال و تعویض','پرسش‌های متداول'])]:
    m.txt(t,x,3205,17,600,COL['bg'])
    for j,r in enumerate(rows):m.txt(r,x,3242+j*31,14,400,'#D5D9CE')
m.line(20,3340,370,3340,'#4B5047'); m.txt('دربارهٔ ما',370,3380,14,400,'#D5D9CE'); m.txt('تماس با ما',248,3380,14,400,'#D5D9CE'); m.txt('پیگیری سفارش',125,3380,14,400,'#D5D9CE')
m.txt('حریم خصوصی  /  شرایط خرید',370,3427,13,400,'#D5D9CE'); m.line(20,3470,370,3470,'#4B5047'); m.txt('همراه روزهای تو.',370,3517,14,400,'#D5D9CE'); m.txt('روزپوش © ۱۴۰۵',20,3546,12,400,'#D5D9CE','left')
m.save('mobile')

font_evidence=[{'weight':w,'path':str(FONT_ROOT/f),'sha256':hashlib.sha256((FONT_ROOT/f).read_bytes()).hexdigest()} for w,f in FONTS.items()]
(ROOT/'generated/qa/font-evidence.json').write_text(json.dumps(font_evidence,ensure_ascii=False,indent=2),encoding='utf-8')
print('Created two editable SVG artboards with embedded real YekanBakh fonts.')
