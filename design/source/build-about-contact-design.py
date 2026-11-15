"""Two static SVG artboards for review. No previous page exports are executed."""
from pathlib import Path
import os
source=Path(__file__).with_name('build-design.py').read_text(encoding='utf-8')
exec(compile(source.split('# The quadrants refer')[0],'drawing-primitives','exec'))
header=source.split('b=Board(1440,2960)')[1].split('b.rect(64,204,1312,626')[0]
footer=source.split("b.rect(0,2698,1440,262,COL['ink'])")[1].split("b.save('desktop')")[0]
def start(height,title):
    global b
    b=Board(1440,height)
    b.p[0]=b.p[0].replace('روزپوش — طرح صفحه اصلی',f'روزپوش — {title}')
    exec(compile(header,'approved-header','exec'),globals())
    b.line(64,194,1376,194)
    b.txt('خانه  /  '+title,1376,234,14,color=COL['muted'])
def end(name,height):
    b.p.append(f'<g transform="translate(0 {height-2960})">')
    b.rect(0,2698,1440,262,COL['ink'])
    exec(compile(footer,'approved-footer','exec'),globals())
    b.p.append('</g>')
    (ROOT/'generated'/f'{name}-desktop-v1.svg').write_text(''.join(b.p)+'</svg>',encoding='utf-8')
def para(text,x,y,size=19):
    for i,t in enumerate(text):b.txt(t,x,y+i*35,size)

start(2150,'دربارهٔ ما')
b.txt('همراه روزهای تو.',1376,316,46,700)
b.txt('روزپوش؛ نگاهی ساده‌تر به پوشیدن لباس‌های روزمره.',1376,357,20,color=COL['muted'])
b.photo('hero',64,402,656,500,'0 0 1536 1024');b.p[-1]=b.p[-1].replace('xMidYMid slice','xMidYMin slice')
b.latin('MADE FOR EVERYDAY MOMENTS',784,434,12)
b.txt('لباس برای زندگی،',1376,512,34,700)
b.txt('با سلیقهٔ خودت.',1376,560,34,700)
para(['بعضی لباس‌ها بی‌سروصدا بخشی از روز ما می‌شوند؛',
      'پیراهنی که با آن راحتیم، شلواری که خوب می‌نشیند',
      'یا بافتی که به ترکیب‌های آشنا حس تازه‌ای می‌دهد.',
      'نگاه روزپوش از همین لحظه‌های کوچک شروع می‌شود.'],1376,624)
para(['در این نگاه، پوشاک زنانه و مردانه قرار است به کار',
      'زندگی روزمره بیاید؛ ساده برای ترکیب کردن و همراه',
      'با فضایی برای نشان دادن سلیقهٔ شخصی تو.'],1376,804)
b.txt('سه اصل برای انتخابی ساده‌تر',1376,994,30,700)
for x,n,t,body in [(948,'۰۱','سادگی',['رنگ‌های آرام و جزئیات به‌اندازه؛','لباس‌هایی که راحت کنار هم می‌نشینند.']), (506,'۰۲','کاربرد روزمره',['از قرار کوتاه تا یک روز پُررفت‌وآمد؛','انتخاب را با ریتم روزت هماهنگ کن.']), (64,'۰۳','توجه به تن‌خور',['به فرم، اندازه و آزادی حرکت دقت کن؛','لباس خوب باید روی تن تو راحت باشد.'])]:
    b.rect(x,1032,428,210,COL['sand'])
    b.txt(n,x+400,1074,15,600,COL['olive'])
    b.txt(t,x+400,1124,25,600)
    for i,s in enumerate(body):b.txt(s,x+400,1168+i*31,16,color=COL['muted'])
b.line(64,1300,1376,1300)
b.txt('از نگاه اول تا انتخاب تو',1376,1360,30,700)
para(['یک انتخاب خوب از شناخت نیاز شروع می‌شود.',
      'پیش از اضافه کردن لباس تازه، به کمدی که داری',
      'و موقعیت‌هایی که پیش روست فکر کن.'],1376,1410)
