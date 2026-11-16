"""Classic Elementor layouts. Executed by build-elementor.py; no runtime site IDs."""
BG,INK,MUTED,OLIVE,SAND,LINE,WHITE='#F7F5EF','#242622','#64675E','#4D593C','#EAE6DA','#DADCD2','#FFFFFF'
def px(n):return {'unit':'px','size':n}
def pct(n):return {'unit':'%','size':n}
def gap(n):return {'unit':'px','size':n,'column':str(n),'row':str(n),'isLinked':True}
def typo(size=16,weight='400',prefix='typography',line=1.8):return {prefix+'_typography':'custom',prefix+'_font_family':'Yekan',prefix+'_font_size':px(size),prefix+'_font_weight':weight,prefix+'_line_height':{'unit':'em','size':line}}
def h(value,level=2,size=None,mobile=None,color=INK,**extra):
    size=size or {1:42,2:34,3:23}.get(level,20)
    return widget('heading',{'title':value,'header_size':'h'+str(level),'title_color':color,'align':'right',**typo(size,'700',line=1.4),'typography_font_size_mobile':px(mobile or min(size,28)),**extra})
def t(value,size=16,color=MUTED,**extra):return widget('text-editor',{'editor':value,'text_color':color,'align':'right',**typo(size),**extra})
def c(children,**settings):return container(children,settings)
def col(children,width=100,**settings):return c(children,**{'width':pct(width),'width_mobile':pct(100),**settings})
def row(children,space=24,**settings):return c(children,**{'flex_direction':'row','flex_direction_mobile':'column','flex_gap':gap(space),**settings})
def wrap(children,top=0,bottom=0,**settings):return c(children,content_width='boxed',boxed_width=px(1312),padding=dims(top,64,bottom,64),padding_mobile=dims(min(top,32),20,min(bottom,40),20),**settings)
def btn(label,url='shop.html',plain=False,color=None,popup=False,**extra):
    s={'text':label,'link':{'url':link(url)},'align':'right','background_color':'#00000000' if plain else OLIVE,'button_text_color':color or (INK if plain else BG),'hover_color':color or (OLIVE if plain else WHITE),'button_background_hover_color':'#00000000' if plain else '#3E4930','border_radius':dims(),'text_padding':dims(0,0,0,0) if plain else dims(12,20,12,20),**typo(16,'600'),'_css_classes':'elin-link' if plain else 'elin-button',**extra}
    if popup:s['elinstyle_popup_link']=True
    return widget('button',s)
def img(url,**extra):
    w=image(url);w['settings'].update({'align':'right','link_to':'none','image_size':'full',**extra});return w
def logo(light=False):return img('assets/brand/logo-light.svg' if light else 'assets/brand/logo.svg',width=px(188),width_mobile=px(156),_element_width='initial',_element_custom_width=px(188),_element_custom_width_mobile=px(156),link_to='custom',link={'url':'{{page:home}}'},_css_classes='elin-logo')
def page_heading(title,description='',crumb='خانه / فروشگاه'):
    return wrap([t(crumb,14,_margin=dims(0,0,24,0)),h(title,1,mobile=32),t(description,20,typography_font_size_mobile=px(17),_margin=dims(16,0,24,0))] if description else [t(crumb,14,_margin=dims(0,0,24,0)),h(title,1,mobile=32)],top=24)
def crop_image(kind='editorial',pos='center center',height=400,width=100,**extra):
    return col([],width,**{'background_background':'classic','background_image':{'url':'{{image:'+kind+'}}'},'background_position':pos,'background_size':'cover','min_height':px(height),'min_height_mobile':px(min(height,300)),**extra})

