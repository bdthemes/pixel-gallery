<?php

namespace PixelGallery\Modules\Mastery\Widgets;

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

class Mastery extends Module_Base
{

	use Global_Widget_Controls;

	public function get_name()
	{
		return 'pg-mastery';
	}

	public function get_title()
	{
		return BDTPG . esc_html__('Mastery', 'pixel-gallery');
	}

	public function get_icon()
	{
		return 'pg-icon-mastery';
	}

	public function get_categories()
	{
		return ['pixel-gallery'];
	}

	public function get_keywords()
	{
		return ['mastery', 'grid', 'gallery'];
	}

	public function get_style_depends()
	{
		return ['pg-mastery'];
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
		$this->register_grid_controls('mastery');
		$this->register_global_height_controls('mastery');
		$this->register_title_tag_controls();
		$this->register_show_meta_controls();
		$this->register_show_date_controls();
		$this->register_alignment_controls('mastery');
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
		$repeater->add_control(
			'date_day',
			[
				'label'       => esc_html__('Day', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('24', 'pixel-gallery'),
				'placeholder' => esc_html__('Day Text', 'pixel-gallery'),
				'condition'   => ['item_hidden' => '']
			]
		);
		$repeater->add_control(
			'date_month',
			[
				'label'       => esc_html__('Month', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('May', 'pixel-gallery'),
				'placeholder' => esc_html__('Month Text', 'pixel-gallery'),
				'condition'   => ['item_hidden' => '']
			]
		);
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
		$this->register_repeater_grid_controls($repeater, 'mastery');
		$this->register_repeater_item_height_controls($repeater, 'mastery');
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
				'selector' => '{{WRAPPER}} .pg-mastery-image-wrap::before',
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
					'{{WRAPPER}} .pg-mastery-image-wrap::before' => 'mix-blend-mode: {{VALUE}};'
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
				'selector' => '{{WRAPPER}} .pg-mastery-item',
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

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'item_border',
				'selector'  => '{{WRAPPER}} .pg-mastery-item',
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
					'{{WRAPPER}} .pg-mastery-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-mastery-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-mastery-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .pg-mastery-item',
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
					'{{WRAPPER}} .pg-mastery-item:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_hover_box_shadow',
				'selector' => '{{WRAPPER}} .pg-mastery-item:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		//Global Title Controls
		$this->register_title_controls('mastery');

		//Global meta Controls
		$this->register_meta_controls('mastery');

		//Global date Controls
		$this->register_date_controls('mastery');

		//Global readmore Controls
		$this->register_readmore_controls('mastery');


		$this->start_controls_section(
			'section_style_additonal',
			[
				'label'     => esc_html__('Additional', 'pixel-gallery'),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'readmore_max_width',
			[
				'label'   => __('Read More Max Width', 'pixel-gallery'),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min' => 50,
						'max' => 500,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .pg-mastery-readmore' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//Clip Path Controls
		$this->register_clip_path_controls('mastery');
	}

	public function render_items()
	{
		$settings = $this->get_settings_for_display();
		$id = 'pg-mastery-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-mastery-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
			<?php $this->render_image_wrap($item, 'mastery'); ?>
					<div class="pg-mastery-top-content">
						<?php $this->render_title($item, 'mastery'); ?>
						<?php if ($settings['show_date']) : ?>
							<div class="pg-mastery-date">
								<span class="pg-mastery-day"><?php echo esc_html($item['date_day']); ?></span>
								<span class="pg-mastery-month"><?php echo esc_html($item['date_month']); ?></span>
							</div>
						<?php endif; ?>
					</div>
					<div class="pg-mastery-bottom-content">
						<?php $this->render_meta($item, 'mastery'); ?>
						<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'only_button') : ?>
							<?php $this->render_readmore($item, $index, $id, 'mastery'); ?>
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

	public function render()
	{
		$settings   = $this->get_settings_for_display();
		$this->add_render_attribute('grid', 'class', 'pg-mastery-grid pg-grid');

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
