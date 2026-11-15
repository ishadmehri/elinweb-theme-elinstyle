"""Draw only the desktop shop artboard; approved homepage files are never written."""
from pathlib import Path
import os
import json, hashlib

# Reuse drawing primitives only. Stop before the original page drawing/export code.
source = Path(__file__).with_name('build-design.py').read_text(encoding='utf-8')
exec(compile(source.split('# The quadrants refer')[0], 'homepage-drawing-primitives', 'exec'))
b = Board(1440, 2300)
b.p[0] = b.p[0].replace('روزپوش — طرح صفحه اصلی', 'روزپوش — فهرست فروشگاه')
b.rect(0,0,1440,34,COL['olive'])
b.txt('روزپوش؛ برای روزهای معمولیِ دوست‌داشتنی',720,23,13,400,COL['white'],'center')
b.txt('روزپوش',1376,98,40,700)
b.rect(460,60,500,48,'#EEEDE6')
b.txt('دنبال چه لباسی می‌گردید؟',932,91,16,color=COL['muted'])
b.icon('search',479,73,21)
b.icon('bag',70,75,24); b.txt('سبد خرید',176,93,15)
b.icon('user',225,75,24); b.txt('حساب کاربری',360,93,15)
b.line(64,132,1376,132)
for t,x in [('تازه‌ها',1376),('زنانه',1276),('مردانه',1184),('لباس‌های پایه',1088),('مجموعه‌ها',920)]: b.txt(t,x,167,16,600)
b.txt('راهنمای اندازه',64,167,14,color=COL['muted'],align='left')
b.line(64,194,1376,194)
b.txt('خانه',1376,234,14,color=COL['muted']); b.p.append('<path d="M1326 223l-5 5 5 5" fill="none" stroke="#64675E" stroke-width="1.3"/>'); b.txt('فروشگاه',1300,234,14)
b.txt('همهٔ محصولات',1376,302,40,700)
b.txt('انتخاب‌های ساده برای کمد روزمره؛ زنانه، مردانه و لباس‌های پایه.',1376,341,18,color=COL['muted'])
b.latin('THE EVERYDAY WARDROBE',64,301,12)
b.line(64,377,1376,377)

# Main results toolbar; current inclusive price range retains all four demo items.
b.txt('۴ محصول',1048,418,18,600)
b.txt('مرتب‌سازی:',468,418,15,color=COL['muted'])
b.rect(64,389,300,44,COL['bg'],'#818775')
b.txt('پیشنهادی',342,418,15,600)
b.p.append('<path d="M83 407l6 6 6-6" fill="none" stroke="#242622" stroke-width="1.5"/>')
b.txt('فیلترها',1376,418,21,600)
b.txt('پاک‌کردن',1130,418,14,600,COL['olive'],align='left')
b.rect(742,455,306,36,COL['pale'])
b.txt('قیمت: ۱ تا ۲ میلیون تومان',1028,479,14,600)
b.txt('×',760,480,20,400)
b.txt('۱ فیلتر فعال',720,479,13,color=COL['muted'])
b.line(1080,389,1080,1764)

def checkbox(t,y,count=None):
    b.rect(1358,y-15,18,18,COL['bg'],'#818775')
    b.txt(t,1344,y,15)
    if count is not None:b.txt(str(count),1130,y,14,color=COL['muted'],align='left')
def section(title,y):
    b.txt(title,1376,y,18,600)
    b.txt('−',1130,y,22,color=COL['muted'],align='left')
def rule(y):b.line(1120,y,1376,y)
section('دسته‌بندی',481)
b.p.append('<circle cx="1367" cy="517" r="9" fill="none" stroke="#4D593C"/><circle cx="1367" cy="517" r="4" fill="#4D593C"/>')
b.txt('همهٔ محصولات',1344,522,15,600); b.txt('۴',1130,522,14,color=COL['muted'],align='left')
for t,y,n in [('زنانه',563,'۲'),('مردانه',604,'۱'),('لباس‌های پایه',645,'۱')]:checkbox(t,y,n)
rule(678); section('اندازه',718)
for x,t in [(1328,'S'),(1264,'M'),(1200,'L'),(1136,'XL')]:
    b.rect(x,742,48,40,COL['bg'],'#818775')
    b.p.append(f'<text x="{x+24}" y="768" text-anchor="middle" font-family="Yekan" font-size="15" fill="#242622">{t}</text>')