# Header: native image, Pro Search, native buttons and Pro Nav Menu. No fixed-position CSS.
announce=t('الین‌استایل؛ برای روزهای معمولی دوست‌داشتنی',13,WHITE,align='center',typography_font_size_mobile=px(11))
search=widget('search-form',{'skin':'minimal','placeholder':'دنبال چه لباسی می‌گردید؟','input_text_color':MUTED,'input_background_color':'#EEEDE6','input_border_color':'#EEEDE6','size':px(48),**typo(16,prefix='input_typography'),'_element_width':'initial','_element_custom_width':px(500),'_element_custom_width_mobile':pct(100),'_flex_order_mobile':'end','_css_classes':'elin-search'})
account_links=row([btn('حساب کاربری','login.html',plain=True),btn('سبد خرید','cart.html',plain=True)],space=24,width=pct(25),width_mobile=pct(100),flex_direction_mobile='row',flex_justify_content='flex-end',flex_justify_content_mobile='space-between')
header_top=wrap([row([logo(),search,account_links],space=28,flex_direction_mobile='row',flex_wrap='wrap',flex_justify_content='space-between',flex_align_items='center')],top=24,bottom=24)
menu=widget('nav-menu',{'menu':'{{menu}}','layout':'horizontal','align_items':'right','pointer':'none','color_menu_item':INK,'color_menu_item_hover':OLIVE,'color_menu_item_active':OLIVE,'padding_horizontal_menu_item':px(20),'padding_vertical_menu_item':px(18),**typo(16,'600','menu_typography'),'breakpoint':'mobile','toggle':'hamburger','toggle_align':'right','toggle_color':INK,'background_color_dropdown_item':BG,'color_dropdown_item':INK,**typo(16,prefix='dropdown_typography'),'_element_width':'initial','_element_custom_width':pct(75),'_element_custom_width_mobile':pct(55)})
nav=wrap([row([menu,btn('راهنمای اندازه',plain=True,popup=True,_element_width='initial')],space=0,flex_direction_mobile='row',flex_justify_content='space-between',flex_align_items='center',border_border='solid',border_width=dims(1,0,1,0),border_color=LINE)])
templates['header']={'content':[c([announce],background_background='classic',background_color=OLIVE,padding=dims(5,20,5,20)),header_top,nav],'page_settings':{}}

# Home retains every approved section and editable image/link/category structure.
hero_copy=col([t('THE EVERYDAY EDIT / 01',13,align='left',hide_mobile='hidden-mobile'),t('مجموعهٔ روزمره / ۰۱',12,hide_desktop='hidden-desktop',hide_tablet='hidden-tablet'),h('سادگی،<br>به سبک تو.',1,66,38,_margin=dims(26,0,12,0)),t('لباس‌هایی برای خودِ واقعی تو؛<br>راحت، خوش‌دوخت و همراه هر روز.',20,typography_font_size_mobile=px(17)),btn('کشف مجموعه',_margin=dims(24,0,0,0)),t('زنانه و مردانه<br>کمتر، بهتر، ماندگارتر.',14,_margin=dims(28,0,0,0),hide_mobile='hidden-mobile')],40,padding=dims(42,56,30,56),padding_mobile=dims(24,20,32,20),flex_justify_content='center')
hero_picture=col([img('assets/images/editorial.png',height=px(626),height_mobile=px(326),**{'object-fit':'cover','object-position':'center center'})],60)
hero=wrap([row([hero_copy,hero_picture],space=0,background_background='classic',background_color=SAND,_css_classes='elin-hero')],top=32)
category_cards=[]
for i,(label,sub,category) in enumerate([('زنانه','فرم‌های آزاد، حس خوب','women'),('مردانه','برای ریتم روزهای تو','men'),('لباس‌های پایه','همیشه به‌دردبخور','basics')]):
    photo=crop_image('editorial' if i<2 else 'catalog','30% center' if i==0 else ('85% center' if i==1 else 'right bottom'),400,background_size='custom',background_bg_width=pct(200 if i==2 else 210),min_height_mobile=px(214))
    category_cards.append(col([photo,h(label,3,28,20,_margin=dims(16,0,0,0)),t(sub,16,hide_mobile='hidden-mobile')],32,html_tag='a',link={'url':'{{page:shop}}?category='+category},width_mobile=pct(48),_css_classes='elin-category'))
