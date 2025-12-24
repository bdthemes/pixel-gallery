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
    }

    /**
     * Add prime slider translation nodes
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
