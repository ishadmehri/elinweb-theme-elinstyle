"""Approved editorial content in native, individually editable classic widgets."""
def soup_for(key):return BeautifulSoup((source/(key+'.html')).read_text(encoding='utf-8'),'html.parser')
def paragraphs(node,size=19,color=MUTED):return [t(p.decode_contents(),size,color,_margin=dims(0,0,24,0),typography_font_size_mobile=px(17)) for p in node.find_all('p',recursive=False)]
def original_heading(soup):
    node=soup.select_one('.page-heading')
    return page_heading(node.h1.decode_contents(),node.find('p').get_text(' ',strip=True) if node.find('p') else '', 'خانه / '+{'about':'دربارهٔ ما','article':'راهنمای استایل','contact':'تماس با ما'}[soup._elin_key])
def main_doc(content):return {'content':[c(content,html_tag='main',_element_id='main',_css_classes='elin-page')],'page_settings':{'hide_title':'yes'}}

s=soup_for('about');s._elin_key='about';story=s.select_one('.about-story');copy=story.find('div',recursive=False)
story_content=[t(copy.small.get_text(' ',strip=True),13,align='left'),h(copy.h2.decode_contents(),2,34,28,_margin=dims(24,0,24,0))]+paragraphs(copy)
about_story=wrap([row([col(story_content,47.5),col([img(story.img['src'],height=px(500),height_mobile=px(300),**{'object-fit':'cover'})],47.5)],space=64,flex_justify_content='space-between')],top=24,bottom=48)
principles=s.select_one('.principles');principle_cards=[]
for node in principles.select('article'):
    principle_cards.append(col([t(node.small.get_text(' ',strip=True),13),h(node.h3.decode_contents(),3,25,23,_margin=dims(24,0,16,0))]+paragraphs(node,16),32,background_background='classic',background_color=SAND,padding=dims(24,28,24,28)))
principles_layout=wrap([h(principles.h2.decode_contents()),row(principle_cards,space=14,flex_justify_content='space-between',margin=dims(28,0,0,0))],top=32,bottom=48)
journey=s.select_one('.journey');left=journey.find('div',recursive=False)
journey_layout=wrap([row([col([h(journey.h2.decode_contents(),_margin=dims(0,0,24,0))]+paragraphs(left),47.5),col([t(str(journey.ol),19,typography_font_size_mobile=px(17))],47.5)],space=64)],top=32,bottom=64,border_border='solid',border_width=dims(1,0,0,0),border_color=LINE)
banner=s.select_one('.about-banner');banner_heading=banner.find(['h2','h3'])
banner_layout=wrap([row([col([h(banner_heading.decode_contents(),3,24,21,WHITE)]+[t(p.decode_contents(),16,BG) for p in banner.find_all('p')],76),btn('دیدن فروشگاه',background_color=BG,button_text_color=OLIVE)],space=24,flex_justify_content='space-between',flex_align_items='center',background_background='classic',background_color=OLIVE,padding=dims(24,32,24,32))],bottom=78)
templates['about']=main_doc([original_heading(s),about_story,principles_layout,journey_layout,banner_layout])

s=soup_for('article');s._elin_key='article';shell=s.select_one('.article-shell');article=s.select_one('.reading-layout article');toc=s.select_one('.toc');reading=[]
for node in article.children:
    if not isinstance(node,Tag):continue
    if node.name=='p':reading.append(t(node.decode_contents(),20,typography_font_size_mobile=px(17),_margin=dims(0,0,24,0)))
    elif node.name=='section':reading.append(c([h(node.h2.decode_contents(),2,28,24,_margin=dims(0,0,18,0))]+paragraphs(node,20),_element_id=node.get('id',''),margin=dims(0,0,26,0)))
    elif 'exercise' in node.get('class',[]):reading.append(c([h(node.h3.decode_contents(),3,19,19)]+paragraphs(node,18),background_background='classic',background_color='#E8ECDF',padding=dims(20,24,20,24),flex_gap=gap(12)))
    elif 'article-end' in node.get('class',[]):reading.append(row([t(node.get_text(' ',strip=True),16),btn('لباس‌های پایه را ببین','shop.html?category=basics',plain=True)],space=24,margin=dims(40,0,0,0),padding=dims(24,0,0,0),border_border='solid',border_width=dims(1,0,0,0),border_color=LINE))