categories=wrap([t('01 — CURATED FOR EVERY DAY',13,align='left',hide_mobile='hidden-mobile'),h('کمدت را از اینجا بساز'),t('انتخاب‌هایی ساده، برای هزار جور خودت بودن.',19,_margin=dims(8,0,28,0)),row(category_cards,space=24,flex_direction_mobile='row',flex_wrap='wrap',flex_justify_content='space-between',flex_gap_mobile=gap(14))],top=60,bottom=56)
products=widget('woocommerce-products',{'columns':4,'columns_mobile':2,'rows':1,'paginate':'','products_post_type':'product','products_posts_per_page':4,'products_orderby':'menu_order','products_order':'ASC','show_result_count':'','show_ordering':'','show_onsale_flash':'','align':'right','column_gap':px(24),'row_gap':px(28),'title_color':INK,**typo(19,'600','title_typography'),'title_typography_font_size_mobile':px(15),'price_color':INK,**typo(16,'600','price_typography'),'button_text_color':WHITE,'button_background_color':OLIVE,'button_border_radius':dims(),'_css_classes':'elin-featured-products'})
featured=wrap([row([h('منتخب این روزها'),btn('دیدن همهٔ محصولات',plain=True)],space=12,flex_justify_content='space-between',flex_align_items='center'),products],top=32,bottom=60,flex_gap=gap(28),border_border='solid',border_width=dims(1,0,0,0),border_color=LINE)
campaign=wrap([row([col([t('LESS, BUT BETTER.',13,OLIVE),h('یک کمد ساده.<br>ترکیب‌های بی‌شمار.',2,48,34,_margin=dims(24,0,12,0)),t('با رنگ‌های خنثی، هر روز یک ترکیب تازه بساز.',19),btn('دیدن لباس‌های پایه','shop.html?category=basics',_margin=dims(24,0,0,0))],55,padding=dims(48,56,48,56),padding_mobile=dims(24,20,32,20),flex_justify_content='center'),crop_image('catalog','right bottom',440,45,background_size='custom',background_bg_width=pct(200),_css_classes='elin-campaign-photo')],space=0,background_background='classic',background_color=SAND)],_css_classes='elin-campaign')
benefits=[]
for title,description,icon in [('انتخاب اندازهٔ مطمئن','راهنمای اندازه برای هر لباس','fas fa-check'),('خرید با خیال راحت','شرایط تعویض روشن و ساده','fas fa-undo'),('ارسال قابل پیگیری','همراه سفارش، تا رسیدن به تو','fas fa-box'),('پاسخ‌گویی همراه','کمک در انتخاب و خرید','far fa-comment')]:
    benefits.append(col([widget('icon',{'selected_icon':{'value':icon,'library':'fa-solid' if icon.startswith('fas') else 'fa-regular'},'primary_color':OLIVE,'size':px(28),'align':'right'}),h(title,3,19,15,_margin=dims(14,0,6,0)),t(description,15,typography_font_size_mobile=px(12))],23,width_mobile=pct(47)))
templates['home']={'content':[hero,categories,featured,campaign,wrap([row(benefits,space=24,flex_direction_mobile='row',flex_wrap='wrap',flex_justify_content='space-between',flex_gap_mobile=gap(28))],top=64,bottom=72)],'page_settings':{'hide_title':'yes'}}

# Footer links remain links; no green button pile or full-width logo.
footer_columns=[col([logo(True),t('پوشاک روزمره، با نگاهی ساده‌تر.<br>همراه روزهای تو.',16,BG,_margin=dims(16,0,0,0))],31)]
for title,links in [('خرید',[('زنانه','shop.html?category=women'),('مردانه','shop.html?category=men'),('لباس‌های پایه','shop.html?category=basics')]),('راهنمای خرید',[('اندازه و تن‌خور',None),('ارسال و تعویض','contact.html'),('پرسش‌های متداول','contact.html')]),('همراه الین‌استایل',[('دربارهٔ ما','about.html'),('تماس با ما','contact.html'),('پیگیری سفارش','my-account.html')])]:
    footer_columns.append(col([h(title,3,18,18,BG,_margin=dims(0,0,16,0))]+[btn(label,url or '',plain=True,color=BG,popup=url is None,**typo(15),_margin=dims(0,0,8,0)) for label,url in links],20,width_mobile=pct(46)))
templates['footer']={'content':[c([wrap([row(footer_columns,space=24,flex_direction_mobile='row',flex_wrap='wrap',flex_justify_content='space-between'),row([t('الین‌استایل © ۱۴۰۵',13,BG),t('حریم خصوصی / شرایط خرید',13,BG,align='left')],space=20,flex_direction_mobile='row',flex_justify_content='space-between',padding=dims(20,0,0,0),margin=dims(40,0,0,0),border_border='solid',border_width=dims(1,0,0,0),border_color='#4B5047')],top=40,bottom=20)],background_background='classic',background_color=INK)],'page_settings':{}}

