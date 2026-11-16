"""Build classic editable Elementor documents; no vendor code, credentials or site IDs."""
from pathlib import Path
import sys,json,re,hashlib,os
sys.path.insert(0,str(Path(__file__).parent/'python'))
from bs4 import BeautifulSoup,Tag
root=Path(__file__).resolve().parents[2]
source=Path(os.environ['ELINSTYLE_REFERENCE_DIR'])
dest=root/'wordpress/plugins/elinstyle-companion/demo-content'
dest.mkdir(exist_ok=True)
counter=0
def ident():
    global counter
    counter+=1
    return f'{counter:07x}'
def dims(t=0,r=0,b=0,l=0):return {'unit':'px','top':str(t),'right':str(r),'bottom':str(b),'left':str(l),'isLinked':False}
def font(size=16,weight='400'):
    return {'typography_typography':'custom','typography_font_family':'Yekan','typography_font_size':{'unit':'px','size':size},'typography_font_weight':weight,'typography_line_height':{'unit':'em','size':1.8}}
def widget(name,settings=None):return {'id':ident(),'elType':'widget','widgetType':name,'settings':settings or {},'elements':[]}
def text(s):return widget('text-editor',{'editor':s,'text_color':'#64675E',**font()})
def heading(s,level=2):return widget('heading',{'title':s,'header_size':f'h{level}','title_color':'#242622',**font({1:42,2:34,3:23}.get(level,20),'700')})
def container(children,settings=None):
    return {'id':ident(),'elType':'container','settings':{'content_width':'full','flex_direction':'column','flex_gap':{'unit':'px','size':0,'column':'0','row':'0','isLinked':True},'padding':dims(),**(settings or {})},'elements':children}
def link(url):
    if not url:return '{{page:shop}}'
    for file,key in [('index','home'),('my-account','account'),('login','login'),('shop','shop'),('article','article'),('about','about'),('contact','contact'),('cart','cart'),('checkout','checkout')]:
        if url.startswith(file+'.html'):return '{{page:'+key+'}}'+url[len(file+'.html'):]
    if url.startswith('#'):return url
    return '{{page:shop}}'
def button(label,url='',popup=False):
    s={'text':label,'link':{'url':link(url)},'background_color':'#4D593C','button_text_color':'#F7F5EF','border_radius':dims(),'text_padding':dims(10,20,10,20),**font(16,'600')}
    if popup:s['elinstyle_popup_link']=True
    return widget('button',s)
def image(url,alt=''):
    url=url.replace('assets/','{{theme}}/assets/')
    for k in ['editorial','catalog']:url=url.replace('{{theme}}/assets/images/'+k+'.png','{{image:'+k+'}}')
    for k in ['logo','logo-light']:url=url.replace('{{theme}}/assets/brand/'+k+'.svg','{{image:'+k+'}}')
    return widget('image',{'image':{'url':url},'image_size':'full','width':{'unit':'%','size':100},'image_alt':alt})
