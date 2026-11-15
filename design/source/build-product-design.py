"""Desktop product design only. Reads local assets; never writes site code."""
from pathlib import Path
import os
source=Path(__file__).with_name('build-design.py').read_text(encoding='utf-8')
exec(compile(source.split('# The quadrants refer')[0],'drawing-primitives','exec'))
b=Board(1440,2500)
b.p[0]=b.p[0].replace('روزپوش — طرح صفحه اصلی','elinstyle — محصول تکی')
site=ROOT
header=source.split('b=Board(1440,2960)')[1].split('b.rect(64,204,1312,626')[0]
header=header.replace("b.txt('روزپوش',1376,98,40,700)","b.p.append(f'<image href=\"{data(site/\"brand/logo.svg\",\"image/svg+xml\")}\" x=\"1188\" y=\"66\" width=\"188\" height=\"42\"/>')").replace('روزپوش؛','الین‌استایل؛')
exec(compile(header,'elinstyle-header','exec'))
b.line(64,194,1376,194)
b.txt('خانه  /  فروشگاه  /  زنانه  /  پیراهن آزاد زنانه',1376,237,14,color=COL['muted'])
pw,ph=Image.open(ROOT/'assets/catalog-contact-sheet-v1.png').size
Q=[f'0 0 {pw/2} {ph/2}',f'{pw/2} 0 {pw/2} {ph/2}',f'0 {ph/2} {pw/2} {ph/2}',f'{pw/2} {ph/2} {pw/2} {ph/2}']
b.photo('products',64,282,700,700,Q[0])
b.rect(80,298,42,42,COL['bg'],r=21);b.icon('heart',91,310,20)
for x,img,v,label in [(648,'products',Q[0],'نمای روبه‌رو'),(510,'products',f'{pw*.16} {ph*.025} {pw*.21} {ph*.21}','جزئیات تصویر'),(372,'hero','240 65 570 690','ایدهٔ استایل')]:
    b.photo(img,x,1006,116,116,v)
    if img=='hero':b.p[-1]=b.p[-1].replace('xMidYMid slice','xMidYMin slice')
    if x==648:b.rect(x-3,1003,122,122,'none',COL['olive'])
    b.txt(label,x+58,1154,13,color=COL['muted'],align='center')
b.txt('زنانه / پیراهن',1376,303,14,600,COL['olive'])
b.txt('پیراهن آزاد زنانه',1376,359,38,700)
b.txt('پنبه‌ای، شیری؛ همراه ترکیب‌های سادهٔ روزمره.',1376,399,18,color=COL['muted'])
b.rect(1294,430,82,28,COL['pale']);b.txt('۱۲٪ تخفیف',1335,450,13,600,COL['olive'],'center')
b.txt('۱٬۶۹۰٬۰۰۰',1266,451,17,color=COL['muted']);b.line(1152,445,1266,445,COL['muted'])
b.txt('۱٬۴۹۰٬۰۰۰ تومان',1376,504,30,700)
b.txt('قیمت و موجودی این طرح نمونه‌اند.',1376,539,13,color=COL['muted'])
b.line(820,562,1376,562)
b.txt('رنگ: شیری',1376,599,18,600)
b.rect(1252,618,124,44,COL['bg'],COL['olive'])
b.p.append('<circle cx="1355" cy="640" r="8" fill="#EDE7D6" stroke="#818775"/>');b.txt('شیری',1337,646,15,600);b.icon('check',1266,630,18,COL['olive'])
b.txt('اندازه: M',1376,711,18,600)
b.txt('راهنمای اندازه',820,711,15,600,COL['olive'],'left')
for x,label,sel,disabled in [(1312,'S',False,False),(1230,'M',True,False),(1148,'L',False,False),(1066,'XL',False,True)]:
    b.rect(x,732,64,48,COL['olive'] if sel else '#DFE2D7' if disabled else COL['bg'],COL['olive'] if sel else COL['line'])
    b.p.append(f'<text x="{x+32}" y="763" text-anchor="middle" font-family="Yekan" font-size="17" fill="{COL["white"] if sel else COL["muted"] if disabled else COL["ink"]}">{label}</text>')
    if disabled:b.line(x+8,772,x+56,740,'#818775')