templates['shop']={'content':[page_heading('همهٔ محصولات','انتخاب‌های ساده برای کمد روزمره؛ زنانه، مردانه و لباس‌های پایه.'),wrap([widget('elinstyle-catalog',{'mode':'shop','columns':2,'columns_mobile':2,'gap':px(32),'gap_mobile':px(14),'layout_gap_mobile':px(24)})],bottom=32),wrap([row([col([h('انتخاب اندازهٔ مطمئن',3,23,18),t('پیش از انتخاب، راهنمای اندازه و تن‌خور را ببینید.',15)],75),btn('راهنمای اندازه',plain=True,popup=True)],flex_align_items='center',background_background='classic',background_color=SAND,padding=dims(24,32,24,32))],bottom=42)],'page_settings':{'hide_title':'yes'}}

# Login uses native Pro fields and controls, with only the missing eye affordance in widget Custom CSS.
login_settings={'custom_labels':'yes','show_labels':'yes','user_label':'ایمیل یا نام کاربری','user_placeholder':'ایمیل یا نام کاربری','password_label':'رمز عبور','password_placeholder':'رمز عبور','show_remember_me':'yes','show_lost_password':'yes','show_register':'','button_text':'ورود به حساب','show_logged_in_message':'no','input_size':'lg','button_size':'lg','align':'stretch','row_gap':px(24),'label_spacing':px(10),'label_color':INK,**typo(16,'600','label_typography'),'field_text_color':INK,'field_background_color':WHITE,'field_border_color':'#818775','field_border_width':dims(1,1,1,1),'field_border_radius':dims(),**typo(16,prefix='field_typography'),'links_color':OLIVE,'links_hover_color':INK,'button_text_color':WHITE,'button_background_background':'classic','button_background_color':OLIVE,'button_background_hover_background':'classic','button_background_hover_color':'#3E4930','button_hover_color':WHITE,'button_border_radius':dims(),**typo(17,'600','button_typography'),'custom_css':'selector .elementor-field-type-text{position:relative}selector .elinstyle-password-toggle{position:absolute;left:0;bottom:0;width:54px;height:54px;border:0;background:none;color:inherit}selector input[name=password]{padding-inline-end:54px}','redirect_after_login':'yes','redirect_url':{'url':'{{page:account}}'},'_css_classes':'elin-login'}
login_card=col([h('دوباره خوش آمدی',2,29,26),t('اطلاعات حسابت را وارد کن.',16,_margin=dims(8,0,24,0)),widget('login',login_settings),btn('ساخت حساب جدید','my-account.html',plain=True,_margin=dims(24,0,0,0)),t('با ورود، می‌توانی سفارش‌ها و اطلاعات حساب خودت را مدیریت کنی.',14,_margin=dims(24,0,0,0))],44,background_background='classic',background_color=WHITE,padding=dims(32,32,28,32),padding_mobile=dims(24,20,24,20))
login_story=col([img('assets/images/editorial.png',height=px(420),height_mobile=px(260),**{'object-fit':'cover'}),h('همراه انتخاب‌های روزمرهٔ تو.',2,32,26,_margin=dims(24,0,12,0)),t('سفارش‌هایت را مرور کن، نشانی‌ها را مدیریت کن و به فروشگاه برگرد.',18),btn('بازگشت به فروشگاه',plain=True,_margin=dims(24,0,0,0))],52)
templates['login']={'content':[page_heading('ورود به حساب','برای دیدن سفارش‌ها و مدیریت اطلاعات حسابت وارد شو.','خانه / حساب کاربری'),wrap([row([login_card,login_story],space=56,flex_justify_content='space-between')],top=24,bottom=80)],'page_settings':{'hide_title':'yes'}}

for key,name in [('cart','woocommerce-cart'),('checkout','woocommerce-checkout-page'),('account','woocommerce-my-account')]:
    templates[key]={'content':[page_heading({'cart':'سبد خرید شما','checkout':'ثبت سفارش و پرداخت','account':'حساب کاربری'}[key],crumb='خانه / '+{'cart':'سبد خرید','checkout':'پرداخت','account':'حساب کاربری'}[key]),wrap([widget(name,{'typography_typography':'custom','typography_font_family':'Yekan'})],top=24,bottom=72)],'page_settings':{'hide_title':'yes'}}

