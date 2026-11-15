"""Login and my-account SVG artboards only; no site/auth implementation."""
from pathlib import Path
import os
source=Path(__file__).with_name('build-design.py').read_text(encoding='utf-8')
exec(compile(source.split('# The quadrants refer')[0],'drawing-primitives','exec'))
site=ROOT
header=source.split('b=Board(1440,2960)')[1].split('b.rect(64,204,1312,626')[0]
header=header.replace("b.txt('روزپوش',1376,98,40,700)","b.p.append(f'<image href=\"{data(site/\"brand/logo.svg\",\"image/svg+xml\")}\" x=\"1188\" y=\"66\" width=\"188\" height=\"42\"/>')").replace('روزپوش؛','الین‌استایل؛')
footer=source.split("b.rect(0,2698,1440,262,COL['ink'])")[1].split("b.save('desktop')")[0]
footer=footer.replace("b.txt('روزپوش',1376,2775,38,700,COL['bg'])","b.p.append(f'<image href=\"{data(site/\"brand/logo-light.svg\",\"image/svg+xml\")}\" x=\"1188\" y=\"2733\" width=\"188\" height=\"42\"/>')").replace('روزپوش','الین‌استایل')
def start(height,title,desc):
    global b
    b=Board(1440,height);b.p[0]=b.p[0].replace('روزپوش — طرح صفحه اصلی','elinstyle — '+title)
    exec(compile(header,'existing-header','exec'),globals())
    b.line(64,194,1376,194);b.txt('خانه  /  حساب کاربری',1376,236,14,color=COL['muted'])
    b.txt(title,1376,310,42,700);b.txt(desc,1376,353,18,color=COL['muted'])
def end(name,height):
    b.p.append(f'<g transform="translate(0 {height-2960})">');b.rect(0,2698,1440,262,COL['ink'])
    exec(compile(footer,'existing-footer','exec'),globals());b.p.append('</g>')
    (ROOT/'generated'/f'{name}-desktop-v1.svg').write_text(''.join(b.p)+'</svg>',encoding='utf-8')
def cta(label,x,y,w):
    b.rect(x,y,w,54,COL['olive']);b.txt(label,x+w/2,y+35,18,600,COL['white'],'center')

start(1440,'ورود به حساب','برای دیدن سفارش‌ها و مدیریت اطلاعات حسابت وارد شو.')
b.photo('hero',64,424,684,420,'0 0 1536 1024');b.p[-1]=b.p[-1].replace('xMidYMid slice','xMidYMin slice')
b.latin('YOUR EVERYDAY, YOUR WAY.',64,891,12)
b.txt('همراه انتخاب‌های روزمرهٔ تو.',748,956,30,700)
b.txt('سفارش‌هایت را مرور کن، نشانی‌ها را مدیریت کن',748,1005,20)
b.txt('و هر وقت خواستی به فروشگاه برگرد.',748,1041,20)
b.rect(804,424,572,674,COL['white'])
b.txt('دوباره خوش آمدی',1344,482,27,700)
b.txt('با اطلاعات حساب الین‌استایل وارد شو.',1344,519,16,color=COL['muted'])
b.txt('نام کاربری یا ایمیل *',1344,581,17,600)
b.rect(836,601,508,54,COL['white'],'#818775');b.txt('نام کاربری یا ایمیل را وارد کن',1328,635,15,color=COL['muted'])
b.txt('رمز عبور *',1344,706,17,600)
b.rect(836,726,508,54,COL['white'],'#818775');b.txt('رمز عبورت را وارد کن',1328,760,15,color=COL['muted'])
b.p.append('<g transform="translate(852 741)" fill="none" stroke="#64675E" stroke-width="1.5"><path d="M1 12Q12-2 23 12Q12 26 1 12Z"/><circle cx="12" cy="12" r="3"/></g>')
b.rect(1326,815,18,18,COL['white'],'#818775');b.txt('مرا به خاطر بسپار',1313,830,15)
b.txt('رمزت را فراموش کرده‌ای؟',836,830,15,600,COL['olive'],'left')
cta('ورود به حساب',836,874,508)
b.line(836,970,1344,970)
b.txt('هنوز حساب نداری؟',1344,1016,16)
b.txt('ساخت حساب',836,1016,16,600,COL['olive'],'left')
b.txt('ادامهٔ خرید بدون ورود',1344,1065,14,600,COL['olive'])
end('login',1440)

