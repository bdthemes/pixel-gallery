<?php
namespace Pixel_Gallery\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WPML_Pixel_Gallery_Draggable
 * Handles translation of repeater items in the Draggable widget
 */
class WPML_Pixel_Gallery_Draggable extends WPML_Module_With_Items {

    /**
     * @return string
     */
    public function get_items_field() {
        return 'draggable_gallery_list';
    }

    /**
     * @return array
     */
    public function get_fields() {
        return array(
            'list_title',
            'list_text',
            'list_url' => ['url'],
        );
    }

    /**
     * @param string $field
     * @return string
     */
    protected function get_title( $field ) {
        switch ( $field ) {
            case 'list_title':
                return esc_html__( 'Title', 'pixel-gallery' );
            case 'list_text':
                return esc_html__( 'Text', 'pixel-gallery' );
            case 'list_url':
                return esc_html__( 'Link', 'pixel-gallery' );
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
            case 'list_title':
                return 'LINE';
            case 'list_text':
                return 'AREA';
            case 'list_url':
                return 'LINK';
            default:
                return 'LINE';
        }
    }
}
