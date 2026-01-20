<?php

namespace Pixel_Gallery\Includes;

/**
 * Pixel_Gallery_WPML class
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Pixel_Gallery_WPML {

    /**
     * A reference to an instance of this class.
     * @since 3.1.0
     * @var   object
     */
    private static $instance = null;

    /**
     * Constructor for the class
     */
    public function init() {

        // WPML existence check - register nodes when WPML core or String Translation is present
        if (defined('WPML_ST_VERSION') || defined('WPML_VERSION') || defined('ICL_SITEPRESS_VERSION') || function_exists('icl_register_string')) {
            add_filter('wpml_elementor_widgets_to_translate', array($this, 'add_translatable_nodes'));
        }
    }

    /**
     * Load wpml required repeater class files.
     * @return void
     */
    public function load_wpml_modules() {

        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/wpml-module-with-items.php' );

        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-alien.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-aware.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-axen.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-craze.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-crop.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-doodle.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-elixir.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-epoch.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-fabric.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-fever.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-fixer.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-flame.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-fluid.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-glam.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-glaze.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-humble.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-insta.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-koral.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-lumen.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-lunar.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-lytical.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-marron.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-mastery.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-mosaic.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-mystic.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-nexus.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-ocean.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-orbit.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-panda.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-plex.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-plumb.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-punch.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-ranch.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-remix.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-ruby.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-shark.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-sonic.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-spirit.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-tour.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-trance.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-turbo.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-verse.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-walden.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-wisdom.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-zilax.php' );
		
        // Pro Widgets
		require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-amaze.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-diamond.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-draggable.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-dream.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-evolve.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-flash.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-floral.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-heron.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-kitec.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-maven.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-menuz.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-pastel.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-polo.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-ridex.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-tread.php' );
        require_once( BDTPG_PATH . 'includes/compatiblity/wpml/class-wpml-pixel-gallery-xero.php' );
    }

    /**
     * Add pixel gallery translation nodes
     * @param array $nodes_to_translate
     * @return array
     */
    public function add_translatable_nodes($nodes_to_translate) {

        $this->load_wpml_modules();

        $nodes_to_translate['pg-alien'] = [
			'conditions'        => [
				'widgetType' => 'pg-alien',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Alien',
			'fields'            => []
		];

        $nodes_to_translate['pg-aware'] = [
			'conditions'        => [
				'widgetType' => 'pg-aware',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Aware',
			'fields'            => []
		];

        $nodes_to_translate['pg-axen'] = [
			'conditions'        => [
				'widgetType' => 'pg-axen',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Axen',
			'fields'            => []
		];

        $nodes_to_translate['pg-craze'] = [
			'conditions'        => [
				'widgetType' => 'pg-craze',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Craze',
			'fields'            => []
		];

        $nodes_to_translate['pg-crop'] = [
			'conditions'        => [
				'widgetType' => 'pg-crop',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Crop',
			'fields'            => []
		];

        $nodes_to_translate['pg-doodle'] = [
			'conditions'        => [
				'widgetType' => 'pg-doodle',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Doodle',
			'fields'            => []
		];

        $nodes_to_translate['pg-elixir'] = [
			'conditions'        => [
				'widgetType' => 'pg-elixir',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Elixir',
			'fields'            => []
		];

        $nodes_to_translate['pg-epoch'] = [
			'conditions'        => [
				'widgetType' => 'pg-epoch',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Epoch',
			'fields'            => []
		];

        $nodes_to_translate['pg-fabric'] = [
			'conditions'        => [
				'widgetType' => 'pg-fabric',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Fabric',
			'fields'            => []
		];

        $nodes_to_translate['pg-fever'] = [
			'conditions'        => [
				'widgetType' => 'pg-fever',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Fever',
			'fields'            => []
		];

        $nodes_to_translate['pg-fixer'] = [
			'conditions'        => [
				'widgetType' => 'pg-fixer',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Fixer',
			'fields'            => []
		];

        $nodes_to_translate['pg-flame'] = [
			'conditions'        => [
				'widgetType' => 'pg-flame',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Flame',
			'fields'            => []
		];

        $nodes_to_translate['pg-fluid'] = [
			'conditions'        => [
				'widgetType' => 'pg-fluid',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Fluid',
			'fields'            => []
		];

        $nodes_to_translate['pg-glam'] = [
			'conditions'        => [
				'widgetType' => 'pg-glam',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Glam',
			'fields'            => []
		];

        $nodes_to_translate['pg-glaze'] = [
			'conditions'        => [
				'widgetType' => 'pg-glaze',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Glaze',
			'fields'            => []
		];

        $nodes_to_translate['pg-humble'] = [
			'conditions'        => [
				'widgetType' => 'pg-humble',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Humble',
			'fields'            => []
		];

        $nodes_to_translate['pg-insta'] = [
			'conditions'        => [
				'widgetType' => 'pg-insta',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Insta',
			'fields'            => []
		];

        $nodes_to_translate['pg-koral'] = [
			'conditions'        => [
				'widgetType' => 'pg-koral',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Koral',
			'fields'            => []
		];

        $nodes_to_translate['pg-lumen'] = [
			'conditions'        => [
				'widgetType' => 'pg-lumen',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Lumen',
			'fields'            => []
		];

        $nodes_to_translate['pg-lunar'] = [
			'conditions'        => [
				'widgetType' => 'pg-lunar',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Lunar',
			'fields'            => []
		];

        $nodes_to_translate['pg-lytical'] = [
			'conditions'        => [
				'widgetType' => 'pg-lytical',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Lytical',
			'fields'            => []
		];

        $nodes_to_translate['pg-marron'] = [
			'conditions'        => [
				'widgetType' => 'pg-marron',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Marron',
			'fields'            => []
		];

        $nodes_to_translate['pg-mastery'] = [
			'conditions'        => [
				'widgetType' => 'pg-mastery',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Mastery',
			'fields'            => []
		];

        $nodes_to_translate['pg-mosaic'] = [
			'conditions'        => [
				'widgetType' => 'pg-mosaic',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Mosaic',
			'fields'            => []
		];

        $nodes_to_translate['pg-mystic'] = [
			'conditions'        => [
				'widgetType' => 'pg-mystic',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Mystic',
			'fields'            => []
		];

        $nodes_to_translate['pg-nexus'] = [
			'conditions'        => [
				'widgetType' => 'pg-nexus',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Nexus',
			'fields'            => []
		];

        $nodes_to_translate['pg-ocean'] = [
			'conditions'        => [
				'widgetType' => 'pg-ocean',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Ocean',
			'fields'            => []
		];

        $nodes_to_translate['pg-orbit'] = [
			'conditions'        => [
				'widgetType' => 'pg-orbit',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Orbit',
			'fields'            => []
		];

        $nodes_to_translate['pg-panda'] = [
			'conditions'        => [
				'widgetType' => 'pg-panda',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Panda',
			'fields'            => []
		];

        $nodes_to_translate['pg-plex'] = [
			'conditions'        => [
				'widgetType' => 'pg-plex',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Plex',
			'fields'            => []
		];

        $nodes_to_translate['pg-plumb'] = [
			'conditions'        => [
				'widgetType' => 'pg-plumb',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Plumb',
			'fields'            => []
		];

        $nodes_to_translate['pg-punch'] = [
			'conditions'        => [
				'widgetType' => 'pg-punch',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Punch',
			'fields'            => []
		];

        $nodes_to_translate['pg-ranch'] = [
			'conditions'        => [
				'widgetType' => 'pg-ranch',
			],
			'integration-class' => [
				__NAMESPACE__ . '\\WPML_Pixel_Gallery_Ranch',
				__NAMESPACE__ . '\\WPML_Pixel_Gallery_Ranch_Social_Link',
			],
			'fields'            => []
		];

        $nodes_to_translate['pg-remix'] = [
			'conditions'        => [
				'widgetType' => 'pg-remix',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Remix',
			'fields'            => []
		];

        $nodes_to_translate['pg-ruby'] = [
			'conditions'        => [
				'widgetType' => 'pg-ruby',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Ruby',
			'fields'            => []
		];

        $nodes_to_translate['pg-shark'] = [
			'conditions'        => [
				'widgetType' => 'pg-shark',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Shark',
			'fields'            => []
		];

        $nodes_to_translate['pg-sonic'] = [
			'conditions'        => [
				'widgetType' => 'pg-sonic',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Sonic',
			'fields'            => []
		];

        $nodes_to_translate['pg-spirit'] = [
			'conditions'        => [
				'widgetType' => 'pg-spirit',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Spirit',
			'fields'            => []
		];

        $nodes_to_translate['pg-tour'] = [
			'conditions'        => [
				'widgetType' => 'pg-tour',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Tour',
			'fields'            => []
		];

        $nodes_to_translate['pg-trance'] = [
			'conditions'        => [
				'widgetType' => 'pg-trance',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Trance',
			'fields'            => []
		];

        $nodes_to_translate['pg-turbo'] = [
			'conditions'        => [
				'widgetType' => 'pg-turbo',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Turbo',
			'fields'            => []
		];

        $nodes_to_translate['pg-verse'] = [
			'conditions'        => [
				'widgetType' => 'pg-verse',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Verse',
			'fields'            => []
		];

        $nodes_to_translate['pg-walden'] = [
			'conditions'        => [
				'widgetType' => 'pg-walden',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Walden',
			'fields'            => []
		];

        $nodes_to_translate['pg-wisdom'] = [
			'conditions'        => [
				'widgetType' => 'pg-wisdom',
			],
			'integration-class' => [
				__NAMESPACE__ . '\\WPML_Pixel_Gallery_Wisdom',
				__NAMESPACE__ . '\\WPML_Pixel_Gallery_Wisdom_Social_Link',
			],
			'fields'            => []
		];

        $nodes_to_translate['pg-zilax'] = [
			'conditions'        => [
				'widgetType' => 'pg-zilax',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Zilax',
			'fields'            => []
		];

        // Pro Widgets
		$nodes_to_translate['pg-amaze'] = [
			'conditions'        => [
				'widgetType' => 'pg-amaze',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Amaze',
			'fields'            => []
		];
		
        $nodes_to_translate['pg-diamond'] = [
			'conditions'        => [
				'widgetType' => 'pg-diamond',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Diamond',
			'fields'            => [
                [
                    'field'       => 'gridItemHoverText',
                    'type'        => __( 'Hover Text', 'pixel-gallery' ),
                    'editor_type' => 'LINE',
                ],
            ]
		];

        $nodes_to_translate['pg-draggable'] = [
			'conditions'        => [
				'widgetType' => 'pg-draggable',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Draggable',
			'fields'            => []
		];

        $nodes_to_translate['pg-dream'] = [
			'conditions'        => [
				'widgetType' => 'pg-dream',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Dream',
			'fields'            => []
		];

        $nodes_to_translate['pg-evolve'] = [
			'conditions'        => [
				'widgetType' => 'pg-evolve',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Evolve',
			'fields'            => []
		];

        $nodes_to_translate['pg-flash'] = [
			'conditions'        => [
				'widgetType' => 'pg-flash',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Flash',
			'fields'            => []
		];

        $nodes_to_translate['pg-floral'] = [
			'conditions'        => [
				'widgetType' => 'pg-floral',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Floral',
			'fields'            => []
		];

        $nodes_to_translate['pg-heron'] = [
			'conditions'        => [
				'widgetType' => 'pg-heron',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Heron',
			'fields'            => []
		];

        $nodes_to_translate['pg-kitec'] = [
			'conditions'        => [
				'widgetType' => 'pg-kitec',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Kitec',
			'fields'            => [
                [
                    'field'       => 'background_text',
                    'type'        => __( 'Background Text', 'pixel-gallery' ),
                    'editor_type' => 'AREA',
                ],
            ]
		];

        $nodes_to_translate['pg-maven'] = [
			'conditions'        => [
				'widgetType' => 'pg-maven',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Maven',
			'fields'            => []
		];

        $nodes_to_translate['pg-menuz'] = [
			'conditions'        => [
				'widgetType' => 'pg-menuz',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Menuz',
			'fields'            => []
		];

        $nodes_to_translate['pg-pastel'] = [
			'conditions'        => [
				'widgetType' => 'pg-pastel',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Pastel',
			'fields'            => []
		];

        $nodes_to_translate['pg-polo'] = [
			'conditions'        => [
				'widgetType' => 'pg-polo',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Polo',
			'fields'            => []
		];

        $nodes_to_translate['pg-ridex'] = [
			'conditions'        => [
				'widgetType' => 'pg-ridex',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Ridex',
			'fields'            => []
		];

        $nodes_to_translate['pg-tread'] = [
			'conditions'        => [
				'widgetType' => 'pg-tread',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Tread',
			'fields'            => [
                [
                    'field'       => 'main_title',
                    'type'        => __( 'Main Title', 'pixel-gallery' ),
                    'editor_type' => 'LINE',
                ],
                [
                    'field'       => 'main_sub_title',
                    'type'        => __( 'Main Sub Title', 'pixel-gallery' ),
                    'editor_type' => 'LINE',
                ],
            ]
		];

        $nodes_to_translate['pg-xero'] = [
			'conditions'        => [
				'widgetType' => 'pg-xero',
			],
			'integration-class' => __NAMESPACE__ . '\\WPML_Pixel_Gallery_Xero',
			'fields'            => [
                [
                    'field'       => 'static_title',
                    'type'        => __( 'Static Title', 'pixel-gallery' ),
                    'editor_type' => 'LINE',
                ],
            ]
		];

        return $nodes_to_translate;
    }

    /**
     * Returns the instance.
     * @since  3.1.0
     * @return object
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }
}
