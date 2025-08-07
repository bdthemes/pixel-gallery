<?php

namespace PixelGallery\Traits;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Embed;
use Elementor\Plugin;
use PixelGallery\Utils;

use Elementor\Modules\DynamicTags\Module as TagsModule;

defined('ABSPATH') || die();

trait Global_Widget_Controls
{
	//Controls Function

	protected function register_title_tag_controls() {
		/**
		 * Masonry
		 */
		$this->add_control(
			'masonry',
			[
				'label'   => __('Masonry', 'pixel-gallery') . BDTPG_NC,
				'type'    => Controls_Manager::SWITCHER,
				'prefix_class' => 'pg-masonry--',
				'description' => esc_html__('Note: If you enable Masonry then Repeater Column Span and Row Span will not work.', 'pixel-gallery'),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'   => __('Show Title', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label'   => __('Title HTML Tag', 'pixel-gallery'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h3',
				'options' => pixel_gallery_title_tags(),
				'condition' => [
					'show_title' => 'yes',
				]
			]
		);
	}

	protected function register_show_meta_controls() {
		$this->add_control(
			'show_meta',
			[
				'label'   => esc_html__('Show Meta', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);
	}

	protected function register_show_pagination_controls() {
		$this->add_control(
			'show_pagination',
			[
				'label'   => esc_html__('Show Pagination', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'condition' => [
					'source' => 'dynamic',
				],
			]
		);
	}

	protected function register_show_text_controls() {
		$this->add_control(
			'show_text',
			[
				'label'   => esc_html__('Show Text', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);
	}

	protected function register_show_date_controls() {
		$this->add_control(
			'show_date',
			[
				'label'   => esc_html__('Show Date', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);
	}

	protected function register_alignment_controls($name) {
		$this->add_responsive_control(
			'text_align',
			[
				'label'     => __('Alignment', 'pixel-gallery'),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => __('Left', 'pixel-gallery'),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => __('Center', 'pixel-gallery'),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => __('Right', 'pixel-gallery'),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __('Justified', 'pixel-gallery'),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-item' => 'text-align: {{VALUE}};',
				],
			]
		);
	}

	protected function register_content_alignment_controls($name) {
		$this->add_responsive_control(
			'text_align',
			[
				'label'     => __('Alignment', 'pixel-gallery'),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [
						'title' => __('Left', 'pixel-gallery'),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [
						'title' => __('Center', 'pixel-gallery'),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [
						'title' => __('Right', 'pixel-gallery'),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __('Justified', 'pixel-gallery'),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-content' => 'text-align: {{VALUE}};',
				],
			]
		);
	}

	protected function register_global_height_controls($name) {
		$this->add_responsive_control(
			'item_height',
			[
				'label'   => __('Item Height', 'pixel-gallery'),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min' => 200,
						'max' => 800,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-item' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'masonry' => ''
				]
			]
		);

		$this->add_responsive_control(
            'items_align',
            [
                'label'     => __('Item Alignment', 'pixel-gallery') . BDTPG_NC,
                'type'      => Controls_Manager::CHOOSE,
                'options'   => [
                    'start'    => [
                        'title' => __('Left', 'pixel-gallery'),
                        'icon'  => 'eicon-v-align-top',
                    ],
                    'center'  => [
                        'title' => __('Center', 'pixel-gallery'),
                        'icon'  => 'eicon-v-align-middle',
                    ],
                    'end'   => [
                        'title' => __('Right', 'pixel-gallery'),
                        'icon'  => ' eicon-v-align-bottom',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .pg-' . $name . '-grid' => 'align-items: {{VALUE}};',
                ],
				'condition' => [
					'masonry' => ''
				]
            ]
        );
	}

	protected function register_thumbnail_size_controls() {
		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'    => 'thumbnail_size',
				'default' => 'large',
			]
		);
	}

	protected function register_video_icon_controls() {
		$this->start_controls_section(
			'section_style_video_icon',
			[
				'label' => __('Play Icon', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'link_to' => 'file',
				]
			]
		);

		$this->add_control(
			'video_icon_color',
			[
				'label'     => __('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-eicon-play' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'video_icon_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-eicon-play' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'video_icon_size',
			[
				'label' => esc_html__('Size', 'pixel-gallery'),
				'type'  => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .pg-eicon-play' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function register_title_controls($name) {

		/**
		 * Post Format Global
		 */
		$this->register_video_icon_controls();

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
					'{{WRAPPER}} .pg-' . $name . '-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label'      => esc_html__('Padding', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-' . $name . '-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-' . $name . '-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .pg-' . $name . '-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'title_text_shadow',
				'label' => __('Text Shadow', 'pixel-gallery'),
				'selector' => '{{WRAPPER}} .pg-' . $name . '-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name' => 'title_text_stroke',
				'selector' => '{{WRAPPER}} .pg-' . $name . '-title',
			]
		);

		$this->end_controls_section();
	}

	protected function register_grid_controls($name) {
		
		/**
		 * Masonry Columns
		 */
		$this->add_responsive_control(
			'columns',
			[
				'label'          => __('Columns', 'pixel-gallery'),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'selectors'      => [
					'{{WRAPPER}} .pg-' . $name . '-grid' => 'columns: {{SIZE}}; display: block;'
				],
				'condition' => [
					'masonry' => 'yes'
				],
			]
		);
		
		$this->add_control(
			'grid_template_columns',
			[
				'label' => esc_html__('Grid Template Columns', 'pixel-gallery'),
				'description' => esc_html__('Note: If you Changed Grid Template Columns then you must set Column Span and Row Span from Repeater Items.', 'pixel-gallery'),
				'type'  => Controls_Manager::NUMBER,
				'min'   => 1,
				'max'   => 12,
				'default' => 12,
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr); grid-auto-flow: dense;',
				],
				'condition' => [
					'masonry' => ''
				],
			]
		);

		$this->add_responsive_control(
			'row_gap',
			[
				'label' => esc_html__('Row Gap', 'pixel-gallery'),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-grid' => 'grid-row-gap: {{SIZE}}{{UNIT}};',
					'(mobile){{WRAPPER}}.pg-masonry--yes .pg-' . $name . '-grid' => 'margin-bottom: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.pg-masonry--yes .pg-' . $name . '-grid .pg-' . $name . '-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label' => esc_html__('Column Gap', 'pixel-gallery'),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-grid' => 'grid-column-gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.pg-masonry--yes .pg-' . $name . '-grid' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

	}

	protected function register_meta_controls($name) {
		$this->start_controls_section(
			'section_style_meta',
			[
				'label' => __('Meta', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_meta' => 'yes',
				]
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label'     => __('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-meta, {{WRAPPER}} .pg-' . $name . '-meta a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'meta_hover_color',
			[
				'label'     => __('Hover Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-meta:hover, {{WRAPPER}} .pg-' . $name . '-meta a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'meta_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-' . $name . '-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .pg-' . $name . '-meta',
			]
		);

		$this->end_controls_section();
	}

	protected function register_date_controls($name) {
		$this->start_controls_section(
			'section_style_date',
			[
				'label' => __('Date', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_date' => 'yes',
				]
			]
		);

		$this->add_control(
			'date_color',
			[
				'label'     => __('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-date' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'date_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-' . $name . '-date' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'date_typography',
				'selector' => '{{WRAPPER}} .pg-' . $name . '-date',
			]
		);

		$this->end_controls_section();
	}

	protected function register_text_controls($name) {
		$this->start_controls_section(
			'section_style_text',
			[
				'label' => __('Text', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_text' => 'yes',
				]
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => __('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'text_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-' . $name . '-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .pg-' . $name . '-text',
			]
		);

		$this->end_controls_section();
	}

	protected function register_readmore_controls($name) {
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
					'{{WRAPPER}} .pg-' . $name . '-readmore a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'readmore_background',
				'selector' => '{{WRAPPER}} .pg-' . $name . '-readmore a',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'readmore_border',
				'label'       => esc_html__('Border', 'pixel-gallery'),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pg-' . $name . '-readmore a',
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
					'{{WRAPPER}} .pg-' . $name . '-readmore a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-' . $name . '-readmore a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-' . $name . '-readmore' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'read_more_typography',
				'selector' => '{{WRAPPER}} .pg-' . $name . '-readmore a',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'readmore_box_shadow',
				'selector' => '{{WRAPPER}} .pg-' . $name . '-readmore a',
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
					'{{WRAPPER}} .pg-' . $name . '-readmore a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'readmore_hover_background',
				'selector' => '{{WRAPPER}} .pg-' . $name . '-readmore a:hover',
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
					'{{WRAPPER}} .pg-' . $name . '-readmore a:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_lightbox_controls() {
		$this->add_control(
			'link_to',
			[
				'label' => esc_html__('Link', 'pixel-gallery'),
				'type' => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none' => esc_html__('None', 'pixel-gallery'),
					'file' => esc_html__('Media File', 'pixel-gallery'),
					'custom' => esc_html__('Custom URL', 'pixel-gallery'),
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'open_lightbox',
			[
				'label' => esc_html__('Lightbox', 'pixel-gallery'),
				'type' => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default' => esc_html__('Default', 'pixel-gallery'),
					'yes' => esc_html__('Yes', 'pixel-gallery'),
					'no' => esc_html__('No', 'pixel-gallery'),
				],
				'condition' => [
					'link_to' => 'file',
				],
			]
		);
	}

	protected function register_link_target_controls() {
		$this->add_control(
			'link_target',
			[
				'label' => esc_html__('Link Target', 'pixel-gallery'),
				'type' => Controls_Manager::SELECT,
				'default' => 'whole_item',
				'options' => [
					'whole_item' => esc_html__('Whole Item', 'pixel-gallery'),
					'only_button' => esc_html__('Only Button', 'pixel-gallery'),
				],
				'condition' => [
					'link_to' => ['file', 'custom'],
				],
			]
		);
	}

	//Clip Path & svg mask
	protected function register_clip_path_controls($name) {
		$this->start_controls_section(
			'pg_section_style_clip_path',
			[
				'label'     => esc_html__('Mask', 'pixel-gallery') . BDTPG_NC,
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'mask_type',
			[
				'label' => esc_html__('Type', 'pixel-gallery'),
				'type' => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none' => esc_html__('None', 'pixel-gallery'),
					'clip_path_mask' => esc_html__('Clip Path Mask', 'pixel-gallery'),
					'svg_mask' => esc_html__('Svg Mask', 'pixel-gallery'),
				],
			]
		);

		$this->start_controls_tabs('tabs_item_clip_path_style');

		$this->start_controls_tab(
			'tab_item_clip_path_normal',
			[
				'label' => esc_html__('Normal', 'pixel-gallery'),
				'condition' => [
					'mask_type' => 'clip_path_mask',
				],
			]
		);

		$this->add_control(
			'clip_path',
			[
				'label'       => esc_html__('Clip Path', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXTAREA,
				'description'   => sprintf(__('Enter your clip path value, if you don\'t understand clip path so please %1s look here %2s', 'pixel-gallery'), '<a href="https://bennettfeely.com/clippy/" target="_blank">', '</a>'),
				'placeholder' => esc_html__('polygon(25% 0%, 100% 0%, 75% 100%, 0% 100%);', 'pixel-gallery'),
				'label_block' => true,
				'selectors'  => [
					'{{WRAPPER}} .pg-' . $name . '-image-wrap' => 'clip-path: {{VALUE}}; -webkit-clip-path: {{VALUE}};',
				],
				'condition' => [
					'mask_type' => 'clip_path_mask',
				],
			]
		);

		$this->add_control(
			'transition',
			[
				'label'       => esc_html__('CSS Transition', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXTAREA,
				'placeholder' => esc_html__('Enter your CSS transition value', 'pixel-gallery'),
				'label_block' => true,
				'selectors'  => [
					'{{WRAPPER}} .pg-' . $name . '-image-wrap' => 'transition: {{VALUE}};',
				],
				'condition' => [
					'mask_type' => 'clip_path_mask',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_item_clip_path_hover',
			[
				'label' => esc_html__('Hover', 'pixel-gallery'),
				'condition' => [
					'mask_type' => 'clip_path_mask',
				],
			]
		);


		$this->add_control(
			'clip_path_hover',
			[
				'label'       => esc_html__('Clip Path', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXTAREA,
				'description'   => sprintf(__('Enter your clip path value, if you don\'t understand clip path so please %1s look here %2s', 'pixel-gallery'), '<a href="https://bennettfeely.com/clippy/" target="_blank">', '</a>'),
				'placeholder' => esc_html__('polygon(25% 0%, 100% 0%, 75% 100%, 0% 100%);', 'pixel-gallery'),
				'label_block' => true,
				'selectors'  => [
					'{{WRAPPER}} .pg-' . $name . '-item:hover .pg-' . $name . '-image-wrap' => 'clip-path: {{VALUE}}; -webkit-clip-path: {{VALUE}};',
				],
				'condition' => [
					'mask_type' => 'clip_path_mask',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		//svg mask

		$this->add_control(
			'image_mask_shape',
			[
				'label'     => esc_html__('Masking Shape', 'pixel-gallery'),
				'title'     => esc_html__('Masking Shape', 'pixel-gallery'),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'default',
				'options'   => [
					'default' => [
						'title' => esc_html__('Default Shapes', 'pixel-gallery'),
						'icon'  => 'eicon-star',
					],
					'custom'  => [
						'title' => esc_html__('Custom Shape', 'pixel-gallery'),
						'icon'  => 'eicon-image-bold',
					],
				],
				'toggle'    => false,
				'condition' => [
					'mask_type' => 'svg_mask',
				],
			]
		);

		$this->add_control(
			'image_mask_shape_default',
			[
				'label'          => _x('Default', 'Mask Image', 'pixel-gallery'),
				'label_block'    => true,
				'show_label'     => false,
				'type'           => Controls_Manager::VISUAL_CHOICE,
				'columns'        => 4,
				'default'        => 'shape-1',
				'options'        => pixel_gallery_mask_shapes_options(),
				'selectors'      => [
					'{{WRAPPER}} .pg-' . $name . '-image-wrap img' => '-webkit-mask-image: url('.BDTPG_ASSETS_URL . 'images/mask/'.'{{VALUE}}.svg); mask-image: url('.BDTPG_ASSETS_URL . 'images/mask/'.'{{VALUE}}.svg);',
					'{{WRAPPER}} .pg-' . $name . '-image-wrap:before' => 'background-image: url('.BDTPG_ASSETS_URL . 'images/mask/color-'.'{{VALUE}}.svg);',
				],
				'condition'      => [
					'image_mask_shape'   => 'default',
					'mask_type' => 'svg_mask',
				],
				'style_transfer' => true,
			]
		);

		$this->add_control(
			'image_mask_shape_custom',
			[
				'label'      => _x('Custom Shape', 'Mask Image', 'pixel-gallery'),
				'type'       => Controls_Manager::MEDIA,
				'show_label' => false,
				'selectors'  => [
					'{{WRAPPER}} .pg-' . $name . '-image-wrap img' => '-webkit-mask-image: url({{URL}}); mask-image: url({{URL}});',
				],
				'condition'  => [
					'image_mask_shape'   => 'custom',
					'mask_type' => 'svg_mask',
				],
			]
		);

		$this->add_control(
			'image_mask_shape_position',
			[
				'label'                => esc_html__('Position', 'pixel-gallery'),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'center-center',
				'options'              => [
					'center-center' => esc_html__('Center Center', 'pixel-gallery'),
					'center-left'   => esc_html__('Center Left', 'pixel-gallery'),
					'center-right'  => esc_html__('Center Right', 'pixel-gallery'),
					'top-center'    => esc_html__('Top Center', 'pixel-gallery'),
					'top-left'      => esc_html__('Top Left', 'pixel-gallery'),
					'top-right'     => esc_html__('Top Right', 'pixel-gallery'),
					'bottom-center' => esc_html__('Bottom Center', 'pixel-gallery'),
					'bottom-left'   => esc_html__('Bottom Left', 'pixel-gallery'),
					'bottom-right'  => esc_html__('Bottom Right', 'pixel-gallery'),
				],
				'selectors_dictionary' => [
					'center-center' => 'center center',
					'center-left'   => 'center left',
					'center-right'  => 'center right',
					'top-center'    => 'top center',
					'top-left'      => 'top left',
					'top-right'     => 'top right',
					'bottom-center' => 'bottom center',
					'bottom-left'   => 'bottom left',
					'bottom-right'  => 'bottom right',
				],
				'selectors'            => [
					'{{WRAPPER}} .pg-' . $name . '-image-wrap img' => '-webkit-mask-position: {{VALUE}}; mask-position: {{VALUE}};',
				],
				'condition'            => [
					'mask_type' => 'svg_mask',
				],
			]
		);

		$this->add_control(
			'image_mask_shape_size',
			[
				'label'     => esc_html__('Size', 'pixel-gallery'),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'contain',
				'options'   => [
					'auto'    => esc_html__('Auto', 'pixel-gallery'),
					'cover'   => esc_html__('Cover', 'pixel-gallery'),
					'contain' => esc_html__('Contain', 'pixel-gallery'),
					'initial' => esc_html__('Custom', 'pixel-gallery'),
				],
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-image-wrap img' => '-webkit-mask-size: {{VALUE}}; mask-size: {{VALUE}};',
				],
				'condition' => [
					'mask_type' => 'svg_mask',
				],
			]
		);

		$this->add_control(
			'image_mask_shape_custom_size',
			[
				'label'      => _x('Custom Size', 'Mask Image', 'pixel-gallery'),
				'type'       => Controls_Manager::SLIDER,
				'responsive' => true,
				'size_units' => ['px', 'em', '%', 'vw'],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
					'em' => [
						'min' => 0,
						'max' => 100,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
					'vw' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'size' => 100,
					'unit' => '%',
				],
				'required'   => true,
				'selectors'  => [
					'{{WRAPPER}} .pg-' . $name . '-image-wrap img' => '-webkit-mask-size: {{SIZE}}{{UNIT}}; mask-size: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'image_mask_shape_size' => 'initial',
					'mask_type' => 'svg_mask',
				],
			]
		);

		$this->add_control(
			'image_mask_shape_repeat',
			[
				'label'                => esc_html__('Repeat', 'pixel-gallery'),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'no-repeat',
				'options'              => [
					'repeat'          => esc_html__('Repeat', 'pixel-gallery'),
					'repeat-x'        => esc_html__('Repeat-x', 'pixel-gallery'),
					'repeat-y'        => esc_html__('Repeat-y', 'pixel-gallery'),
					'space'           => esc_html__('Space', 'pixel-gallery'),
					'round'           => esc_html__('Round', 'pixel-gallery'),
					'no-repeat'       => esc_html__('No-repeat', 'pixel-gallery'),
					'repeat-space'    => esc_html__('Repeat Space', 'pixel-gallery'),
					'round-space'     => esc_html__('Round Space', 'pixel-gallery'),
					'no-repeat-round' => esc_html__('No-repeat Round', 'pixel-gallery'),
				],
				'selectors_dictionary' => [
					'repeat'          => 'repeat',
					'repeat-x'        => 'repeat-x',
					'repeat-y'        => 'repeat-y',
					'space'           => 'space',
					'round'           => 'round',
					'no-repeat'       => 'no-repeat',
					'repeat-space'    => 'repeat space',
					'round-space'     => 'round space',
					'no-repeat-round' => 'no-repeat round',
				],
				'selectors'            => [
					'{{WRAPPER}} .pg-' . $name . '-image-wrap img' => '-webkit-mask-repeat: {{VALUE}}; mask-repeat: {{VALUE}};',
				],
				'condition'            => [
					'mask_type' => 'svg_mask',
				],
			]
		);

		$this->end_controls_section();
	}

	

	
	/**
	 * Repeater Title Global
	 */

	protected function register_repeater_title_controls($repeater){
		$repeater->add_control(
			'title',
			[
				'label'       => __('Title', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [
					'active' => true,
				],
				'default'     => esc_html__('Gallery Title Here', 'pixel-gallery'),
				'placeholder' => __('Enter your title', 'pixel-gallery'),
				'label_block' => true,
				'condition' => ['item_hidden' => ''],
				'separator' => 'before'
			]
		);
		
	}

	/**
	 * Repeater Meta Global
	 */

	protected function register_repeater_meta_controls($repeater){
		$repeater->add_control(
			'meta',
			[
				'label'       => esc_html__('Meta', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('Meta content', 'pixel-gallery'),
				'placeholder' => esc_html__('Enter your content', 'pixel-gallery'),
				'label_block' => true,
				'condition' => ['item_hidden' => '']
			]
		);
	}

	/**
	 * Repeater text Global
	 */

	protected function register_repeater_text_controls($repeater){
		$repeater->add_control(
			'text',
			[
				'label'       => esc_html__('Text', 'pixel-gallery'),
				'type'        => Controls_Manager::WYSIWYG,
				'label_block' => true,
				'dynamic'     => ['active' => true],
				'default'     => esc_html__('Lorem ipsum may be used as a placeholder before final.', 'pixel-gallery'),
				'condition' => ['item_hidden' => '']
			]
		);
	}

	/**
	 * Repeater readmore Global
	 */

	protected function register_repeater_readmore_controls($repeater){
		$repeater->add_control(
			'readmore_text',
			[
				'label'       => esc_html__('Read More Text', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('Read More', 'pixel-gallery'),
				'placeholder' => esc_html__('Read More', 'pixel-gallery'),
				'condition' => ['item_hidden' => '']
			]
		);
	}

	/**
	 * Repeater date Global
	 */

	protected function register_repeater_date_controls($repeater){
		$repeater->add_control(
			'date',
			[
				'label'       => esc_html__('Date', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('February 3, 2022', 'pixel-gallery'),
				'placeholder' => esc_html__('Date Text', 'pixel-gallery'),
				'condition' => ['item_hidden' => '']
			]
		);
	}

	/**
	 * Repeater Custom URL Global
	 */

	protected function register_repeater_custom_url_controls($repeater){
		$repeater->add_control(
			'link',
			[
				'label'       => esc_html__('Custom URL', 'pixel-gallery'),
				'type'        => Controls_Manager::URL,
				'dynamic'     => ['active' => true],
				'placeholder' => 'http://your-link.com',
				'condition' => ['item_hidden' => '']
			]
		);
	}

	/**
	 * Repeater Item Height Global
	 */

	protected function register_repeater_item_height_controls($repeater, $name){
		$repeater->add_responsive_control(
			'current_item_height',
			[
				'label'   => __('Height', 'pixel-gallery'),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min' => 200,
						'max' => 800,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-grid {{CURRENT_ITEM}}' => 'height: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before'
			]
		);
	}

	/**
	 * Repeater Grid Global
	 */

	protected function register_repeater_grid_controls($repeater, $name){
		$repeater->add_responsive_control(
			'column_span',
			[
				'label' => esc_html__('Column Span', 'pixel-gallery'),
				'type'  => Controls_Manager::SELECT,
				'default'        => '4',
				'tablet_default' => '6',
				'mobile_default' => '12',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
					'8' => '8',
					'9' => '9',
					'10' => '10',
					'11' => '11',
					'12' => '12',
				],
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-grid {{CURRENT_ITEM}}' => 'grid-column: span {{VALUE}} / auto;',
				],
			]
		);

		$repeater->add_responsive_control(
			'row_span',
			[
				'label' => esc_html__('Row Span', 'pixel-gallery'),
				'type'  => Controls_Manager::SELECT,
				'default'        => '1',
				'tablet_default' => '1',
				'mobile_default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
					'7' => '7',
					'8' => '8',
					'9' => '9',
					'10' => '10',
					'11' => '11',
					'12' => '12',
				],
				'selectors' => [
					'{{WRAPPER}} .pg-' . $name . '-grid {{CURRENT_ITEM}}' => 'grid-row: span {{VALUE}} / auto;',
				],
			]
		);
	}

	/**
	 * Repeater Grid Global
	 */

	protected function register_repeater_items_controls($repeater){
		$this->add_control(
			'items',
			[
				'show_label'  => false,
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '<div style="display: flex !important; align-items: center; height: 100%;"><img src="{{{ image.url }}}" style="height: 24px; width: 24px; border-radius: 40px; margin-right: 10px; object-fit: cover;"> <span>{{{ title }}}</span></div>',
				'default'     => [
					[
						'title' => __('Gallery Item one', 'pixel-gallery'),
						'image' => ['url' => BDTPG_ASSETS_URL . 'images/item-1.svg']
					],
					[
						'title' => __('Gallery Item two', 'pixel-gallery'),
						'image' => ['url' => BDTPG_ASSETS_URL . 'images/item-2.svg']
					],
					[
						'title' => __('Gallery Item three', 'pixel-gallery'),
						'image' => ['url' => BDTPG_ASSETS_URL . 'images/item-3.svg']
					],
					[
						'title' => __('Gallery Item four', 'pixel-gallery'),
						'image' => ['url' => BDTPG_ASSETS_URL . 'images/item-4.svg']
					],
					[
						'title' => __('Gallery Item five', 'pixel-gallery'),
						'image' => ['url' => BDTPG_ASSETS_URL . 'images/item-5.svg']
					],
					[
						'title' => __('Gallery Item six', 'pixel-gallery'),
						'image' => ['url' => BDTPG_ASSETS_URL . 'images/item-6.svg']
					],
				]
			]
		);
	}

	/**
	 * Repeater Hidden Item Global
	 */

	protected function register_repeater_hidden_item_controls($repeater){
		$repeater->add_control(
			'item_hidden',
			[
				'label'   => __('Item Hidden', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'render_type' => 'template',
				'separator' => 'before'
			]
		);

		$repeater->add_control(
			'item_hidden_on_tablet',
			[
				'label'   => __('Blank Space Hide on Tablet', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'selectors' => [
					'(tablet){{WRAPPER}} {{CURRENT_ITEM}}' => 'display: none;',
				],
				'condition' => ['item_hidden' => 'yes']
			]
		);

		$repeater->add_control(
			'item_hidden_on_mobile',
			[
				'label'   => __('Item Hide on Mobile', 'pixel-gallery'),
				'type'    => Controls_Manager::HIDDEN,
				'default' => '1',
				'selectors' => [
					'(mobile){{WRAPPER}} {{CURRENT_ITEM}}' => 'display: none;',
				],
				'condition' => ['item_hidden' => 'yes']
			]
		);
	}

	 /**
	  * Start Media, Video Repeater part global
	  */
	protected function register_repeater_media_controls($repeater){
		$repeater->add_control(
			'media_type',
			[
				'label'       => __('Media Type', 'pixel-gallery'),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'image' => [
						'title' => __('Image', 'pixel-gallery'),
						'icon'  => 'eicon-image',
					],
					'video' => [
						'title' => __('Video', 'pixel-gallery'),
						'icon'  => 'eicon-video-playlist',
					],
				],
				'toggle'      => false,
				'default'     => 'image',
				'condition' => [
					'item_hidden' => '',
				]
			]
		);

		$repeater->add_control(
			'image',
			[
				'label'       => __('Image', 'pixel-gallery'),
				'type'        => Controls_Manager::MEDIA,
				'render_type' => 'template',
				'dynamic'     => [
					'active' => true,
				],
				'default'     => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'item_hidden' => '',
					'media_type' => 'image'
				]
			]
		);

		$repeater->add_control(
			'poster',
			[
				'label'       => __('Poster', 'pixel-gallery'),
				'type'        => Controls_Manager::MEDIA,
				'render_type' => 'template',
				'dynamic'     => [
					'active' => true,
				],
				'default'     => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'item_hidden' => '',
					'media_type' => 'video'
				]
			]
		);

		$repeater->add_control(
			'video_type',
			[
				'label' => esc_html__('Source', 'pixel-gallery'),
				'type' => Controls_Manager::SELECT,
				'default' => 'youtube',
				'options' => [
					'youtube' => esc_html__('YouTube', 'pixel-gallery'),
					'vimeo' => esc_html__('Vimeo', 'pixel-gallery'),
					'dailymotion' => esc_html__('Dailymotion', 'pixel-gallery'),
					'hosted' => esc_html__('Self Hosted', 'pixel-gallery'),
				],
				'frontend_available' => true,
				'condition' => [
					'item_hidden' => '',
					'media_type' => 'video'
				]
			]
		);

		$repeater->add_control(
			'youtube_url',
			[
				'label' => esc_html__('Source Link', 'pixel-gallery'),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'placeholder' => esc_html__('Enter your URL', 'pixel-gallery') . ' (YouTube)',
				'default' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
				'label_block' => true,
				'condition' => [
					'video_type' => 'youtube',
					'item_hidden' => '',
					'media_type' => 'video'
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'vimeo_url',
			[
				'label' => esc_html__('Source Link', 'pixel-gallery'),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'placeholder' => esc_html__('Enter your URL', 'pixel-gallery') . ' (Vimeo)',
				'default' => 'https://vimeo.com/235215203',
				'label_block' => true,
				'condition' => [
					'video_type' => 'vimeo',
					'item_hidden' => '',
					'media_type' => 'video'
				],
			]
		);

		$repeater->add_control(
			'dailymotion_url',
			[
				'label' => esc_html__('Source Link', 'pixel-gallery'),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'placeholder' => esc_html__('Enter your URL', 'pixel-gallery') . ' (Dailymotion)',
				'default' => 'https://www.dailymotion.com/video/x6tqhqb',
				'label_block' => true,
				'condition' => [
					'video_type' => 'dailymotion',
					'item_hidden' => '',
					'media_type' => 'video'
				],
			]
		);

		$repeater->add_control(
			'insert_url',
			[
				'label' => esc_html__('External URL', 'pixel-gallery'),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video_type' => 'hosted',
					'item_hidden' => '',
					'media_type' => 'video'
				],
			]
		);

		$repeater->add_control(
			'hosted_url',
			[
				'label' => esc_html__('Choose File', 'pixel-gallery'),
				'type' => Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::MEDIA_CATEGORY,
					],
				],
				'media_type' => 'video',
				'condition' => [
					'video_type' => 'hosted',
					'insert_url' => '',
					'item_hidden' => '',
					'media_type' => 'video'
				],
			]
		);

		$repeater->add_control(
			'external_url',
			[
				'label' => esc_html__('URL', 'pixel-gallery'),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'options' => false,
				'label_block' => true,
				'show_label' => false,
				'dynamic' => [
					'active' => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
				'media_type' => 'video',
				'placeholder' => esc_html__('Enter your URL', 'pixel-gallery'),
				'condition' => [
					'video_type' => 'hosted',
					'insert_url' => 'yes',
					'item_hidden' => '',
					'media_type' => 'video'
				],
			]
		);

		$repeater->add_control(
			'aspect_ratio',
			[
				'label' => esc_html__('Aspect Ratio', 'pixel-gallery'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'169' => '16:9',
					'219' => '21:9',
					'43' => '4:3',
					'32' => '3:2',
					'11' => '1:1',
					'916' => '9:16',
				],
				'default' => '169',
				'prefix_class' => 'elementor-aspect-ratio-',
				'frontend_available' => true,
				'separator' => 'before',
				'condition' => [
					'item_hidden' => '',
					'media_type' => 'video'
				],
			]
		);

		$repeater->add_control(
			'autoplay',
			[
				'label' => esc_html__('Autoplay', 'pixel-gallery'),
				'type' => Controls_Manager::SWITCHER,
				'frontend_available' => true,
				'default' => 'yes',
				'condition' => [
					'item_hidden' => '',
					'media_type' => 'video'
				],
			]
		);

		$repeater->add_control(
			'loop',
			[
				'label' => esc_html__('Loop', 'pixel-gallery'),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video_type!' => 'dailymotion',
					'item_hidden' => '',
					'media_type' => 'video'
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'controls',
			[
				'label' => esc_html__('Player Controls', 'pixel-gallery'),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => esc_html__('Hide', 'pixel-gallery'),
				'label_on' => esc_html__('Show', 'pixel-gallery'),
				'default' => 'yes',
				'condition' => [
					'video_type!' => 'vimeo',
					'item_hidden' => '',
					'media_type' => 'video'
				],
				'frontend_available' => true,
			]
		);

		$repeater->add_control(
			'mute',
			[
				'label' => esc_html__('Mute', 'pixel-gallery'),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'frontend_available' => true,
				'condition' => [
					'item_hidden' => '',
					'media_type' => 'video'
				],
			]
		);

	}
	/**
	 * end Media, Video Part
	 */

	/**
	 * Register Justified Gallery Controls
	 * @param string $prefix
	 * @since 1.0.0
	 */
	protected function register_justified_gallery_controls() {
		$this->add_control(
			'justified_gallery',
			[
				'label'       => esc_html__('Justified Gallery', 'pixel-gallery') . BDTPG_NC . BDTPG_PC,
				'type'        => Controls_Manager::SWITCHER,
				'description' => esc_html__('Note: If you enable Justified Gallery then Grid, Masonry, and Column Span/Row Span settings will not work.', 'pixel-gallery'),
				'separator'   => 'before',
				'classes'   => BDTPG_IS_PC
			]
		);
		
		$this->add_control(
			'gallery_item_height',
			[
				'label'   => esc_html__('Image Height', 'pixel-gallery'),
				'description'   => esc_html__('Some times image height not exactly same because of auto row adjustment.', 'pixel-gallery'),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 260,
				],
				'range' => [
					'px' => [
						'min'  => 50,
						'max'  => 500,
						'step' => 5,
					],
				],
				'condition' => [
					'justified_gallery' => 'yes'
				]
			]
		);

		$this->add_responsive_control(
			'row_column_gap',
			[
				'label'   => esc_html__('Item Gap', 'pixel-gallery'),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
					],
				],
				'condition' => [
					'justified_gallery' => 'yes'
				]
			]
		);
	}




	/**
	 * Render Function Part Start
	 */
	function get_link_url($item) {
		$settings   = $this->get_settings_for_display();
		if ('none' === $settings['link_to']) {
			return false;
		}

		if ('custom' === $settings['link_to']) {
			if (empty($item['link']['url'])) {
				return false;
			}

			return $item['link'];
		}

		/**
		 * Video Condition Added
		 * @since 1.0.0
		 */
		if ($item['media_type'] !== 'video') {
			// Sanitize the image URL to prevent XSS
			$image_url = isset($item['image']['url']) ? esc_url($item['image']['url']) : 'javascript:void(0);';
			return [
				'url' => $image_url,
			];
		}
	}

	protected function render_lightbox_link_url($item, $index, $id) {
		$settings   = $this->get_settings_for_display();
		$link = $this->get_link_url($item);
		if ($link) {
			$this->add_link_attributes('link' . $index, $link, true);
			$this->add_render_attribute('link' . $index, 'class', 'pg-open-lightbox', true);

			/**
			 * If the Video Added then No need Image Lightbox
			 * Condition added `media_type`
			 * @since 1.0.0
			 */

			if ('custom' !== $settings['link_to'] && $item['media_type'] !== 'video') {
				$this->add_lightbox_data_attributes('link' . $index, $item['image']['id'], $settings['open_lightbox'], '', true);
				$this->add_render_attribute(
					'link' . $index,
					[
						'data-elementor-lightbox-slideshow' => $id,
					]
				);
			}
		}
		?>
		<?php if ($link) : ?>
			<a <?php $this->print_render_attribute_string('link' . $index); ?>></a>
		<?php endif; ?>
		<?php
	}

    protected function render_dynamic_lightbox_link_url($index, $id) {
		$settings   = $this->get_settings_for_display();

		//$link = $this->get_link_url($item);

		//if ($link) {
			//$this->add_link_attributes('link' . $index, $link, true);
			$this->add_render_attribute('link' . $index, 'class', 'pg-open-lightbox', true);

			/**
			 * If the Video Added then No need Image Lightbox
			 * Condition added `media_type`
			 * @since 1.0.0
			 */

			if ('custom' !== $settings['link_to']) {
				$this->add_lightbox_data_attributes('link' . $index, get_post_thumbnail_id(get_the_ID()), $settings['open_lightbox'], '', true);
				$this->add_render_attribute(
					'link' . $index,
					[
						'data-elementor-lightbox-slideshow' => $id,
					]
				);
			}
		//}
		?>
		<?php //if ($link) : ?>
			<a <?php $this->print_render_attribute_string('link' . $index); ?>></a>
		<?php //endif; ?>
		<?php
	}

	protected function render_readmore($item, $index, $id, $name)
	{
		$settings   = $this->get_settings_for_display();
		$link = $this->get_link_url($item);
		if ($link) {
			$this->add_link_attributes('link' . $index, $link, true);

			/**
			 * If the Video Added then No need Image Lightbox
			 * Condition added `media_type`
			 * @since 1.0.0
			 */

			if ('custom' !== $settings['link_to'] && $item['media_type'] !== 'video') {
				$this->add_lightbox_data_attributes('link' . $index, $item['image']['id'], $settings['open_lightbox'], '', true);
				$this->add_render_attribute(
					'link' . $index,
					[
						'data-elementor-lightbox-slideshow' => $id,
					]
				);
			}
		}
	?>
		<?php if ($link && !empty($item['readmore_text'])) : ?>
			<div class="pg-<?php echo esc_attr($name); ?>-readmore">
				<a <?php $this->print_render_attribute_string('link' . $index); ?>>
					<?php echo esc_html($item['readmore_text']); ?>
				</a>
			</div>
		<?php endif; ?>
	<?php
	}
	protected function render_readmore_span($item, $index, $id, $name)
	{
		$settings   = $this->get_settings_for_display();
		$link = $this->get_link_url($item);
		if ($link) {
			$this->add_link_attributes('link' . $index, $link, true);

			/**
			 * If the Video Added then No need Image Lightbox
			 * Condition added `media_type`
			 * @since 1.0.0
			 */

			if ('custom' !== $settings['link_to'] && $item['media_type'] !== 'video') {
				$this->add_lightbox_data_attributes('link' . $index, $item['image']['id'], $settings['open_lightbox'], '', true);
				$this->add_render_attribute(
					'link' . $index,
					[
						'data-elementor-lightbox-slideshow' => $id,
					]
				);
			}
		}
	?>
		<?php if ($link && !empty($item['readmore_text'])) : ?>
			<div class="pg-<?php echo esc_attr($name); ?>-readmore">
				<a <?php $this->print_render_attribute_string('link' . $index); ?>>
					<span><?php echo esc_html($item['readmore_text']); ?></span>
				</a>
			</div>
		<?php endif; ?>
	<?php
	}
	protected function render_readmore_icon($item, $index, $id, $name)
	{
		$settings   = $this->get_settings_for_display();
		$link = $this->get_link_url($item);
		if ($link) {
			$this->add_link_attributes('link' . $index, $link, true);
			if ('custom' !== $settings['link_to'] && $item['media_type'] !== 'video') {
				$this->add_lightbox_data_attributes('link' . $index, $item['image']['id'], $settings['open_lightbox'], '', true);
				$this->add_render_attribute(
					'link' . $index,
					[
						'data-elementor-lightbox-slideshow' => $id,
					]
				);
			}
		}
	?>
		<?php if ($link) : ?>
			<div class="pg-<?php echo esc_attr($name); ?>-readmore">
				<a <?php $this->print_render_attribute_string('link' . $index); ?>>
					<i class="pg-icon-arrow-right"></i>
				</a>
			</div>
		<?php endif; ?>
	<?php
	}

	protected function render_image($item, $name) {
		$settings = $this->get_settings_for_display();

		$thumb_url = Group_Control_Image_Size::get_attachment_image_src($item['image']['id'], 'thumbnail_size', $settings);
		if (!$thumb_url) {
			// Sanitize the image URL to prevent XSS
			$image_url = isset($item['image']['url']) ? esc_url($item['image']['url']) : '';
			printf('<img src="%1$s" alt="%2$s" class="pg-%3$s-img">', esc_url( $image_url ), esc_html($item['title']), esc_attr($name));
		} else {
			print(wp_get_attachment_image(
				$item['image']['id'],
				$settings['thumbnail_size_size'],
				false,
				[
					'class' => 'pg-'. esc_attr($name) .'-img',
					'alt' => esc_html($item['title'])
				]
			));
		}
           
	}

    protected function render_dynamic_image($post_id, $size, $name) {
		$settings = $this->get_settings_for_display();

        $placeholder_image_src = Utils::get_placeholder_image_src();
        $image_src = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), $size);

		if (!$image_src) {
			printf('<img src="%1$s" alt="%2$s" class="pg-%3$s-img">', esc_url( $placeholder_image_src ), esc_html(get_the_title()), esc_attr($name));
		} else {
			print(wp_get_attachment_image(
				get_post_thumbnail_id(),
				$size,
				false,
				[
					'class' => 'pg-'. esc_attr($name) .'-img',
					'alt' => esc_html(get_the_title())
				]
			));
		}

	}

	/**
	 * Video Poster Global
	 */
	protected function render_image_wrap($item, $name) {
		$settings = $this->get_settings_for_display();
		?>
		<div class="pg-<?php echo esc_attr($name); ?>-image-wrap bdt-pg-img-mask">
			<?php
			/**
			 * Added Poster for Video
			 * @since 1.0.0
			 */
			if ($item['media_type'] == 'video') {
				$this->render_poster($item, $name);
			} else {
				$this->render_image($item, $name);
			}
			?>
			<?php if ('file' == $settings['link_to'] && $item['media_type'] == 'video') : ?>
			<span class="pg-video-icon-wrap">
				<i class="pg-icon-play-circle pg-eicon-play"></i>
			</span>
			<?php endif; ?>
		</div>
		<?php
	}

    protected function render_dynamic_image_wrap($post_id, $size, $name) {
		$settings = $this->get_settings_for_display();
		?>
		<div class="pg-<?php echo esc_attr($name); ?>-image-wrap">
			<?php
			/**
			 * Added Poster for Video
			 * @since 1.0.0
			 */
			//if ($item['media_type'] == 'video') {
				//$this->render_poster($item, $name);
			//} else {
				$this->render_dynamic_image($post_id, $size, $name);
			//}
			?>



		</div>
		<?php
	}

	protected function render_poster($item, $name) {
		$settings = $this->get_settings_for_display();

		$thumb_url = Group_Control_Image_Size::get_attachment_image_src($item['poster']['id'], 'thumbnail_size', $settings);
		if (!$thumb_url) {
			// Sanitize the poster URL to prevent XSS
			$poster_url = isset($item['poster']['url']) ? esc_url($item['poster']['url']) : '';
			printf('<img src="%1$s" alt="%2$s" class="pg-%3$s-img">', esc_url( $poster_url ), esc_html($item['title']), esc_attr($name));
		} else {
			print(wp_get_attachment_image(
				$item['poster']['id'],
				$settings['thumbnail_size_size'],
				false,
				[
					'class' => 'pg-'. esc_attr($name) .'-img',
					'alt' => esc_html($item['title'])
				]
			));
		}

	}

	protected function render_title($item, $name) {
		$settings = $this->get_settings_for_display();

		if (!$settings['show_title']) {
			return;
		}

		if (!empty($item['title'])) {
			printf('<%1$s class="pg-%3$s-title">%2$s</%1$s>', esc_attr( Utils::get_valid_html_tag($settings['title_tag']) ), wp_kses_post($item['title']), esc_attr($name));
		}
	}

    protected function render_dynamic_title($name) {
		$settings = $this->get_settings_for_display();

		if (!$settings['show_title']) {
			return;
		}

        printf('<%1$s class="pg-%3$s-title">%2$s</%1$s>', esc_attr( Utils::get_valid_html_tag($settings['title_tag']) ), esc_html( get_the_title() ), esc_attr($name));
	}

	protected function render_meta($item, $name) {
		$settings = $this->get_settings_for_display();

		if (!$settings['show_meta']) {
			return;
		}

	?>
		<?php if (!empty($item['meta'])) : ?>
			<div class="pg-<?php echo esc_attr($name); ?>-meta">
				<?php echo wp_kses_post($item['meta']); ?>
			</div>
		<?php endif;
	}

    protected function render_dynamic_meta($name) {
		$settings = $this->get_settings_for_display();

		if (!$settings['show_meta']) {
			return;
		}

	    ?>

        <div class="pg-<?php echo esc_attr($name); ?>-meta">
            <?php echo pixel_gallery_get_category_list($settings['posts_source']); ?>
        </div>

        <?php

	}

	protected function render_text($item, $name)
	{
		$settings = $this->get_settings_for_display();

		if (!$settings['show_text']) {
			return;
		}

		?>
		<?php if ($item['text']) : ?>
			<div class="pg-<?php echo esc_attr($name); ?>-text">
				<?php echo wp_kses_post($item['text']); ?>
			</div>
		<?php endif;
	}

	protected function render_date($item, $name)
	{
		$settings = $this->get_settings_for_display();

		if (!$settings['show_date']) {
			return;
		}

		?>
		<?php if ($item['date']) : ?>
			<div class="pg-<?php echo esc_attr($name); ?>-date">
				<span><?php echo esc_html($item['date']); ?></span>
			</div>
	<?php endif;
	}

	/**
	 * Video Source function here
	 * It's work for media file and lightbox
	 * @since 1.0.0
	 */
	protected function get_embed_params($item) {
		$settings = $this->get_settings_for_display();

		$params = [];

		if ($item['autoplay']){
			$params['autoplay'] = '1';
		}

		$params['playsinline'] = '1';

		$params_dictionary = [];

		if ('youtube' === $item['video_type']) {
			$params_dictionary = [
				'loop',
				'controls',
				'mute',
				'rel',
				'modestbranding',
			];

			$params['wmode'] = 'opaque';
		} elseif ('vimeo' === $item['video_type']) {
			$params_dictionary = [
				'loop',
				'mute' => 'muted',
				'vimeo_title' => 'title',
				'vimeo_portrait' => 'portrait',
				'vimeo_byline' => 'byline',
			];


			$params['autopause'] = '0';
		} elseif ('dailymotion' === $item['video_type']) {
			$params_dictionary = [
				'controls',
				'mute',
				'showinfo' => 'ui-start-screen-info',
				'logo' => 'ui-logo',
			];

			$params['endscreen-enable'] = '0';
		}

		foreach ($params_dictionary as $key => $param_name) {
			$setting_name = $param_name;

			if (is_string($key)) {
				$setting_name = $key;
			}
			if(isset($item[$setting_name])){
				$setting_value = $item[$setting_name] ? '1' : '0';
			}

			$params[$param_name] = $setting_value;
		}

		return $params;
	}
	protected function get_hosted_params($item) {
		$settings = $this->get_settings_for_display();

		$video_params = [];

		foreach (['autoplay', 'loop', 'controls'] as $option_name) {
			if ($item[$option_name]) {
				$video_params[$option_name] = '';
			}
		}

		if ($item['mute']) {
			$video_params['muted'] = 'muted';
		}

		$video_params['muted'] = 'muted';

		if (isset($item['play_on_mobile'])) {
			$video_params['playsinline'] = '';
		}

		$video_params['controlsList'] = 'nodownload';

		if ($item['poster']['url']) {
			$video_params['poster'] = $item['poster']['url'];
		}

		return $video_params;
	}
	protected function get_hosted_video_url($item) {

		if (!empty($item['insert_url'])) {
			$video_url = $item['external_url']['url'];
		} else {
			$video_url = $item['hosted_url']['url'];
		}

		if (empty($video_url)) {
			return '';
		}

		return $video_url;
	}
	protected function render_hosted_video($item) {
		$settings = $this->get_settings_for_display();
		$video_url = $this->get_hosted_video_url($item);
		if (empty($video_url)) {
			return;
		}

		$video_params['muted'] = 'muted';
		$video_params['controlsList'] = 'nodownload';
		$video_params['poster'] = $item['poster']['url'];
		/* Sometimes the video url is base64, therefore we use `esc_attr` in `src`. */
		?>
		<video class="elementor-video" src="<?php echo esc_attr($video_url); ?>" <?php Utils::print_html_attributes($video_params); ?>></video>
		<?php
	}
	protected function render_video_frame($item, $attr, $id) {
		$settings = $this->get_settings_for_display();

		if ('none' == $settings['link_to']) {
			return;
		}
		

		$video_url = $item[$item['video_type'] . '_url'];

		if ('hosted' === $item['video_type']) {
			$video_url = $this->get_hosted_video_url($item);
		} else {
			$embed_params = $this->get_embed_params($item);
			// $embed_options = $this->get_embed_options();
		}

		$embed_options['lazy_load'] = false;

		if (empty($video_url)) {
			return;
		}

		if ('youtube' === $item['video_type']) {
			$video_html = '<div class="elementor-video"></div>';
		}

		if ('hosted' === $item['video_type']) {
			$this->add_render_attribute('video-wrapper', 'class', 'e-hosted-video');

			ob_start();

			$this->render_hosted_video($item);

			$video_html = ob_get_clean();
		} else {
			if ('youtube' !== $item['video_type']) {
				$video_html = Embed::get_embed_html($video_url, $embed_params, $embed_options);
			}
		}

		if (empty($video_html)) {
			echo esc_url($video_url);

			return;
		}
		if ('hosted' === $item['video_type']) {
			$lightbox_url = $video_url;
		} else {
			$lightbox_url = Embed::get_embed_url($video_url, $embed_params, []);
		}

		$lightbox_options = [
			'type'      	=> 'video',
			'videoType' 	=> $item['video_type'],
			'url'       	=> $lightbox_url,
			'modalOptions' 	=> [
				'id'                    => 'elementor-lightbox-' . $this->get_id(),
				// 'id'                       => $id,
				// 'entranceAnimation'        => $item['lightbox_content_animation'],
				// 'entranceAnimation_tablet' => $item['lightbox_content_animation_tablet'],
				// 'entranceAnimation_mobile' => $item['lightbox_content_animation_mobile'],
				'videoAspectRatio'         => $item['aspect_ratio'],
			],
		];

		if ('hosted' === $item['video_type']) {
			$lightbox_options['videoParams'] = $this->get_hosted_params($item);
		}

		if ('file' == $settings['link_to'] && 'no' !== $settings['open_lightbox']) {
			$this->add_render_attribute($attr, [
				'data-elementor-open-lightbox' => 'yes',
				'data-elementor-lightbox' => wp_json_encode($lightbox_options),
				'e-action-hash' => Plugin::instance()->frontend->create_action_hash('lightbox', $lightbox_options),
				// 'class' => 'pg-open-lightbox',
				// 'data-elementor-lightbox-slideshow' => $id,
			]);
		}

		if ('file' == $settings['link_to'] && 'no' == $settings['open_lightbox']) {
			$this->add_render_attribute($attr, 'onclick', "window.open('" . esc_url($lightbox_url) . "', '_self')", true);
		}

		if (Plugin::$instance->editor->is_edit_mode()) {
			$this->add_render_attribute($attr, [
				'class' => 'elementor-clickable',
			]);
		}
	}

	/**
	 * Render Justified Gallery Attributes
	 * @param string $element
	 * @since 1.0.0
	 */
	protected function render_justified_gallery_attributes($element = 'grid') {
		$settings = $this->get_settings_for_display();
		
		if (isset($settings['justified_gallery']) && $settings['justified_gallery'] === 'yes') {
			$this->add_render_attribute($element, 'class', 'jgallery');
			if ($settings['gallery_item_height']['size']) {
				$this->add_render_attribute($element, 'data-jgallery-jfheight', esc_attr($settings['gallery_item_height']['size']));
			}
			if ($settings['row_column_gap']['size']) {
				$this->add_render_attribute($element, 'data-jgallery-itemgap', esc_attr($settings['row_column_gap']['size']));
			}
		}
	}


	
}