templates['product']={'content':[wrap([row([col([widget('woocommerce-product-images')],56),col([widget('woocommerce-product-title',{'__dynamic__':{'title':'[elementor-tag id="elinproducttitle" name="woocommerce-product-title-tag" settings="%7B%7D"]'},'title_color':INK,**typo(38,'700',line=1.4)}),widget('woocommerce-product-price',{'price_color':OLIVE,**typo(24,'600')}),widget('woocommerce-product-short-description',{'text_color':MUTED,**typo(18)}),widget('woocommerce-product-add-to-cart'),btn('راهنمای اندازه',plain=True,popup=True),widget('woocommerce-product-meta')],40,flex_gap=gap(24))],space=48,flex_justify_content='space-between')],top=40,bottom=48),wrap([widget('woocommerce-product-data-tabs'),h('کنار این انتخاب',_margin=dims(40,0,24,0)),widget('woocommerce-product-related',{'columns':4,'columns_mobile':2,'posts_per_page':4})],bottom=72)],'page_settings':{'hide_title':'yes'}}

# Refine remaining semantic source conversions using native parent/child controls.
def refine(nodes,parent=''):
    for n in nodes:
        s=n['settings'];cl=s.get('_css_classes','');kids=n['elements']
        if n['elType']=='container':
            if 'elin-el-wrap' in cl:s.update(content_width='boxed',boxed_width=px(1312),padding=dims(24,64,40,64),padding_mobile=dims(24,20,32,20))
            if 'elin-el-page-heading' in cl:
                s.update(flex_gap=gap(16),padding=dims(24,64,32,64))
            if any(x in cl for x in ['elin-el-contact','elin-el-reading-layout','elin-el-journey']):s.update(flex_direction='row',flex_direction_mobile='column',flex_gap=gap(64))
            if 'elin-el-about-story' in cl:
                s.update(flex_gap=gap(64),background_color=BG)
                for k in kids:k['settings'].update(width=pct(48),width_mobile=pct(100))
            if 'elin-el-contact' in cl:
                for k in kids:k['settings'].update(width=pct(46),width_mobile=pct(100))
            if 'elin-el-principles' in cl and parent.endswith('elin-el-principles'):
                s.update(flex_direction='row',flex_wrap='wrap',flex_gap=gap(16))
            if parent.endswith('elin-el-principles') and not cl:
                s.update(flex_direction='row',flex_direction_mobile='column',flex_gap=gap(16))
                for k in kids:k['settings'].update(width=pct(32),width_mobile=pct(100),background_background='classic',background_color=SAND,padding=dims(24,28,24,28))
            if 'elin-el-reading-layout' in cl:
                for i,k in enumerate(kids):k['settings'].update(width=pct(23 if i==0 else 72),width_mobile=pct(100))
            if 'elin-el-article-shell' in cl:s.update(padding=dims(24,64,64,64),padding_mobile=dims(24,20,40,20))
            if 'elin-el-exercise' in cl:s.update(background_background='classic',background_color='#E8ECDF',padding=dims(20,24,20,24),flex_gap=gap(12))
            if 'elin-el-about-banner' in cl:s.update(background_background='classic',background_color=OLIVE,padding=dims(24,32,24,32),flex_gap=gap(16))
            if 'elin-el-contact-help' in cl:s.update(flex_direction='row',flex_direction_mobile='column',flex_gap=gap(48),padding=dims(32,0,64,0))
        elif n.get('widgetType')=='heading':
            s.update(align='right',typography_line_height={'unit':'em','size':1.4},typography_font_size_mobile=px(32 if s.get('header_size')=='h1' else 26))
            if 'elin-el-about-banner' in parent:s['title_color']=WHITE
        elif n.get('widgetType')=='form':
            s.update(button_text='بررسی فرم',field_background_color=WHITE,field_text_color=INK,label_color=INK,button_background_color=OLIVE,button_text_color=WHITE,**typo(16,prefix='field_typography'),**typo(16,'600','label_typography'),row_gap=px(24))
        refine(kids,cl)
for key in ['about','article','contact']:refine(templates[key]['content'])