def convert(node):
    if not isinstance(node,Tag):return []
    tag=node.name;classes=node.get('class',[])
    if tag in ['script','style','dialog','noscript']:return []
    if 'shop-layout' in classes:return [widget('elinstyle-catalog',{'mode':'shop','columns':2,'columns_mobile':2,'gap':{'size':32,'unit':'px'},'gap_mobile':{'size':14,'unit':'px'}})]
    if node.get('id')=='products':return [widget('elinstyle-catalog',{'mode':'featured','columns':4,'columns_mobile':2})]
    if tag in ['h1','h2','h3','h4','h5','h6']:return [heading(node.decode_contents(),int(tag[1]))]
    if tag in ['p','small','blockquote','ul','ol']:return [text(str(node))]
    if tag=='img':return [image(node.get('src',''),node.get('alt',''))]
    if tag=='svg':return [] # Decorative SVG is represented by native button/icon UI, not page HTML.
    if tag in ['button','a']:
        if node.find('img'):return [image(node.img.get('src',''))]
        label=node.get_text(' ',strip=True)
        if not label:return []
        return [button(label,node.get('href',''),bool(node.get('data-size-guide') is not None or 'اندازه' in node.get('data-demo','')))]
    if tag=='form':
        if 'search' in classes:return [widget('search-form',{'skin':'minimal','placeholder':'دنبال چه لباسی می‌گردید؟'})]
        return [widget('form',{'form_name':'تماس الین‌استایل','form_fields':[{'_id':'name','field_type':'text','field_label':'نام','required':'true'},{'_id':'email','field_type':'email','field_label':'ایمیل','required':'true'},{'_id':'message','field_type':'textarea','field_label':'پیام','required':'true'}],'button_text':'بررسی فرم','submit_actions':[],'success_message':'برای ارسال واقعی، اقدام و گیرندهٔ فرم را در ویرایشگر تنظیم کنید.'})]
    settings={'_css_classes':' '.join('elin-el-'+c for c in classes)}
    if node.get('id'):settings['_element_id']=node['id']
    if 'wrap' in classes:settings.update(content_width='boxed',boxed_width={'unit':'px','size':1312},padding=dims(0,64,0,64),padding_mobile=dims(0,20,0,20))
    if tag in ['header','nav'] or any(c in classes for c in ['headrow','section-heading','filter-title','auth-options']):settings.update(flex_direction='row',flex_justify_content='space-between',flex_align_items='center',flex_wrap='wrap',flex_gap={'unit':'px','size':24})
    if any(c in classes for c in ['grid','category-grid','benefits','footergrid','category-grid']):settings.update(flex_direction='row',flex_wrap='wrap',flex_gap={'unit':'px','size':24})
    if any(c in classes for c in ['page-heading','categories','featured','principles','journey']):settings.update(padding=dims(32,0,40,0))
    if any(c in classes for c in ['hero','campaign','about-story']):settings.update(flex_direction='row',flex_direction_mobile='column',background_background='classic',background_color='#EAE6DA',min_height={'unit':'px','size':400})
    if 'hero-copy' in classes:settings.update(width={'unit':'%','size':40},width_mobile={'unit':'%','size':100},padding=dims(42,56,42,56),padding_mobile=dims(24,20,24,20))
    if 'announce' in classes:settings.update(background_background='classic',background_color='#4D593C',padding=dims(4,20,4,20))
    if tag=='footer':settings.update(background_background='classic',background_color='#242622',padding=dims(40,64,20,64),padding_mobile=dims(32,20,20,20))
    if 'crop' in classes or 'editorial-photo' in classes:
        settings.update(min_height={'unit':'px','size':300},background_background='classic',background_image={'url':'{{image:catalog}}' if 'crop' in classes else '{{image:editorial}}'},background_size='custom',background_bg_width={'unit':'%','size':200},background_position='top left')
        for c,pos in [('p1','top right'),('p2','bottom left'),('p3','bottom right')]:
            if c in classes:settings['background_position']=pos
    children=[]
    for child in node.children:children+=convert(child)
    if not children and not settings.get('background_image'):
        value=node.get_text(' ',strip=True)
        if value:children=[text(value)]
    return [container(children,settings)] if children or settings.get('background_image') else []
def normal(file):
    soup=BeautifulSoup((source/(file+'.html')).read_text(encoding='utf-8'),'html.parser')
    return convert(soup.select_one('main'))