b.txt('اندازهٔ مناسب خودت را انتخاب کن.',1376,816,13,color=COL['muted'])
rule(847); section('رنگ',888)
for j,(t,c) in enumerate([('شیری','#EDE7D6'),('زیتونی','#62694A'),('زغالی','#494B47'),('جوگندمی','#C4B99F')]):
    y=931+j*38
    b.p.append(f'<circle cx="1366" cy="{y-6}" r="9" fill="{c}" stroke="#818775"/>')
    b.txt(t,1344,y,15)
rule(1076); section('محدودهٔ قیمت',1116)
b.txt('۱ تا ۲ میلیون تومان',1376,1152,15,600,COL['olive'])
b.line(1134,1183,1362,1183,COL['olive'])
for x in [1134,1362]:b.p.append(f'<circle cx="{x}" cy="1183" r="7" fill="#F7F5EF" stroke="#4D593C" stroke-width="2"/>')
for x,t,label in [(1254,'۱٬۰۰۰٬۰۰۰','از'),(1120,'۲٬۰۰۰٬۰۰۰','تا')]:
    b.rect(x,1210,122,42,COL['bg'],'#818775'); b.txt(t,x+61,1237,14,600,align='center'); b.txt(label,x+122,1200,12,color=COL['muted'])
b.txt('تومان',1376,1278,12,color=COL['muted'])
rule(1304); section('جنس',1344)
for t,y in [('پنبه‌ای',1386),('کتان',1426),('پارچه‌ای',1466),('بافت',1506)]:checkbox(t,y)
rule(1538)
b.txt('فیلترها با انتخاب شما',1376,1578,14,color=COL['muted'])
b.txt('نتایج را محدود می‌کنند.',1376,1605,14,color=COL['muted'])

pw,ph=Image.open(ROOT/'assets/catalog-contact-sheet-v1.png').size
Q=[f'0 0 {pw/2} {ph/2}',f'{pw/2} 0 {pw/2} {ph/2}',f'0 {ph/2} {pw/2} {ph/2}',f'{pw/2} {ph/2} {pw/2} {ph/2}']
products=[('پیراهن آزاد زنانه','پنبه‌ای · شیری','۱٬۴۹۰٬۰۰۰ تومان','#EDE7D6'),('اورشرت مردانه','کتان · زیتونی','۱٬۸۹۰٬۰۰۰ تومان','#62694A'),('شلوار واید زنانه','پارچه‌ای · زغالی','۱٬۶۹۰٬۰۰۰ تومان','#494B47'),('بافت ساده روزمره','بافت ریز · جوگندمی','۱٬۳۹۰٬۰۰۰ تومان','#C4B99F')]
for i,(title,sub,price,c) in enumerate(products):
    x=572 if i%2==0 else 64; y=522+(i//2)*608; w=476
    b.photo('products',x,y,w,w,Q[i])
    b.rect(x+16,y+16,32,32,COL['bg'],r=16); b.icon('heart',x+24,y+24,16)
    if i==0:
        b.rect(x+w-62,y+16,46,26,COL['bg']); b.txt('تازه',x+w-39,y+34,13,600,align='center')
    b.txt(title,x+w,y+w+34,21,600)
    b.txt(sub,x+w,y+w+65,16,color=COL['muted'])
    b.txt(price,x+w,y+w+99,18,600)
    b.p.append(f'<circle cx="{x+10}" cy="{y+w+89}" r="7" fill="{c}" stroke="#818775"/>')
b.line(64,1756,1048,1756)
b.txt('هر ۴ محصول را دیدید.',556,1800,16,color=COL['muted'],align='center')
b.rect(532,1823,48,44,COL['olive']); b.txt('۱',556,1852,16,600,COL['white'],'center')

b.rect(64,1900,1312,96,COL['sand'])
b.icon('check',1324,1934,25,COL['olive']); b.txt('انتخاب اندازهٔ مطمئن',1296,1943,19,600)
b.txt('پیش از انتخاب، راهنمای اندازه و تن‌خور را ببینید.',1296,1973,15,color=COL['muted'])
b.txt('راهنمای اندازه',96,1958,16,600,COL['olive'],'left'); b.icon('arrow',70,1942,20,COL['olive'])

# Reuse the approved footer verbatim, translated only to its shop position.
footer = source.split("b.rect(0,2698,1440,262,COL['ink'])")[1].split("b.save('desktop')")[0]
b.p.append('<g transform="translate(0 -660)">')
b.rect(0,2698,1440,262,COL['ink'])
exec(compile(footer,'approved-footer','exec'))
b.p.append('</g>')
(ROOT/'generated'/'shop-desktop-v1.svg').write_text(''.join(b.p)+'</svg>',encoding='utf-8')
print('Shop artboard exported: 1440 x 2300; homepage untouched.')
