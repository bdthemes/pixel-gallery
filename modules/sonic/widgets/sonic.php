<?php

namespace PixelGallery\Modules\Sonic\Widgets;

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

class Sonic extends Module_Base
{

	use Global_Widget_Controls;

	public function get_name()
	{
		return 'pg-sonic';
	}

	public function get_title()
	{
		return BDTPG . esc_html__('Sonic', 'pixel-gallery');
	}

	public function get_icon()
	{
		return 'pg-icon-sonic';
	}

	public function get_categories()
	{
		return ['pixel-gallery'];
	}

	public function get_keywords()
	{
		return ['sonic', 'grid', 'gallery'];
	}

	public function get_style_depends()
	{
		return ['pg-sonic'];
	}
	public function get_script_depends() {
		if ( true === _is_pg_pro_activated() ) {
			return ['justified-gallery'];
		} else {
			return [];
		}
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/8KkaSa-v8l8';
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
		$this->register_grid_controls('sonic');
		$this->register_global_height_controls('sonic');
		
		/**
		 * Justified Gallery Controls
		 */
		$this->register_justified_gallery_controls();
		
		$this->register_title_tag_controls();
		$this->register_alignment_controls('sonic');
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
		$this->register_repeater_custom_url_controls($repeater);
		$this->register_repeater_hidden_item_controls($repeater);

		$repeater->end_controls_tab();
		$repeater->start_controls_tab(
			'tab_item_grid',
			[
				'label' => esc_html__('Grid', 'pixel-gallery'),
			]
		);
		$this->register_repeater_grid_controls($repeater, 'sonic');
		$this->register_repeater_item_height_controls($repeater, 'sonic');
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

		$this->start_controls_tabs('tabs_item_style');

		$this->start_controls_tab(
			'tab_item_normal',
			[
				'label' => esc_html__('Normal', 'pixel-gallery'),
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'item_border',
				'selector'  => '{{WRAPPER}} .pg-sonic-item',
			]
		);

		$this->add_responsive_control(
			'item_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-sonic-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-sonic-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .pg-sonic-item',
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
					'{{WRAPPER}} .pg-sonic-item:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_hover_box_shadow',
				'selector' => '{{WRAPPER}} .pg-sonic-item:hover',
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

		$this->add_control(
			'glassmorphism_effect',
			[
				'label' => esc_html__('Glassmorphism', 'pixel-gallery') . BDTPG_NC,
				'type'  => Controls_Manager::SWITCHER,
				'description' => sprintf(esc_html__('This feature will not work in the Firefox browser untill you enable browser compatibility so please %1s look here %2s', 'pixel-gallery'), '<a href="https://developer.mozilla.org/en-US/docs/Web/CSS/backdrop-filter#Browser_compatibility" target="_blank">', '</a>'),

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
					'{{WRAPPER}} .pg-sonic-content' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);'
				],
				'condition' => [
					'glassmorphism_effect' => 'yes',
				]
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'content_background',
				'selector' => '{{WRAPPER}} .pg-sonic-content',
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'content_border',
				'selector'  => '{{WRAPPER}} .pg-sonic-content',
				'separator' => 'before'
			]
		);

		$this->add_responsive_control(
			'content_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-sonic-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-sonic-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-sonic-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//Global Title Controls
		$this->register_title_controls('sonic');

		$this->start_controls_section(
			'section_style_meta',
			[
				'label'     => esc_html__('Link Button', 'pixel-gallery'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'link_to' => ['file', 'custom'],
					'link_target' => 'only_button',
				],
			]
		);

		$this->start_controls_tabs('tabs_meta_style');

		$this->start_controls_tab(
			'tab_meta_normal',
			[
				'label' => esc_html__('Normal', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label'     => esc_html__('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-sonic-readmore a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'meta_background',
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				// 'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .pg-sonic-readmore a',
				// 'fields_options' => [
				// 	'background' => [
				// 		'default' => 'classic',
				// 	],
				// 	'color' => [
				// 		'default' => '#fff',
				// 	],
				// ],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'meta_border',
				'label'       => esc_html__('Border', 'pixel-gallery'),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pg-sonic-readmore a',
				'separator'   => 'before',
			]
		);

		$this->add_responsive_control(
			'meta_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-sonic-readmore a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'meta_padding',
			[
				'label'      => esc_html__('Padding', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-sonic-readmore a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-sonic-readmore' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .pg-sonic-readmore a span',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'meta_box_shadow',
				'selector' => '{{WRAPPER}} .pg-sonic-readmore a',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_meta_hover',
			[
				'label' => esc_html__('Hover', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'meta_hover_color',
			[
				'label'     => esc_html__('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-sonic-readmore a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'meta_hover_background',
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				// 'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .pg-sonic-readmore a::before',
				// 'fields_options' => [
				// 	'background' => [
				// 		'default' => 'classic',
				// 	],
				// 	'color' => [
				// 		'default' => '#d90429',
				// 	],
				// ],
			]
		);

		$this->add_control(
			'meta_hover_border_color',
			[
				'label'     => esc_html__('Border Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'meta_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .pg-sonic-readmore a:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		//Clip Path Controls
		$this->register_clip_path_controls('sonic');
	}

	public function render_readmore_icon($item, $index, $id)
	{
		$settings   = $this->get_settings_for_display();
		$link = $this->get_link_url($item);
		if ($link) {
			$this->add_link_attributes('link' . $index, $link, true);
			if ('custom' !== $settings['link_to']) {
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
			<div class="pg-sonic-readmore">
				<a <?php $this->print_render_attribute_string('link' . $index); ?>>
					<i class="pg-icon-search"></i>
				</a>
				<a <?php $this->print_render_attribute_string('link' . $index); ?>>
					<i class="pg-icon-link"></i>
				</a>
			</div>
		<?php endif; ?>
		<?php
	}

	public function render_items()
	{
		$settings = $this->get_settings_for_display();
		$id = 'pg-sonic-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-sonic-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
			<?php $this->render_image_wrap($item, 'sonic'); ?>
					<div class="pg-sonic-content">
						<?php $this->render_title($item, 'sonic'); ?>
					</div>
					<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'only_button') : ?>
						<?php $this->render_readmore_icon($item, $index, $id); ?>
					<?php endif; ?>

					<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'whole_item') : ?>
						<?php $this->render_lightbox_link_url($item, $index, $id); ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>

		<?php
			$slide_index++;
		endforeach;
	}

	public function render()
	{
		$settings   = $this->get_settings_for_display();
		$this->add_render_attribute('grid', 'class', 'pg-sonic-grid pg-grid');

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
