(function (wp) {
  var el = wp.element.createElement, __ = wp.i18n.__;
  wp.blocks.registerBlockType('elinstyle/commerce',{apiVersion:3,title:__('سبد / پرداخت WooCommerce','elinstyle-companion'),icon:'cart',category:'widgets',attributes:{kind:{type:'string',default:'cart'}},edit:function(props){return el('div',wp.blockEditor.useBlockProps(),el(wp.blockEditor.InspectorControls,{},el(wp.components.PanelBody,{title:__('نوع صفحه','elinstyle-companion')},el(wp.components.SelectControl,{label:__('صفحه','elinstyle-companion'),value:props.attributes.kind,options:[{label:__('سبد','elinstyle-companion'),value:'cart'},{label:__('پرداخت','elinstyle-companion'),value:'checkout'}],onChange:function(kind){props.setAttributes({kind:kind});}}))),el(wp.serverSideRender,{block:'elinstyle/commerce',attributes:props.attributes}));},save:function(){return null;}});
  ['product','product-content'].forEach(function(name){wp.blocks.registerBlockType('elinstyle/'+name,{
    apiVersion:3,title:name==='product'?__('خرید و گالری محصول Woo','elinstyle-companion'):__('محتوای محصول Woo','elinstyle-companion'),icon:'products',category:'widgets',
    attributes:{productId:{type:'number',default:0},part:{type:'string',default:'description'}},
    edit:function(props){return el('div',wp.blockEditor.useBlockProps(),el(wp.blockEditor.InspectorControls,{},el(wp.components.PanelBody,{title:__('محصول پیش‌نمایش','elinstyle-companion')},el(wp.components.TextControl,{label:__('شناسه محصول؛ صفر برای انتخاب خودکار','elinstyle-companion'),type:'number',value:props.attributes.productId,onChange:function(value){props.setAttributes({productId:Math.max(0,parseInt(value,10)||0)});}}),name==='product-content'?el(wp.components.SelectControl,{label:__('بخش داده','elinstyle-companion'),value:props.attributes.part,options:[{label:__('توضیح','elinstyle-companion'),value:'description'},{label:__('ویژگی‌ها','elinstyle-companion'),value:'attributes'},{label:__('محصولات مرتبط','elinstyle-companion'),value:'related'}],onChange:function(part){props.setAttributes({part:part});}}):null)),el(wp.serverSideRender,{block:'elinstyle/'+name,attributes:props.attributes}));},save:function(){return null;}
  });});
  wp.blocks.registerBlockType('elinstyle/fragment', {
    apiVersion:3,title:__('جزء نمایشی HTML/SVG','elinstyle-companion'),icon:'editor-code',category:'design',
    attributes:{content:{type:'string',source:'html'}},
    edit:function(props){
      var node=new DOMParser().parseFromString(props.attributes.content||'<div></div>','text/html').body.firstElementChild;
      var tag=node?node.tagName.toLowerCase():'div', attrs={};
      if(node)Array.prototype.forEach.call(node.attributes,function(a){if(!/^on/i.test(a.name)&&a.name!=='style')attrs[({class:'className',for:'htmlFor',tabindex:'tabIndex',viewbox:'viewBox'})[a.name]||a.name]=a.value;});
      attrs.onClick=function(e){if(e.target.closest('a,button'))e.preventDefault();};attrs.onSubmit=function(e){e.preventDefault();};
      var blockProps=wp.blockEditor.useBlockProps(attrs);
      if(!/^(img|hr|input|br|wbr)$/.test(tag))blockProps.dangerouslySetInnerHTML={__html:node?node.innerHTML:''};
      return el(wp.element.Fragment,{},el(wp.blockEditor.InspectorControls,{},el(wp.components.PanelBody,{title:__('جزء نمایشی؛ ساختار مصوب حفظ شود','elinstyle-companion')},el(wp.components.TextareaControl,{label:__('HTML جزء','elinstyle-companion'),value:props.attributes.content,onChange:function(content){props.setAttributes({content:content});}}))),el(tag,blockProps));
    },
    save:function(props){return el(wp.element.RawHTML,{},props.attributes.content);}
  });
  ['catalog','contact','account'].forEach(function (name) {
    wp.blocks.registerBlockType('elinstyle/' + name, {
      apiVersion: 3,
      title: name === 'catalog' ? __('کاتالوگ واقعی فروشگاه','elinstyle-companion') : name === 'account' ? __('حساب واقعی WooCommerce','elinstyle-companion') : __('فرم تماس نمایشی','elinstyle-companion'),
      icon: name === 'catalog' ? 'store' : 'email', category: 'widgets',
      attributes: name === 'catalog' ? {mode:{type:'string',default:'featured'}} : {},
      edit: function (props) {
        var settings = name === 'catalog' ? el(wp.blockEditor.InspectorControls, {}, el(wp.components.PanelBody, {title:__('نمایش','elinstyle-companion')}, el(wp.components.SelectControl, {label:__('حالت','elinstyle-companion'),value:props.attributes.mode,options:[{label:__('منتخب','elinstyle-companion'),value:'featured'},{label:__('فروشگاه با فیلتر','elinstyle-companion'),value:'shop'}],onChange:function (mode) {props.setAttributes({mode:mode});}}))) : null;
        return el('div', wp.blockEditor.useBlockProps(), settings, el(wp.serverSideRender, {block:'elinstyle/'+name,attributes:props.attributes}));
      },
      save: function () {return null;}
    });
  });
})(window.wp);
