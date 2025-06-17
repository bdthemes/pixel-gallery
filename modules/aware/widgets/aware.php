<?php

namespace PixelGallery\Modules\Aware\Widgets;

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

class Aware extends Module_Base {

	use Global_Widget_Controls;

	public function get_name() {
		return 'pg-aware';
	}

	public function get_title() {
		return BDTPG . esc_html__('Aware', 'pixel-gallery');
	}

	public function get_icon() {
		return 'pg-icon-aware';
	}

	public function get_categories() {
		return ['pixel-gallery'];
	}

	public function get_keywords() {
		return ['aware', 'grid', 'gallery'];
	}

	public function get_style_depends() {
		return ['pg-aware'];
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/r6XFiNTDFOA';
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
					'1'	=> '01',
					'2'	=> '02',
					'3'	=> '03',
					'4'	=> '04',
					'5'	=> '05',
				],
			]
		);

		//Global
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
					'{{WRAPPER}} .pg-aware-grid' => 'columns: {{SIZE}}; display: block;'
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
					'{{WRAPPER}} .pg-aware-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr); grid-auto-flow: dense;',
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
					'size' => 40,
				],
				'selectors' => [
					'{{WRAPPER}} .pg-aware-grid' => 'grid-row-gap: {{SIZE}}{{UNIT}};',
					'(mobile){{WRAPPER}}.pg-masonry--yes .pg-aware-grid' => 'margin-bottom: -{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.pg-masonry--yes .pg-aware-grid .pg-aware-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'column_gap',
			[
				'label' => esc_html__('Column Gap', 'pixel-gallery'),
				'type'  => Controls_Manager::SLIDER,
				'default' => [
					'size' => 40,
				],
				'selectors' => [
					'{{WRAPPER}} .pg-aware-grid' => 'grid-column-gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.pg-masonry--yes .pg-aware-grid' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->register_global_height_controls('aware');
		$this->register_title_tag_controls();
		$this->register_thumbnail_size_controls();

		//Global Lightbox Controls
		$this->register_lightbox_controls();

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
		$this->register_repeater_custom_url_controls($repeater);
		$this->register_repeater_hidden_item_controls($repeater);

		$repeater->end_controls_tab();
		$repeater->start_controls_tab(
			'tab_item_grid',
			[
				'label' => esc_html__('Grid', 'pixel-gallery'),
			]
		);
		$this->register_repeater_grid_controls($repeater, 'aware');
		$this->register_repeater_item_height_controls($repeater, 'aware');
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
				'selector' => '{{WRAPPER}} .pg-aware-item',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'item_border',
				'selector'  => '{{WRAPPER}} .pg-aware-item',
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
					'{{WRAPPER}} .pg-aware-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-aware-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-aware-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		// $this->add_group_control(
		// 	Group_Control_Box_Shadow::get_type(),
		// 	[
		// 		'name'     => 'item_box_shadow',
		// 		'selector' => '{{WRAPPER}} .pg-aware-item',
		// 	]
		// );

		$this->add_control(
			'item_accent_shadow_color',
			[
				'label'     => __('Accent Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				// 'default' => '#ffeb37',
				'selectors' => [
					'{{WRAPPER}}' => '--pg-accent: {{VALUE}}',
				],
				'separator' => 'before'
			]
		);


		$this->add_control(
			'item_primary_shadow_color',
			[
				'label'     => __('Primary Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				// 'default' => '#d1fefc',
				'selectors' => [
					'{{WRAPPER}}' => '--pg-primary: {{VALUE}}',
				],
				
			]
		);

		$this->add_control(
			'item_secondary_shadow_color',
			[
				'label'     => __('Secondary Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				// 'default' => '#ffe1e1',
				'selectors' => [
					'{{WRAPPER}}' => '--pg-secondary: {{VALUE}}',
				],
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
				'selector' => '{{WRAPPER}} .pg-aware-item:hover',
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
					'{{WRAPPER}} .pg-aware-item:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		//Global Title Controls
		$this->register_title_controls('aware');

	}

	public function render_items() {
		$settings = $this->get_settings_for_display();
		$id = 'pg-aware-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-aware-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
					<?php $this->render_image_wrap($item, 'aware'); ?>
					<?php $this->render_title($item, 'aware'); ?>
				<?php $this->render_lightbox_link_url($item, $index, $id); ?>
				<?php endif; ?>
			</div>

			<?php
			$slide_index++;
		endforeach;
	}

	public function render() {
		$settings   = $this->get_settings_for_display();
		$this->add_render_attribute('grid', 'class', 'pg-aware-grid pg-grid pg-aware-style-' . $settings["layout_style"]);

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