toc_content=[h('در این مطلب',3,18,18)]+[btn(a.get_text(' ',strip=True),a.get('href'),plain=True,color=MUTED,**typo(15),_margin=dims(20,0,0,0)) for a in toc.find_all('a')]
article_photo=wrap([img(shell.img['src'],height=px(560),height_mobile=px(280),**{'object-fit':'cover','object-position':'center center'}),t(shell.figcaption.get_text(' ',strip=True),13,_margin=dims(14,0,32,0))],top=24)
article_reading=wrap([row([col(toc_content,23,padding=dims(0,0,0,20),border_border='solid',border_width=dims(0,0,0,1),border_color=LINE),col(reading,72)],space=64,flex_align_items='flex-start')],bottom=66)
templates['article']=main_doc([original_heading(s),article_photo,article_reading])

s=soup_for('contact');s._elin_key='contact';aside=s.select_one('.contact aside');contact_info=[h(aside.h2.decode_contents(),2,25,25)]+paragraphs(aside,16)
for node in aside.find_all('div',recursive=False):
    if 'exercise' in node.get('class',[]):contact_info.append(c([h(node.h3.decode_contents(),3,19,19)]+paragraphs(node,16),background_background='classic',background_color='#E8ECDF',padding=dims(20,24,20,24),margin=dims(24,0,0,0)))
    else:contact_info.append(c([t(p.decode_contents(),19,typography_font_size_mobile=px(17)) for p in node.find_all(['p','small'],recursive=False)],padding=dims(24,0,24,0),border_border='solid',border_width=dims(1,0,0,0),border_color=LINE))
fields=[{'_id':'name','field_type':'text','field_label':'نام و نام خانوادگی','placeholder':'نام شما','required':'true','width':'50'},{'_id':'email','field_type':'email','field_label':'ایمیل','placeholder':'ایمیل شما','required':'true','width':'50'},{'_id':'subject','field_type':'text','field_label':'موضوع پیام','required':'true'},{'_id':'message','field_type':'textarea','field_label':'پیام','placeholder':'چطور می‌توانیم کمک کنیم؟','required':'true','rows':6}]
contact_form=widget('form',{'form_name':'تماس الین‌استایل','form_fields':fields,'button_text':'بررسی فرم','submit_actions':[],'success_message':'فرم بررسی شد. برای ارسال واقعی، اقدام و گیرنده را در تنظیمات این فرم مشخص کنید.','label_color':INK,'field_text_color':INK,'field_background_color':WHITE,'field_border_color':'#818775','field_border_width':dims(1,1,1,1),'field_border_radius':dims(),'button_text_color':WHITE,'button_background_color':OLIVE,'button_border_radius':dims(),'row_gap':px(24),**typo(16,'600','label_typography'),**typo(16,prefix='field_typography')})
contact_layout=wrap([row([col(contact_info,33),col([h('پیامت را برای ما بنویس',2,27,26),contact_form],60,background_background='classic',background_color=WHITE,padding=dims(28,40,38,40),padding_mobile=dims(24,20,24,20),flex_gap=gap(24))],space=88,flex_justify_content='space-between')],top=24)
help_node=s.select_one('.contact-help');contact_help=wrap([row([col([h(help_node.h2.decode_contents(),2,26,26)]+paragraphs(help_node.find('div',recursive=False),16),48),col([btn('پرسش‌های متداول','contact.html',plain=True),btn('پیگیری سفارش','my-account.html',plain=True),btn('راهنمای اندازه',plain=True,popup=True)],48,flex_gap=gap(20))],space=48,border_border='solid',border_width=dims(1,0,0,0),border_color=LINE,padding=dims(30,0,0,0))],top=58,bottom=84)
templates['contact']=main_doc([original_heading(s),contact_layout,contact_help])
