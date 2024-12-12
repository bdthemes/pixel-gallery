<?php

namespace PixelGallery\Modules\Fluid\Widgets;

use PixelGallery\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Repeater;
use PixelGallery\Utils;
use PixelGallery\Traits\Global_Widget_Controls;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Fluid extends Module_Base {

	use Global_Widget_Controls;

	public function get_name() {
		return 'pg-fluid';
	}

	public function get_title() {
		return BDTPG . esc_html__('Fluid', 'pixel-gallery');
	}

	public function get_icon() {
		return 'pg-icon-fluid';
	}

	public function get_categories() {
		return ['pixel-gallery'];
	}

	public function get_keywords() {
		return ['fluid', 'grid', 'gallery'];
	}

	public function get_style_depends() {
		return ['pg-fluid'];
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/1Ca7lCGxVdE';
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return false;
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __('Layout', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		//Global
		$this->register_grid_controls('fluid');
		$this->register_global_height_controls('fluid');
		$this->register_title_tag_controls();

		$this->add_control(
			'show_social_link',
			[
				'label'   => esc_html__('Show Social Link', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				// 'default' => 'yes',
			]
		);

		$this->register_alignment_controls('fluid');
		$this->register_thumbnail_size_controls();

		//Global Lightbox Controls
		$this->register_lightbox_controls();
		$this->register_link_target_controls();

		$this->end_controls_section();

		//Repeater
		$this->start_controls_section(
			'section_item_content',
			[
				'label' => __('Items', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);
		$repeater = new Repeater();
		$repeater->start_controls_tabs('tabs_item_content');
		$repeater->start_controls_tab(
			'tab_item_content',
			[
				'label' => esc_html__('Content', 'pixel-gallery'),
			]
		);
		$this->register_repeater_media_controls($repeater);
		$this->register_repeater_title_controls($repeater);
		$this->register_repeater_readmore_controls($repeater);
		$this->register_repeater_custom_url_controls($repeater);
		$this->register_repeater_hidden_item_controls($repeater);

		$repeater->end_controls_tab();
		$repeater->start_controls_tab(
			'tab_item_grid',
			[
				'label' => esc_html__('Grid', 'pixel-gallery'),
			]
		);
		$this->register_repeater_grid_controls($repeater, 'fluid');
		$this->register_repeater_item_height_controls($repeater, 'fluid');
		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();
		$this->register_repeater_items_controls($repeater);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_social_link',
			[
				'label' 	=> __('Social Link', 'pixel-gallery'),
				'condition' => [
					'show_social_link' => 'yes',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'social_link_title',
			[
				'label'   => __('Title', 'pixel-gallery'),
				'type'    => Controls_Manager::TEXT,
				'default' => 'Facebook',
			]
		);

		$repeater->add_control(
			'social_link',
			[
				'label'   => __('Link', 'pixel-gallery'),
				'type'    => Controls_Manager::TEXT,
				'default' => __('http://www.facebook.com/bdthemes/', 'pixel-gallery'),
			]
		);

		$repeater->add_control(
			'social_link_offset_toggle',
			[
				'label' => __('Offset', 'pixel-gallery'),
				'type' => Controls_Manager::POPOVER_TOGGLE,
				'label_off' => __('None', 'pixel-gallery'),
				'label_on' => __('Custom', 'pixel-gallery'),
				'return_value' => 'yes',
				'separator' => 'before'
			]
		);

		$repeater->start_popover();

		$repeater->add_responsive_control(
			'social_link_horizontal_offset',
			[
				'label' => __('Horizontal', 'pixel-gallery'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -100,
						'step' => 1,
						'max' => 100,
					],
					'%' => [
						'min' => -100,
						'step' => 1,
						'max' => 100,
					],
				],
				'condition' => [
					'social_link_offset_toggle' => 'yes'
				],
				'render_type' => 'ui',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => '--fluid-h-offset: {{SIZE}}{{UNIT}};'
				],
			]
		);

		$repeater->add_responsive_control(
			'social_link_vertical_offset',
			[
				'label' => __('Vertical', 'pixel-gallery'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => -100,
						'step' => 1,
						'max' => 100,
					],
					'%' => [
						'min' => -100,
						'step' => 1,
						'max' => 100,
					],
				],
				'condition' => [
					'social_link_offset_toggle' => 'yes'
				],
				'render_type' => 'ui',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => '--fluid-v-offset: {{SIZE}}{{UNIT}};'
				],
			]
		);

		$repeater->end_popover();

		$this->add_control(
			'social_link_list',
			[
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'default' => [
					[
						'social_link'       => __('http://www.facebook.com/bdthemes/', 'pixel-gallery'),
						'social_link_title' => 'Facebook',
					],
					[
						'social_link'       => __('http://www.twitter.com/bdthemes/', 'pixel-gallery'),
						'social_link_title' => 'Twitter',
					],
					[
						'social_link'       => __('http://www.instagram.com/bdthemes/', 'pixel-gallery'),
						'social_link_title' => 'Instagram',
					],
				],
				'title_field' => '{{{ social_link_title }}}',
			]
		);

		$this->end_controls_section();

		//Style
		$this->start_controls_section(
			'pg_section_style',
			[
				'label'     => esc_html__('Items', 'pixel-gallery'),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'overlay_type',
			[
				'label'   => esc_html__('Overlay', 'pixel-gallery'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'background',
				'options' => [
					'none'       => esc_html__('None', 'pixel-gallery'),
					'background' => esc_html__('Background', 'pixel-gallery'),
					'blend'      => esc_html__('Blend', 'pixel-gallery'),
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'overlay_color',
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .pg-fluid-content::before, {{WRAPPER}} .pg-fluid-content::after',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => 'rgba(13, 59, 84, 0.2)',
					],
				],
				'condition' => [
					'overlay_type' => ['background', 'blend'],
				],
			]
		);

		$this->add_control(
			'blend_type',
			[
				'label'     => esc_html__('Blend Type', 'pixel-gallery'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'multiply',
				'options'   => pixel_gallery_blend_options(),
				'condition' => [
					'overlay_type' => 'blend',
				],
				'selectors' => [
					'{{WRAPPER}} .pg-fluid-content::before, {{WRAPPER}} .pg-fluid-content::after' => 'mix-blend-mode: {{VALUE}};'
				],
			]
		);

		$this->add_control(
			'glassmorphism_effect',
			[
				'label' => esc_html__('Glassmorphism', 'pixel-gallery') . BDTPG_NC,
				'type'  => Controls_Manager::SWITCHER,
				'description' => sprintf(esc_html__('This feature will not work in the Firefox browser untill you enable browser compatibility so please %1s look here %2s', 'pixel-gallery'), '<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/backdrop-filter#Browser_compatibility" target="_blank">', '</a>'),
				'condition' => [
					'overlay_type' => 'background',
				]
			]
		);

		$this->add_control(
			'glassmorphism_blur_level',
			[
				'label'       => esc_html__('Blur Level', 'pixel-gallery'),
				'type'        => Controls_Manager::SLIDER,
				'range'       => [
					'px' => [
						'min'  => 0,
						'step' => 1,
						'max'  => 50,
					]
				],
				'default'     => [
					'size' => 5
				],
				'selectors'   => [
					'{{WRAPPER}} .pg-fluid-content::before, {{WRAPPER}} .pg-fluid-content::after' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);'
				],
				'condition' => [
					'glassmorphism_effect' => 'yes',
					'overlay_type' => 'background',
				]
			]
		);

		$this->start_controls_tabs('tabs_item_style');

		$this->start_controls_tab(
			'tab_item_normal',
			[
				'label' => esc_html__('Normal', 'pixel-gallery'),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'item_background',
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .pg-fluid-item',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => 'rgba(13, 59, 84, 0.2)',
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'item_border',
				'selector'  => '{{WRAPPER}} .pg-fluid-item',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_padding',
			[
				'label'      => esc_html__('Padding', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .pg-fluid-item',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_item_hover',
			[
				'label' => esc_html__('Hover', 'pixel-gallery'),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'item_hover_background',
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .pg-fluid-item:hover',
			]
		);

		$this->add_control(
			'item_hover_border_color',
			[
				'label'     => esc_html__('Border Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'item_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .pg-fluid-item:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_hover_box_shadow',
				'selector' => '{{WRAPPER}} .pg-fluid-item:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_title',
			[
				'label' => __('Title', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_title' => 'yes',
				]
			]
		);

		$this->add_control(
			'title_color',
			[
				'label'     => __('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-fluid-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'title_background',
				'selector' => '{{WRAPPER}} .pg-fluid-title',
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label'      => esc_html__('Padding', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .pg-fluid-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'title_text_shadow',
				'label' => __('Text Shadow', 'pixel-gallery'),
				'selector' => '{{WRAPPER}} .pg-fluid-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name' => 'title_text_stroke',
				'selector' => '{{WRAPPER}} .pg-fluid-title',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_readmore',
			[
				'label'     => esc_html__('Read More', 'pixel-gallery'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'link_to' => ['file', 'custom'],
					'link_target' => 'only_button',
				],
			]
		);

		$this->start_controls_tabs('tabs_readmore_style');

		$this->start_controls_tab(
			'tab_readmore_normal',
			[
				'label' => esc_html__('Normal', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'readmore_color',
			[
				'label'     => esc_html__('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-fluid-readmore a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'readmore_background',
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .pg-fluid-readmore a',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => '#fff',
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'readmore_border',
				'label'       => esc_html__('Border', 'pixel-gallery'),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pg-fluid-readmore a',
				'separator'   => 'before',
			]
		);

		$this->add_responsive_control(
			'readmore_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-readmore a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'readmore_padding',
			[
				'label'      => esc_html__('Padding', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-readmore a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'readmore_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-readmore' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'readmore_typography',
				'selector' => '{{WRAPPER}} .pg-fluid-readmore a span',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'readmore_box_shadow',
				'selector' => '{{WRAPPER}} .pg-fluid-readmore a',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_readmore_hover',
			[
				'label' => esc_html__('Hover', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'readmore_hover_color',
			[
				'label'     => esc_html__('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-fluid-readmore a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pg-fluid-readmore a span::before, {{WRAPPER}} .pg-fluid-readmore a span::after' => 'border-top-color: {{VALUE}}; border-right-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'readmore_hover_background',
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .pg-fluid-readmore a::before',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => '#0D3B54',
					],
				],
			]
		);

		$this->add_control(
			'readmore_hover_border_color',
			[
				'label'     => esc_html__('Border Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'readmore_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .pg-fluid-readmore a:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_social_link',
			[
				'label'     => esc_html__('Social Link', 'pixel-gallery'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_social_link' => 'yes',
				],
			]
		);

		$this->start_controls_tabs('tabs_social_link_style');

		$this->start_controls_tab(
			'tab_social_link_normal',
			[
				'label' => esc_html__('Normal', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'social_link_color',
			[
				'label'     => esc_html__('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-fluid-social-link a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'social_link_background',
				'selector'  => '{{WRAPPER}} .pg-fluid-social-link a',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'social_link_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pg-fluid-social-link a',
				'separator'   => 'before'
			]
		);

		$this->add_responsive_control(
			'social_link_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-social-link a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'social_link_padding',
			[
				'label'      => esc_html__('Padding', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-social-link a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'social_link_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-fluid-social-link a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' 		=> 'social_text_typography',
				'selector' 	=> '{{WRAPPER}} .pg-fluid-social-link a',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'social_link_shadow',
				'selector' => '{{WRAPPER}} .pg-fluid-social-link a',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_social_link_hover',
			[
				'label' => esc_html__('Hover', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'social_link_hover_color',
			[
				'label'     => esc_html__('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-fluid-social-link a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'social_link_hover_background',
				'types'     => ['classic', 'gradient'],
				'selector'  => '{{WRAPPER}} .pg-fluid-social-link a:hover',
			]
		);

		$this->add_control(
			'icon_hover_border_color',
			[
				'label'     => esc_html__('Border Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'social_link_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .pg-fluid-social-link a:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		//Clip Path Controls
		$this->register_clip_path_controls('fluid');
	}

	public function render_social_link() {
		$settings  = $this->get_active_settings();

		if ('' == $settings['show_social_link']) {
			return;
		}

		$this->add_render_attribute('social-link', 'class', 'pg-fluid-social-link', true);

?>
		<div <?php $this->print_render_attribute_string('social-link'); ?>>
			<?php foreach ($settings['social_link_list'] as $link) : ?>
				<a class="elementor-repeater-item-<?php echo esc_attr($link['_id']); ?>" href="<?php echo esc_url($link['social_link']); ?>" target="_blank">
					<?php echo esc_html($link['social_link_title']); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public function render_items() {
		$settings = $this->get_settings_for_display();
		$id = 'pg-fluid-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-fluid-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

			/**
			 * Render Video Inject Here
			 * Video Would be work on Media File & Lightbox
			 * @since 1.0.0
			 */
			if ($item['media_type'] == 'video') {
				$this->render_video_frame($item, $attr_name, $id);
			}

		?>

		<div <?php $this->print_render_attribute_string($attr_name); ?>>
			<?php if ($item['item_hidden'] !== 'yes') : ?>
			<div class="pg-fluid-content">
					<?php $this->render_image_wrap($item, 'fluid'); ?>
					<?php $this->render_title($item, 'fluid'); ?>
					<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'only_button') : ?>
						<?php $this->render_readmore_span($item, $index, $id, 'fluid'); ?>
					<?php endif; ?>
				</div>
				<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'whole_item') : ?>
					<?php $this->render_lightbox_link_url($item, $index, $id); ?>
				<?php endif; ?>
				
				<?php $this->render_social_link(); ?>
				
				<?php endif; ?>
			</div>

		<?php
			$slide_index++;
		endforeach;
	}

	public function render() {
		$settings   = $this->get_settings_for_display();
		$this->add_render_attribute('grid', 'class', 'pg-fluid-grid pg-grid');

		if (isset($settings['pg_in_animation_show']) && ($settings['pg_in_animation_show'] == 'yes')) {
			$this->add_render_attribute( 'grid', 'class', 'pg-in-animation' );
			if (isset($settings['pg_in_animation_delay']['size'])) {
				$this->add_render_attribute( 'grid', 'data-in-animation-delay', $settings['pg_in_animation_delay']['size'] );
			}
		}

		?>
		<div <?php $this->print_render_attribute_string('grid'); ?>>
			<?php $this->render_items(); ?>
		</div>
<?php
	}
}
