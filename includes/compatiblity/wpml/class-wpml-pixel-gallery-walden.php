<?php
namespace Pixel_Gallery\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WPML_Pixel_Gallery_Walden
 * Handles translation of repeater items in the Walden widget
 */
class WPML_Pixel_Gallery_Walden extends WPML_Module_With_Items {

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
            'follow',
            'like',
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
            case 'follow':
                return esc_html__( 'Follow', 'pixel-gallery' );
            case 'like':
                return esc_html__( 'Like', 'pixel-gallery' );
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
            case 'follow':
            case 'like':
                return 'LINE';
            case 'link':
                return 'LINK';
            default:
                return 'LINE';
        }
    }
}