templates={}
for key,file in [('home','index'),('shop','shop'),('article','article'),('about','about'),('contact','contact')]:templates[key]={'content':normal(file),'page_settings':{'hide_title':'yes'}}
for key,name in [('cart','woocommerce-cart'),('checkout','woocommerce-checkout-page'),('account','woocommerce-my-account')]:templates[key]={'content':[container([heading({'cart':'سبد خرید شما','checkout':'ثبت سفارش و پرداخت','account':'حساب کاربری'}[key],1),widget(name)],{'content_width':'boxed','boxed_width':{'size':1312,'unit':'px'},'padding':dims(32,64,64,64),'padding_mobile':dims(24,20,40,20)})],'page_settings':{'hide_title':'yes'}}
templates['login']={'content':[container([heading('ورود به حساب',1),text('برای دیدن سفارش‌ها و مدیریت اطلاعات حسابت وارد شو.'),container([container([heading('دوباره خوش آمدی',2),widget('login',{'show_labels':'yes','show_remember_me':'yes','show_lost_password':'yes','show_register':'yes','button_text':'ورود به حساب','show_logged_in_message':'no','custom_css':'selector .elementor-field-type-text{position:relative}selector .elinstyle-password-toggle{position:absolute;left:0;bottom:0;width:54px;height:54px;border:0;background:none;color:inherit}selector input[name=password]{padding-inline-end:54px}','redirect_after_login':'yes','redirect_url':{'url':'{{page:account}}'}})],{'width':{'unit':'%','size':44},'width_mobile':{'unit':'%','size':100},'background_background':'classic','background_color':'#FFFFFF','padding':dims(32,32,28,32)}),container([image('assets/images/editorial.png'),heading('همراه انتخاب‌های روزمرهٔ تو.',2),text('سفارش‌هایت را مرور کن، نشانی‌ها را مدیریت کن و به فروشگاه برگرد.')],{'width':{'unit':'%','size':52},'width_mobile':{'unit':'%','size':100}})],{'flex_direction':'row','flex_direction_mobile':'column','flex_gap':{'unit':'px','size':56}})],{'content_width':'boxed','boxed_width':{'unit':'px','size':1312},'padding':dims(32,64,80,64),'padding_mobile':dims(24,20,40,20)})],'page_settings':{'hide_title':'yes'}}
templates['product']={'content':[container([container([widget('woocommerce-product-images')],{'width':{'unit':'%','size':56},'width_mobile':{'unit':'%','size':100}}),container([widget('woocommerce-product-title'),widget('woocommerce-product-price'),widget('woocommerce-product-short-description'),widget('woocommerce-product-add-to-cart'),button('راهنمای اندازه',popup=True),widget('woocommerce-product-meta')],{'width':{'unit':'%','size':40},'width_mobile':{'unit':'%','size':100}})],{'content_width':'boxed','boxed_width':{'size':1312,'unit':'px'},'flex_direction':'row','flex_direction_mobile':'column','flex_gap':{'size':48,'unit':'px'},'padding':dims(32,64,40,64)}),container([widget('woocommerce-product-data-tabs'),heading('کنار این انتخاب',2),widget('woocommerce-product-related')],{'content_width':'boxed','boxed_width':{'size':1312,'unit':'px'}})],'page_settings':{'hide_title':'yes'}}
html=BeautifulSoup((source/'index.html').read_text(encoding='utf-8'),'html.parser')
templates['header']={'content':[container([text('الین‌استایل؛ برای روزهای معمولی دوست‌داشتنی')],{'background_background':'classic','background_color':'#4D593C'}),container([widget('theme-site-logo',{'image':{'url':'{{theme}}/assets/brand/logo.svg'}}),widget('search-form',{'skin':'minimal'}),button('حساب کاربری','login.html'),button('سبد خرید','cart.html')],{'content_width':'boxed','boxed_width':{'size':1312,'unit':'px'},'flex_direction':'row','flex_wrap':'wrap','flex_justify_content':'space-between','flex_align_items':'center','padding':dims(24,64,24,64),'padding_mobile':dims(16,20,16,20)}),container([widget('nav-menu',{'menu':'{{menu}}','layout':'horizontal'}),button('راهنمای اندازه',popup=True)],{'content_width':'boxed','boxed_width':{'size':1312,'unit':'px'},'flex_direction':'row','flex_justify_content':'space-between'})],'page_settings':{}}
templates['footer']={'content':convert(html.select_one('footer')),'page_settings':{}}
charts={'زنانه':[['S','۸۴–۸۸','۶۶–۷۰','۹۲–۹۶'],['M','۸۹–۹۳','۷۱–۷۵','۹۷–۱۰۱'],['L','۹۴–۹۸','۷۶–۸۰','۱۰۲–۱۰۶'],['XL','۹۹–۱۰۳','۸۱–۸۵','۱۰۷–۱۱۱']],'مردانه':[['S','۹۰–۹۴','۷۶–۸۰','۹۰–۹۴'],['M','۹۵–۹۹','۸۱–۸۵','۹۵–۹۹'],['L','۱۰۰–۱۰۴','۸۶–۹۰','۱۰۰–۱۰۴'],['XL','۱۰۵–۱۰۹','۹۱–۹۵','۱۰۵–۱۰۹']]}
tabs=[]
for label,rows in charts.items():
    content='<table><caption>جدول نمونهٔ '+label+'، سانتی‌متر</caption><thead><tr>'+''.join('<th>'+v+'</th>' for v in ['اندازه','دور سینه','دور کمر','دور باسن'])+'</tr></thead><tbody>'+''.join('<tr>'+''.join('<td>'+v+'</td>' for v in row)+'</tr>' for row in rows)+'</tbody></table>'
    tabs.append({'_id':ident(),'tab_title':label,'tab_content':content})