kit=templates['kit']['page_settings']
kit.update(system_colors=[{'_id':'primary','title':'زیتونی','color':OLIVE},{'_id':'secondary','title':'متن کم‌رنگ','color':MUTED},{'_id':'text','title':'متن','color':INK},{'_id':'accent','title':'تأکید','color':OLIVE}],custom_colors=[{'_id':'elinbg','title':'زمینهٔ روشن','color':BG},{'_id':'elinsand','title':'ماسه','color':SAND},{'_id':'elinline','title':'خطوط','color':LINE}],system_typography=[{'_id':key,'title':title,**typo(size,weight)} for key,title,size,weight in [('primary','عنوان اصلی',42,'700'),('secondary','عنوان بخش',34,'700'),('text','متن',16,'400'),('accent','دکمه و تأکید',16,'600')]],paragraph_spacing=px(0),container_width=px(1312),space_between_widgets=px(0),body_color=INK,body_background_background='classic',body_background_color=BG,link_normal_color=INK,link_hover_color=OLIVE,button_background_background='classic',button_background_color=OLIVE,button_text_color=WHITE,custom_css='')
kit['custom_css']='.skip{position:absolute;inset-inline-start:16px;top:-100px;background:#fff;color:#242622;padding:8px 16px;z-index:10000}.skip:focus{top:0}'
for key in ['home','shop','about','article','contact','login','account','cart','checkout','product']:
    templates[key]['content']=[c(templates[key]['content'],html_tag='main',_element_id='main',_css_classes='elin-page')]

# Responsive geometry and native Woo widget controls; never repair these in global CSS.
account_links['settings']['hide_mobile']='hidden-mobile'
header_top['settings']['padding_mobile']=dims(16,20,0,20)
header_row=header_top['elements'][0]
header_row['settings']['flex_gap_mobile']=gap(14)
header_row['elements'].append(widget('icon',{'selected_icon':{'value':'fas fa-shopping-bag','library':'fa-solid'},'link':{'url':'{{page:cart}}'},'primary_color':INK,'size':px(24),'_element_width':'initial','_element_custom_width':px(44),'hide_desktop':'hidden-desktop','hide_tablet':'hidden-tablet'}))
menu['settings'].update(dropdown='mobile',toggle='burger',background_color_dropdown_item_hover=SAND,background_color_dropdown_item_active=SAND,color_dropdown_item_active=INK,color_dropdown_item_hover=INK)
for i,card in enumerate(category_cards):
    if i<2:card['elements'][0]['settings'].update(background_xpos_mobile=pct(20 if i==0 else 80),background_position_mobile='custom',background_ypos_mobile=pct(50),background_bg_width_mobile=pct(250))
    else:
        card['settings'].update(width_mobile=pct(100),flex_direction_mobile='row',flex_align_items_mobile='center',background_background='classic',background_color_mobile=SAND)
        card['elements'][0]['settings'].update(width_mobile=px(112),min_height_mobile=px(112),background_bg_width_mobile=pct(200),flex_shrink='0')
for benefit in benefits:benefit['settings']['width_mobile']=pct(47)
templates['home']['content'][0]['elements'][-1]['elements'][0]['settings']['flex_gap_mobile']={'unit':'px','size':14,'column':'14','row':'44','isLinked':False}
footer_columns[-1]['settings'].update(width_mobile=pct(100),flex_direction_mobile='row',flex_wrap='wrap',flex_gap_mobile=gap(24),border_border='solid',border_width=dims(),border_width_mobile=dims(1,0,0,0),border_color='#4B5047',padding_mobile=dims(24,0,0,0))
footer_columns[-1]['elements'][0]['settings']['hide_mobile']='hidden-mobile'

