<?php
defined( 'ABSPATH' ) || exit;
class Elinstyle_Elementor_Catalog extends \Elementor\Widget_Base {
	public function get_name() { return 'elinstyle-catalog'; }
	public function get_title() { return 'کاتالوگ و فیلتر واقعی الین‌استایل'; }
	public function get_icon() { return 'eicon-products'; }
	public function get_categories() { return array( 'general' ); }
	public function get_style_depends() { return array( 'elinstyle-shared', 'elinstyle-catalog' ); }
	public function get_script_depends() { return array( 'elinstyle-catalog' ); }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'دادهٔ WooCommerce' ) );
		$this->add_control( 'catalog_help', array( 'type' => \Elementor\Controls_Manager::RAW_HTML, 'raw' => 'فیلتر چندانتخابی با query واقعی WooCommerce. برای فهرست منتخب بدون فیلتر از ویجت اصلی Products استفاده کنید.' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'layout', array( 'label' => 'چیدمان و تایپوگرافی', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'columns', array( 'label' => 'ستون محصولات', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 1, 'max' => 6, 'default' => 2, 'selectors' => array( '{{WRAPPER}} .elinstyle-surface .results, {{WRAPPER}} .elinstyle-surface .grid' => 'grid-template-columns:repeat({{VALUE}},minmax(0,1fr));' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => 'فاصله محصولات', 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'default' => array( 'size' => 32 ), 'selectors' => array( '{{WRAPPER}} .elinstyle-surface .results, {{WRAPPER}} .elinstyle-surface .grid' => 'gap:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'color', array( 'label' => 'رنگ عنوان و قیمت', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .elinstyle-surface .product h3, {{WRAPPER}} .elinstyle-surface .price' => 'color:{{VALUE}};' ) ) );
		$this->add_responsive_control( 'sidebar_width', array( 'label' => 'عرض پنل فیلتر', 'type' => \Elementor\Controls_Manager::SLIDER, 'default' => array( 'unit'=>'px', 'size'=>256 ), 'range'=>array('px'=>array('min'=>180,'max'=>400)), 'selectors'=>array('{{WRAPPER}} .elinstyle-surface .shop-layout'=>'grid-template-columns:{{SIZE}}{{UNIT}} minmax(0,1fr);') ) );
		$this->add_responsive_control( 'layout_gap', array( 'label'=>'فاصله پنل و محصولات','type'=>\Elementor\Controls_Manager::SLIDER,'default'=>array('unit'=>'px','size'=>72),'selectors'=>array('{{WRAPPER}} .elinstyle-surface .shop-layout'=>'gap:{{SIZE}}{{UNIT}};') ) );
		$this->add_control( 'accent', array('label'=>'رنگ تأکید فیلتر','type'=>\Elementor\Controls_Manager::COLOR,'selectors'=>array('{{WRAPPER}} .elinstyle-surface'=>'--olive:{{VALUE}};')) );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array( 'name'=>'filter_type','selector'=>'{{WRAPPER}} .elinstyle-surface .filters' ) );
		$this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array( 'name' => 'product_type', 'selector' => '{{WRAPPER}} .elinstyle-surface .product h3' ) );
		$this->end_controls_section();
	}
	protected function render() { echo '<div class="elinstyle-surface">' . elinstyle_catalog( array( 'mode' => 'shop' ) ) . '</div>'; }
}
