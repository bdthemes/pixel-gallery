<?php
namespace Pixel_Gallery\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WPML_Pixel_Gallery_Polo
 * Handles translation of repeater items in the Polo widget
 */
class WPML_Pixel_Gallery_Polo extends WPML_Module_With_Items {

    /**
     * @return string
     */
    public function get_items_field() {
        return 'items';
    }

    /**
     * @return array
     */
    public function get_fields() {
        return array(
            'title',
            'meta',
            'text',
            'link' => ['url'],
        );
    }

    /**
     * @param string $field
     * @return string
     */
    protected function get_title( $field ) {
        switch ( $field ) {
            case 'title':
                return esc_html__( 'Title', 'pixel-gallery' );
            case 'meta':
                return esc_html__( 'Meta', 'pixel-gallery' );
            case 'text':
                return esc_html__( 'Text', 'pixel-gallery' );
            case 'link':
                return esc_html__( 'Custom URL', 'pixel-gallery' );
            default:
                return '';
        }
    }

    /**
     * @param string $field
     * @return string
     */
    protected function get_editor_type( $field ) {
        switch ( $field ) {
            case 'title':
            case 'meta':
                return 'LINE';
            case 'text':
                return 'VISUAL';
            case 'link':
                return 'LINK';
            default:
                return 'LINE';
        }
    }
}
