<?php

namespace PixelGallery\Modules\Humble\Widgets;

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

class Humble extends Module_Base {

	use Global_Widget_Controls;

	public function get_name() {
		return 'pg-humble';
	}

	public function get_title() {
		return BDTPG . esc_html__('Humble', 'pixel-gallery');
	}

	public function get_icon() {
		return 'pg-icon-humble';
	}

	public function get_categories() {
		return ['pixel-gallery'];
	}

	public function get_keywords() {
		return ['humble', 'grid', 'gallery'];
	}

	public function get_style_depends() {
		return ['pg-humble'];
	}

	public function get_script_depends() {
		if ( true === _is_pg_pro_activated() ) {
			return ['justified-gallery'];
		} else {
			return [];
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
		$this->register_grid_controls('humble');
		$this->register_global_height_controls('humble');
		$this->register_justified_gallery_controls();
		$this->add_responsive_control(
            'content_height',
            [
                'label'   => __( 'Content Height', 'pixel-gallery' ),
                'type'    => Controls_Manager::SLIDER,
                'range'   => [
                    'px' => [
                        'min' => 50,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
					'{{WRAPPER}} .pg-humble-content' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .pg-humble-item:hover .pg-humble-image-wrap' => 'transform: translateY(-{{SIZE}}{{UNIT}});',
				],
            ]
        );
		$this->register_title_tag_controls();
		$this->register_show_meta_controls();
		
		$this->add_responsive_control(
            'content_align',
            [
                'label'     => __('Content Position', 'pixel-gallery'),
                'type'      => Controls_Manager::CHOOSE,
				'default' => 'space-between',
                'options'   => [
                    'left'    => [
                        'title' => __('Left', 'pixel-gallery'),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'  => [
                        'title' => __('Center', 'pixel-gallery'),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'   => [
                        'title' => __('Right', 'pixel-gallery'),
                        'icon'  => 'eicon-text-align-right',
                    ],
                    'space-between' => [
                        'title' => __('Space Between', 'pixel-gallery'),
                        'icon'  => 'eicon-text-align-justify',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .pg-humble-content' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

		$this->register_content_alignment_controls('humble');
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
		$this->register_repeater_grid_controls($repeater, 'humble');
		$this->register_repeater_item_height_controls($repeater, 'humble');
		$repeater->end_controls_tab();
		$repeater->end_controls_tabs();
		$this->register_repeater_items_controls($repeater);
		$this->end_controls_section();

        //Style
        $this->start_controls_section(
			'pg_section_style',
			[
				'label'     => esc_html__( 'Content', 'pixel-gallery' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'content_background',
				'label' => esc_html__( 'Background', 'pixel-gallery' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ],
				'selector' => '{{WRAPPER}} .pg-humble-content',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => 'rgba(13, 59, 84, 0.8)',
					],
				],
			]
		);

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => 'content_border',
                'selector'  => '{{WRAPPER}} .pg-humble-content',
                'separator' => 'before',
            ]
        );

		$this->add_responsive_control(
			'content_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'pixel-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pg-humble-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'content_padding',
			[
				'label'      => esc_html__( 'Padding', 'pixel-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pg-humble-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'content_box_shadow',
				'selector' => '{{WRAPPER}} .pg-humble-content',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_image',
			[
				'label'     => esc_html__( 'Image', 'pixel-gallery' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => 'image_border',
                'selector'  => '{{WRAPPER}} .pg-humble-image-wrap',
                'separator' => 'before',
            ]
        );

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'pixel-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .pg-humble-image-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//Global Title Controls
		$this->register_title_controls('humble');
        
		//Global Meta Controls
		$this->register_meta_controls('humble');

		//Global Readmore Controls
		$this->register_readmore_controls('humble');

		//Clip Path Controls
		$this->register_clip_path_controls('humble');

	}

	public function render_items() {
        $settings = $this->get_settings_for_display();
		$id = 'pg-humble-' . $this->get_id();
		$slide_index = 1;
		foreach ($settings['items'] as $index => $item) :

			$attr_name = 'grid-item' . $index;
			$this->add_render_attribute($attr_name, 'class', 'pg-humble-item pg-item elementor-repeater-item-' . esc_attr($item['_id']), true);

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
			<?php $this->render_image_wrap($item, 'humble'); ?>
			<div class="pg-humble-content">
				<div>
					<?php $this->render_title($item, 'humble'); ?>
					<?php $this->render_meta($item, 'humble'); ?>
				</div>
				<?php if ( 'none' !== $settings['link_to'] && $settings['link_target'] == 'only_button' ) : ?>
				<?php $this->render_readmore($item, $index, $id, 'humble'); ?>
				<?php endif; ?>
			</div>
			<?php if ( 'none' !== $settings['link_to'] && $settings['link_target'] == 'whole_item' ) : ?>
			<?php $this->render_lightbox_link_url( $item, $index, $id ); ?>
			<?php endif; ?>
			<?php endif; ?>
		</div>

		<?php 
		$slide_index++;
		endforeach;
    }

	public function render() {
		$settings   = $this->get_settings_for_display();
		$this->add_render_attribute('grid', 'class', 'pg-humble-grid pg-grid');

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