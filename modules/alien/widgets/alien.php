<?php

namespace PixelGallery\Modules\Alien\Widgets;

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

class Alien extends Module_Base {

	use Global_Widget_Controls;

	public function get_name() {
		return 'pg-alien';
	}

	public function get_title() {
		return BDTPG . esc_html__('Alien', 'pixel-gallery');
	}

	public function get_icon() {
		return 'pg-icon-alien';
	}

	public function get_categories() {
		return ['pixel-gallery'];
	}

	public function get_keywords() {
		return ['alien', 'grid', 'gallery'];
	}

	public function get_style_depends() {
		return ['pg-alien'];
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/O4YS5LNJI2g';
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
		$this->register_grid_controls('alien');
		$this->register_global_height_controls('alien');
		$this->register_title_tag_controls();
		$this->register_show_meta_controls();
		$this->register_alignment_controls('alien');
		$this->register_thumbnail_size_controls();

		//Global Lightbox Controls
		$this->register_lightbox_controls();
		$this->register_link_target_controls();

		$this->end_controls_section();

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
		$this->register_repeater_meta_controls($repeater);
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
		$this->register_repeater_grid_controls($repeater, 'alien');
		$this->register_repeater_item_height_controls($repeater, 'alien');
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
			Group_Control_Background::get_type(),
			[
				'name' => 'item_background',
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .pg-alien-item',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'item_border',
				'selector'  => '{{WRAPPER}} .pg-alien-item',
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
					'{{WRAPPER}} .pg-alien-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-alien-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-alien-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .pg-alien-item',
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
					'{{WRAPPER}} .pg-alien-item:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_hover_box_shadow',
				'selector' => '{{WRAPPER}} .pg-alien-item:hover',
			]
		);

		$this->add_control(
			'item_hover_title_style_color',
			[
				'label'     => esc_html__('Title Style Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-alien-title:after' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before'
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
			Group_Control_Background::get_type(),
			[
				'name' => 'content_background',
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .pg-alien-content',
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
				'name'      => 'content_border',
				'selector'  => '{{WRAPPER}} .pg-alien-content',
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
					'{{WRAPPER}} .pg-alien-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-alien-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-alien-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'content_box_shadow',
				'selector' => '{{WRAPPER}} .pg-alien-content',
			]
		);

		$this->end_controls_section();

		//Global Title Controls
		$this->register_title_controls('alien');

		//Global Meta Controls
		$this->register_meta_controls('alien');
		$this->register_readmore_controls('alien');
		
		//Clip Path Controls
		$this->register_clip_path_controls('alien');

	}

	protected function render_readmore($item, $index, $id, $name) {
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
					<i class="pg-icon-plus"></i>
					<i class="pg-icon-plus"></i>
				</a>
			</div>
		<?php endif; ?>
	<?php
	}

	public function render_items() {
		$settings = $this->get_settings_for_display();
		$id = 'pg-alien-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-alien-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
			<?php if ($item['item_hidden'] !== 'yes' ) : ?>
				<div class="pg-alien-img-btn">
					<?php $this->render_image_wrap($item, 'alien'); ?>
					<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'only_button') : ?>
						<?php $this->render_readmore($item, $index, $id, 'alien'); ?>
					<?php endif; ?>
				</div>
			<div class="pg-alien-content">
				<?php $this->render_meta($item, 'alien'); ?>
				<?php $this->render_title($item, 'alien'); ?>
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
		$this->add_render_attribute('grid', 'class', 'pg-alien-grid pg-grid');

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