b.txt('XL در این رنگ ناموجود است.',1376,811,13,color=COL['muted'])
b.txt('پاک‌کردن انتخاب',820,811,13,600,COL['olive'],'left')
b.icon('check',1354,835,21,COL['olive']);b.txt('انتخاب نمونه موجود است',1326,854,15,600,COL['olive'])
b.txt('تعداد',1376,900,15,600)
b.rect(1220,917,156,48,COL['bg'],'#818775')
b.txt('−',1352,949,24,align='center');b.txt('۱',1298,949,18,600,align='center');b.txt('+',1244,949,24,align='center')
b.rect(820,986,556,56,COL['olive']);b.txt('افزودن به سبد خرید',1098,1022,19,600,COL['white'],'center')
b.txt('ارسال و شرایط تعویض را پیش از خرید بررسی کن.',1376,1085,15,color=COL['muted'])
b.line(64,1200,1376,1200)
b.txt('دربارهٔ این لباس',1376,1261,28,700)
for j,t in enumerate(['یک پیراهن با فرم آزاد برای ترکیب‌های روزمره؛','رنگ شیری آن کنار شلوار زغالی یا بافت ساده می‌نشیند.','برای انتخاب اندازه، به دور سینه و فرم دلخواهت توجه کن.']):b.txt(t,1376,1305+j*35,18)
b.txt('ارسال و تعویض',1376,1467,23,600)
for j,t in enumerate(['روش و هزینهٔ ارسال در مرحلهٔ تسویه مشخص می‌شود.','جزئیات ارسال و امکان تعویض را در راهنمای خرید ببین.','پیش از ثبت سفارش، شرایط خرید را بخوان.']):b.txt(t,1376,1508+j*32,16,color=COL['muted'])
b.txt('مشخصات نمونه',684,1261,23,600)
for j,(key,value) in enumerate([('جنس','پنبه‌ای'),('تن‌خور','آزاد'),('رنگ فعلی','شیری'),('اندازهٔ انتخاب‌شده','M'),('نگهداری','طبق برچسب مراقبت لباس')]):
    y=1304+j*53;b.txt(key,684,y,16,color=COL['muted']);b.txt(value,64,y,16,600,align='left');b.line(64,y+18,684,y+18)
b.line(64,1644,1376,1644)
b.txt('برای کامل کردن ترکیبت',1376,1711,30,700)
for x,i,title,sub,price in [(948,2,'شلوار واید زنانه','پارچه‌ای · زغالی','۱٬۶۹۰٬۰۰۰ تومان'),(506,3,'بافت ساده روزمره','بافت ریز · جوگندمی','۱٬۳۹۰٬۰۰۰ تومان'),(64,1,'اورشرت مردانه','کتان · زیتونی','۱٬۸۹۰٬۰۰۰ تومان')]:
    b.rect(x,1754,428,280,'#E8E6E0');b.photo('products',x+74,1754,280,280,Q[i]);b.txt(title,x+428,2075,21,600);b.txt(sub,x+428,2106,15,color=COL['muted']);b.txt(price,x+428,2140,17,600)
footer=source.split("b.rect(0,2698,1440,262,COL['ink'])")[1].split("b.save('desktop')")[0]
footer=footer.replace("b.txt('روزپوش',1376,2775,38,700,COL['bg'])","b.p.append(f'<image href=\"{data(site/\"brand/logo-light.svg\",\"image/svg+xml\")}\" x=\"1188\" y=\"2733\" width=\"188\" height=\"42\"/>')").replace('روزپوش','الین‌استایل')
b.p.append('<g transform="translate(0 -460)">');b.rect(0,2698,1440,262,COL['ink']);exec(compile(footer,'elinstyle-footer','exec'));b.p.append('</g>')
(ROOT/'generated'/'product-desktop-v1.svg').write_text(''.join(b.p)+'</svg>',encoding='utf-8')
print('Product design exported; no site code changed.')
