<?php

namespace PixelGallery\Modules\Ocean\Widgets;

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
use Elementor\Embed;
use Elementor\Plugin;
use PixelGallery\Utils;
use PixelGallery\Traits\Global_Widget_Controls;
use PixelGallery\Includes\Controls\GroupQuery\Group_Control_Query;

use Elementor\Modules\DynamicTags\Module as TagsModule;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Ocean extends Module_Base {

	use Global_Widget_Controls;
	use Group_Control_Query;

	public function get_query() {
		return $this->_query;
	}

	public function get_name() {
		return 'pg-ocean';
	}

	public function get_title() {
		return BDTPG . esc_html__('Ocean', 'pixel-gallery');
	}

	public function get_icon() {
		return 'pg-icon-ocean';
	}

	public function get_categories() {
		return ['pixel-gallery'];
	}

	public function get_keywords() {
		return ['ocean', 'grid', 'gallery'];
	}

	public function get_style_depends() {
		return ['pg-ocean'];
	}

	public function get_script_depends() {
		if ( true === _is_pg_pro_activated() ) {
			return ['justified-gallery'];
		} else {
			return [];
		}
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/150N81SaAHQ';
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
			'source',
			[
				'label'   => esc_html__('Select Source', 'pixel-gallery'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => [
					'custom' => esc_html__('Custom Content', 'pixel-gallery'),
					'dynamic'  => esc_html__('Dynamic Query', 'pixel-gallery'),
				],
			]
		);

		//Global
		$this->register_grid_controls('ocean');
		$this->register_global_height_controls('ocean');
		$this->register_justified_gallery_controls();
		$this->register_title_tag_controls();
		$this->register_show_meta_controls();
		$this->register_show_pagination_controls();
		// $this->register_alignment_controls('ocean');
		$this->register_thumbnail_size_controls();

		//Global Lightbox Controls
		$this->register_lightbox_controls();
		$this->end_controls_section();

		//Dynamic query
		$this->start_controls_section(
			'section_post_query_builder',
			[
				'label' => __('Query', 'pixel-gallery') . BDTPG_NC,
				'tab' => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'source' => 'dynamic',
				],
			]
		);

		$this->register_query_builder_controls();

		// $this->update_control(
		// 	'posts_per_page',
		// 	[
		// 		'type' => Controls_Manager::HIDDEN,
		// 	]
		// );

		$this->end_controls_section();

		//Repeater
		$this->start_controls_section(
			'section_item_content',
			[
				'label' => __('Items', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'source' => 'custom',
				],
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
		$this->register_repeater_custom_url_controls($repeater);
		$this->register_repeater_hidden_item_controls($repeater);

		$repeater->end_controls_tab();
		$repeater->start_controls_tab(
			'tab_item_grid',
			[
				'label' => esc_html__('Grid', 'pixel-gallery'),
			]
		);
		$this->register_repeater_grid_controls($repeater, 'ocean');
		$this->register_repeater_item_height_controls($repeater, 'ocean');
		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();
		$this->register_repeater_items_controls($repeater);
		$this->end_controls_section();

		//Gallery Builder Repeater
		$this->start_controls_section(
			'section_gallery_builder',
			[
				'label' => __('Gallery Builder', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_CONTENT,
				'condition' => [
					'source' => 'dynamic',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'gallery_builder_title',
			[
				'label'       => __('Title', 'pixel-gallery'),
				'type'        => Controls_Manager::HIDDEN,
				'dynamic'     => [
					'active' => true,
				],
				'default'     => esc_html__('Gallery Title Here', 'pixel-gallery'),
				'placeholder' => __('Enter your title', 'pixel-gallery'),
				'label_block' => true,
			]
		);

		$repeater->add_responsive_control(
			'gallery_builder_column_span',
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
					'{{WRAPPER}} .pg-ocean-grid {{CURRENT_ITEM}}' => 'grid-column: span {{VALUE}} / auto;',
				],
			]
		);

		$repeater->add_responsive_control(
			'gallery_builder_row_span',
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
					'{{WRAPPER}} .pg-ocean-grid {{CURRENT_ITEM}}' => 'grid-row: span {{VALUE}} / auto;',
				],
			]
		);

		$repeater->add_responsive_control(
			'gallery_builder_current_item_height',
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
					'{{WRAPPER}} .pg-ocean-grid {{CURRENT_ITEM}}' => 'height: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before'
			]
		);

		$repeater->add_control(
			'gallery_builder_item_hidden',
			[
				'label'   => __('Item Hidden', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'render_type' => 'template',
				'separator' => 'before'
			]
		);

		$repeater->add_control(
			'gallery_builder_item_hidden_on_tablet',
			[
				'label'   => __('Blank Space Hide on Tablet', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'selectors' => [
					'(tablet){{WRAPPER}} {{CURRENT_ITEM}}' => 'display: none;',
				],
				'condition' => ['gallery_builder_item_hidden' => 'yes']
			]
		);

		$repeater->add_control(
			'gallery_builder_item_hidden_on_mobile',
			[
				'label'   => __('Item Hide on Mobile', 'pixel-gallery'),
				'type'    => Controls_Manager::HIDDEN,
				'default' => '1',
				'selectors' => [
					'(mobile){{WRAPPER}} {{CURRENT_ITEM}}' => 'display: none;',
				],
				'condition' => ['gallery_builder_item_hidden' => 'yes']
			]
		);

		$this->add_control(
			'gallery_builder_items',
			[
				'show_label'  => false,
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => [
					['gallery_builder_title' => __('Gallery Item #1', 'pixel-gallery')],
					['gallery_builder_title' => __('Gallery Item #2', 'pixel-gallery')],
					['gallery_builder_title' => __('Gallery Item #3', 'pixel-gallery')],
					['gallery_builder_title' => __('Gallery Item #4', 'pixel-gallery')],
					['gallery_builder_title' => __('Gallery Item #5', 'pixel-gallery')],
					['gallery_builder_title' => __('Gallery Item #6', 'pixel-gallery')],
				]
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
				'selector' => '{{WRAPPER}} .pg-ocean-item',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'item_border',
				'selector'  => '{{WRAPPER}} .pg-ocean-item',
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
					'{{WRAPPER}} .pg-ocean-item, {{WRAPPER}} .pg-ocean-image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-ocean-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-ocean-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .pg-ocean-item',
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
					'{{WRAPPER}} .pg-ocean-item:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_hover_box_shadow',
				'selector' => '{{WRAPPER}} .pg-ocean-item:hover',
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
					'{{WRAPPER}} .pg-ocean-overlay' => 'backdrop-filter: blur({{SIZE}}px); -webkit-backdrop-filter: blur({{SIZE}}px);'
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
				'label' => esc_html__('Background', 'pixel-gallery'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .pg-ocean-overlay',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => '#fff',
					],
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'content_border',
				'selector'  => '{{WRAPPER}} .pg-ocean-overlay',
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
					'{{WRAPPER}} .pg-ocean-content, {{WRAPPER}} .pg-ocean-overlay' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-ocean-content-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-ocean-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//Global Title Controls
		$this->register_title_controls('ocean');

		//Global meta Controls
		$this->register_meta_controls('ocean');

		//Clip Path Controls
		$this->register_clip_path_controls('ocean');
	}

	/**
	 * Get post query builder arguments
	 */
	public function query_posts($posts_per_page) {
		$settings = $this->get_settings();

		$args = [];
		if ($posts_per_page) {
			$args['posts_per_page'] = $posts_per_page;
			if ($settings['show_pagination']) { // fix query offset
				$args['paged']  = max(1, get_query_var('paged'), get_query_var('page'));
			}
		}

		$default = $this->getGroupControlQueryArgs();
		$args = array_merge($default, $args);

		$this->_query = new \WP_Query($args);
	}


	// public function render_image($image_id, $size) {
	// 	$placeholder_image_src = Utils::get_placeholder_image_src();

	// 	$image_src = wp_get_attachment_image_src($image_id, $size);

	// 	if (!$image_src) {
	// 		$image_src = $placeholder_image_src;
	// 	} else {
	// 		$image_src = $image_src[0];
	// 	}

	// 	echo
	// 		'<div class="bdt-post-grid-img-wrap bdt-overflow-hidden">
	// 			<a href="' . esc_url(get_permalink()) . '" class="bdt-transition-' . esc_attr($this->get_settings('image_animation')) . ' bdt-background-cover bdt-transition-opaque bdt-flex" title="' . esc_attr(get_the_title()) . '" style="background-image: url(' . esc_url($image_src) . ')">
	// 			</a>
	// 		</div>';
	// }



	public function render_items() {
		$settings = $this->get_settings_for_display();
		$id = 'pg-ocean-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-ocean-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
					<?php $this->render_image_wrap($item, 'ocean'); ?>
					<div class="pg-ocean-content">
						<span class="pg-ocean-overlay"></span>
						<div class="pg-ocean-content-inner">
							<?php $this->render_title($item, 'ocean'); ?>
							<?php $this->render_meta($item, 'ocean'); ?>
						</div>
					</div>
					<?php $this->render_lightbox_link_url($item, $index, $id); ?>
				<?php endif; ?>
			</div>

		<?php
			$slide_index++;
		endforeach;
	}

	public function dynamic_items() {
		$settings = $this->get_settings_for_display();
		$id = 'pg-ocean-' . $this->get_id();
		$index = 1;


		$this->query_posts($settings['posts_per_page']);

		$wp_query = $this->get_query();

		if (!$wp_query->found_posts) {
			return;
		}

		// print_r($settings['gallery_builder_items']);

		$test_id = [];

		foreach ($settings['gallery_builder_items'] as $index => $item) :
			array_push($test_id, 'pg-ocean-item pg-item elementor-repeater-item-' . esc_attr($item['_id']));
		endforeach;

		// print_r($test_id);


		$i = 0;
		while ($wp_query->have_posts()) :
			// foreach ($settings['gallery_builder_items'] as $index => $item) :
			$wp_query->the_post();

			$attr_name = 'grid-item' . $index;
			if (isset($test_id[$i])) {
				$this->add_render_attribute($attr_name, 'class', esc_attr($test_id[$i]), true);
			} else {
				$this->add_render_attribute($attr_name, 'class', 'pg-ocean-item pg-item elementor-repeater-item-', true);
			}

			/**
			 * Render Video Inject Here
			 * Video Would be work on Media File & Lightbox
			 * @since 1.0.0
			 */
			//			if ($item['media_type'] == 'video') {
			//				$this->render_video_frame($item, $attr_name, $id);
			//			}

			// if($i > 2){
			// 	$i = 0;
			// }

			// echo $i;

			$i++;

		?>

			<div <?php $this->print_render_attribute_string($attr_name); ?>>
				<?php if ($item['gallery_builder_item_hidden'] !== 'yes') : 
				?>
				<?php $this->render_dynamic_image_wrap(get_the_ID(), 'thumbnail_size', 'ocean'); ?>
				<div class="pg-ocean-content">
					<span class="pg-ocean-overlay"></span>
					<div class="pg-ocean-content-inner">
						<?php $this->render_dynamic_title('ocean'); ?>
						<?php $this->render_dynamic_meta('ocean'); ?>
					</div>
				</div>
				<?php $this->render_dynamic_lightbox_link_url($index, $id); ?>
				<?php endif; 
				?>
			</div>

		<?php

			$index++;
		// endforeach;

		endwhile;
		wp_reset_postdata();
	}

	public function render() {
		$settings   = $this->get_settings_for_display();
		$this->add_render_attribute('grid', 'class', 'pg-ocean-grid pg-grid');

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

			<?php if ('dynamic' == $settings['source']) : ?>
				<?php $this->dynamic_items(); ?>
			<?php else : ?>
				<?php $this->render_items(); ?>
			<?php endif; ?>


		</div>
<?php
	}
}