def store_style(nodes):
    for n in nodes:
        kind=n.get('widgetType');s=n['settings']
        if kind=='woocommerce-product-add-to-cart':s.update(button_bg_color=OLIVE,button_text_color=WHITE,button_bg_color_hover='#3E4930',button_text_color_hover=WHITE,button_border_radius=dims(),button_padding=dims(12,20,12,20),**typo(16,'600','button_typography'),quantity_padding=dims(10,8,10,8),quantity_border_radius=dims(),quantity_text_color=INK,quantity_bg_color=WHITE,quantity_border_color='#818775',variations_select_color=INK,variations_select_bg_color=WHITE,variations_select_border_color='#818775',variations_select_border_radius=dims(),variations_spacing=px(20),variations_space_between=px(16),**typo(16,prefix='variations_select_typography'),**typo(16,'600','variations_label_typography'))
        if kind=='woocommerce-product-related':s.update(show_heading='',title_color=INK,**typo(19,'600','title_typography'),title_typography_font_size_mobile=px(15),price_color=INK,**typo(16,'600','price_typography'),price_typography_font_size_mobile=px(14),button_background_color=OLIVE,button_text_color=WHITE,button_border_radius=dims(),**typo(15,'600','button_typography'),column_gap=px(24),row_gap=px(28),align='right')
        if kind in ['woocommerce-cart','woocommerce-checkout-page','woocommerce-my-account']:
            s.update(sections_background_color=WHITE,sections_border_radius=dims(),sections_border_color=LINE,sections_border_width=dims(1,1,1,1),sections_padding=dims(28,28,28,28),sections_padding_mobile=dims(20,16,20,16),sections_title_color=INK,typography_section_titles_color=INK,forms_label_color=INK,forms_fields_normal_color=INK,forms_fields_normal_background_background='classic',forms_fields_normal_background_color=WHITE,forms_fields_border_border='solid',forms_fields_border_color='#818775',forms_fields_border_width=dims(1,1,1,1),forms_fields_border_radius=dims(),forms_fields_padding=dims(12,14,12,14),forms_buttons_normal_text_color=WHITE,forms_buttons_background_background='classic',forms_buttons_background_color=OLIVE,forms_buttons_border_radius=dims(),forms_buttons_padding=dims(12,20,12,20),**typo(16,prefix='forms_field_typography'),**typo(16,'600','forms_label_typography'),**typo(16,'600','forms_button_typography'),links_normal_color=OLIVE,links_hover_color=INK)
            if kind!='woocommerce-my-account':s.update(forms_buttons_normal_background_background='classic',forms_buttons_normal_background_color=OLIVE)
            if kind=='woocommerce-cart':s.update(checkout_button_normal_text_color=WHITE,checkout_button_normal_background_background='classic',checkout_button_normal_background_color=OLIVE,**typo(17,'600','checkout_button_typography'))
            if kind=='woocommerce-checkout-page':s.update(billing_details_section_title='جزئیات صورتحساب',shipping_details_section_title='ارسال به نشانی دیگر؟',additional_information_section_title='توضیحات سفارش',order_summary_section_title='سفارش شما',purchase_button_text='ثبت سفارش و پرداخت',purchase_button_normal_text_color=WHITE,purchase_button_normal_background_background='classic',purchase_button_normal_background_color=OLIVE,**typo(17,'600','purchase_button_typography'))
        store_style(n['elements'])
for template in templates.values():store_style(template['content'])

# Screenshot review against the approved source: responsive geometry and focal points.
hero['settings'].update(padding=dims(10,64,0,64),padding_mobile=dims())
hero_copy['elements'][2]['settings']['title']='سادگی، به سبک تو.'
hero_picture['elements'][0]['settings']['height_mobile']=px(326)
hero_picture['elements'][0]['settings']['object-position']='center top'
for i,card in enumerate(category_cards):
    photo=card['elements'][0]['settings']
    photo.update(background_repeat='no-repeat')
    if i<2:
        photo.update(background_position='custom',background_xpos=pct(27 if i==0 else 77),background_ypos=pct(45),background_bg_width=pct(270),background_bg_width_mobile=px(420),background_xpos_mobile=pct(29 if i==0 else 85),background_ypos_mobile=px(0))
    else:
        photo.update(background_position='bottom right',background_position_mobile='bottom right',background_bg_width_mobile=px(224),flex_order_mobile='end')
        card['elements'][2]['settings']['hide_mobile']=''
        card['elements'][2]['settings']['typography_font_size_mobile']=px(14)
        card['settings'].update(padding_mobile=dims(0,20,0,0))
        card['elements']=[card['elements'][0],col(card['elements'][1:],100,width_mobile=pct(64),flex_gap=gap(4))]
        card['elements'][1]['elements'][0]['settings']['_margin_mobile']=dims()
        card['settings'].update(flex_direction_mobile='row-reverse',flex_wrap_mobile='nowrap',background_color=SAND)
campaign['settings']['padding_mobile']=dims()
campaign_row=campaign['elements'][0]
campaign_row['settings']['background_color']=OLIVE
campaign_copy=campaign_row['elements'][0]
campaign_copy['settings']['width']=pct(50)
campaign_copy['elements'][0]['settings'].update(text_color='#E1E6D7',align='left',align_mobile='right')
campaign_copy['elements'][1]['settings'].update(title_color=WHITE,typography_font_size=px(42))
campaign_copy['elements'][2]['settings'].update(text_color='#E1E6D7',typography_font_size_mobile=px(15))
campaign_copy['elements'][3]['settings'].update(background_color=BG,button_text_color=INK)
campaign_row['elements'][1]['settings'].update(width=pct(50),min_height_mobile=px(250),background_bg_width_mobile=pct(200),background_position_mobile='bottom right')
