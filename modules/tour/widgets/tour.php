<?php

namespace PixelGallery\Modules\Tour\Widgets;

use PixelGallery\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Stroke;
use Elementor\Repeater;
use PixelGallery\Utils;
use PixelGallery\Traits\Global_Widget_Controls;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Tour extends Module_Base {

	use Global_Widget_Controls;

	public function get_name() {
		return 'pg-tour';
	}

	public function get_title() {
		return BDTPG . esc_html__('Tour', 'pixel-gallery');
	}

	public function get_icon() {
		return 'pg-icon-tour';
	}

	public function get_categories() {
		return ['pixel-gallery'];
	}

	public function get_keywords() {
		return ['tour', 'grid', 'gallery'];
	}

	public function get_style_depends() {
		return ['pg-tour'];
	}

	public function get_script_depends() {
		if ( true === _is_pg_pro_activated() ) {
			return ['justified-gallery'];
		} else {
			return [];
		}
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/kDB1kUXChP0';
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
		$this->register_grid_controls('tour');
		$this->register_global_height_controls('tour');
		
		/**
		 * Justified Gallery Controls
		 */
		$this->register_justified_gallery_controls();
		
		$this->register_title_tag_controls();
		$this->register_show_meta_controls();
		$this->add_control(
			'show_price',
			[
				'label'   => esc_html__('Show Price', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);
		$this->register_alignment_controls('tour');
		$this->register_thumbnail_size_controls();

		//Global Lightbox Controls
		$this->register_lightbox_controls();
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
		$repeater->add_control(
			'price',
			[
				'label'       => esc_html__('Price', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('$540', 'pixel-gallery'),
				'placeholder' => esc_html__('Enter your price', 'pixel-gallery'),
				'condition' => ['item_hidden' => '']
			]
		);

		$repeater->add_control(
			'meta_days',
			[
				'label'       => esc_html__('Meta Days', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('10 Days', 'pixel-gallery'),
				'placeholder' => esc_html__('Enter your Meta Day', 'pixel-gallery'),
				'condition' => ['item_hidden' => '']
			]
		);

		$repeater->add_control(
			'meta_member',
			[
				'label'       => esc_html__('Meta Member', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('10+', 'pixel-gallery'),
				'placeholder' => esc_html__('Enter your Meta Member', 'pixel-gallery'),
				'condition' => ['item_hidden' => '']
			]
		);

		$repeater->add_control(
			'meta_location',
			[
				'label'       => esc_html__('Meta Location', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('Istanbul', 'pixel-gallery'),
				'placeholder' => esc_html__('Enter your Meta Location', 'pixel-gallery'),
				'condition' => ['item_hidden' => '']
			]
		);
		$this->register_repeater_custom_url_controls($repeater);
		$this->register_repeater_hidden_item_controls($repeater);

		$repeater->end_controls_tab();
		$repeater->start_controls_tab(
			'tab_item_grid',
			[
				'label' => esc_html__('Grid', 'pixel-gallery'),
			]
		);
		$this->register_repeater_grid_controls($repeater, 'tour');
		$this->register_repeater_item_height_controls($repeater, 'tour');
		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();
		$this->register_repeater_items_controls($repeater);
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
			'overlay',
			[
				'label'     => esc_html__( 'Overlay', 'pixel-gallery' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'background',
				'options'   => [
					'none'       => esc_html__( 'None', 'pixel-gallery' ),
					'background' => esc_html__( 'Background', 'pixel-gallery' ),
					'blend'      => esc_html__( 'Blend', 'pixel-gallery' ),
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
				'selector' => '{{WRAPPER}} .pg-tour-image-wrap:before',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => 'rgba(13, 59, 84, 0.2)',
					],
				],
				'condition' => [
					'overlay' => ['background', 'blend'],
				],
			]
		);
		
		$this->add_control(
			'blend_type',
			[
				'label'     => esc_html__( 'Blend Type', 'pixel-gallery' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'multiply',
				'options'   => pixel_gallery_blend_options(),
				'condition' => [
					'overlay' => 'blend',
				],
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
				'selector' => '{{WRAPPER}} .pg-tour-item',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'item_border',
				'selector'  => '{{WRAPPER}} .pg-tour-item',
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
					'{{WRAPPER}} .pg-tour-item, {{WRAPPER}} .pg-tour-image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-tour-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-tour-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .pg-tour-item',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_item_hover',
			[
				'label' => esc_html__('Hover', 'pixel-gallery'),
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
					'{{WRAPPER}} .pg-tour-item:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_hover_box_shadow',
				'selector' => '{{WRAPPER}} .pg-tour-item:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_content',
			[
				'label'     => esc_html__('Content', 'pixel-gallery'),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'content_border',
				'selector'  => '{{WRAPPER}} .pg-tour-content',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'content_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-tour-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => esc_html__('Padding', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-tour-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);


		$this->add_responsive_control(
			'content_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-tour-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'content_box_shadow',
				'selector' => '{{WRAPPER}} .pg-tour-content',
			]
		);

		$this->add_control(
			'line_color',
			[
				'label'     => __('Line Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-tour-line' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before'
			]
		);

		$this->end_controls_section();

		//Global Title Controls
		$this->register_title_controls('tour');

		$this->start_controls_section(
			'section_style_price',
			[
				'label' => __('Price', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_price' => 'yes',
				]
			]
		);

		$this->add_control(
			'price_color',
			[
				'label'     => __('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-tour-price' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'price_background',
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .pg-tour-price',
			]
		);

		$this->add_responsive_control(
			'price_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-tour-price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'price_typography',
				'selector' => '{{WRAPPER}} .pg-tour-price',
			]
		);

		$this->end_controls_section();

		//Global Meta Controls
		$this->register_meta_controls('tour');

		//Clip Path Controls
		$this->register_clip_path_controls('tour');
	}

	public function render_meta($item, $name) {
		$settings = $this->get_settings_for_display();

		if (!$settings['show_meta']) {
			return;
		}

?>
		<?php if (!empty($item['meta_days']) or !empty($item['meta_member']) or !empty($item['meta_location'])) : ?>
			<div class="pg-tour-meta">
				<?php if (!empty($item['meta_days'])) : ?>
					<div class="pg-tour-days">
						<i class="pg-icon-calendar"></i>
						<span><?php echo wp_kses_post($item['meta_days']); ?></span>
					</div>
				<?php endif; ?>
				<?php if (!empty($item['meta_member'])) : ?>
					<div class="pg-tour-member">
						<i class="pg-icon-user"></i>
						<span><?php echo wp_kses_post($item['meta_member']); ?></span>
					</div>
				<?php endif; ?>
				<?php if (!empty($item['meta_location'])) : ?>
					<div class="pg-tour-location">
						<i class="pg-icon-globe"></i>
						<span><?php echo wp_kses_post($item['meta_location']); ?></span>
					</div>
				<?php endif; ?>
			</div>
		<?php endif;
	}

	public function render_price($item, $name) {
		$settings = $this->get_settings_for_display();

		if (!$settings['show_price']) {
			return;
		}

		?>
		<?php if (!empty($item['price'])) : ?>
			<div class="pg-<?php echo esc_attr($name); ?>-price">
				<span><?php echo wp_kses_post($item['price']); ?></span>
			</div>
		<?php endif;
	}

	public function render_items() {
		$settings = $this->get_settings_for_display();
		$id = 'pg-tour-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-tour-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
			<?php $this->render_image_wrap($item, 'tour'); ?>
			<?php $this->render_price($item, 'tour'); ?>
			<div class="pg-tour-content">
				<?php $this->render_title($item, 'tour'); ?>
				<div class="pg-tour-line"></div>
				<?php $this->render_meta($item, 'tour'); ?>
			</div>
			<?php $this->render_lightbox_link_url($item, $index, $id); ?>
			<?php endif; ?>
		</div>

		<?php
			$slide_index++;
		endforeach;
	}

	public function render() {
		$settings   = $this->get_settings_for_display();
		$this->add_render_attribute('grid', 'class', 'pg-tour-grid pg-grid');

		/**
		 * Render Justified Gallery Attributes
		 */
		$this->render_justified_gallery_attributes('grid');
		
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
