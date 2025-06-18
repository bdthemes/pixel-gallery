<?php

namespace PixelGallery\Modules\Zilax\Widgets;

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

class Zilax extends Module_Base {

	use Global_Widget_Controls;

	public function get_name() {
		return 'pg-zilax';
	}

	public function get_title() {
		return BDTPG . esc_html__('Zilax', 'pixel-gallery');
	}

	public function get_icon() {
		return 'pg-icon-zilax';
	}

	public function get_categories() {
		return ['pixel-gallery'];
	}

	public function get_keywords() {
		return ['zilax', 'grid', 'gallery'];
	}

	public function get_style_depends() {
		return ['pg-zilax'];
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/XTTuvGCPnuI';
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
				'label'   => __('Layout Style', 'pixel-gallery') . BDTPG_NC,
				'type'    => Controls_Manager::SELECT,
				'default' => '1',
				'options' => [
					'1'       => __('01', 'pixel-gallery'),
					'2'       => __('02', 'pixel-gallery'),
				],
			]
		);

		//Global
		$this->register_grid_controls('zilax');
		$this->register_global_height_controls('zilax');
		$this->register_title_tag_controls();
		$this->register_show_meta_controls();
		$this->register_show_date_controls();
		$this->register_alignment_controls('zilax');
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
		$this->register_repeater_meta_controls($repeater);
		$this->register_repeater_date_controls($repeater);
		$this->register_repeater_custom_url_controls($repeater);
		$this->register_repeater_hidden_item_controls($repeater);

		$repeater->end_controls_tab();
		$repeater->start_controls_tab(
			'tab_item_grid',
			[
				'label' => esc_html__('Grid', 'pixel-gallery'),
			]
		);
		$this->register_repeater_grid_controls($repeater, 'zilax');
		$this->register_repeater_item_height_controls($repeater, 'zilax');
		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();
		$this->register_repeater_items_controls($repeater);
		$this->end_controls_section();

		//Style
		$this->start_controls_section(
			'pg_section_style',
			[
				'label'     => esc_html__('Image', 'pixel-gallery'),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs('tabs_image_style');

		$this->start_controls_tab(
			'tab_image_normal',
			[
				'label' => esc_html__('Normal', 'pixel-gallery'),
			]
		);



		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .pg-zilax-image-wrap',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-zilax-image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .pg-zilax-item',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_image_hover',
			[
				'label' => esc_html__('Hover', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'image_hover_border_color',
			[
				'label'     => esc_html__('Border Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'image_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} pg-zilax-grid .pg-zilax-image-wrap:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_hover_box_shadow',
				'selector' => '{{WRAPPER}} .pg-zilax-item:hover',
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
				'selector' => '{{WRAPPER}} .pg-zilax-content',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'content_border',
				'selector'  => '{{WRAPPER}} .pg-zilax-content',
			]
		);

		$this->add_responsive_control(
			'content_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-zilax-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-zilax-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-zilax-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//Global Title Controls
		$this->register_title_controls('zilax');

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
					'{{WRAPPER}} .pg-zilax-meta-wrap' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .pg-zilax-meta-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'meta_space_between',
			[
				'label'   => __('Space Between', 'pixel-gallery'),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .pg-zilax-separator' => 'margin: 0 {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .pg-zilax-meta-wrap',
			]
		);

		$this->end_controls_section();

		//Global Readmore Controls
		$this->register_readmore_controls('zilax');

		//Clip Path Controls
		$this->register_clip_path_controls('zilax');
	}

	public function render_items() {
		$settings = $this->get_settings_for_display();
		$id = 'pg-zilax-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-zilax-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
					<?php $this->render_image_wrap($item, 'zilax'); ?>
					<div class="pg-zilax-content">
						<?php $this->render_title($item, 'zilax'); ?>
						<div class="pg-zilax-meta-wrap">
							<?php $this->render_meta($item, 'zilax'); ?>
							<div class="pg-zilax-separator">
								<span>//</span>
							</div>
							<?php $this->render_date($item, 'zilax'); ?>
						</div>
						<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'only_button') : ?>
							<?php $this->render_readmore_icon($item, $index, $id, 'zilax'); ?>
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
		$this->add_render_attribute('grid', 'class', 'pg-zilax-grid pg-grid pg-zilax-style-' . $settings['layout_style']);

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