templates['guide']={'content':[container([heading('راهنمای اندازه و تن‌خور',2),text('با متر نرم اندازهٔ بدن را بگیر و با جدول اختصاصی همان محصول مقایسه کن.'),widget('tabs',{'tabs':tabs,'custom_css':'selector table{width:100%;border-collapse:collapse}selector th,selector td{padding:12px;text-align:right;border-bottom:1px solid #DADCD2}'}),text('این جدول نمونه است؛ مربوط به بدن است، نه ابعاد لباس.')],{'padding':dims(32,32,32,32)})],'page_settings':{'width':{'unit':'px','size':900},'height_type':'auto','close_button':'yes','close_button_position':'inside','entrance_animation':'fadeIn','exit_animation':'fadeOut'}}
templates['kit']={'content':[],'page_settings':{'system_colors':[{'_id':'primary','title':'زیتونی','color':'#4D593C'},{'_id':'secondary','title':'ماسه','color':'#EAE6DA'},{'_id':'text','title':'متن','color':'#242622'},{'_id':'accent','title':'زمینه','color':'#F7F5EF'}],'body_typography_typography':'custom','body_typography_font_family':'Yekan','body_typography_font_size':{'unit':'px','size':16},'body_typography_line_height':{'unit':'em','size':1.8},'body_color':'#242622','body_background_background':'classic','body_background_color':'#F7F5EF','custom_css':''}}
# Explicit native layouts replace the lossy generic conversion for the store shell.
exec((Path(__file__).with_name('elementor-layouts.py')).read_text(encoding='utf-8'))
exec((Path(__file__).with_name('elementor-editorial-layouts.py')).read_text(encoding='utf-8'))
types=set()
def visit(nodes):
    for n in nodes:
        for setting,value in list(n['settings'].items()):
            if setting.startswith(('background_size','background_position')) and value=='custom':n['settings'][setting]='initial'
        if n['elType']=='container' and '_css_classes' in n['settings']:n['settings']['css_classes']=n['settings'].pop('_css_classes')
        if n['elType']=='widget':types.add(n['widgetType'])
        visit(n['elements'])
for t in templates.values():visit(t['content'])
bundle={'version':'0.4.0','elementor_schema':'0.4','built_from':'approved local HTML; no runtime/customer data','required_widgets':sorted(types),'templates':templates}
(dest/'elementor-0.4.0.json').write_text(json.dumps(bundle,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps({'templates':len(templates),'widgets':sorted(types),'elements':counter},ensure_ascii=False))
