"""Desktop editorial category artboard. No previous exports are written."""
from pathlib import Path
import os
source=Path(__file__).with_name('build-design.py').read_text(encoding='utf-8')
exec(compile(source.split('# The quadrants refer')[0],'drawing-primitives','exec'))
b=Board(1440,1920)
b.p[0]=b.p[0].replace('روزپوش — طرح صفحه اصلی','روزپوش — دسته‌بندی راهنمای استایل')
header=source.split('b=Board(1440,2960)')[1].split('b.rect(64,204,1312,626')[0]
exec(compile(header,'approved-header','exec'))
b.line(64,194,1376,194)
b.txt('خانه  /  مقاله‌ها  /  راهنمای استایل',1376,234,14,color=COL['muted'])
b.txt('راهنمای استایل',1376,310,42,700)
b.txt('ایده‌هایی ساده برای پوشیدن لباس‌های آشنا، به سبک خودت.',1376,350,19,color=COL['muted'])
b.latin('THE EVERYDAY JOURNAL',64,302,12)
b.line(64,380,1376,380)
for t,x,w,active in [('همهٔ مقاله‌ها',1244,132,False),('راهنمای استایل',1064,160,True),('نگهداری لباس',892,152,False),('راهنمای خرید',724,148,False)]:
    if active:b.rect(x,398,w,44,COL['olive'])
    b.txt(t,x+w/2,427,16,600,COL['white'] if active else COL['muted'],'center')
b.txt('۴ مقاله',64,426,15,color=COL['muted'],align='left')

b.rect(64,478,1312,414,COL['sand'])
b.photo('hero',64,478,656,414,'0 0 1536 1024')
b.p[-1]=b.p[-1].replace('xMidYMid slice','xMidYMin slice')
b.txt('پیشنهاد تحریریه  /  راهنمای استایل',1328,531,14,600,COL['olive'])
b.txt('چطور با لباس‌های ساده،',1328,604,34,700)
b.txt('استایل روزمره بسازیم؟',1328,653,34,700)
b.txt('از انتخاب رنگ تا تن‌خور و لایه‌پوشی؛',1328,711,19)
b.txt('چهار قدم برای ساختن یک کمد کاربردی.',1328,746,19)
b.txt('تحریریهٔ روزپوش  ·  ۲۱ آبان ۱۴۰۵',1328,794,14,color=COL['muted'])
b.txt('خواندن مقاله',1328,851,16,600,COL['olive']);b.icon('arrow',1160,834,22,COL['olive'])
b.txt('بیشتر در راهنمای استایل',1376,966,28,700)
b.txt('از جزئیات کوچک شروع کن.',64,964,16,color=COL['muted'],align='left')
pw,ph=Image.open(ROOT/'assets/catalog-contact-sheet-v1.png').size
cards=[(948,2,['شلوار واید را چطور','با بالاتنهٔ ساده هماهنگ کنیم؟'],['تعادل حجم و قد لباس را در ترکیب‌های','روزمره بهتر بشناسیم.'],'۲۰ آبان ۱۴۰۵'),(506,0,['رنگ‌های خنثی؛','نقطهٔ شروع یک کمد کاربردی'],['شیری و زغالی را کنار رنگ‌های همراه','بگذاریم و ترکیب‌های تازه بسازیم.'],'۱۹ آبان ۱۴۰۵'),(64,3,['لایه‌پوشی سبک','برای روزهای خنک'],['از بافت سبک تا پیراهن ساده؛','ترکیبی راحت برای تغییر دمای روز.'],'۱۸ آبان ۱۴۰۵')]
Q=[f'0 0 {pw/2} {ph/2}',f'{pw/2} 0 {pw/2} {ph/2}',f'0 {ph/2} {pw/2} {ph/2}',f'{pw/2} {ph/2} {pw/2} {ph/2}']
for x,q,title,desc,date in cards:
    # Square image displayed with generous neutral margins; whole garment stays visible.
    b.rect(x,1004,428,280,'#E8E6E0')
    b.photo('products',x+74,1004,280,280,Q[q])
    b.txt('راهنمای استایل',x+428,1316,13,600,COL['olive'])
    for j,t in enumerate(title):b.txt(t,x+428,1354+j*34,23,600)
    for j,t in enumerate(desc):b.txt(t,x+428,1427+j*29,16,color=COL['muted'])
    b.txt(date,x+428,1493,13,color=COL['muted'])
    b.txt('خواندن مقاله',x+428,1533,15,600,COL['olive']);b.icon('arrow',x+4,1517,20,COL['olive'])
b.line(64,1564,1376,1564)
b.txt('هر ۴ مقالهٔ این دسته را دیدید.',1376,1606,15,color=COL['muted'])
b.rect(64,1583,44,40,COL['olive']);b.txt('۱',86,1610,15,600,COL['white'],'center')
footer=source.split("b.rect(0,2698,1440,262,COL['ink'])")[1].split("b.save('desktop')")[0]
b.p.append('<g transform="translate(0 -1040)">');b.rect(0,2698,1440,262,COL['ink'])
exec(compile(footer,'approved-footer','exec'));b.p.append('</g>')
(ROOT/'generated'/'article-category-desktop-v1.svg').write_text(''.join(b.p)+'</svg>',encoding='utf-8')
print('Article category artboard exported at 1440x1920.')