start(1600,'حساب کاربری','اطلاعات این حساب و سفارش، نمونهٔ نمایشی است.')
b.rect(1088,424,288,644,COL['white'])
b.p.append('<circle cx="1324" cy="480" r="24" fill="#EAE6DA"/>');b.txt('ک',1324,489,24,700,align='center')
b.txt('کاربر نمونه',1282,487,19,600)
b.line(1112,527,1352,527)
for i,(label,active) in enumerate([('پیشخوان',True),('سفارش‌ها',False),('نشانی‌ها',False),('جزئیات حساب',False),('خروج از حساب',False)]):
    y=550+i*72
    if active:b.rect(1112,y,240,52,COL['olive'])
    b.txt(label,1328,y+34,17,600 if active else 400,COL['white'] if active else COL['ink'])
b.txt('بازگشت به فروشگاه',1352,1009,15,600,COL['olive'])

b.txt('سلام، کاربر نمونه',1040,465,30,700)
b.txt('از اینجا سفارش‌ها و اطلاعات حسابت را مدیریت کن.',1040,504,18,color=COL['muted'])
for x,title,desc,link in [(564,'سفارش‌ها','وضعیت و جزئیات سفارش‌هایت را ببین.','دیدن سفارش‌ها'),(64,'نشانی‌ها','نشانی ارسال و صورتحساب را مدیریت کن.','مدیریت نشانی‌ها')]:
    b.rect(x,541,476,158,COL['sand']);b.txt(title,x+448,585,23,600);b.txt(desc,x+448,624,16,color=COL['muted']);b.txt(link,x+448,667,15,600,COL['olive']);b.icon('arrow',x+28,649,20,COL['olive'])
b.txt('سفارش‌های اخیر',1040,761,27,700)
b.txt('دیدن همهٔ سفارش‌ها',64,760,15,600,COL['olive'],'left')
b.rect(64,792,976,340,COL['white'])
for label,x,align in [('شمارهٔ سفارش',1008,'right'),('تاریخ',802,'right'),('وضعیت',590,'right'),('مبلغ سفارش',96,'left')]:b.txt(label,x,831,14,600,COL['muted'],align)
b.line(96,850,1008,850)
b.txt('۱۰۴۲',1008,890,18,600);b.txt('۱۴۰۵/۰۸/۲۳',802,890,16)
b.rect(404,863,186,38,'#FFF3D8');b.txt('در انتظار پرداخت',497,888,15,600,'#705017','center')
b.txt('۱٬۵۷۰٬۰۰۰ تومان',96,890,18,600,align='left')
b.txt('مشاهدهٔ سفارش',1008,946,15,600,COL['olive'])
b.rect(696,916,140,44,COL['bg'],COL['olive']);b.txt('پرداخت',766,945,15,600,COL['olive'],'center')
b.txt('سفارش نمونه؛ پرداخت تأیید نشده است.',96,944,13,color=COL['muted'],align='left')
b.line(96,983,1008,983)
pw,ph=Image.open(ROOT/'assets/catalog-contact-sheet-v1.png').size
b.photo('products',912,1004,96,96,f'0 0 {pw/2} {ph/2}')
b.txt('پیراهن آزاد زنانه',888,1037,19,600);b.txt('شیری / M  ·  ۱ عدد',888,1074,15,color=COL['muted'])
b.txt('کالا: ۱٬۴۹۰٬۰۰۰ + ارسال نمونه: ۸۰٬۰۰۰ تومان',96,1056,14,color=COL['muted'],align='left')
b.rect(64,1180,976,100,COL['pale']);b.txt('دربارهٔ سفارشت پرسشی داری؟',1008,1221,21,600);b.txt('شمارهٔ سفارش را در پیام بنویس.',1008,1254,16,color=COL['muted'])
b.txt('تماس با ما',96,1239,16,600,COL['olive'],'left')
end('my-account',1600)
print('Login 1440x1440 and my-account 1440x1600 designs exported.')
