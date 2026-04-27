<?php

namespace PixelGallery\Modules\Turbo\Widgets;

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

class Turbo extends Module_Base {

	use Global_Widget_Controls;

	public function get_name() {
		return 'pg-turbo';
	}

	public function get_title() {
		return BDTPG . esc_html__('Turbo', 'pixel-gallery');
	}

	public function get_icon() {
		return 'pg-icon-turbo';
	}

	public function get_categories() {
		return ['pixel-gallery'];
	}

	public function get_keywords() {
		return ['turbo', 'grid', 'gallery'];
	}

	public function get_style_depends() {
		return ['pg-turbo'];
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/2wVj9Uhgti4';
	}

	public function get_script_depends() {
		if ($this->pg_is_edit_mode()) {
			if ( true === _is_pg_pro_activated() ) {
				return ['pg-scripts', 'justified-gallery'];
			} else {
				return ['pg-scripts'];
			}
		} else {
			if ( true === _is_pg_pro_activated() ) {
				return ['pg-turbo', 'justified-gallery'];
			} else {
				return ['pg-turbo'];
			}
		}
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
		$this->register_grid_controls('turbo');
		$this->register_global_height_controls('turbo');
		
		/**
		 * Justified Gallery Controls
		 */
		$this->register_justified_gallery_controls();
		
		$this->register_title_tag_controls();
		$this->register_show_meta_controls();
		// $this->register_content_alignment_controls('turbo');
		$this->register_thumbnail_size_controls();

		//Global Lightbox Controls
		$this->register_lightbox_controls();
		$this->register_link_target_controls();

		$this->add_control(
			'cursor_type',
			[
				'label'   => esc_html__( 'Cursor Type', 'pixel-gallery' ) . BDTPG_NC,
				'type'    => Controls_Manager::SELECT,
				'default' => 'inherit',
				'options' => [
					'inherit'     => esc_html__( 'Theme Default', 'pixel-gallery' ),
					'default'     => esc_html__( 'Default', 'pixel-gallery' ),
					'pointer'     => esc_html__( 'Pointer', 'pixel-gallery' ),
					'zoom-in'     => esc_html__( 'Zoom In', 'pixel-gallery' ),
					'zoom-out'    => esc_html__( 'Zoom Out', 'pixel-gallery' ),
					'grab'        => esc_html__( 'Grab', 'pixel-gallery' ),
					'grabbing'    => esc_html__( 'Grabbing', 'pixel-gallery' ),
					'crosshair'   => esc_html__( 'Crosshair', 'pixel-gallery' ),
					'move'        => esc_html__( 'Move', 'pixel-gallery' ),
					'not-allowed' => esc_html__( 'Not Allowed', 'pixel-gallery' ),
		
					// Additional useful cursor types
					'help'        => esc_html__( 'Help', 'pixel-gallery' ),
					'progress'    => esc_html__( 'Progress', 'pixel-gallery' ),
					'wait'        => esc_html__( 'Wait', 'pixel-gallery' ),
					'text'        => esc_html__( 'Text', 'pixel-gallery' ),
					'vertical-text' => esc_html__( 'Vertical Text', 'pixel-gallery' ),
					'alias'       => esc_html__( 'Alias', 'pixel-gallery' ),
					'copy'        => esc_html__( 'Copy', 'pixel-gallery' ),
					'no-drop'     => esc_html__( 'No Drop', 'pixel-gallery' ),
					'all-scroll'  => esc_html__( 'All Scroll', 'pixel-gallery' ),
					'col-resize'  => esc_html__( 'Column Resize', 'pixel-gallery' ),
					'row-resize'  => esc_html__( 'Row Resize', 'pixel-gallery' ),
				],
				'selectors' => [
					'{{WRAPPER}} .pg-turbo-item, {{WRAPPER}} .pg-turbo-item a' => 'cursor: {{VALUE}};',
				],
			]
		);

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
		$this->register_repeater_custom_url_controls($repeater);
		$this->register_repeater_hidden_item_controls($repeater);

		$repeater->end_controls_tab();
		$repeater->start_controls_tab(
			'tab_item_grid',
			[
				'label' => esc_html__('Grid', 'pixel-gallery'),
			]
		);
		$this->register_repeater_grid_controls($repeater, 'turbo');
		$this->register_repeater_item_height_controls($repeater, 'turbo');
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

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .pg-turbo-item',
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
					'{{WRAPPER}} .pg-turbo-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-turbo-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .pg-turbo-item',
			]
		);

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
					'{{WRAPPER}} .pg-turbo-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'title_background',
				'selector' => '{{WRAPPER}} .pg-turbo-title',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'title_border',
				'selector'  => '{{WRAPPER}} .pg-turbo-title',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'title_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-turbo-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-turbo-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-turbo-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .pg-turbo-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'title_text_shadow',
				'label' => __('Text Shadow', 'pixel-gallery'),
				'selector' => '{{WRAPPER}} .pg-turbo-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Stroke::get_type(),
			[
				'name' => 'title_text_stroke',
				'selector' => '{{WRAPPER}} .pg-turbo-title',
			]
		);

		$this->end_controls_section();

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
					'{{WRAPPER}} .pg-turbo-meta' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'meta_background',
				'selector' => '{{WRAPPER}} .pg-turbo-meta',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'meta_border',
				'selector'  => '{{WRAPPER}} .pg-turbo-meta',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'meta_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-turbo-meta' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-turbo-meta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-turbo-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'meta_typography',
				'selector' => '{{WRAPPER}} .pg-turbo-meta',
			]
		);

		$this->end_controls_section();

		//Global Readmore Controls
		$this->register_readmore_controls('turbo');

		//Clip Path Controls
		$this->register_clip_path_controls('turbo');
	}

	public function render_items() {
		$settings = $this->get_settings_for_display();
		$id = 'pg-turbo-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-turbo-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
					<?php $this->render_image_wrap($item, 'turbo'); ?>
					<div class="pg-turbo-content">
						<?php $this->render_meta($item, 'turbo'); ?>
						<?php $this->render_title($item, 'turbo'); ?>
					</div>
					<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'only_button') : ?>
						<?php $this->render_readmore_icon($item, $index, $id, 'turbo'); ?>
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

	public function render() {
		$settings   = $this->get_settings_for_display();
		$this->add_render_attribute('grid', 'class', 'pg-turbo-grid pg-grid');

		/**
		 * Render Justified Gallery Attributes
		 */
		$this->render_justified_gallery_attributes('grid');

		?>
		<div <?php $this->print_render_attribute_string('grid'); ?>>
			<?php $this->render_items(); ?>
		</div>
<?php
	}
	/**
	 * Elementor editor Backbone template.
	 */
	protected function content_template() {
		?>
		<#
	var items = settings.items || [];
	var animDelay = ( settings.pg_in_animation_delay && settings.pg_in_animation_delay.size ) ? settings.pg_in_animation_delay.size : '';
	var gridClass = 'pg-turbo-grid pg-grid';
	if ( settings.pg_in_animation_show === 'yes' ) { gridClass += ' pg-in-animation'; }
	#>
		<div class="{{{ gridClass }}}"
			<# if ( settings.pg_in_animation_show === 'yes' && animDelay !== '' ) { #> data-in-animation-delay="{{ animDelay }}"<# } #>
		>
		<# _.each( items, function( item, index ) {
			var itemClass = 'pg-turbo-item pg-item elementor-repeater-item-' + item._id;
		#>
			<div class="{{{ itemClass }}}">
				<# if ( item.item_hidden !== 'yes' ) { #>
										<div class="pg-turbo-image-wrap bdt-pg-img-mask">
						<# if ( item.media_type === 'video' ) { #>
							<# if ( item.poster && item.poster.url ) { #>
								<img src="{{ item.poster.url }}" alt="{{ item.title }}" class="pg-turbo-img">
							<# } #>
						<# } else if ( item.image && item.image.url ) { #>
							<img src="{{ item.image.url }}" alt="{{ item.title }}" class="pg-turbo-img">
						<# } #>
						<# if ( settings.link_to === 'file' && item.media_type === 'video' ) { #>
							<span class="pg-video-icon-wrap">
								<i class="pg-icon-play-circle pg-eicon-play"></i>
							</span>
						<# } #>
					</div>
					<div class="pg-turbo-content">
						<# if ( settings.show_meta === 'yes' && item.meta ) { #>
							<div class="pg-turbo-meta">{{{ item.meta }}}</div>
						<# } #>
						<# if ( settings.show_title === 'yes' && item.title ) { #>
							<# var ttag = settings.title_tag || 'h3'; #>
							<{{{ ttag }}} class="pg-turbo-title">{{{ item.title }}}</{{{ ttag }}}>
						<# } #>
					</div>
					<# if ( settings.link_to !== 'none' && ( settings.link_target || 'whole_item' ) === 'only_button' ) { #>
						<div class="pg-turbo-readmore">
<?php $this->print_content_template_item_link_prepare( 'turbo' ); ?>
<?php $this->print_content_template_item_link_wrap_open(); ?>
<?php $this->print_content_template_item_link_a_open(); ?>
							<i class="pg-icon-arrow-right" aria-hidden="true"></i>
<?php $this->print_content_template_item_link_a_close(); ?>
<?php $this->print_content_template_item_link_wrap_close(); ?>
						</div>
					<# } #>
					<# if ( settings.link_to !== 'none' && ( settings.link_target || 'whole_item' ) === 'whole_item' ) { #>
<?php $this->print_content_template_lightbox_overlay( 'turbo' ); ?>
					<# } #>
				<# } #>
			</div>
		<# } ); #>
		</div>
		<?php
	}

}
