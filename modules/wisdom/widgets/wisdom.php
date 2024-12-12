<?php

namespace PixelGallery\Modules\Wisdom\Widgets;

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

class Wisdom extends Module_Base {

	use Global_Widget_Controls;

	public function get_name() {
		return 'pg-wisdom';
	}

	public function get_title() {
		return BDTPG . esc_html__('Wisdom', 'pixel-gallery');
	}

	public function get_icon() {
		return 'pg-icon-wisdom';
	}

	public function get_categories() {
		return ['pixel-gallery'];
	}

	public function get_keywords() {
		return ['wisdom', 'grid', 'gallery'];
	}

	public function get_style_depends() {
		return ['pg-wisdom'];
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/OsMmP5IPGKc';
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

		//Global Grid Controls
		$this->register_grid_controls('wisdom');
		$this->register_global_height_controls('wisdom');
		$this->register_title_tag_controls();
		$this->register_show_text_controls();
		$this->register_show_date_controls();
		$this->add_control(
			'show_social_link',
			[
				'label'   => esc_html__('Show Social Link', 'pixel-gallery'),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			]
		);
		$this->register_alignment_controls('wisdom');
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
			'date_day',
			[
				'label'       => esc_html__('Day', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('03', 'pixel-gallery'),
				'placeholder' => esc_html__('Day Text', 'pixel-gallery'),
				'condition'   => ['item_hidden' => '']
			]
		);

		$repeater->add_control(
			'date_month',
			[
				'label'       => esc_html__('Month', 'pixel-gallery'),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__('March', 'pixel-gallery'),
				'placeholder' => esc_html__('Month Text', 'pixel-gallery'),
				'condition'   => ['item_hidden' => '']
			]
		);

		$this->register_repeater_text_controls($repeater);
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
		$this->register_repeater_grid_controls($repeater, 'wisdom');
		$this->register_repeater_item_height_controls($repeater, 'wisdom');
		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();
		$this->register_repeater_items_controls($repeater);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_social_link',
			[
				'label' 	=> __('Social Link', 'pixel-gallery'),
				'condition' => [
					'show_social_link' => 'yes',
				],
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'social_link_title',
			[
				'label'   => __('Title', 'pixel-gallery'),
				'type'    => Controls_Manager::TEXT,
				'default' => 'Facebook',
			]
		);

		$repeater->add_control(
			'social_link',
			[
				'label'   => __('Link', 'pixel-gallery'),
				'type'    => Controls_Manager::TEXT,
				'default' => __('http://www.facebook.com/bdthemes/', 'pixel-gallery'),
			]
		);

		$repeater->add_control(
			'social_icon',
			[
				'label'   => __('Choose Icon', 'pixel-gallery'),
				'type'    => Controls_Manager::ICONS,
				'skin' => 'inline',
				'label_block' => false
			]
		);

		$this->add_control(
			'social_link_list',
			[
				'type'    => Controls_Manager::REPEATER,
				'fields'  => $repeater->get_controls(),
				'default' => [
					[
						'social_link'       => __('http://www.facebook.com/bdthemes/', 'pixel-gallery'),
						'social_icon' 		=> [
							'value'   => 'fab fa-facebook-f',
							'library' => 'fa-brands',
						],
						'social_link_title' => 'Facebook',
					],
					[
						'social_link'       => __('http://www.twitter.com/bdthemes/', 'pixel-gallery'),
						'social_icon'		=> [
							'value'   => 'fab fa-twitter',
							'library' => 'fa-brands',
						],
						'social_link_title' => 'Twitter',
					],
					[
						'social_link'       => __('http://www.instagram.com/bdthemes/', 'pixel-gallery'),
						'social_icon'		=> [
							'value'   => 'fab fa-instagram',
							'library' => 'fa-brands',
						],
						'social_link_title' => 'Instagram',
					],
				],
				'title_field' => '{{{ social_link_title }}}',
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
				'selector' => '{{WRAPPER}} .pg-wisdom-item',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => 'rgba(13, 59, 84, 0.1)',
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'item_border',
				'selector'  => '{{WRAPPER}} .pg-wisdom-item',
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
					'{{WRAPPER}} .pg-wisdom-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-wisdom-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'{{WRAPPER}} .pg-wisdom-top-content, {{WRAPPER}} .pg-wisdom-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'selector' => '{{WRAPPER}} .pg-wisdom-item',
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
				'selector' => '{{WRAPPER}} .pg-wisdom-item:hover',
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
					'{{WRAPPER}} .pg-wisdom-item:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_hover_box_shadow',
				'selector' => '{{WRAPPER}} .pg-wisdom-item:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_image',
			[
				'label' => __('Image', 'pixel-gallery'),
				'tab'   => Controls_Manager::TAB_STYLE,
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
				'selector' => '{{WRAPPER}} .pg-wisdom-image-wrap:hover:before',
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
					'{{WRAPPER}} .pg-wisdom-image-wrap:hover:before' => 'mix-blend-mode: {{VALUE}};'
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .pg-wisdom-image-wrap',
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
					'{{WRAPPER}} .pg-wisdom-image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'      => 'css_filters',
				'selector'  => '{{WRAPPER}} .pg-wisdom-image-wrap img',
			]
		);

		$this->end_controls_section();

		//Global Title Controls
		$this->register_title_controls('wisdom');

		//Global Text Controls
		$this->register_text_controls('wisdom');

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

		// spacing control
		$this->add_responsive_control(
			'date_spacing',
			[
				'label'      => esc_html__('Spacing', 'pixel-gallery') . BDTPG_NC,
				'type'       => Controls_Manager::SLIDER,
				'selectors'  => [
					'{{WRAPPER}} .pg-wisdom-date-wrap' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs('tabs_date_style');

		$this->start_controls_tab(
			'tab_date_day',
			[
				'label' => esc_html__('Day', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'date_day_color',
			[
				'label'     => __('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-wisdom-day' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'date_day_typography',
				'selector' => '{{WRAPPER}} .pg-wisdom-day',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_date_month',
			[
				'label' => esc_html__('Month', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'date_month_color',
			[
				'label'     => __('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-wisdom-month' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'date_month_margin',
			[
				'label'      => esc_html__('Margin', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-wisdom-month' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'date_month_typography',
				'selector' => '{{WRAPPER}} .pg-wisdom-month',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		//Global Readmore Controls
		$this->register_readmore_controls('wisdom');

		$this->start_controls_section(
			'section_style_social_icon',
			[
				'label'     => esc_html__('Social Link', 'pixel-gallery'),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_social_link' => 'yes',
				],
			]
		);

		$this->start_controls_tabs('tabs_social_icon_style');

		$this->start_controls_tab(
			'tab_social_icon_normal',
			[
				'label' => esc_html__('Normal', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'social_icon_color',
			[
				'label'     => esc_html__('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-wisdom-social-link a' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pg-wisdom-prime-slider .pg-wisdom-prime-slider-social-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'social_icon_background',
				'types'     => ['classic', 'gradient'],
				'selector'  => '{{WRAPPER}} .pg-wisdom-social-link a',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'        => 'social_icon_border',
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} .pg-wisdom-social-link a',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'social_icon_radius',
			[
				'label'      => esc_html__('Border Radius', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-wisdom-social-link a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'social_icon_padding',
			[
				'label'      => esc_html__('Padding', 'pixel-gallery'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors'  => [
					'{{WRAPPER}} .pg-wisdom-social-link a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);



		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'social_icon_shadow',
				'selector' => '{{WRAPPER}} .pg-wisdom-social-link a',
			]
		);

		$this->add_responsive_control(
			'social_icon_size',
			[
				'label' => __('Icon Size', 'pixel-gallery'),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min'  => 10,
						'max'  => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .pg-wisdom-social-link a' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'social_icons_spacing',
			[
				'label' => __('Icon Spacing', 'pixel-gallery'),
				'type'  => Controls_Manager::SLIDER,
				'range' 		 => [
					'px' 		 => [
						'min' 		=> 0,
						'max' 		=> 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .pg-wisdom-social-link a + a' => 'margin-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_social_icon_hover',
			[
				'label' => esc_html__('Hover', 'pixel-gallery'),
			]
		);

		$this->add_control(
			'social_icon_hover_color',
			[
				'label'     => esc_html__('Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .pg-wisdom-social-link a:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .pg-wisdom-social-link a:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'social_icon_hover_background',
				'types'     => ['classic', 'gradient'],
				'selector'  => '{{WRAPPER}} .pg-wisdom-social-link a:hover',
				'separator' => 'after',
			]
		);

		$this->add_control(
			'icon_hover_border_color',
			[
				'label'     => esc_html__('Border Color', 'pixel-gallery'),
				'type'      => Controls_Manager::COLOR,
				'condition' => [
					'social_icon_border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .pg-wisdom-social-link a:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		//Clip Path Controls
		$this->register_clip_path_controls('wisdom');
	}

	public function render_social_link() {
		$settings  = $this->get_active_settings();

		if ('' == $settings['show_social_link']) {
			return;
		}

?>
		<div class="pg-wisdom-social-link">
			<?php foreach ($settings['social_link_list'] as $link) : ?>
				<a href="<?php echo esc_url($link['social_link']); ?>" target="_blank">
					<?php Icons_Manager::render_icon($link['social_icon'], ['aria-hidden' => 'true', 'class' => 'fa-fw']); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
	}

	public function render_items() {
		$settings = $this->get_settings_for_display();
		$id = 'pg-wisdom-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-wisdom-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
				<div class="pg-wisdom-item-inner">
					<div class="pg-wisdom-head-content">
						<?php if($settings['show_date'] == 'yes') : ?>
						<div class="pg-wisdom-date-wrap">
							<span class="pg-wisdom-day"><?php echo esc_html($item['date_day']); ?></span>
							<span class="pg-wisdom-month"><?php echo esc_html($item['date_month']); ?></span>
						</div>
						<?php endif; ?>
						<?php $this->render_title($item, 'wisdom'); ?>
						<?php $this->render_text($item, 'wisdom'); ?>
					</div>
					<div class="pg-wisdom-image-wrap">
						<?php
						/**
						 * Added Poster for Video
						 * @since 1.0.0
						 */
						if ($item['media_type'] == 'video') {
							$this->render_poster($item, 'wisdom');
						} else {
							$this->render_image($item, 'wisdom');
						}
						?>
						<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'only_button') : ?>
							<?php $this->render_readmore_icon($item, $index, $id, 'wisdom'); ?>
						<?php endif; ?>
					</div>
					<?php $this->render_social_link(); ?>

					<?php if ('none' !== $settings['link_to'] && $settings['link_target'] == 'whole_item') : ?>
						<?php $this->render_lightbox_link_url($item, $index, $id); ?>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

		<?php
			$slide_index++;
		endforeach;
	}

	public function render() {
		$settings   = $this->get_settings_for_display();
		$this->add_render_attribute('grid', 'class', 'pg-wisdom-grid pg-grid');

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
