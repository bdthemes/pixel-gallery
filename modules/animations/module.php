<?php
namespace PixelGallery\Modules\Animations;

use Elementor\Controls_Manager;
use PixelGallery\Base\Pixel_Gallery_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Pixel_Gallery_Module_Base {

	public function get_name() {
		return 'animations';
	}

	public function __construct() {
		parent::__construct();
		$this->add_actions();
		
	}

	public function register_section($element) {
		
		

		$element->start_controls_section(
			'section_pg_in_animation_controls',
			[
				'tab'   => Controls_Manager::TAB_CONTENT,
				'label' => esc_html__('Entrance Animation', 'pixel-gallery') . BDTPG_NC,
			]
		);

		$element->end_controls_section();
	}


	public function register_controls( $widget, $args ) {
			
		$widget->add_control(
			'pg_in_animation_show',
			[
				'label'              => esc_html__( 'Entrance Animation', 'pixel-gallery' ),
				'type'               => Controls_Manager::SWITCHER,
				'render_type'        => 'template',
				'frontend_available' => true,
			]
		);

		$widget->add_control(
			'pg_in_animation_perspective',
			[
				'label'       => esc_html__('Perspective', 'pixel-gallery'),
				'type'        => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 50,
						'max' => 1000,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-perspective: {{SIZE}}px;'
				],
				'condition' => [
					'pg_in_animation_show' => 'yes',
				],
				'separator' => 'before',
				'render_type' => 'template',
				'classes' => BDTPG_IS_PC
			]
		);

		$widget->add_control(
			'pg_in_animation_delay',
			[
				'label' => esc_html__('Delay(ms)', 'pixel-gallery'),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'step' => 10,
						'max' => 1000,
					],
				],
				'condition' 	=> [
					'pg_in_animation_show' => 'yes',
				],
				'render_type' => 'template',
				'classes' => BDTPG_IS_PC
			]
		);
		
		$widget->add_control(
			'pg_in_animation_transition_duration',
			[
				'label' => esc_html__('Transition Duration(ms)', 'pixel-gallery'),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'step' => 10,
						'max' => 2000,
					],
				],
				'condition' 	=> [
					'pg_in_animation_show' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-transition-duration: {{SIZE}}ms;'
				],
				'render_type' => 'template',
				'classes' => BDTPG_IS_PC
			]
		);

		$widget->add_control(
			'pg_in_animation_transform_origin',
			[
				'label'     => esc_html__('Transform Origin', 'pixel-gallery'),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'center top',
				'condition' 	=> [
					'pg_in_animation_show' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-transform-origin: {{VALUE}};'
				],
				'render_type' => 'template',
				'classes' => BDTPG_IS_PC
			]
		);

		$widget->add_control(
			'pg_in_animation_transform_heading',
			[
				'label' 	=> __( 'TRANSFORM', 'pixel-gallery' ),
				'type' 		=> Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'pg_in_animation_show' => 'yes',
				],
			]
		);

		$widget->add_control(
			'pg_in_animation_translate_toggle',
			[
				'label' 		=> __( 'Translate', 'pixel-gallery' ),
				'type' 			=> Controls_Manager::POPOVER_TOGGLE,
				'return_value' 	=> 'yes',
				'condition' 	=> [
					'pg_in_animation_show' => 'yes',
				],
				'render_type' => 'template',
				'classes' => BDTPG_IS_PC
			]
		);

		$widget->start_popover();

		$widget->add_responsive_control(
			'pg_in_animation_translate_x',
			[
				'label'      => esc_html__( 'Translate X', 'pixel-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'default' => [
					'unit' => '%',
				],
				'range'      => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'condition' => [
					'pg_in_animation_translate_toggle' => 'yes',
					'pg_in_animation_show' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-translate-x: {{SIZE}}{{UNIT}};'
				],
				'render_type' => 'template',
			]
		);

		$widget->add_responsive_control(
			'pg_in_animation_translate_y',
			[
				'label'      => esc_html__( 'Translate Y', 'pixel-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'range'      => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-translate-y: {{SIZE}}{{UNIT}};'
				],
				'condition' => [
					'pg_in_animation_translate_toggle' => 'yes',
					'pg_in_animation_show' => 'yes',
				],
				'render_type' => 'template',
			]
		);


		$widget->end_popover();

		$widget->add_control(
			'pg_in_animation_rotate_toggle',
			[
				'label' 		=> __( 'Rotate', 'pixel-gallery' ),
				'type' 			=> Controls_Manager::POPOVER_TOGGLE,
				'return_value' 	=> 'yes',
				'condition' 	=> [
					'pg_in_animation_show' => 'yes',
				],
				'render_type' => 'template',
				'classes' => BDTPG_IS_PC
			]
		);

		$widget->start_popover();


		$widget->add_responsive_control(
			'pg_in_animation_rotate_x',
			[
				'label'      => esc_html__( 'Rotate X', 'pixel-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'default' => [
					'size' => -80,
				],
				'range'      => [
					'px' => [
						'min'  => -180,
						'max'  => 180,
					],
				],
				'condition' => [
					'pg_in_animation_rotate_toggle' => 'yes',
					'pg_in_animation_show' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-rotate-x: {{SIZE||0}}deg;'
				],
				'render_type' => 'template',
			]
		);

		$widget->add_responsive_control(
			'pg_in_animation_rotate_y',
			[
				'label'      => esc_html__( 'Rotate Y', 'pixel-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => [
					'px' => [
						'min'  => -180,
						'max'  => 180,
					],
				],
				'condition' => [
					'pg_in_animation_rotate_toggle' => 'yes',
					'pg_in_animation_show' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-rotate-y: {{SIZE||0}}deg;'
				],
				'render_type' => 'template',
			]
		);


		$widget->add_responsive_control(
			'pg_in_animation_rotate_z',
			[
				'label'   => __( 'Rotate Z', 'pixel-gallery' ),
				'type'    => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min'  => -180,
						'max'  => 180,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-rotate-z: {{SIZE||0}}deg;'
				],
				'condition' => [
					'pg_in_animation_rotate_toggle' => 'yes',
					'pg_in_animation_show' => 'yes',
				],
				'render_type' => 'template',
			]
		);

		$widget->end_popover();


		$widget->add_control(
			'pg_in_animation_scale',
			[
				'label' 		=> __( 'Scale', 'pixel-gallery' ),
				'type' 			=> Controls_Manager::POPOVER_TOGGLE,
				'return_value' 	=> 'yes',
				'condition' 	=> [
					'pg_in_animation_show' => 'yes',
				],
				'render_type' => 'template',
				'classes' => BDTPG_IS_PC
			]
		);

		$widget->start_popover();

		$widget->add_responsive_control(
			'pg_in_animation_scale_x',
			[
				'label'      => esc_html__( 'Scale X', 'pixel-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1
					],
				],
				'condition' => [
					'pg_in_animation_scale' => 'yes',
					'pg_in_animation_show' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-scale-x: {{SIZE}};'
				],
				'render_type' => 'template',
			]
		);

		$widget->add_responsive_control(
			'pg_in_animation_scale_y',
			[
				'label'      => esc_html__( 'Scale Y', 'pixel-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1
					],
				],
				'condition' => [
					'pg_in_animation_scale' => 'yes',
					'pg_in_animation_show' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-scale-y: {{SIZE}};'
				],
				'render_type' => 'template',
			]
		);

		$widget->end_popover();

		$widget->add_control(
			'pg_in_animation_skew',
			[
				'label' 		=> __( 'Skew', 'pixel-gallery' ),
				'type' 			=> Controls_Manager::POPOVER_TOGGLE,
				'return_value' 	=> 'yes',
				'condition' 	=> [
					'pg_in_animation_show' => 'yes',
				],
				'render_type' => 'template',
				'classes' => BDTPG_IS_PC
			]
		);

		$widget->start_popover();

		$widget->add_responsive_control(
			'pg_in_animation_skew_x',
			[
				'label'      => esc_html__( 'Skew X', 'pixel-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min'  => -180,
						'max'  => 180,
					],
				],
				'condition' => [
					'pg_in_animation_skew' => 'yes',
					'pg_in_animation_show' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-skew-x: {{SIZE}}deg;'
				],
				'render_type' => 'template',
			]
		);

		$widget->add_responsive_control(
			'pg_in_animation_skew_y',
			[
				'label'      => esc_html__( 'Skew Y', 'pixel-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min'  => -180,
						'max'  => 180,
					],
				],
				'condition' => [
					'pg_in_animation_skew' => 'yes',
					'pg_in_animation_show' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}' => '--pg-skew-y: {{SIZE}}deg;'
				],
				'render_type' => 'template',
			]
		);

		$widget->end_popover();

	}

	public function in_animation_before_render( $widget ) {
		$settings = $widget->get_settings_for_display();
		
		
		if ( isset($settings['pg_in_animation_show']) and $settings['pg_in_animation_show'] == 'yes' ) {
			wp_enqueue_script( 'pg-animations' );
		}
	}

	protected function add_actions() {
		
		$widgets = [
			'pg-alien',
			'pg-aware',
			'pg-axen',
			'pg-craze',
			'pg-crop',
			'pg-doodle',
			'pg-elixir',
			'pg-epoch',
			'pg-fabric',
			'pg-fever',
			'pg-fixer',
			'pg-flame',
			'pg-fluid',
			'pg-glam',
			'pg-glaze',
			'pg-humble',
			'pg-insta',
			'pg-koral',
			'pg-lumen',
			'pg-lunar',
			'pg-lytical',
			'pg-marron',
			'pg-mastery',
			'pg-mosaic',
			'pg-mystic',
			'pg-nexus',
			'pg-ocean',
			'pg-orbit',
			'pg-panda',
			'pg-plex',
			'pg-plumb',
			'pg-punch',
			'pg-ranch',
			'pg-remix',
			'pg-ruby',
			'pg-shark',
			'pg-sonic',
			'pg-spirit',
			'pg-tour',
			'pg-trance',
			// 'pg-turbo',
			'pg-verse',
			'pg-walden',
			'pg-wisdom',
			'pg-zilax',
			// 'pg-heron',
			'pg-maven',
		];
		
		foreach ( $widgets as $widget) {
			add_action(
				'elementor/element/' .$widget. '/pg_section_style/before_section_start', [
				$this,
				'register_section'
			] );
			
			add_action(
				'elementor/element/' .$widget. '/section_pg_in_animation_controls/before_section_end', [
				$this,
				'register_controls'
			], 10, 2 );
			
			add_action( 'elementor/frontend/widget/before_render', [
				$this,
				'in_animation_before_render'
			], 10, 1 );
		}
	}
}
