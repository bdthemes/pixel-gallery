<?php

namespace PixelGallery\Modules\Walden\Widgets;

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
use PixelGallery\Base\Module_Base;
use PixelGallery\Traits\Global_Widget_Controls;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Walden extends Module_Base {

	use Global_Widget_Controls;

	public function get_name() {
		return 'pg-walden';
	}

	public function get_title() {
		return BDTPG . esc_html__('Walden', 'pixel-gallery');
	}

	public function get_icon() {
		return 'pg-icon-walden';
	}

	public function get_categories() {
		return ['pixel-gallery'];
	}

	public function get_keywords() {
		return ['walden', 'grid', 'gallery'];
	}

	public function get_style_depends() {
		return ['pg-walden'];
	}

	public function get_script_depends() {
		if ( true === _is_pg_pro_activated() ) {
			return ['justified-gallery'];
		} else {
			return [];
		}
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/lwkQIcLuE0k';
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

		$this->add_control(
			'layout_style',
			[
				'label'   => __('Layout Style', 'pixel-gallery'),
				'type'    => Controls_Manager::SELECT,
				'default' => '1',
				'options' => [
					'1'       => __('01', 'pixel-gallery'),
					'2'       => __('02', 'pixel-gallery'),
				],
			]
		);

		//Global Grid Controls
		$this->register_grid_controls('walden');
		$this->register_global_height_controls('walden');
		
		/**
		 * Justified Gallery Controls
		 */
		$this->register_justified_gallery_controls();
		
		$this->register_title_tag_controls();

		$this->add_control(
			'show_follow',
			[
				'label'   => esc_html__('Show Follow', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_like',
			[
				'label'   => esc_html__('Show Like', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);

		$this->register_alignment_controls('walden');
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

		$repeater->add_control(
			'follow',
			[
				'label'       => esc_html__('Follow', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('403', 'pixel-gallery'),
				'placeholder' => esc_html__('Follow Number', 'pixel-gallery'),
				'condition'   => ['item_hidden' => '']
			]
		);

		$repeater->add_control(
			'like',
			[
				'label'       => esc_html__('Like', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('604', 'pixel-gallery'),
				'placeholder' => esc_html__('Like Number', 'pixel-gallery'),
				'condition'   => ['item_hidden' => '']
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
		$this->register_repeater_grid_controls($repeater, 'walden');
		$this->register_repeater_item_height_controls($repeater, 'walden');
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
			'overlay_heading',
			[
				'label'     => esc_html__('OVERLAY', 'pixel-gallery'),
				'type'      => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'primary_overlay_color',
			[
				'label'     => esc_html__('Primary Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'layout_style' => '1',
				],
				'selectors' => [
					'{{WRAPPER}} .pg-walden-grid.pg-walden-effect-style-1 .pg-walden-image-wrap::after' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'secondary_overlay_color',
			[
				'label'     => esc_html__('Secondary Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'layout_style' => '1',
				],
				'selectors' => [
					'{{WRAPPER}} .pg-walden-grid.pg-walden-effect-style-1 .pg-walden-image-wrap::before' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'custom_css_filters',
				'selector' => '{{WRAPPER}} .pg-walden-grid.pg-walden-effect-style-2 .pg-walden-image-wrap::before',
				'condition' => [
					'layout_style' => '2',
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
				'selector' => '{{WRAPPER}} .pg-walden-grid .pg-walden-item',
				// 'fields_options' => [
				// 	'background' => [
				// 		'default' => 'classic',
				// 	],
				// 	'color' => [
				// 		'default' => '#F5FCFF',
				// 	],
				// ],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'item_border',
				'selector'  => '{{WRAPPER}} .pg-walden-grid .pg-walden-item',
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
					'{{WRAPPER}} .pg-walden-grid .pg-walden-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-walden-grid .pg-walden-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-walden-grid .pg-walden-top-content, {{WRAPPER}} .pg-walden-grid .pg-walden-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .pg-walden-grid .pg-walden-item',
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
				'selector' => '{{WRAPPER}} .pg-walden-grid .pg-walden-item:hover',
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
					'{{WRAPPER}} .pg-walden-grid .pg-walden-item:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_hover_box_shadow',
				'selector' => '{{WRAPPER}} .pg-walden-grid .pg-walden-item:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		//Global Title Controls
		$this->register_title_controls('walden');

		$this->start_controls_section(
			'section_style_meta',
			[
				'label' => __('Meta', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_STYLE,
				'conditions'   => [
					'terms' => [
						[
							'name'  => 'show_follow',
							'value' => 'yes',
						],
						[
							'name'     => 'show_like',
							'value'    => 'yes',
						],
					],
				],
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label'     => __('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-walden-grid .pg-walden-follow-btn, {{WRAPPER}} .pg-walden-grid .pg-walden-like-btn' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .pg-walden-grid .pg-walden-follow-btn, {{WRAPPER}} .pg-walden-grid .pg-walden-like-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .pg-walden-grid .pg-walden-follow-btn, {{WRAPPER}} .pg-walden-grid .pg-walden-like-btn',
			]
		);

		$this->end_controls_section();

		//Global Readmore Controls
		$this->register_readmore_controls('walden');

		//Clip Path Controls
		$this->register_clip_path_controls('walden');
	}

	public function render_items() {
		$settings = $this->get_settings_for_display();
		$id = 'pg-walden-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-walden-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
					<?php $this->render_image_wrap($item, 'walden'); ?>
					<div class="pg-walden-head-content">
						<?php $this->render_title($item, 'walden'); ?>
					</div>
					<div class="pg-walden-center-content">
						<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'only_button') : ?>
							<?php $this->render_readmore_icon($item, $index, $id, 'walden'); ?>
						<?php endif; ?>
					</div>
					<div class="pg-walden-bottom-content">
						<?php if ($settings['show_follow'] == 'yes') : ?>
							<div class="pg-walden-follow-btn">
								<i class="pg-icon-preview"></i>
								<span><?php echo esc_html($item['follow']); ?></span>
							</div>
						<?php endif; ?>
						<?php if ($settings['show_follow'] == 'yes') : ?>
							<div class="pg-walden-like-btn">
								<i class="pg-icon-heart"></i>
								<span><?php echo esc_html($item['like']); ?></span>
							</div>
						<?php endif; ?>
					</div>
					<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'whole_item') : ?>
						<?php $this->render_lightbox_link_url($item, $index, $id); ?>
					<?php endif; ?>
				<?php endif; ?>
			</div>

		<?php
			$slide_index++;
		endforeach;
	}

	public function render() {
		$settings   = $this->get_settings_for_display();
		$this->add_render_attribute('grid', 'class', 'pg-walden-grid pg-grid pg-walden-effect-style-' . $settings["layout_style"] . '');

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