b.txt('کمتر، بهتر، ماندگارتر.',1376,1576,20,600,COL['olive'])
for i,(title,desc) in enumerate([('نیازت را مشخص کن','لباس را برای روز و موقعیتی که در پیش داری انتخاب کن.'),('اندازه و جزئیات را ببین','راهنمای اندازه، فرم لباس و توضیحات آن را کنار هم بخوان.'),('ترکیب خودت را بساز','با لباس‌های کمدت مقایسه کن و جای تکهٔ تازه را پیدا کن.')]):
    y=1398+i*82
    b.txt('۰'+str(i+1).translate(str.maketrans('123','۱۲۳')),700,y,16,600,COL['olive'])
    b.txt(title,650,y,21,600)
    b.txt(desc,650,y+32,16,color=COL['muted'])
b.rect(64,1694,1312,116,COL['olive'])
b.txt('ترکیب بعدی‌ات از یک انتخاب ساده شروع می‌شود.',1328,1740,24,600,COL['white'])
b.txt('نگاهی به لباس‌های روزمره بینداز.',1328,1778,17,400,'#E1E6D7')
b.button('دیدن فروشگاه',96,1728,204,True)
end('about',2150)

start(1620,'تماس با ما')
b.txt('گفت‌وگو از اینجا شروع می‌شود.',1376,316,42,700)
b.txt('برای انتخاب لباس یا پرسشی دربارهٔ خرید، پیامت را با ما در میان بگذار.',1376,357,19,color=COL['muted'])
b.txt('راه‌های ارتباطی',1376,449,25,700)
b.txt('اطلاعات تماس نمونه است.',1376,483,14,color=COL['muted'])
b.line(944,512,1376,512)
b.txt('ایمیل',1376,555,16,600)
b.p.append('<text x="944" y="597" font-family="Yekan" font-size="25" fill="#4D593C">hello@roozpoosh.example</text>')
b.line(944,632,1376,632)
b.txt('تلفن',1376,674,16,600)
b.p.append('<text x="944" y="716" font-family="Yekan" font-size="24" fill="#242622">021 — •••• ••••</text>')
b.line(944,752,1376,752)
b.txt('ساعت پاسخ‌گویی نمونه',1376,796,17,600)
b.txt('شنبه تا چهارشنبه، ۹ تا ۱۷',1376,835,20)
b.txt('به وقت تهران',1376,867,14,color=COL['muted'])
b.rect(944,922,432,154,COL['pale'])
b.txt('دربارهٔ سفارشت سؤال داری؟',1352,963,20,600)
b.txt('شمارهٔ سفارش را در پیام بنویس.',1352,1000,17)
b.txt('اطلاعات کارت بانکی را ارسال نکن.',1352,1034,15,color=COL['muted'])

b.rect(64,416,792,690,COL['white'])
b.txt('برای ما بنویس',816,465,27,700)
b.txt('فیلدهای ستاره‌دار ضروری‌اند.',816,498,14,color=COL['muted'])
def field(label,x,y,w,h=52,hint=''):
    b.txt(label,x+w,y,16,600)
    b.rect(x,y+16,w,h,COL['white'],'#818775')
    if hint:b.txt(hint,x+w-16,y+49,15,color=COL['muted'])
field('نام شما *',464,543,352,hint='نام و نام خانوادگی')
field('ایمیل یا شمارهٔ تماس *',104,543,336,hint='یک راه برای پاسخ به پیام')
field('موضوع پیام *',104,654,712,hint='موضوع را انتخاب کنید')
b.p.append('<path d="M124 692l6 6 6-6" fill="none" stroke="#242622" stroke-width="1.5"/>')
field('پیام شما *',104,765,712,160,'چطور می‌توانیم کمکت کنیم؟')
b.txt('راه ارتباطی را برای پاسخ به همین پیام وارد کن.',816,984,14,color=COL['muted'])
b.button('ارسال پیام',612,1016,204)

b.line(64,1164,1376,1164)
b.txt('شاید پاسخ اینجا باشد',1376,1220,26,700)
b.txt('پیش از نوشتن پیام، راهنمای خرید را هم ببین.',1376,1257,17,color=COL['muted'])
b.txt('پرسش‌های متداول',96,1218,17,600,COL['olive'],'left');b.icon('arrow',70,1202,20,COL['olive'])
b.txt('پیگیری سفارش',510,1218,17,600,COL['olive'],'left');b.icon('arrow',484,1202,20,COL['olive'])
b.txt('راهنمای اندازه',96,1261,17,600,COL['olive'],'left');b.icon('arrow',70,1245,20,COL['olive'])
end('contact',1620)
print('About 1440x2150 and contact 1440x1620 SVG artboards created.')
